<?php

namespace App\Http\Controllers;

use App\Models\TipoCambio;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class TipoCambioController extends Controller
{
    public function __construct() {
        $this->middleware('permission:TipoCambio.index')->only('index');
        $this->middleware('permission:TipoCambio.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tiposCambio = TipoCambio::all()->groupBy('seccion_uso');
        
        return view('tiposcambio.index',['tiposCambio' => $tiposCambio]);
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
     * @param  \App\Models\TipoCambio  $tipoCambio
     * @return \Illuminate\Http\Response
     */
    public function show(TipoCambio $tipoCambio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TipoCambio  $tipoCambio
     * @return \Illuminate\Http\Response
     */
    public function edit(TipoCambio $tipoCambio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  INT $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $tipoCambio                  = TipoCambio::find($id);
            $tipoCambio->precio_compra   = $request->precio_compra;
            $tipoCambio->precio_venta    = $request->precio_venta;
            $tipoCambio->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return json_encode(['result' => is_numeric($tipoCambio['id']) ? 'Success' : 'Error']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoCambio  $tipoCambio
     * @return \Illuminate\Http\Response
     */
    public function destroy(TipoCambio $tipoCambio)
    {
        //
    }
}
