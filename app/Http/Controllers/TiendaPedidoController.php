<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaImpuesto;
use App\Models\TiendaPedido;
use App\Models\TiendaPedidoDetalle;
use App\Models\TiendaPedidoImpuesto;
use App\Models\TiendaProducto;
use App\Models\TiendaProductoImpuesto;
use App\Models\TiendaProveedor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaPedidoController extends Controller
{


    public $folioSufijo   = "-C";
    public $longitudFolio = 7;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pedidos.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($pedido = [])//(Pedido $pedido)
    {
        $proveedores = TiendaProveedor::where('estatus',1)->orderBy('razon_social','asc')->get();
        $productos = TiendaProducto::where('estatus',1)->get()->toArray();
        $productosImpuestos = TiendaProductoImpuesto::get()->toArray();
        $impuestos = TiendaImpuesto::where('estatus',1)->get()->toArray();

        return view('pedidos.create',[
            'pedido' => $pedido,
            'proveedores' => $proveedores,
            'productos' => $productos,
            'productosImpuestos' => $productosImpuestos,
            'impuestos' => $impuestos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) 
    {
        DB::beginTransaction();
        try{
            $pedido = TiendaPedido::create([
                'proveedor_id'  => mb_strtoupper($request->proveedor),
                'comentarios'   => mb_strtoupper($request->comentarios),
                'fecha_pedido'  => $request->fecha,
                'fecha_autorizacion' => null
            ]);

            foreach($request->pedidoProductos as $pedidoProducto){
                TiendaPedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'CPU'         =>  $pedidoProducto['costo'],
                    'IPU_total'   =>  $this->getTotalImpuestosPorUnidad($pedidoProducto['impuestosPU']),
                    'subtotal'    =>  (float)$pedidoProducto['cantidad']*$pedidoProducto['costo'],
                ]);
            }

            foreach($request->impuestosTotales as $impuestoTotal){
                TiendaPedidoImpuesto::create([
                    'pedido_id'   =>  $pedido['id'],
                    'impuesto_id' =>  $impuestoTotal['impuestoId'],
                    'total'       =>  (float)$impuestoTotal['impuesto'],
                ]);
            }

            $pedido->save();

            DB::commit();

            return json_encode(
                [
                    'result' => 'Success',
                    'id' => $pedido['id'],
                    'pedido' => $pedido
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }
 
	public function validatePedido(){
        return view('pedidos.validate');
	}

    public function updateProductoStock($id){
        try { 
            DB::beginTransaction();

            $Productos = new TiendaProductoController();
            $Inventario = new TiendaInventarioController();
            $pedido = TiendaPedido::find($id);

            foreach($pedido->pedidoDetalle as $pedidoDetalle){
                $Productos->updateFechaMovimientoStock($pedidoDetalle->producto_id, 'ultima_entrada');

                $Inventario->setMovimientoInventario([
                    'producto_id' => $pedidoDetalle->producto_id,
                    'movimiento' => "Pedido",
                    'cantidad' => $pedidoDetalle->cantidad,
                    'usuario_id' => auth()->user()->id,
                    'comentarios' => "PedidoId: {$id}"
                ]);

                $producto          = TiendaProducto::find($pedidoDetalle->producto_id);
                $producto->stock   = $producto->stock + $pedidoDetalle->cantidad;
                $producto->save();   
            }

            $pedido->estatus_proceso = 1;
            $pedido->fecha_autorizacion = Carbon::now()->format('Y-m-d H:i:m');
            $pedido->save();   

            DB::commit();

            return json_encode(
                [
                    'result' => 'Success',
                    'id' => $pedido->id,
                    'pedido' => $pedido
                ]
            );


        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$id);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
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

       
       DB::enableQueryLog();
       $pedidos = TiendaPedido::where("estatus", 1)->orderByDesc('id');
       if($request->view === "validate"){
            $pedidos = $pedidos->where("estatus_proceso", 0);
       }else{
            $pedidos = $pedidos->whereBetween("fecha_pedido", [$fechaInicio, $fechaFinal]);
       }
	   $pedidos = $pedidos->get();
       //dd(DB::getQueryLog());
   

       $pedidoDetalleArray = [];
       foreach($pedidos as $pedido){ 
           $cantidad = 0;
           $productos    = "";
           foreach($pedido->pedidoDetalle as $pedidoDetalle){
               $cantidad += $pedidoDetalle->cantidad;
               $productos = ($productos != "" ? $productos.", " : "").@$pedidoDetalle->producto->nombre;
           }
           $pedidoDetalleArray[] = [
               'id'           => @$pedido->id,
               'productos'    => $productos, 
               'fechaPedido' => @Carbon::parse($pedido->fecha_pedido)->format('d/m/Y'),
               'fechaAutorizacion' => isset($pedido->fecha_autorizacion) ? @Carbon::parse($pedido->fecha_autorizacion)->format('d/m/Y') : "",
               'proveedor'    => @$pedido->proveedor->razon_social,
               'cantidad'     => $cantidad,
               'comentarios'  => @$pedido->comentarios,
               'estatusProceso' => @$pedido->estatus_proceso,
               'estatus'        => @$pedido->estatus
           ];
       }
       
       return json_encode(['data' => $pedidoDetalleArray]);
   }


    /**
     * Display the specified resource.
     *
     */
    public function show(TiendaPedido $pedido)
    {
        $impuestos = TiendaPedidoImpuesto::where('pedido_id',$pedido->id)->get();
        $impuestosTotales = 0;
        $subtotal = 0;
        foreach($pedido->pedidoDetalle as $pedidoDetalle){
            $subtotal += $pedidoDetalle->subtotal;
        }

        foreach($impuestos as $impuesto){
            $impuestosTotales += $impuesto->total;
        }

        $total = ($subtotal + $impuestosTotales);
        return view('pedidos.view',[
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'total' => $total,
            'impuestos' => $impuestos
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEstatus(Request $request, $id){
        try{
            $pedido          = TiendaPedido::find($id);
            $pedido->estatus = $request->estatus;
            $pedido->save();

            $pedidoDetalles = TiendaPedidoDetalle::where('pedido_id',$id)->get();

            foreach($pedidoDetalles as $pedidoDetalle){
                $producto          = TiendaProducto::find($pedidoDetalle->producto_id);
                $producto->stock   = $request->estatus ? $producto->stock + $pedidoDetalle->cantidad : $producto->stock - $pedidoDetalle->cantidad;
                $producto->save();
            }
            return json_encode(['result' => 'Success']);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => 'Success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaPedido $pedido)
    {
        // $impuestos = TiendaPedidoImpuesto::where('pedido_id',$pedido->id)->get();
        $productosImpuestos = TiendaProductoImpuesto::get()->toArray();

        $impuestos = TiendaImpuesto::with(['tiendaPedidoImpuesto' => function ($query) use($pedido) {
            $query->where('pedido_id',$pedido->id);
        }])->where('estatus',1)
            ->get();

        // dd($impuestos[0]->tiendaPedidoImpuesto->total);

        if($pedido->estatus_proceso){
            return view('pedidos.index');
        }
        
        $proveedores = TiendaProveedor::where('estatus',1)->get();
        $productos   = TiendaProducto::where('estatus',1)->get()->toArray();

        // dd($pedido->proveedor_id);

        return view('pedidos.edit',[
            'pedido' => $pedido,
            'proveedores' => $proveedores,
            'productos' => $productos,
            'impuestos' => $impuestos,
            'productosImpuestos' => $productosImpuestos
        ]);
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
        $Productos = new TiendaProductoController();

        DB::beginTransaction();
        try{
            $pedido = TiendaPedido::find($id);
            $pedido->proveedor_id    = mb_strtoupper($request->proveedor);
            $pedido->comentarios     = mb_strtoupper($request->comentarios);
            $pedido->fecha_pedido    = $request->fecha;
            $pedido->save();

            TiendaPedidoDetalle::where('pedido_id', $pedido['id'])->delete();

            foreach($request->pedidoProductos as $pedidoProducto){
                // $Productos->updateFechaMovimientoStock($pedidoProducto['productoId'], 'ultima_entrada');
                TiendaPedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'CPU'         =>  $pedidoProducto['costo'],
                    'IPU_total'   =>  $this->getTotalImpuestosPorUnidad($pedidoProducto['impuestosPU']),
                    'subtotal'    =>  (float)$pedidoProducto['cantidad']*$pedidoProducto['costo'],
                ]);
                
                // $producto          = TiendaProducto::find($pedidoProducto['productoId']);
                // $producto->stock   = $producto->stock + $pedidoProducto['cantidad'];
                // $producto->save();
            }

            $pedido->save();

            DB::commit();

            return json_encode(
                [
                    'result' => 'Success',
                    'id' => $pedido['id'],
                    'pedido' => $pedido
                ]
            );
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    public function getTotalImpuestosPorUnidad($impuestosPU){
        $impuestosTotales = 0;
        foreach($impuestosPU as $impuestoPU){
            $impuestosTotales = $impuestoPU['impuesto'];
        }
        return $impuestosTotales;
    }

    // private function removeProductoStock($pedidoId){
    //     $pedidoDetalles = TiendaPedidoDetalle::where('pedido_id',$pedidoId)->get();
    //     foreach($pedidoDetalles as $pedidoDetalle){
    //         $producto          = TiendaProducto::find($pedidoDetalle['producto_id']);
    //         $producto->stock   = $producto->stock - $pedidoDetalle['cantidad'];
    //         $producto->save();
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    } 
}
