<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class ActividadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('actividades.index');
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
            $actividad = Actividad::create([
                'clave'           => $request->clave,
                'nombre'          => $request->nombre,
                'precio'       => $request->precio,
                'capacidad'       => $request->capacidad,
                'duracion'        => $request->duracion,
                'fecha_inicial'   => $request->fechaInicial,
                'fecha_final'     => $request->fechaFinal,
            ]);        
            for ($i=0; $i < count($request->horarioInicial); $i++) { 
                $actividadHorario = ActividadHorario::create([
                    'actividad_id'    => $actividad['id'],
                    'horario_inicial' => $request->horarioInicial[$i],
                    'horario_final'   => $request->horarioFinal[$i],
                    'duracion'        => $request->duracion
                ]);
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($actividadHorario['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Actividad  $actividad = null)
    {   
        if(is_null($actividad)){
            $actividades = Actividad::all();
            return json_encode(['data' => $actividades]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Actividad  $actividad)
    {
        return view('actividades.edit',['actividad' => $actividad]);
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
        try{
            $actividad                  = Actividad::find($id);
            $actividad->nombre          = $request->nombre;
            $actividad->precio          = $request->precio;
            $actividad->capacidad       = $request->capacidad;
            $actividad->horario_inicial = $request->horario_inicial;
            $actividad->horario_final   = $request->horario_final;
            $actividad->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return json_encode(['result' => is_numeric($actividad['id']) ? 'Success' : 'Error']);
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
    public function destroy(Actividad  $actividad)
    {
        $result = $actividad->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}