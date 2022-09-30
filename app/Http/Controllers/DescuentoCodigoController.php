<?php

namespace App\Http\Controllers;

use App\Models\DescuentoCodigo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class DescuentoCodigoController extends Controller
{
    public function __construct() {
        $this->middleware('permission:CodigosDescuento.index')->only('index');
        $this->middleware('permission:CodigosDescuento.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('descuentocodigos.index');
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
            $codigodescuento = DescuentoCodigo::create([
                'codigo'        => $request->codigo,
                'nombre'        => strtoupper($request->nombre),
                'tipo'          => $request->tipo,
                'descuento'     => $request->descuento
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($codigodescuento['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\DescuentoCodigo  $codigodescuento
     * @return \Illuminate\Http\Response
     */
    public function show(DescuentoCodigo $descuentocodigo = null)
    {
        if(is_null($descuentocodigo)){
            $descuentocodigos      = DescuentoCodigo::all();
            $descuentocodigosArray = [];
            foreach ($descuentocodigos as $descuentocodigo) {
                $descuentocodigosArray[] = [
                    'id'           => $descuentocodigo->id,
                    'nombre'       => $descuentocodigo->nombre,
                    'tipo'         => $descuentocodigo->tipo,
                    'descuento'    => $descuentocodigo->descuento,
                    'estatus'      => $descuentocodigo->estatus,
                ];
            }
            return json_encode(['data' => $descuentocodigosArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DescuentoCodigo  $codigo
     * @return \Illuminate\Http\Response
     */
    public function edit(DescuentoCodigo $descuentocodigo)
    {
        return view('descuentocodigos.edit',['descuentocodigo' => $descuentocodigo]);
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
        try {
            $codigodescuento           = DescuentoCodigo::find($id);
            $codigodescuento->nombre   = strtoupper($request->nombre);
            $codigodescuento->tipo     = $request->tipo;
            $codigodescuento->descuento= $request->descuento;
            $codigodescuento->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("descuentocodigos.index")->with(["result" => "Codigo actualizado"]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
            $Alojamiento          = DescuentoCodigo::find($id);
            $Alojamiento->estatus = $request->estatus;
            $Alojamiento->save();
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
     * @param  codigodescuento  $codigodescuento
     * @return \Illuminate\Http\Response
     */
    public function destroy(DescuentoCodigo $descuentocodigo)
    {
        $result = $descuentocodigo->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}