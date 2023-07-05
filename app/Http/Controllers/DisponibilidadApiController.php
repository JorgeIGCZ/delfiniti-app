<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Actividad;
use App\Models\ReservacionDetalle;
use Illuminate\Database\Eloquent\Builder;

class DisponibilidadApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $actividadesHorarios = ActividadHorario::whereHas('actividad', function (Builder $query) {
        //     $query
        //         ->whereRaw('NOW() >= fecha_inicial')
        //         ->whereRaw('NOW() <= fecha_final')
        //         ->orWhere('duracion','indefinido');
        //    })->get()->groupBy('horario_inicial');

        $actividades = Actividad::with(['horarios' => function ($query) {
            $query->where('estatus',1)
            ->orderBy('horario_inicial', 'ASC');
        }])->whereRaw('NOW() >= fecha_inicial')
            ->whereRaw('NOW() <= fecha_final')
            ->orWhere('duracion','indefinido')
            ->whereRaw('estatus = 1')
            ->orderBy('nombre', 'ASC')
            ->get(); 
        
        $actividadesHorarios = [];
        foreach ($actividades as $key => $value) {
            $actividadesHorarios[] = ['actividad'=>$value,'horarios'=>$value->horarios];
        }
        
        return response()->json([
            'status' => true,
            'message' => "Success",
            'disponibilidad' => $actividadesHorarios

        ],200);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actividadDisponibilidad($id,$fecha,$horario)
    {
        $actividad = new ActividadController();

        $actividadDisponibilidad = $actividad->getActividadDisponibilidad($id,$fecha,$horario);
        return response()->json([
            'status' => true,
            'message' => "Success",
            'disponibilidad' => ($actividadDisponibilidad)

        ],200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
