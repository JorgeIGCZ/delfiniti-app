<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Estado;
use App\Models\FotoVideoComision;
use App\Models\FotoVideoComisionista;
use App\Models\FotoVideoProducto;
use App\Models\FotoVideoVenta;
use App\Models\FotoVideoVentaDetalle;
use App\Models\FotoVideoVentaFactura;
use App\Models\FotoVideoVentaPago;
use App\Models\FotoVideoVentaTicket;
use App\Models\TipoCambio;
use App\Models\TipoPago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;

class FotoVideoVentaController extends Controller
{

    protected $tipoCambio = 0;

    public function __construct() {
        $this->middleware('permission:FotoVideoVentas.index')->only('index'); 
        $this->middleware('permission:FotoVideoVentas.create')->only('create'); 
        $this->middleware('permission:FotoVideoVentas.update')->only('edit'); 

        $this->tipoCambio = TipoCambio::where("seccion_uso","reportes")->first()["precio_compra"];
    }

    public $folioSufijo   = "-A";
    public $longitudFolio = 7;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fotovideoventas.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(FotoVideoVenta $fotoVideoVenta)
    {
        $productos     = FotoVideoProducto::where('estatus',1)->get()->toArray();
        $estados       = Estado::all();
        $fotografos = FotoVideoComisionista::where('estatus',1)->orderBy('nombre', 'asc')->get();


        $dolarPrecio = TipoCambio::where('seccion_uso', 'general')->first();

        return view('fotovideoventas.create',['venta' => $fotoVideoVenta,'productos' => $productos,'estados' => $estados, 'fotografos' => $fotografos,'dolarPrecio' => $dolarPrecio]);
    }

    public function updateEstatusVenta(Request $request){
        try{ 
            $venta          = FotoVideoVenta::find($request->ventaId);
            $venta->estatus = ($request->accion == 'cancelar' ? 0 : 1);
            $venta->save();
            
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $email     = Auth::user()->email;
        $isPago  = ($request->estatus === "pagar");
        $pagado   = ($isPago ? (count($request->pagos) > 0 ? $this->getCantidadPagada($request,$email) : 0) : 0);
        $adeudo   = ((float)$request->total - (float)$pagado);

        $ordenes = $this->definirOrdenes($request);

        // $Productos = new FotoVideoProductoController();
        DB::beginTransaction();
        try{
            foreach($ordenes as $fotografoId => $orden){
                $venta = FotoVideoVenta::create([
                    'folio'          => '',
                    'nombre_cliente' => mb_strtoupper(isset($orden['nombre']) ? $orden['nombre'] : "cliente en mostrador"),
                    'email'          => mb_strtoupper($orden['email']),
                    'direccion'      => mb_strtoupper($orden['direccion']),
                    'origen'         => mb_strtoupper($orden['origen']),
                    'RFC'            => mb_strtoupper($orden['rfc']),
                    'estatus_pago'   => $isPago,
                    'fecha'          => $orden['fecha'],
                    'fecha_creacion' => date('Y-m-d'),
                    'usuario_id'     => is_numeric($orden['usuario']) ? $orden['usuario'] : 0,
                    'comisionista_id'=> is_numeric($fotografoId) ? $fotografoId : 0,
                    'comentarios'    => mb_strtoupper($orden['comentarios'])
                ]);
    
                $factura = FotoVideoVentaFactura::create([
                    'venta_id' =>  $venta['id'],
                    'total'    =>  (float)$orden['total'],
                    'pagado'   =>  $pagado,
                    'adeudo'   =>  $adeudo
                ]);
    
                foreach($orden['ventaProductos'] as $ventaProducto){
                    // $Productos->updateFechaMovimientoStock($ventaProducto['productoId'], 'ultima_salida');
                    // $Productos->updateStock($ventaProducto['productoId'], 'baja', $ventaProducto['cantidad']);
                    FotoVideoVentaDetalle::create([
                        'venta_id'            =>  $venta['id'],
                        'factura_id'          =>  $factura['id'],
                        'producto_id'         =>  $ventaProducto['productoId'],
                        'numero_productos'    =>  $ventaProducto['cantidad'],
                        'PPU'                 =>  $ventaProducto['precio']
                    ]);
                }
    
                if($isPago){
                    $this->setFaturaPago($venta['id'], $factura['id'], $orden['pagos'], "efectivo", $orden['usuario']);
                    $this->setFaturaPago($venta['id'], $factura['id'], $orden['pagos'], "efectivoUsd", $orden['usuario']);
                    $this->setFaturaPago($venta['id'], $factura['id'], $orden['pagos'], "tarjeta", $orden['usuario']);
                    $this->setFaturaPago($venta['id'], $factura['id'], $orden['pagos'], "deposito", $orden['usuario']);
                    $this->setFaturaPago($venta['id'], $factura['id'], $orden['pagos'], "cambio", $orden['usuario']);
    
                    // if($this->isValidDescuentoCupon($request)){
                    //     $this->setFaturaPago($venta['id'],$factura['id'],$request,'cupon');
                    // }
    
                    // if($this->isValidDescuentoCodigo($request,$email)){
                    //     $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoCodigo");
                    // }
    
                    if($this->isValidDescuentoPersonalizado($orden,$email)){
                        $this->setFaturaPago($venta['id'], $factura['id'], $orden, "descuentoPersonalizado", $orden['usuario']);
                    }
                }
    
                $venta        = FotoVideoVenta::find($venta['id']);
                $venta->folio = str_pad($venta['id'],$this->longitudFolio,0,STR_PAD_LEFT).$this->folioSufijo;
                $venta->save();
    
                $venta = FotoVideoVenta::find($venta['id']);
    
                $this->setEstatusPago($venta['id']);
    
                $fechaComisiones = Carbon::now()->format('Y-m-d H:i:m');
    
                $comisiones     = new FotoVideoComisionController();
                $comisiones->setComisiones($venta['id'], $fechaComisiones);
            }

            DB::commit();

            return json_encode(
                [
                    'result' => 'Success',
                    'id' => $venta['id'],
                    'venta' => $venta
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    public function updateEstatus(Request $request){
        try{
            $reservacion          = FotoVideoVenta::find($request->ventaId);
            $reservacion->estatus = ($request->accion == 'cancelar' ? 0 : 1);
            $reservacion->save();
            
            return json_encode(['result' => "Success"]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => "Error"]);
        } 
    }
    
    private function getCantidadPagada($request, $email){
        $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();

        $pagado          = (
                //   (float)$request->cupon['cantidad'] + 
                  (float)$request->pagos['efectivoUsd']*$dolarPrecioCompra->precio_compra
                + (float)$request->pagos['efectivo']
                + (float)$request->pagos['tarjeta']
                + (float)$request->pagos['deposito']
                + (float)$request->pagos['cambio']
            );

        // if($this->isValidDescuentoCodigo($request,$email)){
        //     $pagado += (float)$request->descuentoCodigo['cantidad'];
        // }

        if($this->isValidDescuentoPersonalizado($request,$email)){
            $pagado += (float)$request->descuentoPersonalizado['cantidad'];
        }

        return $pagado;
    }

    public function getPagosAnteriores($id){
        $factura = FotoVideoVentaFactura::where("venta_id",$id)->first();
        return $factura['pagado'];
    }

    public function getTipoPagoId($tipoPago){
        $tipoPagoId = TipoPago::where('nombre',$tipoPago)->first()->id;
        return $tipoPagoId;
    }

    private function isDescuentoValid($total,$email){
        $limite = $this->getLimitesDescuentoPersonalizado(['email' => $email]);
        $maximoDescuento = (float)(($total/100) * $limite);
        $total = (float)$total;

        return (round($total,2) >= round($maximoDescuento,2));
    }

    private function setFaturaPago($ventaId, $facturaId, $request, $tipoPago, $usuario){
        $dolarPrecioCompra   = TipoCambio::where('seccion_uso', 'general')->first();
        $tipoPagoId = $this->getTipoPagoId($tipoPago);
        $result     = true;
        $cantidad   = is_array($request[$tipoPago]) ?  $request[$tipoPago]['cantidad'] : $request[$tipoPago];
        if((float)$cantidad !== (float)0){
            $pago = FotoVideoVentaPago::create([
                'venta_id' =>  $ventaId,
                'factura_id'     =>  $facturaId,
                'cantidad'       =>  (float)$cantidad,
                'tipo_pago_id'   =>  $tipoPagoId,
                'tipo_cambio_usd'=>  $dolarPrecioCompra->precio_compra,
                'valor'          =>  $request[$tipoPago]['valor'] ?? '',
                'tipo_valor'     =>  $request[$tipoPago]['tipoValor'] ?? '',
                'usuario_id'     => is_numeric($usuario) ? $usuario : 0
            ]);
            $result = is_numeric($pago['id']);

        }
        return $result;
    }
    
    public function show(FotoVideoVenta $fotoVideoVenta)
    {
        $productos = FotoVideoProducto::where('estatus',1)->get();
        $estados = Estado::all();

        $dolarPrecio = TipoCambio::where('seccion_uso', 'general')->first();

        $fotografos = FotoVideoComisionista::where('estatus',1)->orderBy('nombre', 'asc')->get();
        $tickets    = FotoVideoVentaTicket::where('venta_id',$fotoVideoVenta->id)->get();
        
        return view('fotovideoventas.view',[
            'venta' => $fotoVideoVenta,
            'productos' => $productos,
            'estados' => $estados,
            'dolarPrecio' => $dolarPrecio,
            'fotografos' => $fotografos,
            'tickets' => $tickets
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
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

        $estatus = [];
        if(!is_null($request->estatus)){
            switch (@$request->estatus) {
                case 'todos':
                    $estatus = [0,1,2];
                    break;
                case 'pendiente':
                    $estatus = [0];
                    break;
                case 'parcial':
                    $estatus = [1];
                    break;
                case 'pagado':
                    $estatus = [2];
                    break;
            }   
        }
        
        DB::enableQueryLog();
        $ventas = FotoVideoVenta::whereBetween("fecha", [$fechaInicio,$fechaFinal])->whereIn('estatus_pago',$estatus);

        // if(!Auth::user()->hasRole('Administrador')){
            $ventas = $ventas->where('estatus',1);
        // }

        $ventas = $ventas->orderByDesc('id')->get();
        // dd(DB::getQueryLog());
        
        $ventaDetalleArray = [];
        foreach($ventas as $venta){ 
            $pagosReales = [
                "efectivo",
                "efectivoUsd",
                "tarjeta",
                "deposito"
            ];
            $numeroProductos = 0;
            $horario        = "";
            $productos    = "";
            $total = 0;
            $tipoPagoNombre = "";
            $tiposPago = [];

            foreach($venta->ventaDetalle as $ventaDetalle){
                $numeroProductos += $ventaDetalle->numero_productos;
                $horario         = ($horario != "" ? $horario.", " : "").@$ventaDetalle->horario->horario_inicial;
                $productos     = ($productos != "" ? $productos.", " : "").@$ventaDetalle->producto->nombre;
            }

            foreach($venta->pagos as $pago){
                $tipoPagoNombre = TipoPago::find($pago->tipo_pago_id)->nombre;
                if(!in_array($tipoPagoNombre, $pagosReales)){
                    continue;
                }
    
                $tiposPago[] = $tipoPagoNombre;
    
                if($pago->tipoPago->nombre == "efectivoUsd"){
                    $cantidadPago = ($pago->cantidad * $pago->tipo_cambio_usd);
                    $cambio = $this->getVentaCambio($venta);

                    $total += ($cantidadPago + $cambio);
                    continue;
                }
                $total += $pago->cantidad;
            }

            $ventaDetalleArray[] = [ 
                'id'           => @$venta->id,
                'folio'        => @$venta->folio,
                'tiposPago'    => implode(', ',$tiposPago), 
                'total'        => $total,
                'fechaCreacion' => @Carbon::parse($venta->fecha_creacion)->format('d/m/Y'),//date_format(date_create($venta->fecha_creacion),"d/m/Y"),
                'fecha'        => @Carbon::parse($venta->fecha)->format('d/m/Y'),//date_format(date_create($venta->fecha),"d-m-Y"),
                'cliente'      => @$venta->nombre_cliente,
                'fotografo'    => @$venta->fotografo->nombre,
                'notas'        => @$venta->comentarios,
                'estatus'      => @$venta->estatus,
                'estatusPago'  => @$venta->estatus_pago
            ];
        }
        
        return json_encode(['data' => $ventaDetalleArray]);
    }

    private function getVentaCambio($venta){
        $cambio = 0;

        foreach($venta->pagos as $pago){
            if($pago->tipoPago->nombre == "cambio"){
                $cambio += $pago->cantidad;
            }
        }

        return $cambio;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FotoVideoVenta  $fotoVideoVenta
     * @return \Illuminate\Http\Response
     */
    public function edit(FotoVideoVenta $fotoVideoVenta)
    {
        $productos = FotoVideoProducto::where('estatus',1)->get();
        $estados = Estado::all();

        $dolarPrecio = TipoCambio::where('seccion_uso', 'general')->first();

        $fotografos = FotoVideoComisionista::where('estatus',1)->orderBy('nombre', 'asc')->get();
        $tickets    = FotoVideoVentaTicket::where('venta_id',$fotoVideoVenta->id)->get();
        // return view('ventas.edit');
        return view('fotovideoventas.edit',[
            'venta' => $fotoVideoVenta,
            'productos' => $productos,
            'estados' => $estados,
            'dolarPrecio' => $dolarPrecio,
            'fotografos' => $fotografos,
            'tickets' => $tickets
        ]);
    }

    public function editPago(Request $request){
        try{
            $pago              = FotoVideoVentaPago::find($request->pagoId);
            $pago->created_at  = $request->fecha;
            $pago->save();
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
    
    public function removeProducto(Request $request){
        try{
            $producto = FotoVideoProducto::where('clave',$request->productoClave)->get()[0];
            
            FotoVideoVentaDetalle::where('venta_id', $request->ventaId)
                ->where('producto_id', $producto['id'])->delete();
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

    public function removePago(Request $request){
        try{
            DB::beginTransaction(); 

            $pago = FotoVideoVentaPago::find($request->pagoId);

            $factura                 = FotoVideoVentaFactura::find($request->ventaId);
            $factura->pagado         = (float)$factura->pagado - (float)$pago['cantidad'];
            $factura->adeudo         = (float)$factura->adeudo + (float)$pago['cantidad'];
            $factura->save();

            $pago->delete();

            DB::commit();
            
            $this->setEstatusPago($request->ventaId);
            
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


    //Define ordenes basado en fotografos
    protected function definirOrdenes($request){
        
        $requestArray = $request->toArray();

        $ordenes = [];
        
        $fotografosVenta = [];

        $pagos = [
            'efectivo' => $requestArray['pagos']['efectivo'],
            'efectivoUsd' => $requestArray['pagos']['efectivoUsd'],
            'tarjeta' => $requestArray['pagos']['tarjeta'],
            'deposito' => $requestArray['pagos']['deposito'],
            'cambio' => $requestArray['pagos']['cambio'],
        ];


        //Duplica la orden pero lo agrega los productos correspondientes al fotografo.
        foreach($requestArray['ventaProductos'] as $ventaProducto){

            if(!in_array($ventaProducto['fotografo'], $fotografosVenta)){
                $fotografosVenta[] = $ventaProducto['fotografo'];     

                $newRequest = $requestArray;
                $newRequest['ventaProductos'] = [$ventaProducto];

                $ordenes[$ventaProducto['fotografo']] = $newRequest;

                continue;
            }

            $ordenes[$ventaProducto['fotografo']]['ventaProductos'][] = $ventaProducto;
        }
            
        $cantidadDescuentoGeneral = ($requestArray['descuentoPersonalizado']['cantidad']);

        $index = 0;
        foreach($ordenes as $ordenKey => $orden){
            $index++;

            $totalOrden = 0;
            $diferenciaOrden = 0;
            $cantidadDescuentoOrden = 0;

            foreach ($orden['ventaProductos'] as $value) {
                $totalOrden += ($value['cantidad'] * $value['precio']);
            }

            // Si existe un descuento trata de aplicar el total del descuento a la primera orden y el restant en la segunda, si no es suficiente para cubrir el total,
            // aplica el correspondiente.
            if($cantidadDescuentoGeneral > 0){
                if($cantidadDescuentoGeneral > $totalOrden){
                    $cantidadDescuentoOrden = $totalOrden;
    
                    $cantidadDescuentoGeneral = ($cantidadDescuentoGeneral - $totalOrden); 
                }else{
                    $cantidadDescuentoOrden = $cantidadDescuentoGeneral;
    
                    $cantidadDescuentoGeneral = 0;
                }
            }

            $diferenciaOrden = $totalOrden - $cantidadDescuentoOrden;

            $ordenes[$ordenKey]['descuentoPersonalizado']['cantidad'] = $cantidadDescuentoOrden;
            
            //Define pagos para cada orden
            foreach($pagos as $key => $pago){
                $pago = (float)$pago;

                if($pago == 0){// || $diferenciaOrden == 0){
                    $ordenes[$ordenKey]['pagos'][$key] = 0;
                    continue;
                }

                if($key == 'efectivoUsd'){
                    $pago = $this->convertUsdToMxn($pago);
                }

                if($pago > $diferenciaOrden){
                    if($key == 'efectivoUsd'){
                        $diferenciaOrden = $this->convertMxnToUsd($diferenciaOrden);
                        
                        //verificamos si es la ultima orden que se generará si es asi le aplicaremos el total de dolares a esta
                        if(count($ordenes) === $index){
                            $diferenciaOrden = $this->convertMxnToUsd($pago);
                        }
                    }

                    $ordenes[$ordenKey]['pagos'][$key] = $diferenciaOrden;

                    //regresamos a MXN para continuar con el proceso
                    if($key == 'efectivoUsd'){
                        $diferenciaOrden = $this->convertUsdToMxn($diferenciaOrden);
                    }

                    $diferenciaPago = ($pago - $diferenciaOrden);

                    if($key == 'efectivoUsd'){
                        $diferenciaPago = $this->convertMxnToUsd($diferenciaPago);
                    }

                    $pagos[$key] = $diferenciaPago;

                    $diferenciaOrden = 0;
                    continue;
                }
                
                if($key == 'efectivoUsd'){
                    $pago = $this->convertMxnToUsd($pago);
                }

                if($key == 'cambio'){
                    //verificamos si es la ultima orden que se generará si es asi le aplicaremos el total de dolares a esta
                    if(count($ordenes) === $index){
                        $ordenes[$ordenKey]['pagos'][$key] = $pago;
                        $pagos[$key] = 0;

                        $diferenciaOrden = ($diferenciaOrden - $pago);
                        
                        continue;
                    }else{
                        $ordenes[$ordenKey]['pagos'][$key] = 0;
                        $pagos[$key] = $pago;

                        $diferenciaOrden = $diferenciaOrden;

                        continue;
                    }
                }

                $ordenes[$ordenKey]['pagos'][$key] = $pago;
                $pagos[$key] = 0;

                $diferenciaOrden = ($diferenciaOrden - $pago);
            }
        }

        return $ordenes;
    }

    private function convertUsdToMxn($pago)
    {
        return ($pago * $this->tipoCambio);
    }

    private function convertMxnToUsd($pago)
    {
        return ($pago / $this->tipoCambio);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FotoVideoVenta  $fotoVideoVenta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $email    = Auth::user()->email;
        // $password = "";
        // $checkin   = new CheckinController();

        DB::beginTransaction();

        try{
            $pagosAnteriores = $this->getPagosAnteriores($id);

            $pagado   = (count($request->pagos) > 0 ? $this->getCantidadPagada($request, $email) : 0);
            $pagado   = ((float) $pagado + (float) $pagosAnteriores);
            $adeudo   = ((float)$request->total - (float)$pagado);
            $pagar    = ($request->estatus == "pagar");

            $venta                  = FotoVideoVenta::find($id);
            $venta->nombre_cliente  = mb_strtoupper($request->nombre);
            $venta->email           = mb_strtoupper($request->email);
            $venta->direccion       = mb_strtoupper($request->direccion);
            $venta->origen          = mb_strtoupper($request->origen);
            $venta->RFC             = mb_strtoupper($request->rfc);
            $venta->fecha           = $request->fecha;
            $venta->comisionista_id = is_numeric($request->fotografo) ? $request->fotografo : 0;
            $venta->comentarios     = mb_strtoupper($request->comentarios);
            if($pagar){
                $venta->estatus_pago = 1;
            }
            $venta->save(); 

            $factura                 = FotoVideoVentaFactura::where("venta_id",$id)->first();
            $factura->venta_id       =  $venta['id'];
            $factura->total          =  $request->total;
            $factura->pagado         =  (float)$pagado;
            $factura->adeudo         =  (float)$adeudo;
            $factura->save();

            FotoVideoVentaDetalle::where('venta_id', $venta['id'])->delete();

            foreach($request->ventaProductos as $ventaProducto){
                FotoVideoVentaDetalle::create([
                    'venta_id'            =>  $venta['id'],
                    'factura_id'          =>  $factura['id'],
                    'producto_id'         =>  $ventaProducto['productoId'],
                    'numero_productos'    =>  $ventaProducto['cantidad'],
                    'PPU'                 =>  $ventaProducto['precio']
                ]);
            }

            if($pagar){

                $this->setFaturaPago($venta['id'], $factura['id'], $request['pagos'], "efectivo", $request->usuario);
                $this->setFaturaPago($venta['id'], $factura['id'], $request['pagos'], "efectivoUsd", $request->usuario);
                $this->setFaturaPago($venta['id'], $factura['id'], $request['pagos'], "tarjeta", $request->usuario);
                $this->setFaturaPago($venta['id'], $factura['id'], $request['pagos'], "deposito", $request->usuario);
                $this->setFaturaPago($venta['id'], $factura['id'], $request['pagos'], "cambio", $request->usuario);

                // if($this->isValidDescuentoCupon($request)){
                //     $this->setFaturaPago($venta['id'],$factura['id'],$request,'cupon');
                // }

                // if($this->isValidDescuentoCodigo($request,$email)){
                //     $this->setFaturaPago($venta['id'],$factura['id'],$request,"descuentoCodigo");
                // }

                if($this->isValidDescuentoPersonalizado($request,$email)){
                    $this->setFaturaPago($venta['id'], $factura['id'], $request, "descuentoPersonalizado", $request->usuario);
                }

            }

            DB::commit();

            $venta = FotoVideoVenta::find($venta['id']);

            $this->setEstatusPago($venta['id']);

            // $checkin->setCheckin($venta);
            $fechaComisiones = Carbon::now()->format('Y-m-d H:i:m');

            $comisiones     = new FotoVideoComisionController();
            $comisiones->setComisiones($venta['id'], $fechaComisiones);

            return json_encode(
                [
                    'result' => 'Success',
                    'venta' => $venta
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($venta['id']) ? 'Success' : 'Error']);
    }

    private function setEstatusPago($ventaId){
        $venta               = FotoVideoVenta::find($ventaId);
        $venta->estatus_pago = $this->getEstatusPagoVenta($ventaId);
        $venta->save();
    }

    public function getEstatusPagoVenta($ventaId){
        $factura = FotoVideoVentaFactura::where('venta_id',$ventaId)->first();
        if($factura->pagado == 0){
            return 0;//'pendiente'
        }else if($factura->pagado < $factura->total){
            return 1;//'parcial'
        }else if($factura->pagado >= $factura->total){
            return 2;//'pagado'
        }
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
                if($this->isDescuentoValid($request['total'],$email)){
                    return true;
                }
            //}
        }
        return false;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FotoVideoVenta  $fotoVideoVenta
     * @return \Illuminate\Http\Response
     */
    public function destroy(FotoVideoVenta $fotoVideoVenta)
    {
        //
    }
}
