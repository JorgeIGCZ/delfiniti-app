<?php

namespace App\Http\Controllers;

use App\Models\ActividadHorario;
use App\Models\Reservacion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class DisponibilidadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fechaActividades    = date('Y-m-d');
        $actividadesHorarios = $this->getActividadesHorarios($fechaActividades);
        
        return view("disponibilidad.index",['actividadesHorarios' => $actividadesHorarios,'fechaActividades' => $fechaActividades]);
    }

    public function getActividadesHorarios($fechaActividades){
        //$fechaActividades = ($fechaActividades != null) ? $fechaActividades : date('Y-m-d');
        DB::enableQueryLog();
        
        $actividadesHorarios = ActividadHorario::whereHas('actividad', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' >= fecha_inicial")
                ->whereRaw(" '$fechaActividades' <= fecha_final")
                ->orWhere('duracion','indefinido');
        })->orWhereHas('reservacionDetalle', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' = actividad_fecha");
        })->get()->groupBy('horario_inicial');
        
        /*
        $fechaActividades = "2022-06-19";
        $actividadesHorarios = ActividadHorario::with(['query' => function($query) {
            $query
                ->whereRaw(" '2022-06-09' = actividad_fecha");
        }])->get()->groupBy('horario_inicial');
        */
        /*
        $actividadesHorarios = ActividadHorario::whereHas('actividad', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' >= fecha_inicial")
                ->whereRaw(" '$fechaActividades' <= fecha_final")
                ->orWhere('duracion','indefinido');
        })->orWhereHas('reservacionDetalle', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' = actividad_fecha");
        })->get()->groupBy('horario_inicial');
        */
        
        //dd(DB::getQueryLog());
        //dd($actividadesHorarios['10:30:00'][0]->reservacionDetalle);
        //dd($actividadesHorarios);

        return $actividadesHorarios;
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $actividadesHorarios = $this->getActividadesHorarios($request->fecha_actividades);

        return redirect()->route("disponibilidad")->with(['actividadesHorarios' => $actividadesHorarios,'fechaActividades' => "2022-06-07"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
