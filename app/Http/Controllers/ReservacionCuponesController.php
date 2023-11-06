<?php

namespace App\Http\Controllers;

use App\Models\Comisionista;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReservacionCuponesController extends Controller
{
    public function __construct() {
        $this->middleware('permission:Cupones.index')->only('index'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reservaciones/cupones.index');
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        if(!is_null($request->fecha)){
            switch (@$request->fecha) {
                case 'dia':
                    $fechaInicio = Carbon::now()->startOfDay();
                    $fechaFinal  = Carbon::now()->endOfDay();
                    break;
                case 'mes':
                    $fechaInicio = Carbon::parse('first day of this month')->startOfDay();
                    $fechaFinal  = Carbon::parse('last day of this month')->endOfDay();
                    break;
                case 'custom':
                    $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
                    $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();
                    break;
            }   
        }

        $comisionistasCupon = Comisionista::where('cupon', 1)->get();
        
        // DB::enableQueryLog();
        $pagos = Pago::whereHas('reservacion', function ($query) use ($fechaInicio, $fechaFinal, $comisionistasCupon){
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->whereIn('comisionista_id', $comisionistasCupon->pluck('id'))
                ->where('estatus',1); 
        })->whereHas('tipoPago', function ($query){
            $query
                ->where('nombre','cupon'); 
        })->get();

        $pagoDetalleArray = [];

        foreach($pagos as $pago){
            $pagoDetalleArray[] = [ 
                'id'           => @$pago->id,
                'folio'        => @$pago->reservacion->folio,
                'reservacionId'=> @$pago->reservacion->id,
                'cupon'        => $pago->reservacion->comisionista->nombre,
                'cantidad'     => $pago->cantidad,
                'fecha'        => @Carbon::parse($pago->reservacion->fecha)->format('d/m/Y'),
                'estatus'      => "",
            ];
        }

        return json_encode(['data' => $pagoDetalleArray]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
