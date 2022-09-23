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
use App\Models\Cerrador;
use App\Models\CodigoAutorizacionPeticion;
use App\Models\DescuentoCodigo;
use App\Models\ReservacionTicket;
use App\Models\TipoCambio;
use App\Models\TipoPago;
use App\Models\User;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Break_;

class ReservacionController extends Controller
{
    public $folioSufijo   = "-B";
    public $longitudFolio = 7;

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
        $estados          = Estado::all();
        $alojamientos     = Alojamiento::where('estatus',1)->orderBy('nombre','asc')->get();
        $descuentosCodigo = DescuentoCodigo::where('estatus',1)->get();
        $actividades      = Actividad::where('estatus',1)
            ->whereRaw('NOW() >= fecha_inicial')
            ->whereRaw('NOW() <= fecha_final')
            ->orWhere('duracion','indefinido')
            ->get();

        $cerradores     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_cerrador',1);
        })->get();
            
        $comisionistas     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_canal',0)
            ->where('comisionista_actividad',0)
            ->where('comisionista_cerrador',0);
        })->get();

        $comisionistasActividad = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_actividad',1);
        })->get();



        $dolarPrecio    = TipoCambio::where('seccion_uso', 'general')->first();

        return view('reservaciones.create',['estados' => $estados,'actividades' => $actividades,'alojamientos' => $alojamientos,'comisionistas' => $comisionistas,'dolarPrecio' => $dolarPrecio, 'cerradores' => $cerradores,'descuentosCodigo' => $descuentosCodigo,'comisionistasActividad' => $comisionistasActividad]);
    }

    public function updateEstatusReservacion(Request $request){
        try{
            $reservacion          = Reservacion::find($request->reservacionId);
            $reservacion->estatus = ($request->accion == 'cancelar' ? 0 : 1);
            $reservacion->save();
            
            return json_encode(['result' => "Success"]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => "Error"]);
        } 
    }

    public function getDescuentoPersonalizadoValidacion(Request $request){
        try{
            $limite = $this->getLimitesDescuentoPersonalizado($request);
            return json_encode(['result' => "Success",'limite' => $limite]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => "Error"]);
        }
    }

    private function getLimitesDescuentoPersonalizado($request){
        $descuento = User::where('email', $request['email'])->first();
        return $descuento->limite_descuento;
    }

    public function getCodigoDescuento(Request $request){
        $codigoDescuento = $request->codigoDescuento;
        if($codigoDescuento !== ""){
            try{
                $descuento = $this->getDescuento($codigoDescuento);
                return json_encode(['result' => "Success",'descuento' => $descuento]);
            } catch (\Exception $e){
                $CustomErrorHandler = new CustomErrorHandler();
                $CustomErrorHandler->saveError($e->getMessage(),$request);
                return json_encode(['result' => "Error"]);
            }
        }
        return json_encode(['result' => "Error"]);
    }

    private function getDescuento($codigoDescuento){
        $descuento = DescuentoCodigo::where('id', $codigoDescuento)->first();
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
        $actividad = new ActividadController();
        $checkin   = new CheckinController();
        $email     = Auth::user()->email;
        $password  = "";
        $estatusPago  = ($request->estatus == "pagar-reservar");
        $pagado   = ($estatusPago ? (count($request->pagos) > 0 ? $this->getCantidadPagada($request,$email) : 0) : 0);
        $adeudo   = ((float)$request->total - (float)$pagado);
        DB::beginTransaction();
        try{
            // if(!$actividad->isDisponible($request)){
            //     return json_encode(['result' => 'Error','message' => 'No hay disponibilidad suficiente en la actividad seleccionada para ese horario.']);
            // }
            $reservacion = Reservacion::create([
                'nombre_cliente'  => $request->nombre,
                'email'           => $request->email,
                'alojamiento'     => $request->alojamiento,
                'origen'          => $request->origen,
                'agente_id'       => is_numeric($request->agente) ? $request->agente : 0,
                'comisionista_id' => is_numeric($request->comisionista) ? $request->comisionista : 0,
                'comisionista_actividad_id' => is_numeric($request->comisionistaActividad) ? $request->comisionistaActividad : 0,
                'cerrador_id'     => is_numeric($request->cerrador) ? $request->cerrador : 0,
                'comentarios'     => $request->comentarios,
                'estatus_pago'    => $estatusPago,
                'fecha'           => $request->fecha,
                'fecha_creacion'  => date('Y-m-d')
            ]);
            $factura = Factura::create([
                'reservacion_id' =>  $reservacion['id'],
                'total'          =>  (float)$request->total,
                'pagado'         =>  $pagado,
                'adeudo'         =>  $adeudo
            ]);
            foreach($request->reservacionArticulos as $reservacionArticulo){
                ReservacionDetalle::create([
                    'reservacion_id'       =>  $reservacion['id'],
                    'factura_id'           =>  $factura['id'],
                    'actividad_id'         =>  $reservacionArticulo['actividad'],
                    'actividad_horario_id' =>  $reservacionArticulo['horario'],
                    'numero_personas'      =>  $reservacionArticulo['cantidad'],
                    'PPU'                  =>  $reservacionArticulo['precio']
                ]);
            }

            if($estatusPago){

                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"efectivo");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"efectivoUsd");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"tarjeta");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"cambio");

                if($this->isValidDescuentoCupon($request)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,'cupon');
                }

                if($this->isValidDescuentoCodigo($request,$email)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"descuentoCodigo");
                }

                if($this->isValidDescuentoPersonalizado($request,$email)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"descuentoPersonalizado");
                }

            }

            $reservacion        = Reservacion::find($reservacion['id']);
            $reservacion->folio = str_pad($reservacion['id'],$this->longitudFolio,0,STR_PAD_LEFT).$this->folioSufijo;
            $reservacion->save();

            DB::commit();

            $reservacion = Reservacion::find($reservacion['id']);

            $this->setEstatusPago($reservacion['id']);

            $checkin->setCheckin($reservacion);

            return json_encode(
                [
                    'result' => 'Success',
                    'id' => $reservacion['id'],
                    'reservacion' => $reservacion
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    private function getCantidadPagada($request,$email){
        $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();

        $pagosAnteriores = (float)($request->pagosAnteriores) ?? 0;
        $pagado          = (
                  (float)$request->cupon['cantidad']
                + (float)$request->pagos['efectivoUsd']*$dolarPrecioCompra->precio_compra
                + (float)$request->pagos['efectivo']
                + (float)$request->pagos['tarjeta']
            );

        if($this->isValidDescuentoCodigo($request,$email)){
            $pagado += (float)$request->descuentoCodigo['cantidad'];
        }

        if($this->isValidDescuentoPersonalizado($request,$email)){
            $pagado += (float)$request->descuentoPersonalizado['cantidad'];
        }

        return $pagado;
    }

    public function getPagosAnteriores($id){
        $factura = Factura::find($id);
        return $factura['pagado'];
    }

    private function isValidDescuentoCupon($request){
        if($request->comisionista == "0"){
            return false;
        }
        $comisionistaId = $request->comisionista;
        $comisionista   = Comisionista::find($comisionistaId);

        return ($comisionista->descuentos);
    }

    private function isDescuentoValid($total,$email){
        $limite = $this->getLimitesDescuentoPersonalizado(['email' => $email]);
        $maximoDescuento = (float)(($total/100) * $limite);
        $total = (float)$total;

        return (round($total,2) >= round($maximoDescuento,2));
    }

    private function setFaturaPago($reservacionId,$facturaId,$request,$tipoPago){
        $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();
        $tipoPagoId = $this->getTipoPagoId($tipoPago);
        $result     = true;
        $cantidad   = is_array($request[$tipoPago]) ?  $request[$tipoPago]['cantidad'] : $request[$tipoPago];
        if((float)$cantidad>0){
            $pago = Pago::create([
                'reservacion_id' =>  $reservacionId,
                'factura_id'     =>  $facturaId,
                'cantidad'       =>  (float)$cantidad,
                'tipo_pago_id'   =>  $tipoPagoId,
                'tipo_cambio_usd'=>  $dolarPrecioCompra->precio_compra,
                'valor'          =>  $request[$tipoPago]['valor'] ?? '',
                'tipo_valor'     =>  $request[$tipoPago]['tipoValor'] ?? '',
                'descuento_codigo_id' => $request[$tipoPago]['descuentoCodigoId'] ?? ''
            ]);
            $result = is_numeric($pago['id']);

        }
        return $result;
    }

    public function getTipoPagoId($tipoPago){
        $tipoPagoId = TipoPago::where('nombre',$tipoPago)->first()->id;
        return $tipoPagoId;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $fechaInicio = date('Y-m-d')." 00:00:00";
        $fechaFinal  = date('Y-m-d')." 23:59:00";

        if(!is_null($request->fecha)){
            switch (@$request->fecha) {
                case 'dia':
                    $fechaInicio = date('Y-m-d')." 00:00:00";
                    $fechaFinal  = date('Y-m-d')." 23:59:00";
                    break;
                case 'mes':
                    $fechaInicio = date('Y-m-01')." 00:00:00";
                    $fechaFinal  = date('Y-m-d')." 23:59:00";
                    break;
                case 'custom':
                    $fechaInicio = $request->fechaInicio." 00:00:00";
                    $fechaFinal  = $request->fechaFinal." 23:59:00";
                    break;
            }   
        }
        
        DB::enableQueryLog();
        $reservaciones = Reservacion::whereBetween("created_at", [$fechaInicio,$fechaFinal])->with(['descuentoCodigo' => function ($query) {
                $query->where("nombre",'like',"%CORTESIA%");
            }])->orderByDesc('id')->where('estatus',1)->get();
        //dd(DB::getQueryLog());
    

        $reservacionDetalleArray = [];
        foreach($reservaciones as $reservacion){ 
            $numeroPersonas = 0;
            $horario        = "";
            $actividades    = "";
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $numeroPersonas += $reservacionDetalle->numero_personas;
                $horario         = ($horario != "" ? $horario.", " : "").@$reservacionDetalle->horario->horario_inicial;
                $actividades     = ($actividades != "" ? $actividades.", " : "").@$reservacionDetalle->actividad->nombre;
            }
            $reservacionDetalleArray[] = [
                'id'           => @$reservacion->id,
                'folio'        => @$reservacion->folio,
                'actividad'    => $actividades,
                'horario'      => $horario,
                'fechaCreacion' => @$reservacion->fecha_creacion,
                'fecha'        => @$reservacion->fecha,
                'cliente'      => @$reservacion->nombre_cliente,
                'personas'     => $numeroPersonas,
                'notas'        => @$reservacion->comentarios,
                'estatus'      => @$reservacion->comentarios,
                'cortesia'     => @($reservacion->descuentoCodigo->id > 0) ? 'Cortesia' : '',
                'estatusPago'  => @$reservacion->estatus_pago
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
    public function edit(Reservacion $reservacion)
    {
        $estados          = Estado::all();
        $alojamientos     = Alojamiento::orderBy('nombre','asc')->get();
        $descuentosCodigo = DescuentoCodigo::where('estatus',1)->get();
        $actividades      = Actividad::where('estatus',1)
            ->whereRaw('NOW() >= fecha_inicial')
            ->whereRaw('NOW() <= fecha_final')
            ->orWhere('duracion','indefinido')
            ->get();

        $cerradores     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_cerrador',1);
        })->get();
            
        $comisionistas     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_canal',0)
            ->where('comisionista_actividad',0)
            ->where('comisionista_cerrador',0);
        })->get();

        $comisionistasActividad = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
            $query
            ->where('comisionista_actividad',1);
        })->get();

        $dolarPrecio = TipoCambio::where('seccion_uso', 'general')->first();
        $tickets           = ReservacionTicket::where('reservacion_id',$reservacion->id)->get();

        return view('reservaciones.edit',[
            'reservacion' => $reservacion,
            'estados' => $estados,
            'actividades' => $actividades,
            'alojamientos' => $alojamientos,
            'comisionistas' => $comisionistas,
            'comisionistasActividad' => $comisionistasActividad,
            'dolarPrecio' => $dolarPrecio,
            'cerradores' => $cerradores,
            'descuentosCodigo' => $descuentosCodigo,
            'tickets' => $tickets
        ]);
    }
    
    public function removeActividad(Request $request){
        try{
            $actividad = Actividad::where('clave',$request->actividadClave)->get()[0];
            
            ReservacionDetalle::where('reservacion_id', $request->reservacionId)
                ->where('actividad_id', $actividad['id'])->delete();
            return json_encode(
                [
                    'result' => 'Success'
                ]
            );
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    public function removeDescuento(Request $request){
        try{
            DB::beginTransaction(); 

            $pago = Pago::find($request->pagoId);

            $factura                 = Factura::find($request->reservacionId);
            $factura->pagado         = (float)$factura->pagado - (float)$pago['cantidad'];
            $factura->adeudo         = (float)$factura->adeudo + (float)$pago['cantidad'];
            $factura->save();

            $pago->delete();

            DB::commit();
            
            $this->setEstatusPago($request->reservacionId);
            
            return json_encode(
                [
                    'result' => 'Success'
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
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
        $email    = Auth::user()->email;
        $password = "";
        $checkin   = new CheckinController();

        DB::beginTransaction();

        try{
            $pagosAnteriores = $this->getPagosAnteriores($id);
            $pagado   = (count($request->pagos) > 0 ? $this->getCantidadPagada($request,$email) : 0);
            $pagado   = ((float) $pagado + (float) $pagosAnteriores);
            $adeudo   = ((float)$request->total - (float)$pagado);
            $pagar    = ($request->estatus == "pagar");

            $reservacion                  = Reservacion::find($id);
            $reservacion->nombre_cliente  = $request->nombre;
            $reservacion->email           = $request->email;
            $reservacion->alojamiento     = $request->alojamiento;
            $reservacion->origen          = $request->origen;
            $reservacion->agente_id       = $request->agente;
            $reservacion->comisionista_id = $request->comisionista;
            $reservacion->comisionista_actividad_id = $request->comisionistaActividad;
            $reservacion->cerrador_id     = $request->cerrador;
            $reservacion->comentarios     = $request->comentarios;
            $reservacion->fecha           = $request->fecha;
            if($pagar){
                $reservacion->estatus_pago = 1;
            }
            $reservacion->save();

            $factura                 = Factura::find($id);
            $factura->reservacion_id =  $reservacion['id'];
            $factura->total          =  $request->total;
            $factura->pagado         =  (float)$pagado;
            $factura->adeudo         =  (float)$adeudo;
            $factura->save();

            ReservacionDetalle::where('reservacion_id', $reservacion['id'])->delete();

            foreach($request->reservacionArticulos as $reservacionArticulo){
                ReservacionDetalle::create([
                    'reservacion_id'       =>  $reservacion['id'],
                    'factura_id'           =>  $factura['id'],
                    'actividad_id'         =>  $reservacionArticulo['actividad'],
                    'actividad_horario_id' =>  $reservacionArticulo['horario'],
                    'numero_personas'      =>  $reservacionArticulo['cantidad'],
                    'PPU'                  =>  $reservacionArticulo['precio']
                ]);
            }

            if($pagar){

                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"efectivo");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"efectivoUsd");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"tarjeta");
                $this->setFaturaPago($reservacion['id'],$factura['id'],$request['pagos'],"cambio");

                if($this->isValidDescuentoCupon($request)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,'cupon');
                }

                if($this->isValidDescuentoCodigo($request,$email)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"descuentoCodigo");
                }

                if($this->isValidDescuentoPersonalizado($request,$email)){
                    $this->setFaturaPago($reservacion['id'],$factura['id'],$request,"descuentoPersonalizado");
                }

            }

            DB::commit();

            $reservacion = Reservacion::find($reservacion['id']);

            $this->setEstatusPago($reservacion['id']);

            $checkin->setCheckin($reservacion);

            return json_encode(
                [
                    'result' => 'Success',
                    'reservacion' => $reservacion
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($reservacion['id']) ? 'Success' : 'Error']);
    }
    

    private function setEstatusPago($reservacionId){
        $reservacion               = Reservacion::find($reservacionId);
        $reservacion->estatus_pago = $this->getEstatusPagoReservacion($reservacionId);
        $reservacion->save();
    }

    public function getEstatusPagoReservacion($reservacionId){
        $factura = Factura::where('reservacion_id',$reservacionId)->first();
        if($factura->pagado == 0){
            return 0;//'pendiente'
        }else if($factura->pagado < $factura->total){
            return 1;//'parcial'
        }else if($factura->pagado >= $factura->total){
            return 2;//'pagado'
        }
    }

    private function isValidDescuentoCodigo($request,$email){
        if((float)$request['descuentoCodigo']['cantidad'] > 0){
            $password = $request['descuentoCodigo']['password'];
            //if($this->verifyUserAuth(
            //    [
            //        'email'    => $email,
            //        'password' => $password
            //    ])
            //){
                return true;
            //}
        }
        return false;
    }

    private function isValidDescuentoPersonalizado($request,$email){
        if((float)$request['descuentoPersonalizado']['cantidad'] > 0){
            //$password = $request['descuentoPersonalizado']['password'];
            //if($this->verifyUserAuth(
            //    [
            //        'email'    => $email,
            //        'password' => $password
            //    ])
            //){
                if($this->isDescuentoValid($request->total,$email)){
                    return true;
                }
            //}
        }
        return false;
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
