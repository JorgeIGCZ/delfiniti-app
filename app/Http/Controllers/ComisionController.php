<?php

namespace App\Http\Controllers;

use App\Models\Comision;
use App\Models\Comisionista;
use App\Models\Pago;
use App\Models\Reservacion;
use finfo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\Cerrador;

class ComisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comisiones.index');
    }

    public function setComisiones($reservacionId){
        $reservacion  = Reservacion::find($reservacionId);
        $pagos        = Pago::where('reservacion_id',$reservacion['id'])->whereHas('tipoPago', function ($query) {
            $query
                ->whereRaw(" nombre IN ('efectivo','efectivoUsd','tarjeta')");
        })->get();

        $this->setComisionComisionista($reservacion,$pagos);
        $this->setComisionCerrador($reservacion,$pagos);
    }

    private function setComisionComisionista($reservacion,$pagos){
        if($reservacion['comisionista_id'] == 0){
            return true;
        }
        
        $comisionista = Comisionista::find($reservacion['comisionista_id']);
        

        $totalPagoReservacion = 0;
        foreach($pagos as $pago){
            $totalPagoReservacion += $pago['cantidad'];
        }

        $cantidadComisionBruta     = number_format((($totalPagoReservacion * $comisionista['comision']) / 100),2);
        $ivaCantidad               = number_format((($cantidadComisionBruta * $comisionista['iva']) / 100),2);
        $descuentoImpuestoCantidad = number_format((($cantidadComisionBruta * $comisionista['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = number_format(($cantidadComisionBruta - ($ivaCantidad + $descuentoImpuestoCantidad)),2);

        $comsion = Comision::create([
            'comisionista_id'         =>  $reservacion['comisionista_id'],
            'reservacion_id'          =>  $reservacion['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'estatus'                 =>  1
        ]);

        return is_numeric($comsion['id']);
    }

    private function setComisionCerrador($reservacion,$pagos){
        if($reservacion['cerrador_id'] == 0){
            return true;
        }
        
        $cerrador = Comisionista::find($reservacion['cerrador_id']);
        

        $totalPagoReservacion = 0;
        foreach($pagos as $pago){
            $totalPagoReservacion += $pago['cantidad'];
        }

        $cantidadComisionBruta     = number_format((($totalPagoReservacion * $cerrador['comision']) / 100),2);
        $ivaCantidad               = number_format((($cantidadComisionBruta * $cerrador['iva']) / 100),2);
        $descuentoImpuestoCantidad = number_format((($cantidadComisionBruta * $cerrador['descuento_impuesto']) / 100),2);
        $cantidadComisionNeta      = number_format(($cantidadComisionBruta - ($ivaCantidad + $descuentoImpuestoCantidad)),2);

        $comsion = Comision::create([
            'comisionista_id'         =>  $reservacion['cerrador_id'],
            'reservacion_id'          =>  $reservacion['id'],
            'pago_total'              =>  $totalPagoReservacion,
            'cantidad_comision_bruta' =>  (float)$cantidadComisionBruta,
            'iva'                     =>  (float)$ivaCantidad,
            'descuento_impuesto'      =>  (float)$descuentoImpuestoCantidad,
            'cantidad_comision_neta'  =>  (float)$cantidadComisionNeta,
            'estatus'                 =>  1
        ]);

        return is_numeric($comsion['id']);
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
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function show(Comision $comision)
    {
        // dd($comision);
        // if(is_null($comision)){

            $comisiones      = Comision::whereHas('reservacion',function ($query){
                $query
                    ->where("estatus", 1);
            })->orderBy('id','desc')->get();

            $comisionesArray = [];
            foreach ($comisiones as $comision) {
                $comisionesArray[] = [
                    'id'                => $comision->id,
                    'comisionista'      => $comision->comisionista->nombre,
                    'reservacion'       => $comision->reservacion->folio,
                    'reservacionId'     => $comision->reservacion->id,
                    'total'             => $comision->pago_total,
                    'comisionBruta'     => $comision->cantidad_comision_bruta,
                    'iva'               => $comision->iva,
                    'descuentoImpuesto' => $comision->descuento_impuesto,
                    'comisionNeta'      => $comision->cantidad_comision_neta,
                    'fecha'             => date_format($comision->created_at,'d-m-Y'),
                    'estatus'           => $comision->estatus
                ];
            }
            return json_encode(['data' => $comisionesArray]);
        //}
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function edit(Comision $comision)
    {
        return view('comisiones.edit',['comision' => $comision]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $comision                         = Comision::find($id);
            $comision->iva                    = $request->iva;
            $comision->descuento_impuesto     = $request->descuento_impuesto;
            $comision->cantidad_comision_neta = $request->cantidad_comision_neta;
            $comision->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("comisiones.index")->with(["result" => "Comision actualizada"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comision  $comision
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comision $comision)
    {
        //
    }
}
