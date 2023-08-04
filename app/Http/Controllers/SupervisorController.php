<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\Supervisor;
use App\Models\SupervisorComisionFotoVideo;
use App\Models\SupervisorComisionFotoVideoDetalle;
use App\Models\SupervisorComisionTiendaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{ 
    public function __construct() 
    {
        $this->middleware('permission:Supervisores.index')->only('index');
        $this->middleware('permission:Supervisores.update')->only('edit'); 
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('supervisores.index');
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
            if(count(Supervisor::where('clave',$request->clave)->get()) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            DB::beginTransaction();

            $supervisor = Supervisor::create([
                'clave'             => $request->clave,
                'nombre'             => mb_strtoupper($request->nombre),
                'direccion'          => mb_strtoupper($request->direccion),
                'telefono'           => $request->telefono
            ]);
            
            $this->createComisionTiendaDetalle($supervisor['id'],$request);
            $this->createComisionFotoVideoDetalle($supervisor['id'],$request);

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($supervisor['id']) ? 'Success' : 'Error']);
    }

    private function createComisionTiendaDetalle($supervisorId,$request)
    {
        SupervisorComisionTiendaDetalle::create([
            'supervisor_id'         => $supervisorId,
            'comision'              => $request->comisionesSobreTienda['comision'],
            'iva'                   => $request->comisionesSobreTienda['iva'],
            'descuento_impuesto'    => $request->comisionesSobreTienda['descuentoImpuesto']
        ]);
    }

    private function createComisionFotoVideoDetalle($supervisorId,$request)
    {
        SupervisorComisionFotoVideo::create([
            'supervisor_id'         => $supervisorId,
            'comision'              => $request->comisionesSobreFotoVideo['comision'],
            'iva'                   => $request->comisionesSobreFotoVideo['iva'],
            'descuento_impuesto'    => $request->comisionesSobreFotoVideo['descuentoImpuesto']
        ]);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Supervisor $supervisor
     * @return \Illuminate\Http\Response
     */
    public function show(Supervisor $supervisor = null)
    {
        if(is_null($supervisor)){
            $supervisores      = Supervisor::all();
            $supervisoresArray = [];
            foreach ($supervisores as $supervisor) {
                $supervisoresArray[] = [
                    'id'                => $supervisor->id,
                    'clave'            => $supervisor->clave,
                    'nombre'            => mb_strtoupper($supervisor->nombre),
                    'direccion'         => mb_strtoupper($supervisor->direccion),
                    'telefono'          => $supervisor->telefono,
                    'estatus'           => $supervisor->estatus
                ];
            }
            return json_encode(['data' => $supervisoresArray]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supervisor $supervisor
     * @return \Illuminate\Http\Response
     */
    public function edit(Supervisor $supervisor)
    {
        return view('supervisores.edit',['supervisor' => $supervisor]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEstatus(Request $request, $id)
    {
        try{
            $supervisor          = Supervisor::find($id);
            $supervisor->estatus = $request->estatus;
            $supervisor->save();
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
            $supervisor                     = Supervisor::find($id);
            $supervisor->nombre             = mb_strtoupper($request->nombre);
            $supervisor->direccion          = mb_strtoupper($request->direccion);
            $supervisor->telefono           = $request->telefono;
            $supervisor->save();

            SupervisorComisionTiendaDetalle::where('supervisor_id',$id)->delete();

            SupervisorComisionTiendaDetalle::create([
                'supervisor_id'          => $id,
                'comision'              => is_null($request->tienda_comision) ? 0 : $request->tienda_comision,
                'iva'                   => is_null($request->tienda_iva) ? 0 : $request->tienda_iva,
                'descuento_impuesto'    => is_null($request->tienda_descuento_impuesto) ? 0 : $request->tienda_descuento_impuesto
            ]);

            SupervisorComisionFotoVideoDetalle::where('supervisor_id',$id)->delete();
            
            SupervisorComisionFotoVideoDetalle::create([
                'supervisor_id'         => $id,
                'comision'              => is_null($request->foto_video_comision) ? 0 : $request->foto_video_comision,
                'iva'                   => is_null($request->foto_video_iva) ? 0 : $request->foto_video_iva,
                'descuento_impuesto'    => is_null($request->foto_video_descuento_impuesto) ? 0 : $request->foto_video_descuento_impuesto
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("supervisores.index")->with(["result" => "Supervisor actualizado"]);
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
     * @param  Supervisor $supervisor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supervisor $supervisor)
    {
        $result = $supervisor->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
