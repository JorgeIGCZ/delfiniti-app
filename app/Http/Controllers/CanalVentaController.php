<?php

namespace App\Http\Controllers;

use App\Models\CanalVenta;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class CanalVentaController extends Controller
{
    
    public function __construct() {
        $this->middleware('permission:CanalesVenta.index')->only('index');
        $this->middleware('permission:CanalesVenta.update')->only('edit'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('canalesventa.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newRequest = [
            'id'     => $request->id,
            'nombre' => strtoupper($request->nombre)
        ];

        if($request->comisionista_tipo == 'comisionesCanal'){
            $newRequest = array_merge($newRequest,['comisionista_canal' => '1']);
        }else if($request->comisionista_tipo == 'comisionesActividad'){
            $newRequest = array_merge($newRequest,['comisionista_actividad' => '1']);
        }else if($request->comisionista_tipo == 'comisionesCerrador'){
            $newRequest = array_merge($newRequest,['comisionista_cerrador' => '1']);
        }

        try {
            $canalVenta = CanalVenta::create($newRequest);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($canalVenta['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function show(CanalVenta $canalVenta = null)
    {   
        if(is_null($canalVenta)){
            $canalesVenta      = CanalVenta::all();
            return json_encode(['data' => $canalesVenta]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function edit(CanalVenta $canalVenta)
    {
        return view('canalesventa.edit',['canalVenta' => $canalVenta]);
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
            $tipoComisionista                       = CanalVenta::find($id);
            $tipoComisionista->nombre               = strtoupper($request->nombre);
            $tipoComisionista->comisionista_canal = ($request->comisionista_canal == 'on' ? 1 :0);
            $tipoComisionista->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("canalesventa.index")->with(["result" => "Tipo de comisionista actualizado"]);
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
     * @param  CanalVenta  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function destroy(CanalVenta $canalVenta)
    {
        $result = $canalVenta->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}