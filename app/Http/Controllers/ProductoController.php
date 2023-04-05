<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Impuesto;
use App\Models\Producto;
use App\Models\ProductoImpuesto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $impuestos = Impuesto::get();
        return view('productos.index',['impuestos' => $impuestos]);
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
                if(count(Producto::
                    where('clave',$request->clave)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
                }
            }

            if(isset($request->codigo)){
                if(count(Producto::
                    where('codigo',$request->codigo)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'El código ya se encuentra registrado.']);
                }
            }

            $producto = Producto::create([
                'clave'    => $request->clave,
                'codigo'   => $request->codigo,
                'nombre'   => mb_strtoupper($request->nombre),
                'costo'    => $request->costo,
                'precio_venta'    => $request->precioVenta,
                'stock_minimo'    => $request->stockMinimo,
                'stock_maximo'    => $request->stockMaximo,
                'margen_ganancia' => $request->margenGanancia,
                'comentarios'     => mb_strtoupper($request->comentarios)
            ]);

            foreach($request->impuestos as $impuestos){
                if($impuestos[1]){
                    ProductoImpuesto::create([
                        'producto_id' => $producto['id'],
                        'impuesto_id' => $impuestos[0]
                    ]);
                }
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($producto['id']) ? 'Success' : 'Error','id' => $producto['id']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function show(Producto  $producto = null)
    {
        if(is_null($producto)){
            $producto = Producto::all();
            return json_encode(['data' => $producto]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(Producto  $producto)
    {
        return view('productos.edit',['producto' => $producto]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function update(Producto $producto, Request $request)
    {
        try {
            $producto->codigo          = $request->codigo;
            $producto->nombre          = mb_strtoupper($request->nombre);
            $producto->costo           = floatval(str_replace('$','',$request->costo));
            $producto->precio_venta    = floatval(str_replace('$','',$request->precioVenta));
            $producto->margen_ganancia = floatval(str_replace('%','',$request->margenGanancia));
            $producto->stock_minimo    = $request->stockMinimo;
            $producto->stock_maximo    = $request->stockMaximo;
            $producto->comentarios     = mb_strtoupper($request->comentarios);
            $producto->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("productos.index")->with(["result" => "Producto actualizado"]);
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
            $producto          = Producto::find($id);
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
        $producto          = Producto::find($productoId);
        if($accion === 'ultima_entrada'){
            $producto->ultima_entrada = date('Y-m-d');
        }else{
            $producto->ultima_salida = date('Y-m-d');
        }
        $producto->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Producto $producto)
    {
        $result = $producto->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
