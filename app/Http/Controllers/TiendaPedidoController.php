<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaPedido;
use App\Models\TiendaPedidoDetalle;
use App\Models\TiendaProducto;
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

        return view('pedidos.create',['pedido' => $pedido,'proveedores' => $proveedores,'productos' => $productos]);
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
                'fecha'           => $request->fecha,
                'fecha_creacion'  => date('Y-m-d') 
            ]);

            foreach($request->pedidoProductos as $pedidoProducto){
                TiendaPedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'PPU'         =>  $pedidoProducto['costo'],
                    'subtotal'    =>  (float)$pedidoProducto['cantidad']*$pedidoProducto['costo'],
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
            $pedido = TiendaPedido::find($id);

            foreach($pedido->pedidoDetalle as $pedidoDetalle){
                $Productos->updateFechaMovimientoStock($pedidoDetalle->producto_id, 'ultima_entrada');

                $producto          = TiendaProducto::find($pedidoDetalle->producto_id);
                $producto->stock   = $producto->stock + $pedidoDetalle->cantidad;
                $producto->save();   
            }

            $pedido->estatus_proceso = 1;
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
            $pedidos = $pedidos->whereBetween("fecha", [$fechaInicio, $fechaFinal]);
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
               'fechaCreacion' => @Carbon::parse($pedido->fecha_creacion)->format('d/m/Y'),
               'fecha'        => @Carbon::parse($pedido->fecha)->format('d/m/Y'),
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
        $subtotal = 0;
        foreach($pedido->pedidoDetalle as $pedidoDetalle){
            $subtotal += $pedidoDetalle->subtotal;
        }
        $total = $subtotal;
        return view('pedidos.view',['pedido' => $pedido, 'subtotal' => $subtotal, 'total' => $total]);
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
        if($pedido->estatus_proceso){
            return view('pedidos.index');
        }
        
        $proveedores = TiendaProveedor::where('estatus',1)->get();
        $productos   = TiendaProducto::where('estatus',1)->get()->toArray();

        return view('pedidos.edit',['pedido' => $pedido,'proveedores' => $proveedores,'productos' => $productos]);
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
            $pedido->fecha           = $request->fecha;
            $pedido->save();

            TiendaPedidoDetalle::where('pedido_id', $pedido['id'])->delete();

            foreach($request->pedidoProductos as $pedidoProducto){
                // $Productos->updateFechaMovimientoStock($pedidoProducto['productoId'], 'ultima_entrada');
                TiendaPedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'PPU'         =>  $pedidoProducto['costo'],
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
