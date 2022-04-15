<?php

namespace App\Http\Controllers;

use App\Models\Agenciacredito;
use Illuminate\Http\Request; 

class AgenciacreditoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('agenciascredito.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = Agenciacredito::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'comision' => $request->comision,
            'iva' => $request->iva,
            'representante' => $request->representante,
            'direcion' => $request->direcion,
            'telefono' => $request->telefono
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Agencia Guardada" : "Error"]);
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
            $agenciascredito = Agenciacredito::all();
            return json_encode(['data' => $agenciascredito]);
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
            $Agenciacredito = Agenciacredito::where('id', $id)->first();
            return view('agenciascredito.edit',['Agenciacredito' => $Agenciacredito]);
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
        $agenciacredito           = Agenciacredito::find($id);
        $agenciacredito->codigo   = $request->codigo;
        $agenciacredito->nombre   = $request->nombre;
        $agenciacredito->comision = $request->comision;
        $agenciacredito->iva      = $request->iva;
        $agenciacredito->representante = $request->representante;
        $agenciacredito->direcion = $request->direcion;
        $agenciacredito->telefono = $request->telefono;
        $agenciacredito->save();

        return redirect()->route("agenciascredito")->with(["result" => "Agencia credito actualizada",]);
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
        $result = Agenciacredito::destroy($id);
        return json_encode(['result' => ($result) ? "Agencia eliminada" : "Error"]);
    }
}