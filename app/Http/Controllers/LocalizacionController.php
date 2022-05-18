<?php

namespace App\Http\Controllers;

use App\Models\Localizacion;
use Illuminate\Http\Request;

class LocalizacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('localizaciones.index');
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
        $result = Localizacion::create([
            'codigo'   => $request->codigo,
            'nombre'   => $request->nombre,
            'comision' => $request->comision,
            'iva'      => $request->iva,
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Localización Guardada" : "Error"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        if(is_null($id)){
            $localizaciones = Localizacion::all();
            return json_encode(['data' => $localizaciones]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if($id > 0){
            $localizacion = Localizacion::where('id', $id)->first();
            return view('localizaciones.edit',['localizacion' => $localizacion]);
        }
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
        $localizacion           = Localizacion::find($id);
        $localizacion->codigo   = $request->codigo;
        $localizacion->nombre   = $request->nombre;
        $localizacion->comision = $request->comision;
        $localizacion->iva      = $request->iva;
        $localizacion->save();

        return redirect()->route("localizaciones")->with(["result" => "Localización actualizado",]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = Localizacion::destroy($id);
        return json_encode(['result' => ($result) ? "Localización eliminada" : "Error"]);
    }
}
