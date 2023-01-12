<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\Comisionista;
use App\Models\Pago;
use App\Models\Reservacion;
use finfo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\Cerrador;
use App\Models\ComisionistaActividadDetalle;
use App\Models\ComisionistaCanalDetalle;
use App\Models\ReservacionDetalle;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComisionController extends Controller
{
    public function __construct() {
        $this->middleware('permission:Comisiones.index')->only('index');
        //$this->middleware('permission:Comisiones.create')->only('create'); 
        $this->middleware('permission:Comisiones.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comisiones.index');
    }

    public function recalculateComisiones(Request $request){
        try {
            Comision::where('reservacion_id',$request->reservacionId)->delete();

            $pagos        = Pago::where('reservacion_id',$request->reservacionId)->where('comision_creada',1)->whereHas('tipoPago', function ($query) {
                $query
                    ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
            })->get();

            $this->setComisionPago($pagos,0);
            $this->setComisiones($request->reservacionId);
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

    public function setComisiones($reservacionId){
        $reservacion  = Reservacion::find($reservacionId);
        $pagos        = Pago::where('reservacion_id',$reservacion['id'])->where('comision_creada',0)->whereHas('tipoPago', function ($query) {
            $query
                ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
        })->get();
        
        // if(!$reservacion['comisionable']){
        //     return;
        // }

        DB::beginTransaction();
        try{
            $this->setComisionComisionista($reservacion,$pagos);
            $this->setComisionCerrador($reservacion,$pagos);
            $this->setComisionComisionistaCanal($reservacion,$pagos);
            $this->setComisionComisionistaActividad($reservacion,$pagos);
            $this->setComisionPago($pagos,1);
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$reservacion);
        }
    }

    private function setComisionPago($pagos,$comisionCreada){
        foreach($pagos as $pago){
            $pago              = Pago::find($pago['id']);
            $pago->comision_creada  = $comisionCreada;
            $pago->save();
        }
    }

    private function setComisionComisionista($reservacion,$pagos){
        if($reservacion['comisionista_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['comisionista_id']);

        return $this->setAllComisiones($pagos,$reservacion,$reservacion['comisionista_id'],$comisionista);
    }

    private function setComisionComisionistaActividad($reservacion,$pagos){
        if($reservacion['comisionista_actividad_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['comisionista_actividad_id']);

        return $this->setComisionesActividad($pagos,$reservacion,$reservacion['comisionista_actividad_id'],$comisionista);
    }

    private function setComisionesActividad($pagos,$reservacion,$comisionistaId,$comisionista){
        $cantidadComisionBruta = 0;
        $descuentoImpuesto = 0;
        $totalPagoReservacion = 0;
        $numeroActividadesComisionables = 0;

        foreach($pagos as $pago){
            $totalPagoReservacion += $pago['cantidad'];
        }
        
        $reservacionDetalles = ReservacionDetalle::where('reservacion_id',$reservacion->id)->get();

        foreach($reservacionDetalles as $reservacionDetalle){
            
            $actividadId = $reservacionDetalle->actividad_id;

            //the cost is minimun to make this query for each activity
            $comisiones = ComisionistaActividadDetalle::where('actividad_id',$actividadId)
                                                    ->where('comisionista_id',$comisionistaId)->get();
            if(count($comisiones) > 0){
                for ($i=0; $i < $reservacionDetalle->numero_personas; $i++) { 
                    $cantidadComisionBruta += $comisiones[0]->comision;
                    $descuentoImpuesto += $comisiones[0]->descuento_impuesto;
                    $numeroActividadesComisionables += 1;
                }
            }
        }

        $descuentoImpuesto = ($descuentoImpuesto/$numeroActividadesComisionables);
        
        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comisionista['iva']/100))),2);
        $ivaCantidad               = round(0,2);
        $cantidadComisionBruta     = round($cantidadComisionBruta,2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $descuentoImpuesto) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = Comision::where('comisionista_id',$comisionistaId)
                                        ->where('reservacion_id',$reservacion['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        Comision::create([   
            'comisionista_id'         =>  $comisionistaId,
            'reservacion_id'          =>  $reservacion['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'estatus'                 =>  1
        ]);

        return true;
    }

    private function setComisionCerrador($reservacion,$pagos){
        if($reservacion['cerrador_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['cerrador_id']);

        return $this->setAllComisiones($pagos,$reservacion,$reservacion['cerrador_id'],$comisionista);
    }

    private function setComisionComisionistaCanal($reservacion,$pagos){
        if($reservacion['comisionista_id'] == 0){
            return true;
        }

        $comisionistasCanales = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query->where('comisionista_canal',1);
        })->get();

        if(count($comisionistasCanales) < 1){
            return true;
        }

        $canalVentaId = Comisionista::find($reservacion['comisionista_id'])['canal_venta_id'];
        
        foreach($comisionistasCanales as $comisionistaCanales){
            $commisionistaId = $comisionistaCanales['id'];

            $comisiones = ComisionistaCanalDetalle::where('comisionista_id',$commisionistaId)
            ->where('canal_venta_id',$canalVentaId)->get();

            foreach($comisiones as $comision){
                // Comisiones seran calculadas segun el tipo de cambio de venta 
                $this->setAllComisiones($pagos,$reservacion,$commisionistaId,$comision);
            }
        }

        return true;
    }

    private function getCantidadPagoActividadNoComisionable($totalPago,$reservacion){
        $sumatoriaActividad = 0;
        $actividadesNoComisionables = [];
        $actividadesCantidad = [];

        foreach($reservacion->actividad as $actividad){
            $sumatoriaActividad += $actividad['precio'];
            //verificar si debe haber otra validacion para permitir actividades comisionables en las ordenes no comisionables 
            if(!$actividad['comisionable']){
                $actividadesNoComisionables[] = $actividad['id'];
            }
        }

        $calculoPorcentaje = (100/$sumatoriaActividad);

        foreach($reservacion->actividad as $actividad){
            foreach($actividadesNoComisionables as $actividadNoComisionable){
                if($actividadNoComisionable == $actividad['id']){
                    $porcentajeActividad = ($calculoPorcentaje*$actividad['precio']);
                    $actividadesCantidad[$actividad['id']] = ($totalPago*$porcentajeActividad)/100;
                }
            }
        }
        return $actividadesCantidad;
    }

    private function setAllComisiones($pagos,$reservacion,$comisionistaId,$comisionista){
        $totalPagoReservacion = 0;
        foreach($pagos as $pago){
            //verifica si el tipo de pago es en USD
            if($pago['tipo_pago_id'] == 2){
                $totalPagoReservacion += ($pago['cantidad'] * $pago['tipo_cambio_usd']);
                continue;
            }
            $totalPagoReservacion += $pago['cantidad'];
        }

        $cantidadesNoComisionables = $this->getCantidadPagoActividadNoComisionable($totalPagoReservacion,$reservacion);
        foreach($cantidadesNoComisionables as $cantidadNoComisionable){
            $totalPagoReservacion = ($totalPagoReservacion - $cantidadNoComisionable);
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comisionista['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comisionista['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comisionista['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comisionista['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        // $isComisionDuplicada = Comision::where('comisionista_id',$comisionistaId)
        //                                 ->where('reservacion_id',$reservacion['id'])->get()->count();
        
        // if($isComisionDuplicada){
        //     return false;
        // }

        $comsion = Comision::create([   
            'comisionista_id'         =>  $comisionistaId,
            'reservacion_id'          =>  $reservacion['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
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
     * @param  \Illuminate\Http\Request  $request
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

            $comisiones      = Comision::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
                $query
                    ->where("estatus", 1)
                    ->where("comisionable", 1);
            })->orderBy('id','desc')->get();

            $comisionesArray = [];
            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->comisionista->nombre,
                    'tipo'              => $comision->comisionista->tipo->nombre,
                    'reservacion'       => $comision->reservacion->folio,
                    'reservacionId'     => $comision->reservacion->id,
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function edit(Comision $comision)
    {
        return view('comisiones.edit',['comision' => $comision]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $comision                         = Comision::find($id);
            $comision->iva                    = $request->iva;
            $comision->descuento_impuesto     = $request->descuento_impuesto;
            $comision->cantidad_comision_neta = $request->cantidad_comision_neta;
            $comision->created_at             = $request->fecha_registro_comision;
            $comision->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("comisiones.index")->with(["result" => "Comision actualizada"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comision $comision)
    {
        //
    }
}
