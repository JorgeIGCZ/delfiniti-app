<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comisionista;

class ComisionistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comisionistas.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = Comisionista::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'comision' => $request->comision,
            'iva' => $request->iva,
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Comisionista Guardado" : "Error"]);
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
            $comisionistas = Comisionista::all();
            return json_encode(['data' => $comisionistas]);
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
            $comisionista = Comisionista::where('id', $id)->first();
            return view('comisionistas.edit',['comisionista' => $comisionista]);
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
        $comisionista           = Comisionista::find($id);
        $comisionista->codigo   = $request->codigo;
        $comisionista->nombre   = $request->nombre;
        $comisionista->comision = $request->comision;
        $comisionista->iva      = $request->iva;
        $comisionista->save();

        return redirect()->route("comisionistas")->with(["result" => "Comisionista actualizado",]);
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
        $result = Comisionista::destroy($id);
        return json_encode(['result' => ($result) ? "Comisionista eliminado" : "Error"]);
        //return redirect()->route("comisionistas")->with(["result" => "Comisionista eliminado",]);
    }
}
