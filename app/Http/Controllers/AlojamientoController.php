<?php

namespace App\Http\Controllers;

use App\Models\Alojamiento;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class AlojamientoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('alojamientos.index');
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
            if(count(Alojamiento::
                where('codigo',$request->codigo)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $alojamiento = Alojamiento::create([
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
        return json_encode(['result' => is_numeric($alojamiento['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function show(Alojamiento  $alojamiento = null)
    {
        if(is_null($alojamiento)){
            $alojamiento = Alojamiento::all();
            return json_encode(['data' => $alojamiento]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Alojamiento  $alojamiento
     * @return \Illuminate\Http\Response
     */
    public function edit(Alojamiento  $alojamiento)
    {
        return view('alojamientos.edit',['alojamiento' => $alojamiento]);
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
            $alojamiento            = Alojamiento::find($id);
            $alojamiento->nombre    = $request->nombre;
            $alojamiento->direccion = $request->direccion;
            $alojamiento->telefono  = $request->telefono;
            $alojamiento->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("alojamientos.index")->with(["result" => "Alojamiento actualizado"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localizacion  $localizacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Alojamiento $alojamiento)
    {
        $result = $alojamiento->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
