<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\Comisionista;
use App\Models\Pago;
use App\Models\Reservacion;
use App\Classes\CustomErrorHandler;
use App\Models\ActividadComisionDetalle;
use App\Models\ComisionistaActividadDetalle;
use App\Models\Directivo;
use App\Models\DirectivoComisionReservacion;
use App\Models\DirectivoComisionReservacionActividadDetalle;
use App\Models\DirectivoComisionReservacionCanalDetalle;
use App\Models\ReservacionDetalle;
use Illuminate\Http\Request;
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
            $oldComision = Comision::where('reservacion_id',$request->reservacionId)->get(); 
            $oldFechaComisiones = Carbon::now()->format('Y-m-d H:i:m');
            if(count($oldComision) > 0){
                $oldFechaComisiones = $oldComision[0]->created_at;
            }
            Comision::where('reservacion_id',$request->reservacionId)->delete();
            DirectivoComisionReservacion::where('reservacion_id',$request->reservacionId)->delete();

            $pagos = Pago::where('reservacion_id',$request->reservacionId)->where('comision_creada',1)->whereHas('tipoPago', function ($query) {
                $query
                    ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
            })->get();

            $this->setComisionPago($pagos,0);
            $this->setComisionesReservacion($request->reservacionId,$oldFechaComisiones);
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

    public function setComisionesReservacion($reservacionId,$fechaComisiones){
        $reservacion  = Reservacion::find($reservacionId);
        $pagos        = Pago::where('reservacion_id',$reservacion['id'])->where('comision_creada',0)->whereHas('tipoPago', function ($query) {
            $query
                ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
        })->get();
        
        if(count($pagos) === 0){
            return;
        }

        DB::beginTransaction();
        try{
            $this->setComisionReservacionComisionista($reservacion,$pagos,$fechaComisiones);
            $this->setComisionReservacionCerrador($reservacion,$pagos,$fechaComisiones);
            $this->setComisionReservacionComisionistaActividad($reservacion,$pagos,$fechaComisiones);
            $this->processComisionReservacionDirectivo($reservacion,$pagos,$fechaComisiones);
            
            $comisiones = Comision::where('reservacion_id',$reservacion['id'])->get();
            
            if(count($comisiones) > 0){
                $this->setComisionPago($pagos,1);
            }
            DB::commit();
        } catch (\Exception $e){
            throw $e;
            DB::rollBack();
        }
    }

    private function setComisionPago($pagos,$comisionCreada){
        foreach($pagos as $pago){
            $pago              = Pago::find($pago['id']);
            $pago->comision_creada  = $comisionCreada;
            $pago->save();
        }
    }

    private function setComisionReservacionComisionista($reservacion,$pagos,$fechaComisiones){
        if($reservacion['comisionista_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['comisionista_id']);

        return $this->setAllComisiones($pagos,$reservacion,$reservacion['comisionista_id'],$comisionista,$fechaComisiones);
    }

    private function setComisionReservacionComisionistaActividad($reservacion,$pagos,$fechaComisiones){
        if($reservacion['comisionista_actividad_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['comisionista_actividad_id']);

        return $this->setComisionesActividad($pagos,$reservacion,$reservacion['comisionista_actividad_id'],$comisionista,$fechaComisiones);
    }

    private function setComisionesActividad($pagos,$reservacion,$comisionistaId,$comisionista,$fechaComisiones){
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
            'created_at'              =>  $fechaComisiones,
            'estatus'                 =>  1
        ]);

        return true;
    }

    private function setComisionReservacionCerrador($reservacion,$pagos,$fechaComisiones){
        if($reservacion['cerrador_id'] == 0){
            return true;
        }

        $comisionista = Comisionista::find($reservacion['cerrador_id']);

        return $this->setAllComisiones($pagos,$reservacion,$reservacion['cerrador_id'],$comisionista,$fechaComisiones);
    }

    private function processComisionReservacionDirectivo($reservacion,$pagos,$fechaComisiones){
        if($reservacion['comisionista_id'] == 0){
            return true;
        }

        $directivos = Directivo::where('estatus',1)->get();

        if(count($directivos) < 1){
            return true;
        }

        //canal de venta sobre el cual se tomara la comision al comisionista de canales
        $canalVentaId = Comisionista::find($reservacion['comisionista_id'])['canal_venta_id'];
        foreach($directivos as $directivo){
            $directivoId = $directivo['id'];
            $comisiones = DirectivoComisionReservacionCanalDetalle::where('directivo_id',$directivoId)->where('canal_venta_id',$canalVentaId)->get();
            foreach($comisiones as $comision){
                // Comisiones seran calculadas segun el tipo de cambio de venta 
                $this->setComisionesReservacionDirectivo($pagos,$reservacion,$directivoId,$comision,$fechaComisiones);
            }
        }

        return true;
    }

    private function setComisionesReservacionDirectivo($pagos,$reservacion,$directivoId,$comision,$fechaComisiones){
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

        //Parche para aceptar comisiones especiales
        if($reservacion['comisiones_especiales']){
            return is_numeric($this->createDirectivoComisionesReservacionEspecial($reservacion,$totalPagoReservacion,$directivoId,$fechaComisiones));
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comision['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comision['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comision['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comision['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = DirectivoComisionReservacion::where('directivo_id',$directivoId)
                                        ->where('reservacion_id',$reservacion['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = DirectivoComisionReservacion::create([   
            'directivo_id'            =>  $directivoId,
            'reservacion_id'          =>  $reservacion['id'],
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

    private function createDirectivoComisionesReservacionEspecial($reservacion,$totalPagoReservacion,$directivoId,$fechaComisiones){
        $comisionista = Directivo::find($directivoId);
        //no se permite tener mas de una actividad en reservaciones de comisiones especiales
        foreach($reservacion->actividad as $actividad){
            $actividadComisionDetalle = DirectivoComisionReservacionActividadDetalle::where('actividad_id',$actividad['id']);
            
            if(count($actividadComisionDetalle->get()) > 0){
                $actividadComisionDetalle = $actividadComisionDetalle->first();
                //solo existe una comision por cada actividad y comisionista (canal_venta_id)
                $totalVentaSinIva          = round($totalPagoReservacion,2); 
                $ivaCantidad               = round(0,2);// no llevan IVA
                $cantidadComisionBruta     = round((($totalVentaSinIva * $actividadComisionDetalle->comision) / 100),2);
                $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $actividadComisionDetalle->descuento_impuesto) / 100),2);
                $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);
                
                $comsion = DirectivoComisionReservacion::create([   
                    'directivo_id'            =>  $comisionista->id,
                    'reservacion_id'          =>  $reservacion['id'],
                    'pago_total'              =>  $totalPagoReservacion,
                    'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
                    'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
                    'iva'                     =>  (float)$ivaCantidad,
                    'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
                    'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
                    'created_at'              =>  $fechaComisiones,
                    'estatus'                 =>  1
                ]);

                return $comsion['id'];
            }
        }
        return false;
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

    // parametro $comisionista puede contener comisionista o comisiones de ComisionistaCanalDetalle en setComisionComisionistaCanal
    private function setAllComisiones($pagos,$reservacion,$comisionistaId,$comisionista,$fechaComisiones){
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

        //Parche para aceptar comisiones especiales
        if($reservacion['comisiones_especiales']){
            return is_numeric($this->createComisionEspecial($reservacion,$totalPagoReservacion,$comisionistaId,$fechaComisiones));
        }

        $totalVentaSinIva          = round(($totalPagoReservacion / (1+($comisionista['iva']/100))),2);
        $ivaCantidad               = round(($totalVentaSinIva * ($comisionista['iva']/100)),2);
        $cantidadComisionBruta     = round((($totalVentaSinIva * $comisionista['comision']) / 100),2);
        $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $comisionista['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);

        $isComisionDuplicada = Comision::where('comisionista_id',$comisionistaId)
                                        ->where('reservacion_id',$reservacion['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = Comision::create([   
            'comisionista_id'         =>  $comisionistaId,
            'reservacion_id'          =>  $reservacion['id'],
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

    private function createComisionEspecial($reservacion,$totalPagoReservacion,$comisionistaId,$fechaComisiones){
        $comisionista = Comisionista::find($comisionistaId);
        //no se permite tener mas de una actividad en reservaciones de comisiones especiales
        foreach($reservacion->actividad as $actividad){
            $actividadComisionDetalle = ActividadComisionDetalle::where('actividad_id',$actividad['id'])->where('canal_venta_id',$comisionista->tipo->id)->get();
            
            if(count($actividadComisionDetalle) > 0){
                //solo existe una comision por cada actividad y comisionista (canal_venta_id)
                $totalVentaSinIva          = round($totalPagoReservacion,2); 
                $ivaCantidad               = round(0,2);// no llevan IVA
                $cantidadComisionBruta     = round((($totalVentaSinIva * $actividadComisionDetalle[0]->comision) / 100),2);
                $descuentoImpuestoCantidad = round((($cantidadComisionBruta * $actividadComisionDetalle[0]->descuento_impuesto) / 100),2);
                $cantidadComisionNeta      = round(($cantidadComisionBruta - $descuentoImpuestoCantidad),2);
                
                $comsion = Comision::create([   
                    'comisionista_id'         =>  $comisionista->id,
                    'reservacion_id'          =>  $reservacion['id'],
                    'pago_total'              =>  $totalPagoReservacion,
                    'pago_total_sin_iva'      =>  (float)$totalVentaSinIva,
                    'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
                    'iva'                     =>  (float)$ivaCantidad,
                    'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
                    'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
                    'created_at'              =>  $fechaComisiones,
                    'estatus'                 =>  1
                ]);

                return $comsion['id'];
            }
        }
        return false;
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
            
            //Agrega comisiones directivo
            $comisiones      = DirectivoComisionReservacion::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
                $query
                    ->where("estatus", 1)
                    ->where("comisionable", 1);
            })->orderBy('id','desc')->get();

            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->directivo->nombre,
                    'tipo'              => 'DIRECTIVO',
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
