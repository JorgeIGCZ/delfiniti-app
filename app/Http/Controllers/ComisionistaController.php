<?php

namespace App\Http\Controllers;

use App\Models\Comisionista;
use App\Models\ComisionistaTipo;
use Illuminate\Http\Request;

class ComisionistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tipos = ComisionistaTipo::all();
        return view('comisionistas.index',['tipos' => $tipos]);
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
            'representante' => $request->representante,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono
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
            $comisionistasAllArray = [];
            $comisionistasArray = [];
            foreach ($comisionistas as $comisionista) {
                $comisionistasArray[] = [
                    'codigo'       => $comisionista->codigo,
                    'nombre'       => $comisionista->nombre,
                    'tipo'         => $comisionista->tipo->nombre,
                    'iva'          => $comisionista->iva,
                    'comision'     => $comisionista->comision,
                    'representante'=> $comisionista->representante,
                    'direccion'    => $comisionista->direccion,
                    'telefono'     => $comisionista->telefono
                ];
            }
            return json_encode(['data' => $comisionistasArray]);
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
        $comisionista->representante = $request->representante;
        $comisionista->direcion = $request->direcion;
        $comisionista->telefono = $request->telefono;
        $comisionista->save();

        return redirect()->route("comisionistas")->with(["result" => "Comisionista actualizado"]);
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
    }
}