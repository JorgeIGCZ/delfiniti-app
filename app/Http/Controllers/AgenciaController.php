<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use Illuminate\Http\Request;

class AgenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('agencias.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = Agencia::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'comision' => $request->comision,
            'iva' => $request->iva,
            'representante' => $request->representante,
            'direcion' => $request->direcion,
            'telefono' => $request->telefono
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Agencia Guardado" : "Error"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {   
        if(is_null($id)){
            $agencias = Agencia::all();
            return json_encode(['data' => $agencias]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if($id > 0){
            $agencia = Agencia::where('id', $id)->first();
            return view('agencias.edit',['agencia' => $agencia]);
        }
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
        $agencia           = Agencia::find($id);
        $agencia->codigo   = $request->codigo;
        $agencia->nombre   = $request->nombre;
        $agencia->comision = $request->comision;
        $agencia->iva      = $request->iva;
        $agencia->representante = $request->representante;
        $agencia->direcion = $request->direcion;
        $agencia->telefono = $request->telefono;
        $agencia->save();

        return redirect()->route("agencias")->with(["result" => "Agencia actualizado"]);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = Agencia::destroy($id);
        return json_encode(['result' => ($result) ? "Agencia eliminado" : "Error"]);
    }
}