<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\MovimientoInventario;
use App\Models\TiendaImpuesto;
use App\Models\TiendaProducto;
use App\Models\TiendaProductoImpuesto;
use App\Models\TiendaProveedor;
use Illuminate\Http\Request;

class TiendaProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $impuestos = TiendaImpuesto::get();
        $proveedores = TiendaProveedor::get();
        return view('productos.index',['impuestos' => $impuestos, 'proveedores' => $proveedores]);
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
        try {
            if(isset($request->clave)){
                if(count(TiendaProducto::
                    where('clave',$request->clave)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
                }
            }else{
                return json_encode(['result' => 'Error','message' => 'El código es obligatorio.']);
            }

            if(isset($request->codigo)){
                if(count(TiendaProducto::
                    where('codigo',$request->codigo)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'El código ya se encuentra registrado.']);
                }
            }

            $producto = TiendaProducto::create([
                'clave'    => $request->clave,
                'codigo'   =>  $this->getProductoCodigo($request),
                'proveedor_id' => $request->proveedorId,
                'nombre'   => mb_strtoupper($request->nombre),
                'costo'    => $request->costo,
                'precio_venta'    => $request->precioVenta,
                'stock_minimo'    => $request->stockMinimo,
                'stock_maximo'    => $request->stockMaximo,
                'margen_ganancia' => $request->margenGanancia,
                'comentarios'     => mb_strtoupper($request->comentarios)
            ]);

            if(isset($request->impuestos)){
                foreach($request->impuestos as $impuestos){
                    if($impuestos[1]){
                        TiendaProductoImpuesto::create([
                            'producto_id' => $producto['id'],
                            'impuesto_id' => $impuestos[0]
                        ]);
                    }
                }
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($producto['id']) ? 'Success' : 'Error','id' => $producto['id']]);
    }

    private function getProductoCodigo($request)
    {
        $codigoPrefix = "NOCODIGO";
        return isset($request->codigo) && $request->codigo !== "" ? $request->codigo : sprintf("%s%s",$codigoPrefix,$request->clave);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function get(TiendaProducto $producto = null)
    {
        if(is_null($producto)){
            $producto = TiendaProducto::all();
            return json_encode(['data' => $producto]);
        }
    }

    public function show(TiendaProducto $producto)
    {
        $impuestos = TiendaImpuesto::get();
        $productoImpuestos = TiendaProductoImpuesto::where('producto_id',$producto->id)->get();

        return view('productos.view',['producto' => $producto, 'impuestos' => $impuestos, 'productoImpuestos' => $productoImpuestos]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaProducto $producto)
    {
        $impuestos = TiendaImpuesto::get();
        $proveedores = TiendaProveedor::get();
        $productoImpuestos = TiendaProductoImpuesto::where('producto_id',$producto->id)->get();

        return view('productos.edit',['producto' => $producto,'impuestos' => $impuestos, 'productoImpuestos' => $productoImpuestos, 'proveedores' => $proveedores]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function update(TiendaProducto $producto, Request $request)
    {
        try {
            $producto->codigo          = $request->codigo;
            $producto->proveedor_id    = $request->proveedor;
            $producto->nombre          = mb_strtoupper($request->nombre);
            $producto->costo           = floatval(str_replace(str_split('$,'), '', $request->costo));
            $producto->precio_venta    = floatval(str_replace(str_split('$,'), '', $request->precioVenta));
            $producto->margen_ganancia = floatval(str_replace('%','',$request->margenGanancia));
            $producto->stock_minimo    = $request->stockMinimo;
            $producto->stock_maximo    = $request->stockMaximo;
            $producto->comentarios     = mb_strtoupper($request->comentarios);
            $producto->save();

            TiendaProductoImpuesto::where('producto_id', $producto->id)->delete();
            if(isset($request->impuestos)){
                foreach($request->impuestos as $impuesto){
                    TiendaProductoImpuesto::create([
                        'producto_id' => $producto->id,
                        'impuesto_id' => $impuesto
                    ]);
                }
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("productos.index")->with(["result" => "Producto actualizado"]);
    }

    public function getProductoByProveedor(Request $request){
        $productos = TiendaProducto::where('proveedor_id', $request->proveedorId)->get();
        return json_encode(['result' => $productos]);
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
            $producto          = TiendaProducto::find($id);
            $producto->estatus = $request->estatus;
            $producto->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => 'Success']);
    }

    public function updateFechaMovimientoStock($productoId, $accion){
        $producto          = TiendaProducto::find($productoId);
        if($accion === 'ultima_entrada'){
            $producto->ultima_entrada = date('Y-m-d');
        }else{
            $producto->ultima_salida = date('Y-m-d');
        }
        $producto->save();
    }

    public function updateStock($productoId, $accion, $numeroProductos){
        $producto          = TiendaProducto::find($productoId);
        $producto->stock = ($accion === 'baja' ? $producto->stock - $numeroProductos : $producto->stock + $numeroProductos);
        $producto->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(TiendaProducto $producto)
    {
        $result = $producto->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
