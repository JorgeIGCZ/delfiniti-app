<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Producto;
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
        return view('productos.index');
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
            if(isset($request->codigo)){
                if(count(Producto::
                    where('codigo',$request->codigo)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
                }
            }

            $producto = Producto::create([
                'clave'    => $request->clave,
                'codigo'   => $request->codigo,
                'costo'    => 0,
                'nombre'   => strtoupper($request->nombre),
                'precio_venta'    => $request->precioVenta,
            ]);
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
    public function update(Request $request, $id)
    {
        try {
            $producto            = Producto::find($id);
            $producto->nombre    = strtoupper($request->nombre);
            // $producto->costo     = $request->costo;
            $producto->precio_venta = strtoupper($request->precioVenta);
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
