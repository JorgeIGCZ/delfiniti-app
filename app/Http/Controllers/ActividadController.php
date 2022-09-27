<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\ReservacionDetalle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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

    public function isDisponible($request){
        foreach($request->reservacionArticulos as $reservacionArticulo){
            $actividadDisponibilidad = $this->getActividadDisponibilidad($reservacionArticulo['actividad'],$request->fecha,$reservacionArticulo['horario']);
            if($actividadDisponibilidad < $reservacionArticulo['cantidad']){
                return false;
            }
        }
        return true;
    }

    public function getActividadDisponibilidad($id,$fecha,$horario){
        $capacidad           = Actividad::where('id',$id)->first()->capacidad;
        $numeroReservaciones = 0;
        $reservacionDetalles = ReservacionDetalle::whereHas('reservacion', function (Builder $query) use ($fecha) {
            $query
                ->where('fecha',"{$fecha}")
                ->where('estatus',1);
            })
            ->where('actividad_id',$id)
            ->where('actividad_horario_id',$horario)
            ->get();
        
        foreach($reservacionDetalles as $reservacionDetalle){
            $numeroReservaciones += $reservacionDetalle->numero_personas;
        }
        
        return ($capacidad-$numeroReservaciones);
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
            if(count(Actividad::
                where('clave',$request->clave)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $actividad = Actividad::create([
                'clave'           => $request->clave,
                'nombre'          => strtoupper($request->nombre),
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
                    'horario_final'   => $request->horarioFinal[$i]
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
        $actividadHorarios = ActividadHorario::where('actividad_id',$actividad['id'])->orderBy('horario_inicial', 'asc')->get();
        return view('actividades.edit',['actividad' => $actividad,'actividadHorarios' => $actividadHorarios]);
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
            $actividad->nombre          = strtoupper($request->nombre);
            $actividad->precio          = $request->precio;
            $actividad->capacidad       = $request->capacidad;
            $actividad->save();

            ActividadHorario::where('actividad_id',$id)->delete();

            for ($i=0; $i < count($request->horario_inicial); $i++) { 
                ActividadHorario::create([
                    'actividad_id'    => $id,
                    'horario_inicial' => $request->horario_inicial[$i],
                    'horario_final'   => $request->horario_final[$i]
                ]);
            }
            
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("actividades.index")->with(["result" => "Actividad actualizada"]);
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
            $actividad          = Actividad::find($id);
            $actividad->estatus = $request->estatus;
            $actividad->save();
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Actividad  $actividad)
    {
        $result = $actividad->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}