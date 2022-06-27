<?php

namespace App\Http\Controllers;

use App\Models\Localizacion;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

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
        try {
            $localizacion = Localizacion::create([
                'codigo'    => $request->codigo,
                'nombre'    => $request->nombre,
                'direccion' => $request->direccion,
                'telefono'  => $request->telefono,
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($localizacion['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function show(Localizacion  $localizacion = null)
    {
        if(is_null($localizacion)){
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
    public function edit(Localizacion  $localizacion)
    {
        return view('localizaciones.edit',['localizacion' => $localizacion]);
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
        try {
            $localizacion            = Localizacion::find($id);
            $localizacion->codigo    = $request->codigo;
            $localizacion->nombre    = $request->nombre;
            $localizacion->direccion = $request->direccion;
            $localizacion->telefono  = $request->telefono;
            $localizacion->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return json_encode(['result' => is_numeric($localizacion['id']) ? 'Success' : 'Error']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Localizacion $localizacion)
    {
        $result = $localizacion->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
