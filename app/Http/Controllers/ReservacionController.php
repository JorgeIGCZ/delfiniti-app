<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ReservacionDetalle;
use App\Models\Comisionista;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Factura;
use App\Models\Localizacion;
use App\Models\Pago;
use App\Models\Reservacion;
use Illuminate\Notifications\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Classes\CustomErrorHandler;
use App\Models\CodigoAutorizacionPeticion;
use App\Models\CodigoDescuento;
use Hamcrest\Type\IsNumeric;

class ReservacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reservacion.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $estados        = Estado::all();
        $localizaciones = Localizacion::all();
        $actividades    = Actividad::whereRaw('NOW() >= fecha_inicial')
                            ->whereRaw('NOW() <= fecha_final')
                            ->orWhere('duracion','indefinido')
                            ->get();
        $comisionistas   = Comisionista::all();
        
        return view('reservacion.create',['estados' => $estados,'actividades' => $actividades,'localizaciones' => $localizaciones,'comisionistas' => $comisionistas]);
    }
    public function getPeticionAutorizacionCodigo(Request $request){
        $codigoDescuentoId    = $this->getCodigoDescuentoId($request->codigoDescuento);
        if($codigoDescuentoId !== 0 ){
            try{
                $peticionAutorizacionCodigo = $this->getPeticionAutorizacionCodigoArray($codigoDescuentoId,$request);
                if($peticionAutorizacionCodigo !== null){
                    if($peticionAutorizacionCodigo->estatus == 1){
                        $descuento = $this->getDescuento($peticionAutorizacionCodigo->codigo_descuento_id);
                        return json_encode(['result' => "Success",'status' => 'authorized','descuento' => $descuento]);
                    }
                }else{
                    $codigoAutorizacionPeticion = CodigoAutorizacionPeticion::create([
                        'codigo_descuento_id' =>  $codigoDescuentoId,
                        'nombre_cliente'      =>  $request->nombre,
                        'fecha_peticion'      =>  date('Y-m-d')
                    ]);
                    if(is_numeric($codigoAutorizacionPeticion['id'])){
                        return json_encode(['result' => "Success",'status' => 'created','descuento' => []]);
                    }else{
                        return json_encode(['result' => "Error"]);
                    }
                }
                return json_encode(['result' => "Success",'status' => 'waiting','descuento' => []]);
            } catch (\Exception $e){
                $CustomErrorHandler = new CustomErrorHandler();
                $CustomErrorHandler->saveError($e->getMessage(),$request);
                return json_encode(['result' => "Error"]);
            }
        }
        return json_encode(['result' => "Error"]);
    }
    private function getDescuento($codigoDescuentoId){
        $descuento = CodigoDescuento::find($codigoDescuentoId);  
        return $descuento;
    }
    private function getPeticionAutorizacionCodigoArray($codigoDescuentoId,$request){
        $codigoDescuento = CodigoAutorizacionPeticion::where('codigo_descuento_id',$codigoDescuentoId)
                                                        ->where('nombre_cliente',$request->nombre)
                                                        ->where('fecha_peticion',date('Y-m-d'))
                                                        ->first();  
        return $codigoDescuento;
    }
    private function getCodigoDescuentoId($nombre){
        $codigoDescuento = CodigoDescuento::where('nombre',$nombre)->first();
        return ($codigoDescuento !== null) ? $codigoDescuento->id : 0;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $pagado  = ((float)$request->cupon + (float)$request->efectioUsd + (float)$request->efectivo + (float)$request->tarjeta);
        $adeudo  = ((float)$request->total - (float)$pagado);
        $estatus = ($request->estatus == "reservar");
        DB::beginTransaction();
        try{
            $reservacion = Reservacion::create([
                'nombre_cliente'  => $request->nombre,
                'email'           => $request->email,
                'localizacion'    => $request->localizacion,
                'origen'          => $request->origen,
                'agente_id'       => $request->agente,
                'comisionista_id' => $request->comisionista,
                'comentarios'     => $request->comentarios,
                'estatus'         => $estatus,
                'fecha_creacion'  => date('Y-m-d')
            ]);
            $factura = Factura::create([
                'reservacion_id' =>  $reservacion['id'],
                'total'          =>  $request->total,
                'pagado'         =>  $pagado,
                'adeudo'         =>  $adeudo
            ]);
            foreach($request->reservacionArticulos as $reservacionArticulo){
                ReservacionDetalle::create([
                    'reservacion_id'       =>  $reservacion['id'],
                    'factura_id'           =>  $factura['id'],
                    'actividad_id'         =>  $reservacionArticulo['actividad'],
                    'actividad_horario_id' =>  $reservacionArticulo['horario'],
                    'actividad_fecha'      =>  $reservacionArticulo['fecha'],
                    'numero_personas'      =>  $reservacionArticulo['cantidad']
                ]); 
            }
            $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"efectivo");
            $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"efectioUsd");
            $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"tarjeta");
            $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"cupon");
            DB::commit();
            return json_encode(['result' => "Success"]);
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => "Error"]);
        }
    }

    private function setFaturaPago($reservacionId,$facturaId,$request,$tipoPago){
        $tipoPagoId = $this->getTipoPagoId($tipoPago);
        $result     = true;
        if((float)$request[$tipoPago]>0){
            $pago = Pago::create([
                'reservacion_id' =>  $reservacionId,
                'factura_id'     =>  $facturaId,
                'cantidad'       =>  $request[$tipoPago],
                'tipo_pago_id'   =>  $tipoPagoId
            ]);
            $result = is_numeric($pago['id']);
        }
        return $result;
    }

    private function getTipoPagoId($tipoPago){
        //$tipoPagoId = tipoPago::where('nombre',$tipoPago);
        return 1;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {   
        return view('reservacion.show');
    }

    public function get($id = null)
    {   
        $reservacionesDetalle = ReservacionDetalle::all();

        $reservacionDetalleArray = [];
            foreach ($reservacionesDetalle as $reservacionDetalle) {
                $reservacionDetalleArray[] = [
                    'id'            => @$reservacionDetalle->id,
                    'reservacionId' => @$reservacionDetalle->reservacion->id,
                    'actividad'     => @$reservacionDetalle->actividad->nombre,
                    'horario'       => @$reservacionDetalle->horario->horario_inicial,
                    'fecha'         => @$reservacionDetalle->actividad_fecha,
                    'cliente'       => @$reservacionDetalle->reservacion->nombre_cliente,
                    'personas'      => @$reservacionDetalle->numero_personas,
                    'notas'         => @$reservacionDetalle->reservacion->comentarios
                ];
            }
        return json_encode(['data' => $reservacionDetalleArray]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        /*
        if($id > 0){
            $promotores = Promotor::where('id', $id)->first();
            return view('promotores.edit',['promotor' => $promotores]);
        }
        */
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /*
        $promotor           = promotor::find($id);
        $promotor->codigo   = $request->codigo;
        $promotor->nombre   = $request->nombre;
        $promotor->comision = $request->comision;
        $promotor->iva      = $request->iva;
        $promotor->save();

        return redirect()->route("promotores")->with(["result" => "Promotor actualizado",]);
        */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /*
        $result = promotor::destroy($id);
        return json_encode(['result' => ($result) ? "Promotor eliminado" : "Error"]);
        */
    }
}
