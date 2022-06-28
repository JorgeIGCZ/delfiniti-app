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
use App\Models\Alojamiento;
use App\Models\Pago;
use App\Models\Reservacion;
use Illuminate\Notifications\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Classes\CustomErrorHandler;
use App\Models\CodigoAutorizacionPeticion;
use App\Models\CodigoDescuento;
use App\Models\TipoCambio;
use App\Models\User;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\Auth;

class ReservacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reservaciones.index');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $estados        = Estado::all();
        $alojamientos   = Alojamiento::all();
        $actividades    = Actividad::whereRaw('NOW() >= fecha_inicial')
                            ->whereRaw('NOW() <= fecha_final')
                            ->orWhere('duracion','indefinido')
                            ->get();
        $comisionistas   = Comisionista::all();
        $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();
        
        return view('reservaciones.create',['estados' => $estados,'actividades' => $actividades,'alojamientos' => $alojamientos,'comisionistas' => $comisionistas,'dolarPrecioCompra' => $dolarPrecioCompra]);
    }
    public function getDescuentoPersonalizadoValidacion(Request $request){
        try{
            if($this->verifyUserAuth($request)){
                $limite = $this->getLimitesDescuentoPersonalizado($request);
                return json_encode(['result' => "Success",'status' => 'authorized','limite' => $limite]);
            }else{
                return json_encode(['result' => "Error"]);
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => "Error"]);
        }
    }
    private function getLimitesDescuentoPersonalizado($request){
        $descuento = User::where('email', $request->email)->first();
        return $descuento->limite_descuento;
    }
    public function getCodigoDescuento(Request $request){
        $codigoDescuento = $request->codigoDescuento;
        if($codigoDescuento !== ""){
            try{
                if($this->verifyUserAuth($request)){
                    $descuento = $this->getDescuento($codigoDescuento);
                    return json_encode(['result' => "Success",'status' => 'authorized','descuento' => $descuento]);
                }else{
                    return json_encode(['result' => "Error"]);
                }
            } catch (\Exception $e){
                $CustomErrorHandler = new CustomErrorHandler();
                $CustomErrorHandler->saveError($e->getMessage(),$request);
                return json_encode(['result' => "Error"]);
            }
        }
        return json_encode(['result' => "Error"]);
    }
    private function getDescuento($codigoDescuento){
        $descuento = CodigoDescuento::where('nombre', $codigoDescuento)->first();
        return $descuento;
    }
    private function verifyUserAuth($request){
        if(Auth::attempt(['email'=>$request->email,'password'=>$request->password]))
        {
            return true;
        } 
        return false;
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
        $estatus = ($request->estatus == "finalizar");
        DB::beginTransaction();
        try{
            $reservacion = Reservacion::create([
                'nombre_cliente'  => $request->nombre,
                'email'           => $request->email,
                'alojamiento'     => $request->alojamiento,
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
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($reservacion['id']) ? 'Success' : 'Error']);
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
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function show(Reservacion  $reservacion = null)
    {   
        return view('reservaciones.show');
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
