<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservacion;
use App\Classes\CustomErrorHandler;

class CheckinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('checkin.index');
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
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function show(Reservacion  $reservacion = null)
    {   
        if(is_null($reservacion)){
            $reservaciones           = Reservacion::where('fecha',date("Y-m-d"))->where('estatus',1)->orderByDesc('id')->get();
            $reservacionDetalleArray = [];
            foreach($reservaciones as $reservacion){
                $numeroPersonas = 0;
                $horario        = "";
                $actividades    = "";
                foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                    $numeroPersonas += $reservacionDetalle->numero_personas;
                    $horario         = ($horario != "" ? $horario.", " : "").@$reservacionDetalle->horario->horario_inicial;
                    $actividades     = ($actividades != "" ? $actividades.", " : "").@$reservacionDetalle->actividad->nombre;
                }
                $reservacionDetalleArray[] = [
                    'id'           => @$reservacion->id,
                    'folio'        => @$reservacion->folio,
                    'actividad'    => $actividades,
                    'horario'      => $horario,
                    'fechaCreacion' => @$reservacion->fecha_creacion,
                    'fecha'        => @$reservacion->fecha,
                    'cliente'      => @$reservacion->nombre_cliente,
                    'personas'     => $numeroPersonas,
                    'notas'        => @$reservacion->comentarios,
                    'checkin'      => @$reservacion->check_in
                ];
            }   
            return json_encode(['data' => $reservacionDetalleArray]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function registroVisita(Request $request, $id){
        $CustomErrorHandler = new CustomErrorHandler();
        try{
            $comisiones            = new ComisionController();
            $Alojamiento           = Reservacion::find($id);
            $Alojamiento->check_in = $request->estatus;
            $Alojamiento->save();

            $comisiones->setComisiones($id);
        } catch (\Exception $e){
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => 'Success']);
    }

    public function setCheckin($reservacion){
        $reservaciones  = new ReservacionController();
        $comisiones     = new ComisionController();
        $estatusPago    = $reservaciones->getEstatusPagoReservacion($reservacion['id']);
        $fechaActividad = $reservacion['fecha'];
        $today          = date("Y-m-d");
        if($estatusPago == 2 && $fechaActividad <= $today){
            $reservacion           = Reservacion::find($reservacion['id']);
            $reservacion->check_in = 1;
            $reservacion->save();
            $comisiones->setComisiones($reservacion['id']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function edit(Reservacion $reservacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reservacion $reservacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reservacion  $reservacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reservacion $reservacion)
    {
        //
    }
}
