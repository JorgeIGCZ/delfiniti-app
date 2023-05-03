<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\FotoVideoProducto;
use Illuminate\Http\Request;

class FotoVideoProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $impuestos = Impuesto::get();
        // $proveedores = Proveedor::get();
        return view('fotovideoproductos.index');
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
                if(count(FotoVideoProducto::
                    where('clave',$request->clave)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
                }
            }

            if(isset($request->codigo)){
                if(count(FotoVideoProducto::
                    where('codigo',$request->codigo)->get()
                ) > 0){
                    return json_encode(['result' => 'Error','message' => 'El código ya se encuentra registrado.']);
                }
            }

            $producto = FotoVideoProducto::create([
                'clave'    => $request->clave,
                'nombre'   => mb_strtoupper($request->nombre),
                'precio_venta'    => $request->precioVenta,
                'comentarios'     => mb_strtoupper($request->comentarios)
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
     * @param  \App\Models\FotoVideoProducto  $fotoVideoProducto
     * @return \Illuminate\Http\Response
     */
    public function show(FotoVideoProducto $fotoVideoProducto = null)
    {
        if(is_null($fotoVideoProducto)){
            $producto = FotoVideoProducto::all();
            return json_encode(['data' => $producto]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FotoVideoProducto $fotoVideoProducto
     * @return \Illuminate\Http\Response
     */
    public function edit(FotoVideoProducto $fotoVideoProducto)
    {
        // $impuestos = Impuesto::get();
        // $proveedores = Proveedor::get();
        // $productoImpuestos = ProductoImpuesto::where('producto_id',$producto->id)->get();
        
        return view('fotovideoproductos.edit',['producto' => $fotoVideoProducto]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FotoVideoProducto  $fotoVideoProducto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FotoVideoProducto $fotoVideoProducto)
    {
        try {
            $fotoVideoProducto->nombre          = mb_strtoupper($request->nombre);
            $fotoVideoProducto->precio_venta    = floatval(str_replace('$','',$request->precioVenta));
            $fotoVideoProducto->comentarios     = mb_strtoupper($request->comentarios);
            $fotoVideoProducto->save();

            // if(isset($request->impuestos)){
            //     ProductoImpuesto::where('producto_id', $fotoVideoProducto->id)->delete();
            //     foreach($request->impuestos as $impuesto){
            //         ProductoImpuesto::create([
            //             'producto_id' => $fotoVideoProducto->id,
            //             'impuesto_id' => $impuesto
            //         ]);
            //     }
            // }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("fotovideoproductos.index")->with(["result" => "Producto actualizado"]);
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
            $producto          = FotoVideoProducto::find($id);
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
     * @param  \App\Models\FotoVideoProducto  $fotoVideoProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy(FotoVideoProducto $fotoVideoProducto)
    {
        //
    }
}
