<?php

namespace App\Http\Controllers;

use App\Models\ActividadHorario;
use App\Models\Reservacion;
use App\Models\ReservacionDetalle;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\HasMany;
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
        $fechaActividades    = (is_null($request->fecha_actividades) ? date('Y-m-d') : $request->fecha_actividades);
        $actividadesHorarios = $this->getActividadesHorarios($fechaActividades);
        
        DB::enableQueryLog();
        return view("disponibilidad.index",[
            'actividadesHorarios' => $actividadesHorarios,
            'fechaActividades'    => $fechaActividades
        ]);
    }

    public function getActividadesHorarios($fechaActividades){
        DB::enableQueryLog();
        $actividadesHorarios = ActividadHorario::whereHas('actividad', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' >= fecha_inicial")
                ->whereRaw(" '$fechaActividades' <= fecha_final")
                ->orWhere('duracion','indefinido');
        })->with(['reservacionDetalle' => function ($query) use ($fechaActividades) {
            $query->where('actividad_fecha', "{$fechaActividades}");
        }])->orderBy('horario_inicial', 'asc')->get()->groupBy('horario_inicial');

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
