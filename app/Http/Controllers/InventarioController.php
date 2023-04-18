<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function __construct() {
        $this->middleware('role:Administrador')->only('edit'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Producto $inventario)
    {
        return view('inventario.edit',['producto' => $inventario]);
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
        try{
            $Productos = new ProductoController();
            $Productos->updateStock($id, $request->accion, $request->numeroProductos);

            MovimientoInventario::create([
                'producto_id' => $id,
                'accion' => $request->accion,
                'usuario_id' => auth()->user()->id,
                'comentarios' => $request->comentarios
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
        }
        return redirect()->route("productos.index")->with(["result" => "Inventario actualizado"]);
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
