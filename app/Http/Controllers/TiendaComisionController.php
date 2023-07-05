<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Directivo;
use App\Models\DirectivoComisionTienda;
use App\Models\DirectivoComisionTiendaDetalle;
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
            $oldComision = DirectivoComisionTienda::where('venta_id',$request->ventaId)->get(); 
            $oldFechaComisiones = Carbon::now()->format('Y-m-d H:i:m');
            if(count($oldComision) > 0){
                $oldFechaComisiones = $oldComision[0]->created_at;
            }

            DirectivoComisionTienda::where('venta_id',$request->ventaId)->delete(); 

            $pagos = TiendaVentaPago::where('venta_id',$request->ventaId)->where('comision_creada',1)->whereHas('tipoPago', function ($query) {
                $query
                    ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
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
                ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
        })->get();
        
        if(count($pagos) === 0){
            return;
        }

        DB::beginTransaction();
        try{
            $this->processComisionDirectivo($venta,$pagos,$fechaComisiones);
            // $this->setComisionCerrador($venta,$pagos,$fechaComisiones);
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
                $this->setComisionesReservacionDirectivo($pagos,$venta,$directivoId,$comision,$fechaComisiones);   
            }
        }

        return true;
    }

    private function setComisionesReservacionDirectivo($pagos,$venta,$directivoId,$comision,$fechaComisiones)
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
                    'estatus'           => $comision->estatus
                ];
            }

            return json_encode(['data' => $comisionesArray]);
        //}
    }
}
