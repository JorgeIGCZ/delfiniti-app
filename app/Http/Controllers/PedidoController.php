<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
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
        $proveedores = Proveedor::where('estatus',1)->get();
        $productos = Producto::where('estatus',1)->get()->toArray();

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
        $Productos = new ProductoController();

        DB::beginTransaction();
        try{
            $pedido = Pedido::create([
                'proveedor_id'  => mb_strtoupper($request->proveedor),
                'comentarios'   => mb_strtoupper($request->comentarios),
                'fecha'           => $request->fecha,
                'fecha_creacion'  => date('Y-m-d') 
            ]);

            foreach($request->pedidoProductos as $pedidoProducto){

                $Productos->updateFechaMovimientoStock($pedidoProducto['productoId'], 'ultima_entrada');
                PedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'PPU'         =>  $pedidoProducto['costo'],
                    'subtotal'    =>  (float)$pedidoProducto['cantidad']*$pedidoProducto['costo'],
                ]);
                
                $producto          = Producto::find($pedidoProducto['productoId']);
                $producto->stock   = $producto->stock + $pedidoProducto['cantidad'];
                $producto->save();
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

        
        DB::enableQueryLog();
        $pedidos = Pedido::whereBetween("fecha", [$fechaInicio,$fechaFinal])->orderByDesc('id')->get();
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
                'fechaCreacion' => @Carbon::parse($pedido->fecha_creacion)->format('d/m/Y'),//date_format(date_create($reservacion->fecha_creacion),"d/m/Y"),
                'fecha'        => @Carbon::parse($pedido->fecha)->format('d/m/Y'),//date_format(date_create($reservacion->fecha),"d-m-Y"),
                'proveedor'    => @$pedido->proveedor->razon_social,
                'cantidad'     => $cantidad,
                'comentarios'  => @$pedido->comentarios,
                'estatus'      => @$pedido->estatus
            ];
        }
        
        return json_encode(['data' => $pedidoDetalleArray]);
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
            $pedido          = Pedido::find($id);
            $pedido->estatus = $request->estatus;
            $pedido->save();

            $pedidoDetalles = PedidoDetalle::where('pedido_id',$id)->get();

            foreach($pedidoDetalles as $pedidoDetalle){
                $producto          = Producto::find($pedidoDetalle->producto_id);
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
    public function edit(Pedido $pedido)
    {
        $proveedores = Proveedor::where('estatus',1)->get();
        $productos   = Producto::where('estatus',1)->get()->toArray();

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
        $Productos = new ProductoController();

        DB::beginTransaction();
        try{
            $pedido = Pedido::find($id);
            $pedido->proveedor_id    = mb_strtoupper($request->proveedor);
            $pedido->comentarios     = mb_strtoupper($request->comentarios);
            $pedido->fecha           = $request->fecha;
            $pedido->save();

            $this->removeProductoStock($pedido['id']);
            PedidoDetalle::where('pedido_id', $pedido['id'])->delete();

            foreach($request->pedidoProductos as $pedidoProducto){

                $Productos->updateFechaMovimientoStock($pedidoProducto['productoId'], 'ultima_entrada');
                PedidoDetalle::create([
                    'pedido_id'   =>  $pedido['id'],
                    'producto_id' =>  $pedidoProducto['productoId'],
                    'cantidad'    =>  $pedidoProducto['cantidad'],
                    'PPU'         =>  $pedidoProducto['costo'],
                    'subtotal'    =>  (float)$pedidoProducto['cantidad']*$pedidoProducto['costo'],
                ]);
                
                $producto          = Producto::find($pedidoProducto['productoId']);
                $producto->stock   = $producto->stock + $pedidoProducto['cantidad'];
                $producto->save();
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

    private function removeProductoStock($pedidoId){
        $pedidoDetalles = PedidoDetalle::where('pedido_id',$pedidoId)->get();
        foreach($pedidoDetalles as $pedidoDetalle){
            $producto          = Producto::find($pedidoDetalle['producto_id']);
            $producto->stock   = $producto->stock - $pedidoDetalle['cantidad'];
            $producto->save();
        }
    }

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
