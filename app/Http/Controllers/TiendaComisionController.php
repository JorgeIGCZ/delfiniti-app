<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Directivo;
use App\Models\DirectivoComisionTienda;
use App\Models\DirectivoComisionTiendaDetalle;
use App\Models\Supervisor;
use App\Models\SupervisorComisionTienda;
use App\Models\SupervisorComisionTiendaDetalle;
use App\Models\TiendaComision;
use App\Models\TiendaComisionista;
use App\Models\TiendaVenta;
use App\Models\TiendaVentaPago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaComisionController extends Controller
{

    public function __construct() {
        $this->middleware('permission:TiendaComisiones.index')->only('index');
        $this->middleware('permission:TiendaComisiones.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tiendacomisiones.index'); 
    }

    public function recalculateComisiones(Request $request)
    {
        try {
            $oldComision = DirectivoComisionTienda::where('venta_id',$request->ventaId)->first(); 
            if(isset($oldComision->created_at)){
                $oldFechaComisiones = $oldComision->created_at;
            }else{
                $venta = TiendaVenta::find($request->ventaId);
                $oldFechaComisiones = $venta->created_at;
            }

            DirectivoComisionTienda::where('venta_id',$request->ventaId)->delete();
            TiendaComision::where('venta_id',$request->ventaId)->delete();
            SupervisorComisionTienda::where('venta_id',$request->ventaId)->delete();

            $pagos = TiendaVentaPago::where('venta_id',$request->ventaId)->where('comision_creada',1)->whereHas('tipoPago', function ($query) {
                $query
                    ->whereRaw(" nombre IN ('efectivo', 'efectivoUsd', 'tarjeta', 'deposito', 'cambio')");
            })->get();

            $this->setComisionPago($pagos,0);

            $this->setComisiones($request->ventaId,$oldFechaComisiones);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return json_encode(
            [
                'result' => 'Success'
            ]
        );
    }

    private function setComisionPago($pagos,$comisionCreada)
    {
        foreach($pagos as $pago){
            $pago              = TiendaVentaPago::find($pago['id']);
            $pago->comision_creada  = $comisionCreada;
            $pago->save();
        }
    }

    public function setComisiones($ventaId,$fechaComisiones)
    {
        $venta  = TiendaVenta::find($ventaId);
        $pagos  = TiendaVentaPago::where('venta_id',$venta['id'])->where('comision_creada',0)->whereHas('tipoPago', function ($query) {
            $query
                ->whereRaw(" nombre IN ('efectivo', 'efectivoUsd', 'tarjeta', 'deposito', 'cambio')");
        })->get();
        
        if(count($pagos) === 0){
            return;
        }

        DB::beginTransaction();
        try{
            $this->processComisionDirectivo($venta,$pagos,$fechaComisiones);
            $this->processComisionSupervisor($venta,$pagos,$fechaComisiones);
            $this->setComisionVentaMostrador($venta,$pagos,$fechaComisiones);
            // $this->setComisionComisionistaCanal($venta,$pagos,$fechaComisiones);
            // $this->setComisionComisionistaActividad($venta,$pagos,$fechaComisiones);
            
            $comisiones = DirectivoComisionTienda::where('venta_id',$venta['id'])->get();
            
            if(count($comisiones) > 0){
                $this->setComisionPago($pagos,1);
            }
            DB::commit();
        } catch (\Exception $e){
            throw $e;
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$venta);
        }
    }

    private function setComisionVentaMostrador($venta,$pagos,$fechaComisiones){
        if($venta['usuario_id'] == 0){
            return true;
        }

        $comisionista = TiendaComisionista::where('usuario_id', $venta['usuario_id'])->first();

        if(!isset($comisionista)){
            return;
        }
        return $this->setAllComisiones($pagos, $venta, $venta['usuario_id'], $comisionista, $fechaComisiones);
    }

    
    private function setAllComisiones($pagos, $venta, $comisionistaId, $comisionista, $fechaComisiones){
        $totalPagoReservacion = 0;

        if($comisionista['comision'] == 0){
            return false;
        }

        foreach($pagos as $pago){
            //verifica si el tipo de pago es en USD
            if($pago['tipo_pago_id'] == 2){
                $totalPagoReservacion += ($pago['cantidad'] * $pago['tipo_cambio_usd']);
                continue;
            }
            $totalPagoReservacion += $pago['cantidad'];
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comisionista['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comisionista['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comisionista['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comisionista['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = TiendaComision::where('comisionista_id',$comisionistaId)
                                        ->where('venta_id',$venta['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = TiendaComision::create([   
            'comisionista_id'         =>  $comisionistaId,
            'venta_id'                =>  $venta['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'created_at'              =>  $fechaComisiones,
            'estatus'                 =>  1
        ]);

        return is_numeric($comsion['id']);
    }


    private function processComisionDirectivo($venta,$pagos,$fechaComisiones)
    {
        $directivos = Directivo::where('estatus',1)->get();

        if(count($directivos) < 1){
            return true;
        }

        foreach($directivos as $directivo){
            $directivoId = $directivo['id'];
            $comision = DirectivoComisionTiendaDetalle::where('directivo_id',$directivoId)->first();
            
            if(isset($comision)){
                $validacionComision = ($comision['iva'] + $comision['comision'] + $comision['descuento_impuesto']);
                if($validacionComision > 0){
                    $this->setComisionesVentaDirectivo($pagos,$venta,$directivoId,$comision,$fechaComisiones);   
                }
            }
        }

        return true;
    }

    private function processComisionSupervisor($venta,$pagos,$fechaComisiones)
    {
        $supervisores = Supervisor::where('estatus',1)->get();

        if(count($supervisores) < 1){
            return true;
        }

        foreach($supervisores as $supervisor){
            $supervisorId = $supervisor['id'];
            $comision = SupervisorComisionTiendaDetalle::where('supervisor_id',$supervisorId)->first();
            
            if(isset($comision)){
                $validacionComision = ($comision['iva'] + $comision['comision'] + $comision['descuento_impuesto']);
                if($validacionComision > 0){
                    $this->setComisionesVentaSupervisor($pagos,$venta,$supervisorId,$comision,$fechaComisiones);   
                }
            }
        }

        return true;
    }

    private function setComisionesVentaDirectivo($pagos,$venta,$directivoId,$comision,$fechaComisiones)
    {
        $totalPagoReservacion = 0;
        foreach($pagos as $pago){
            //verifica si el tipo de pago es en USD
            if($pago['tipo_pago_id'] == 2){
                $totalPagoReservacion += ($pago['cantidad'] * $pago['tipo_cambio_usd']);
                continue;
            }
            $totalPagoReservacion += $pago['cantidad'];
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comision['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comision['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comision['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comision['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = DirectivoComisionTienda::where('directivo_id',$directivoId)
                                        ->where('venta_id',$venta['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = DirectivoComisionTienda::create([   
            'directivo_id'            =>  $directivoId,
            'venta_id'                =>  $venta['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'created_at'              =>  $fechaComisiones,
            'estatus'                 =>  1
        ]);

        return is_numeric($comsion['id']);
    }

    private function setComisionesVentaSupervisor($pagos,$venta,$supervisorId,$comision,$fechaComisiones)
    {
        $totalPagoReservacion = 0;
        foreach($pagos as $pago){
            //verifica si el tipo de pago es en USD
            if($pago['tipo_pago_id'] == 2){
                $totalPagoReservacion += ($pago['cantidad'] * $pago['tipo_cambio_usd']);
                continue;
            }
            $totalPagoReservacion += $pago['cantidad'];
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comision['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comision['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comision['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comision['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = SupervisorComisionTienda::where('supervisor_id',$supervisorId)
                                        ->where('venta_id',$venta['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = SupervisorComisionTienda::create([   
            'supervisor_id'            =>  $supervisorId,
            'venta_id'                =>  $venta['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'created_at'              =>  $fechaComisiones,
            'estatus'                 =>  1
        ]);

        return is_numeric($comsion['id']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TiendaComision  $tiendaComision
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    { 
        if(!is_null($request->fecha)){
            switch (@$request->fecha) {
                case 'dia':
                    $fechaInicio = Carbon::now()->startOfDay();
                    $fechaFinal  = Carbon::now()->endOfDay();
                    break;
                case 'mes':
                    $fechaInicio = Carbon::parse('first day of this month')->startOfDay();
                    $fechaFinal  = Carbon::parse('last day of this month')->endOfDay();
                    break;
                case 'custom':
                    $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
                    $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();
                    break;
            }   
        }

        $comisiones      = TiendaComision::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->orderBy('id','desc')->get();

        $comisionesArray = [];

        foreach ($comisiones as $comision) {
            // if($comision->venta->folio == "0000011-D"){
            //     dd($comision->usuario);
            // }
            $comisionesArray[] = [
                'id'                => $comision->id,
                'comisionista'      => $comision->usuario->name,
                'venta'             => $comision->venta->folio,
                'ventaId'           => $comision->venta->id,
                'total'             => $comision->pago_total,
                'comisionBruta'     => $comision->cantidad_comision_bruta,
                'iva'               => $comision->iva,
                'descuentoImpuesto' => $comision->descuento_impuesto,
                'comisionNeta'      => $comision->cantidad_comision_neta,
                'fecha'             => date_format($comision->created_at,'d-m-Y'),
                'estatus'           => $comision->estatus,
                'tipo'              => 'comisionista'
            ];
        }

        // if(is_null($comision)){
            //Agrega comisiones directivo
            $comisiones      = DirectivoComisionTienda::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
                $query
                    ->where("estatus", 1);
            })->orderBy('id','desc')->get();

            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->directivo->nombre,
                    'venta'             => $comision->venta->folio,
                    'ventaId'           => $comision->venta->id,
                    'total'             => $comision->pago_total,
                    'comisionBruta'     => $comision->cantidad_comision_bruta,
                    'iva'               => $comision->iva,
                    'descuentoImpuesto' => $comision->descuento_impuesto,
                    'comisionNeta'      => $comision->cantidad_comision_neta,
                    'fecha'             => date_format($comision->created_at,'d-m-Y'),
                    'estatus'           => $comision->estatus,
                    'tipo'              => 'directivo'
                ];
            }

            $comisiones      = SupervisorComisionTienda::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
                $query
                    ->where("estatus", 1);
            })->orderBy('id','desc')->get();

            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->supervisor->nombre,
                    'venta'             => $comision->venta->folio,
                    'ventaId'           => $comision->venta->id,
                    'total'             => $comision->pago_total,
                    'comisionBruta'     => $comision->cantidad_comision_bruta,
                    'iva'               => $comision->iva,
                    'descuentoImpuesto' => $comision->descuento_impuesto,
                    'comisionNeta'      => $comision->cantidad_comision_neta,
                    'fecha'             => date_format($comision->created_at,'d-m-Y'),
                    'estatus'           => $comision->estatus,
                    'tipo'              => 'directivo'
                ];
            }

            return json_encode(['data' => $comisionesArray]);
        //}
    }
}
