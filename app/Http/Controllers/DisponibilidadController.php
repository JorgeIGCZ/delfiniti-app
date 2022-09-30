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
    public function __construct() {
        $this->middleware('permission:Disponibilidad.index')->only('index'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fechaActividades    = (is_null($request->fecha_actividades) ? date('Y-m-d') : $request->fecha_actividades);
        $actividadesHorarios = $this->getActividadesHorarios($fechaActividades);

        $reservaciones       = Reservacion::where('fecha',$fechaActividades)->where('estatus',1)->get();
        $reservacionesPersonas = 0;
        foreach($reservaciones as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $reservacionesPersonas += $reservacionDetalle->numero_personas;
            }
        }
        
        $reservacionesPendientes= Reservacion::where('fecha',$fechaActividades)->where('estatus',1)->whereRaw('estatus_pago IN (0,1)')->get();
        $reservacionesPendientesPersonas = 0;
        foreach($reservacionesPendientes as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $reservacionesPendientesPersonas += $reservacionDetalle->numero_personas;
            }
        }

        $cortesias           = Reservacion::where('fecha',$fechaActividades)->where('estatus',1)->whereHas('descuentoCodigo', function (Builder $query) {
            $query
                ->whereRaw("nombre LIKE '%CORTESIA%' ");
        })->get();
        $cortesiasPersonas = 0;
        foreach($cortesias as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $cortesiasPersonas += $reservacionDetalle->numero_personas;
            }
        }


        $reservacionesPagadas= Reservacion::where('fecha',$fechaActividades)->where('estatus',1)->where('estatus_pago',2)->get();
        $reservacionesPagadasPersonas = 0;
        $reservacionesPagadasSinCortesiasPersonas = 0;
        foreach($reservacionesPagadas as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $reservacionesPagadasPersonas += $reservacionDetalle->numero_personas;
            }
        }
        $reservacionesPagadasSinCortesiasPersonas = $reservacionesPagadasPersonas - $cortesiasPersonas;
        
        return view("disponibilidad.index",[
            'actividadesHorarios' => $actividadesHorarios,
            'fechaActividades'    => $fechaActividades,
            'reservaciones'       => $reservacionesPersonas,
            'reservacionesPagadas'=> $reservacionesPagadasSinCortesiasPersonas,
            'reservacionesPendientes'=> $reservacionesPendientesPersonas,
            'cortesias'           => $cortesiasPersonas
        ]);
    }

    public function getActividadesHorarios($fechaActividades){
        DB::enableQueryLog();
        $actividadesHorarios = ActividadHorario::whereHas('actividad', function (Builder $query) use ($fechaActividades) {
            $query
                ->whereRaw(" '$fechaActividades' >= fecha_inicial")
                ->whereRaw(" '$fechaActividades' <= fecha_final")
                ->orWhere('duracion','indefinido')
                ->whereRaw('estatus = 1');
        })->with(['reservacion' => function ($query) use ($fechaActividades) {
                $query->where('fecha', "{$fechaActividades}")
                ->where('estatus',1);
        }])->orderBy('horario_inicial', 'asc')->orderBy('id', 'asc')->get()->groupBy('horario_inicial');
/*
->with(['reservacion' => function ($query) use ($fechaActividades) {
                $query->where('fecha', "{$fechaActividades}");
        }])
        */
        //print_r($fechaActividades);
        //echo("<pre>");
        //dd($actividadesHorarios['08:00:00'][0]);
        //dd($actividadesHorarios['08:00:00'][0]->reservacion[0]->reservacionDetalle);
        //dd($actividadesHorarios['08:00:00'][0]->reservacionDetalle[1]->reservacion);
        //dd(DB::getQueryLog());

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
