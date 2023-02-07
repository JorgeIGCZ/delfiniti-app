<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\VentaDetalle;
use App\Models\Comisionista;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Estado;
use App\Models\Factura;
use App\Models\Alojamiento;
use App\Models\Pago;
use App\Models\Venta;
use App\Classes\CustomErrorHandler;
use App\Models\DescuentoCodigo;
use App\Models\VentaTicket;
use App\Models\TipoCambio;
use App\Models\TipoPago;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VentaController extends Controller
{
    // public function __construct() {
    //     $this->middleware('permission:Ventas.index')->only('index'); 
    //     $this->middleware('permission:Ventas.create')->only('create'); 
    //     $this->middleware('permission:Ventas.update')->only('edit'); 
    // }

    public $folioSufijo   = "-D"; //????
    public $longitudFolio = 7;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ventas.index');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Venta $venta)
    {
        $estados          = Estado::all();
        $descuentosCodigo = DescuentoCodigo::where('estatus',1)->get();
        // $productoes      = Producto::where('estatus',1)
        //     ->whereRaw('NOW() >= fecha_inicial')
        //     ->whereRaw('NOW() <= fecha_final')
        //     ->orWhere('duracion','indefinido')
        //     ->get();


        $dolarPrecio    = TipoCambio::where('seccion_uso', 'general')->first();

        return view('ventas.create',['venta' => $venta, 'estados' => $estados,'productoes' => [],'comisionistas' => [],'dolarPrecio' => $dolarPrecio, 'cerradores' => [],'descuentosCodigo' => [],'comisionistasProducto' => []]);
    }

    public function updateEstatus(Request $request){
        // try{
        //     $venta          = Venta::find($request->ventaId);
        //     $venta->estatus = ($request->accion == 'cancelar' ? 0 : 1);
        //     $venta->save();
            
        //     return json_encode(['result' => "Success"]);
        // } catch (\Exception $e){
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => "Error"]);
        // } 
    }

    public function getDescuentoPersonalizadoValidacion(Request $request){
        // try{
        //     $limite = $this->getLimitesDescuentoPersonalizado($request);
        //     return json_encode(['result' => "Success",'limite' => $limite]);
        // } catch (\Exception $e){
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => "Error"]);
        // }
    }

    private function getLimitesDescuentoPersonalizado($request){
        // $descuento = User::where('email', $request['email'])->first();
        // return $descuento->limite_descuento;
    }

    public function getCodigoDescuento(Request $request){
        // $codigoDescuento = $request->codigoDescuento;
        // if($codigoDescuento !== ""){
        //     try{
        //         $descuento = $this->getDescuento($codigoDescuento);
        //         return json_encode(['result' => "Success",'descuento' => $descuento]);
        //     } catch (\Exception $e){
        //         $CustomErrorHandler = new CustomErrorHandler();
        //         $CustomErrorHandler->saveError($e->getMessage(),$request);
        //         return json_encode(['result' => "Error"]);
        //     }
        // }
        // return json_encode(['result' => "Error"]);
    }

    private function getDescuento($codigoDescuento){
        // $descuento = DescuentoCodigo::where('id', $codigoDescuento)->first();
        // return $descuento;
    }

    private function verifyUserAuth($request){
        // if(Auth::attempt(['email'=>$request->email,'password'=>$request->password]))
        // {
        //     return true;
        // }
        // return false;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $producto = new ProductoController();
        // $checkin   = new CheckinController();
        // $email     = Auth::user()->email;
        // $password  = "";
        // $estatusPago  = ($request->estatus == "pagar-reservar");
        // $pagado   = ($estatusPago ? (count($request->pagos) > 0 ? $this->getCantidadPagada($request,$email) : 0) : 0);
        // $adeudo   = ((float)$request->total - (float)$pagado);
        // DB::beginTransaction();
        // try{
        //     $venta = Venta::create([
        //         'nombre_cliente'  => strtoupper($request->nombre),
        //         'email'           => strtoupper($request->email),
        //         'alojamiento'     => strtoupper($request->alojamiento),
        //         'origen'          => strtoupper($request->origen),
        //         'agente_id'       => is_numeric($request->agente) ? $request->agente : 0,
        //         'comisionista_id' => is_numeric($request->comisionista) ? $request->comisionista : 0,
        //         'comisionista_producto_id' => is_numeric($request->comisionistaProducto) ? $request->comisionistaProducto : 0,
        //         'cerrador_id'     => is_numeric($request->cerrador) ? $request->cerrador : 0,
        //         'comentarios'     => strtoupper($request->comentarios),
        //         'estatus_pago'    => $estatusPago,
        //         'comisionable'    => $request->comisionable,
        //         'fecha'           => $request->fecha,
        //         'fecha_creacion'  => date('Y-m-d')
        //     ]);
        //     $factura = Factura::create([
        //         'venta_id' =>  $venta['id'],
        //         'total'          =>  (float)$request->total,
        //         'pagado'         =>  $pagado,
        //         'adeudo'         =>  $adeudo
        //     ]);

        //     foreach($request->ventaArticulos as $ventaArticulo){
        //         VentaDetalle::create([
        //             'venta_id'       =>  $venta['id'],
        //             'factura_id'           =>  $factura['id'],
        //             'producto_id'         =>  $ventaArticulo['producto'],
        //             'producto_horario_id' =>  $ventaArticulo['horario'],
        //             'numero_personas'      =>  $ventaArticulo['cantidad'],
        //             'PPU'                  =>  $ventaArticulo['precio']
        //         ]);
        //     }

        //     if($estatusPago){
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"efectivo");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"efectivoUsd");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"tarjeta");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"deposito");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"cambio");

        //         if($this->isValidDescuentoCupon($request)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,'cupon');
        //         }

        //         if($this->isValidDescuentoCodigo($request,$email)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoCodigo");
        //         }

        //         if($this->isValidDescuentoPersonalizado($request,$email)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoPersonalizado");
        //         }

        //     }

        //     $venta        = Venta::find($venta['id']);
        //     $venta->folio = str_pad($venta['id'],$this->longitudFolio,0,STR_PAD_LEFT).$this->folioSufijo;
        //     $venta->save();

        //     DB::commit();

        //     $venta = Venta::find($venta['id']);

        //     $this->setEstatusPago($venta['id']);

        //     // $checkin->setCheckin($venta);
        //     $comisiones     = new ComisionController();
        //     $comisiones->setComisiones($venta['id']);

        //     return json_encode(
        //         [
        //             'result' => 'Success',
        //             'id' => $venta['id'],
        //             'venta' => $venta
        //         ]
        //     );
        // } catch (\Exception $e){
        //     DB::rollBack();
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        // }
    }

    private function getCantidadPagada($request,$email){
        // $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();

        // $pagosAnteriores = (float)($request->pagosAnteriores) ?? 0;
        // $pagado          = (
        //           (float)$request->cupon['cantidad']
        //         + (float)$request->pagos['efectivoUsd']*$dolarPrecioCompra->precio_compra
        //         + (float)$request->pagos['efectivo']
        //         + (float)$request->pagos['tarjeta']
        //         + (float)$request->pagos['deposito']
        //     );

        // if($this->isValidDescuentoCodigo($request,$email)){
        //     $pagado += (float)$request->descuentoCodigo['cantidad'];
        // }

        // if($this->isValidDescuentoPersonalizado($request,$email)){
        //     $pagado += (float)$request->descuentoPersonalizado['cantidad'];
        // }

        // return $pagado;
    }

    public function getPagosAnteriores($id){
        // $factura = Factura::where("venta_id",$id)->first();
        // return $factura['pagado'];
    }

    private function isValidDescuentoCupon($request){
        // if($request->comisionista == "0"){
        //     return false;
        // }
        // $comisionistaId = $request->comisionista;
        // $comisionista   = Comisionista::find($comisionistaId);

        // return ($comisionista->descuentos);
    }

    private function isDescuentoValid($total,$email){
        // $limite = $this->getLimitesDescuentoPersonalizado(['email' => $email]);
        // $maximoDescuento = (float)(($total/100) * $limite);
        // $total = (float)$total;

        // return (round($total,2) >= round($maximoDescuento,2));
    }

    private function setFaturaPago($ventaId,$facturaId,$request,$tipoPago){
        // $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();
        // $tipoPagoId = $this->getTipoPagoId($tipoPago);
        // $result     = true;
        // $cantidad   = is_array($request[$tipoPago]) ?  $request[$tipoPago]['cantidad'] : $request[$tipoPago];
        // if((float)$cantidad>0){
        //     $pago = Pago::create([
        //         'venta_id' =>  $ventaId,
        //         'factura_id'     =>  $facturaId,
        //         'cantidad'       =>  (float)$cantidad,
        //         'tipo_pago_id'   =>  $tipoPagoId,
        //         'tipo_cambio_usd'=>  $dolarPrecioCompra->precio_compra,
        //         'valor'          =>  $request[$tipoPago]['valor'] ?? '',
        //         'tipo_valor'     =>  $request[$tipoPago]['tipoValor'] ?? '',
        //         'descuento_codigo_id' => $request[$tipoPago]['descuentoCodigoId'] ?? ''
        //     ]);
        //     $result = is_numeric($pago['id']);

        // }
        // return $result;
    }

    public function getTipoPagoId($tipoPago){
        // $tipoPagoId = TipoPago::where('nombre',$tipoPago)->first()->id;
        // return $tipoPagoId;
    }
    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

        // if(!is_null($request->fecha)){
        //     switch (@$request->fecha) {
        //         case 'dia':
        //             $fechaInicio = Carbon::now()->startOfDay();
        //             $fechaFinal  = Carbon::now()->endOfDay();
        //             break;
        //         case 'mes':
        //             $fechaInicio = Carbon::parse('first day of this month')->startOfDay();
        //             $fechaFinal  = Carbon::parse('last day of this month')->endOfDay();
        //             break;
        //         case 'custom':
        //             $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
        //             $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();
        //             break;
        //     }   
        // }

        // $estatus = [];
        // if(!is_null($request->estatus)){
        //     switch (@$request->estatus) {
        //         case 'todos':
        //             $estatus = [0,1,2];
        //             break;
        //         case 'pendiente':
        //             $estatus = [0];
        //             break;
        //         case 'parcial':
        //             $estatus = [1];
        //             break;
        //         case 'pagado':
        //             $estatus = [2];
        //             break;
        //     }   
        // }
        
        // DB::enableQueryLog();
        // $ventas = Venta::whereBetween("fecha", [$fechaInicio,$fechaFinal])->with(['descuentoCodigo' => function ($query) {
        //         $query->where("nombre",'like',"%CORTESIA%");
        //     }])->orderByDesc('id')->where('estatus',1)->whereIn('estatus_pago',$estatus)->get();
        // //dd(DB::getQueryLog());
    

        // $ventaDetalleArray = [];
        // foreach($ventas as $venta){ 
        //     $numeroPersonas = 0;
        //     $horario        = "";
        //     $productoes    = "";
        //     foreach($venta->ventaDetalle as $ventaDetalle){
        //         $numeroPersonas += $ventaDetalle->numero_personas;
        //         $horario         = ($horario != "" ? $horario.", " : "").@$ventaDetalle->horario->horario_inicial;
        //         $productoes     = ($productoes != "" ? $productoes.", " : "").@$ventaDetalle->producto->nombre;
        //     }
        //     $ventaDetalleArray[] = [
        //         'id'           => @$venta->id,
        //         'folio'        => @$venta->folio,
        //         'producto'    => $productoes, 
        //         'horario'      => $horario,
        //         'fechaCreacion' => @Carbon::parse($venta->fecha_creacion)->format('d/m/Y'),//date_format(date_create($venta->fecha_creacion),"d/m/Y"),
        //         'fecha'        => @Carbon::parse($venta->fecha)->format('d/m/Y'),//date_format(date_create($venta->fecha),"d-m-Y"),
        //         'cliente'      => @$venta->nombre_cliente,
        //         'personas'     => $numeroPersonas,
        //         'notas'        => @$venta->comentarios,
        //         'estatus'      => @$venta->comentarios,
        //         'cortesia'     => @($venta->descuentoCodigo->id > 0) ? 'Cortesia' : '',
        //         'estatusPago'  => @$venta->estatus_pago
        //     ];
        // }
        
        // return json_encode(['data' => $ventaDetalleArray]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //public function edit(Venta $venta)
    public function edit()
    {
        // $estados          = Estado::all();
        // $alojamientos     = Alojamiento::orderBy('nombre','asc')->get();
        // $descuentosCodigo = DescuentoCodigo::where('estatus',1)->get();
        // $productoes      = Producto::where('estatus',1)
        //     ->whereRaw('NOW() >= fecha_inicial')
        //     ->whereRaw('NOW() <= fecha_final')
        //     ->orWhere('duracion','indefinido')
        //     ->get();

        // $cerradores     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
        //     $query
        //     ->where('comisionista_cerrador',1);
        // })->get();
            
        // $comisionistas     = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
        //     $query
        //     ->where('comisionista_canal',0)
        //     ->where('comisionista_producto',0)
        //     ->where('comisionista_cerrador',0);
        // })->get();

        // $comisionistasProducto = Comisionista::where('estatus',1)->whereHas('tipo', function ($query) {
        //     $query
        //     ->where('comisionista_producto',1);
        // })->get();

        // $dolarPrecio = TipoCambio::where('seccion_uso', 'general')->first();
        // $tickets           = VentaTicket::where('venta_id',$venta->id)->get();
        return view('ventas.edit');
        // return view('ventas.edit',[
        //     'venta' => $venta,
        //     'estados' => $estados,
        //     'productoes' => $productoes,
        //     'alojamientos' => $alojamientos,
        //     'comisionistas' => $comisionistas,
        //     'comisionistasProducto' => $comisionistasProducto,
        //     'dolarPrecio' => $dolarPrecio,
        //     'cerradores' => $cerradores,
        //     'descuentosCodigo' => $descuentosCodigo,
        //     'tickets' => $tickets
        // ]);
    }

    public function editPago(Request $request){
        // try{
        //     $pago              = Pago::find($request->pagoId);
        //     $pago->created_at  = $request->fecha;
        //     $pago->save();
        //     return json_encode(
        //         [
        //             'result' => 'Success'
        //         ]
        //     );
        // } catch (\Exception $e){
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        // }
    }
    
    public function removeProducto(Request $request){
        // try{
        //     $producto = Producto::where('clave',$request->productoClave)->get()[0];
            
        //     VentaDetalle::where('venta_id', $request->ventaId)
        //         ->where('producto_id', $producto['id'])->delete();
        //     return json_encode(
        //         [
        //             'result' => 'Success'
        //         ]
        //     );
        // } catch (\Exception $e){
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        // }
    }

    public function removeDescuento(Request $request){
        // try{
        //     DB::beginTransaction(); 

        //     $pago = Pago::find($request->pagoId);

        //     $factura                 = Factura::find($request->ventaId);
        //     $factura->pagado         = (float)$factura->pagado - (float)$pago['cantidad'];
        //     $factura->adeudo         = (float)$factura->adeudo + (float)$pago['cantidad'];
        //     $factura->save();

        //     $pago->delete();

        //     DB::commit();
            
        //     $this->setEstatusPago($request->ventaId);
            
        //     return json_encode(
        //         [
        //             'result' => 'Success'
        //         ]
        //     );
        // } catch (\Exception $e){
        //     DB::rollBack();
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        // }
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
        // $email    = Auth::user()->email;
        // $password = "";
        // $checkin   = new CheckinController();

        // DB::beginTransaction();

        // try{
        //     $pagosAnteriores = $this->getPagosAnteriores($id);

        //     $pagado   = (count($request->pagos) > 0 ? $this->getCantidadPagada($request,$email) : 0);
        //     $pagado   = ((float) $pagado + (float) $pagosAnteriores);
        //     $adeudo   = ((float)$request->total - (float)$pagado);
        //     $pagar    = ($request->estatus == "pagar");

        //     $venta                  = Venta::find($id);
        //     $venta->nombre_cliente  = strtoupper($request->nombre);
        //     $venta->email           = strtoupper($request->email);
        //     $venta->alojamiento     = strtoupper($request->alojamiento);
        //     $venta->origen          = strtoupper($request->origen);
        //     $venta->agente_id       = $request->agente;
        //     $venta->comisionista_id = $request->comisionista;
        //     $venta->comisionista_producto_id = $request->comisionistaProducto;
        //     $venta->cerrador_id     = $request->cerrador;
        //     $venta->comentarios     = strtoupper($request->comentarios);
        //     $venta->comisionable    = $request->comisionable;
        //     $venta->fecha           = $request->fecha;
        //     if($pagar){
        //         $venta->estatus_pago = 1;
        //     }
        //     $venta->save();

        //     $factura                 = Factura::where("venta_id",$id)->first();
        //     $factura->venta_id =  $venta['id'];
        //     $factura->total          =  $request->total;
        //     $factura->pagado         =  (float)$pagado;
        //     $factura->adeudo         =  (float)$adeudo;
        //     $factura->save();

        //     VentaDetalle::where('venta_id', $venta['id'])->delete();

        //     foreach($request->ventaArticulos as $ventaArticulo){
        //         VentaDetalle::create([
        //             'venta_id'       =>  $venta['id'],
        //             'factura_id'           =>  $factura['id'],
        //             'producto_id'         =>  $ventaArticulo['producto'],
        //             'producto_horario_id' =>  $ventaArticulo['horario'],
        //             'numero_personas'      =>  $ventaArticulo['cantidad'],
        //             'PPU'                  =>  $ventaArticulo['precio']
        //         ]);
        //     }

        //     if($pagar){

        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"efectivo");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"efectivoUsd");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"tarjeta");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"deposito");
        //         $this->setFaturaPago($venta['id'],$factura['id'],$request['pagos'],"cambio");

        //         if($this->isValidDescuentoCupon($request)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,'cupon');
        //         }

        //         if($this->isValidDescuentoCodigo($request,$email)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoCodigo");
        //         }

        //         if($this->isValidDescuentoPersonalizado($request,$email)){
        //             $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoPersonalizado");
        //         }

        //     }

        //     DB::commit();

        //     $venta = Venta::find($venta['id']);

        //     $this->setEstatusPago($venta['id']);

        //     // $checkin->setCheckin($venta);
        //     $comisiones     = new ComisionController();
        //     $comisiones->setComisiones($venta['id']);

        //     return json_encode(
        //         [
        //             'result' => 'Success',
        //             'venta' => $venta
        //         ]
        //     );
        // } catch (\Exception $e){
        //     DB::rollBack();
        //     $CustomErrorHandler = new CustomErrorHandler();
        //     $CustomErrorHandler->saveError($e->getMessage(),$request);
        //     return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        // }
        // return json_encode(['result' => is_numeric($venta['id']) ? 'Success' : 'Error']);
    }
    

    private function setEstatusPago($ventaId){
        // $venta               = Venta::find($ventaId);
        // $venta->estatus_pago = $this->getEstatusPagoVenta($ventaId);
        // $venta->save();
    }

    public function getEstatusPagoVenta($ventaId){
        // $factura = Factura::where('venta_id',$ventaId)->first();
        // if($factura->pagado == 0){
        //     return 0;//'pendiente'
        // }else if($factura->pagado < $factura->total){
        //     return 1;//'parcial'
        // }else if($factura->pagado >= $factura->total){
        //     return 2;//'pagado'
        // }
    }

    private function isValidDescuentoCodigo($request,$email){
        // if((float)$request['descuentoCodigo']['cantidad'] > 0){
        //     $password = $request['descuentoCodigo']['password'];
        //     //if($this->verifyUserAuth(
        //     //    [
        //     //        'email'    => $email,
        //     //        'password' => $password
        //     //    ])
        //     //){
        //         return true;
        //     //}
        // }
        // return false;
    }

    private function isValidDescuentoPersonalizado($request,$email){
        // if((float)$request['descuentoPersonalizado']['cantidad'] > 0){
        //     //$password = $request['descuentoPersonalizado']['password'];
        //     //if($this->verifyUserAuth(
        //     //    [
        //     //        'email'    => $email,
        //     //        'password' => $password
        //     //    ])
        //     //){
        //         if($this->isDescuentoValid($request->total,$email)){
        //             return true;
        //         }
        //     //}
        // }
        // return false;
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
