<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaImpuesto;
use Illuminate\Http\Request;

class TiendaImpuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('impuestos.index');
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

            $impuesto = TiendaImpuesto::create([
                'nombre'   => mb_strtoupper($request->nombre),
                'impuesto'    => $request->impuesto
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($impuesto['id']) ? 'Success' : 'Error','id' => $impuesto['id']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TiendaImpuesto  $impuesto
     * @return \Illuminate\Http\Response
     */
    public function show(TiendaImpuesto $impuesto = null)
    {
        if(is_null($impuesto)){
            
            $impuesto = TiendaImpuesto::all();

            return json_encode(['data' => $impuesto]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TiendaImpuesto  $impuesto
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaImpuesto $impuesto)
    {
        return view('impuestos.edit',['impuesto' => $impuesto]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TiendaImpuesto  $impuesto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TiendaImpuesto $impuesto)
    {
        try {
            $impuesto->nombre   = mb_strtoupper($request->nombre);
            $impuesto->impuesto = floatval(str_replace('%','',$request->impuesto));
            $impuesto->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("impuestos.index")->with(["result" => "Impuesto actualizado"]);
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
            $impuesto          = TiendaImpuesto::find($id);
            $impuesto->estatus = $request->estatus;
            $impuesto->save();
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
     * @param  \App\Models\TiendaImpuesto  $impuesto
     * @return \Illuminate\Http\Response
     */
    public function destroy(TiendaImpuesto $impuesto)
    {
        $result = $impuesto->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
