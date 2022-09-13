<?php

namespace App\Http\Controllers;

use App\Models\Comisionista;
use App\Models\CanalVenta;
use App\Models\Actividad;
use App\Models\ComisionistaCanalDetalle;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\ComisionistaActividadDetalle;
use App\Models\ComisionistaCanalActividad;

class ComisionistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tipos = CanalVenta::all();
        $tiposSinComisionSobreTipos = CanalVenta::where('comisionista_canal',0)->get();
        $actividades = Actividad::get();
        return view('comisionistas.index',['tipos' => $tipos,'tiposSinComision' => $tiposSinComisionSobreTipos,'actividades' => $actividades]);
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
            if(count(Comisionista::
                where('codigo',$request->codigo)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $comisionista = Comisionista::create([
                'codigo'             => $request->codigo,
                'nombre'             => $request->nombre,
                'canal_venta_id'     => $request->tipo,
                'comision'           => $request->comision,
                'iva'                => $request->iva,
                'descuento_impuesto' => $request->descuentoImpuesto,
                'descuentos'         => $request->descuentos,
                'representante'      => $request->representante,
                'direccion'          => $request->direccion,
                'telefono'           => $request->telefono
            ]);
            
            if($request->tipoComisionista == 'comisionistaCanal'){
                $this->createComisionistaCanalDetalle($comisionista['id'],$request);
            }else if($request->tipoComisionista == 'comisionistaActividad'){
                $this->createComisionistaActividadDetalle($comisionista['id'],$request);
            }
            
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($comisionista['id']) ? 'Success' : 'Error']);
    }

    private function createComisionistaCanalDetalle($comisionistaId,$request){
        foreach($request->comisionesSobreCanales as $key => $comisionSobreCanales){
            ComisionistaCanalDetalle::create([
                'comisionista_id'       => $comisionistaId,
                'canal_venta_id'        => $key,
                'comision'              => $comisionSobreCanales['comision'],
                'iva'                   => $comisionSobreCanales['iva'],
                'descuento_impuesto'    => $comisionSobreCanales['descuentoImpuesto']
            ]);
        }
    }

    private function createComisionistaActividadDetalle($comisionistaId,$request){
        foreach($request->comisionesSobreActividades as $key => $comisionSobreActividades){
            ComisionistaActividadDetalle::create([
                'comisionista_id'       => $comisionistaId,
                'actividad_id'          => $key,
                'comision'              => $comisionSobreActividades['comision']
            ]);
        }
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function show(Comisionista $comisionista = null)
    {
        if(is_null($comisionista)){
            $comisionistas      = Comisionista::all();
            $comisionistasArray = [];
            foreach ($comisionistas as $comisionista) {
                $comisionistasArray[] = [
                    'id'                => $comisionista->id,
                    'codigo'            => $comisionista->codigo,
                    'nombre'            => $comisionista->nombre,
                    'canal_venta_id'    => $comisionista->tipo->nombre,
                    'iva'               => $comisionista->iva,
                    'descuentoImpuesto' => $comisionista->descuento_impuesto,
                    'descuentos'        => $comisionista->descuentos,
                    'comision'          => $comisionista->comision,
                    'representante'     => $comisionista->representante,
                    'direccion'         => $comisionista->direccion,
                    'telefono'          => $comisionista->telefono,
                    'estatus'           => $comisionista->estatus
                ];
            }
            return json_encode(['data' => $comisionistasArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function edit(Comisionista $comisionista)
    {
        $comisionistaGeneralTipos     = CanalVenta::where('comisionista_canal',0)->get();
        $canalesVenta                 = CanalVenta::all();
        $actividades                  = Actividad::all();
        $comisionistaActividadesDetalle = ComisionistaActividadDetalle::where('comisionista_id',$comisionista->id)->get();
        return view('comisionistas.edit',['comisionista' => $comisionista,'tipos' => $canalesVenta,'comisionistaGeneralTipos' => $comisionistaGeneralTipos,'actividades' => $actividades,'comisionistaActividadesDetalle' => $comisionistaActividadesDetalle]);
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
            $Alojamiento          = Comisionista::find($id);
            $Alojamiento->estatus = $request->estatus;
            $Alojamiento->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => 'Success']);
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
        try {
            $comisionista                     = Comisionista::find($id);
            $comisionista->nombre             = $request->nombre;
            $comisionista->comision           = @$request->comision;
            $comisionista->iva                = @$request->iva;
            $comisionista->descuento_impuesto = @$request->descuento_impuesto;
            $comisionista->descuentos         = $request->has('descuentos');
            $comisionista->canal_venta_id     = $request->tipo;
            $comisionista->representante      = $request->representante;
            $comisionista->direccion          = $request->direccion;
            $comisionista->telefono           = $request->telefono;
            $comisionista->save();

            $comisionistaCanalVenta = Comisionista::find($id)->tipo;

            ComisionistaCanalDetalle::where('comisionista_id',$id)->delete();
            ComisionistaActividadDetalle::where('comisionista_id',$id)->delete();

            if($comisionistaCanalVenta->comisionista_canal == 1){

                foreach($request->comisionista_canal_detalles as $comisionistaCanalDetalle){
                    foreach($comisionistaCanalDetalle as $key => $detalle){
                        ComisionistaCanalDetalle::create([
                            'comisionista_id'       => $id,
                            'canal_venta_id'        => $key,
                            'comision'              => is_null($detalle['comision']) ? 0 : $detalle['comision'],
                            'iva'                   => is_null($detalle['iva']) ? 0 : $detalle['iva'],
                            'descuento_impuesto'    => is_null($detalle['descuento_impuesto']) ? 0 : $detalle['descuento_impuesto']
                        ]);
                    }
                }
            }else if($comisionistaCanalVenta->comisionista_actividad == 1){
                foreach($request->comisionista_actividad_detalles as $comisionistaActividadDetalle){
                    foreach($comisionistaActividadDetalle as $key => $detalle){
                        ComisionistaActividadDetalle::create([
                            'comisionista_id'       => $id,
                            'actividad_id'          => $key,
                            'comision'              => is_null($detalle['comision']) ? 0 : $detalle['comision']
                        ]);
                    }
                }
            }
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("comisionistas.index")->with(["result" => "Comisionista actualizado"]);
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
     * @param  Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comisionista $comisionista)
    {
        $result = $comisionista->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}