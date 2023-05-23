<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\FotoVideoComision;
use App\Models\FotoVideoComisionista;
use App\Models\FotoVideoVenta;
use App\Models\FotoVideoVentaPago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FotoVideoComisionController extends Controller
{

    public function __construct() {
        $this->middleware('permission:FotoVideoComisiones.index')->only('index');
        $this->middleware('permission:FotoVideoComisiones.update')->only('edit'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        return view('fotovideocomisiones.index'); 
    }

    public function recalculateComisiones(Request $request){
        try {
            $oldComision = FotoVideoComision::where('venta_id',$request->ventaId)->get(); 
            $oldFechaComisiones = Carbon::now()->format('Y-m-d H:i:m');
            if(count($oldComision) > 0){
                $oldFechaComisiones = $oldComision[0]->created_at;
            }

            FotoVideoComision::where('venta_id',$request->ventaId)->delete(); 

            $pagos = FotoVideoVentaPago::where('venta_id',$request->ventaId)->where('comision_creada',1)->whereHas('tipoPago', function ($query) {
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

    private function setComisionPago($pagos,$comisionCreada){
        foreach($pagos as $pago){
            $pago              = FotoVideoVentaPago::find($pago['id']);
            $pago->comision_creada  = $comisionCreada;
            $pago->save();
        }
    }

    public function setComisiones($ventaId,$fechaComisiones){
        $venta  = FotoVideoVenta::find($ventaId);
        $pagos  = FotoVideoVentaPago::where('venta_id',$venta['id'])->where('comision_creada',0)->whereHas('tipoPago', function ($query) {
            $query
                ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta','deposito')");
        })->get();
        
        if(count($pagos) === 0){
            return;
        }

        DB::beginTransaction();
        try{
            $this->setComisionComisionista($venta,$pagos,$fechaComisiones);
            // $this->setComisionCerrador($venta,$pagos,$fechaComisiones);
            // $this->setComisionComisionistaCanal($venta,$pagos,$fechaComisiones);
            // $this->setComisionComisionistaActividad($venta,$pagos,$fechaComisiones);
            
            $comisiones = FotoVideoComision::where('venta_id',$venta['id'])->get();
            
            if(count($comisiones) > 0){
                $this->setComisionPago($pagos,1);
            }
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$venta);
        }
    }

    private function setComisionComisionista($venta,$pagos,$fechaComisiones){
        if($venta['comisionista_id'] == 0){
            return true;
        }

        $comisionista = FotoVideoComisionista::find($venta['comisionista_id']);

        return $this->setAllComisiones($pagos,$venta,$venta['comisionista_id'],$comisionista,$fechaComisiones);
    }

    private function setAllComisiones($pagos,$venta,$comisionistaId,$comisionista,$fechaComisiones){
        $totalPagoReservacion = 0;
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

        $isComisionDuplicada = FotoVideoComision::where('comisionista_id',$comisionistaId)
                                        ->where('venta_id',$venta['id'])->get()->count();
        
        if($isComisionDuplicada){
            return false;
        }

        $comsion = FotoVideoComision::create([   
            'comisionista_id'  =>  $comisionistaId,
            'venta_id'         =>  $venta['id'],
            'pago_total'                  =>  $totalPagoReservacion,
            'pago_total_sin_iva'          =>  (float)$totalVentaSinIva,
            'cantidad_comision_bruta'     =>  (float)$cantidadComisionBruta,
            'iva'                         =>  (float)$ivaCantidad,
            'descuento_impuesto'          =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'      =>  (float)$cantidadComisionNeta,
            'created_at'                  =>  $fechaComisiones,
            'estatus'                     =>  1
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
     * @param  \App\Models\FotoVideoComision  $fotoVideoComision
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

            $comisiones      = FotoVideoComision::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
                $query
                    ->where("estatus", 1);
            })->orderBy('id','desc')->get();

            $comisionesArray = [];
            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->comisionista->nombre,
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FotoVideoComision  $fotoVideoComision
     * @return \Illuminate\Http\Response
     */
    public function edit(FotoVideoComision $fotovideocomision)
    {
        return view('fotovideocomisiones.edit',['comision' => $fotovideocomision]);
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
            $comision                         = FotoVideoComision::find($id);
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

        return redirect()->route("fotovideocomisiones.index")->with(["result" => "Comision actualizada"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FotoVideoComision  $fotoVideoComision
     * @return \Illuminate\Http\Response
     */
    public function destroy(FotoVideoComision $fotoVideoComision)
    {
        //
    }
}
