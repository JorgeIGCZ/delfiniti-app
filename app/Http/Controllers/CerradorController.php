<?php

namespace App\Http\Controllers;

use App\Models\Cerrador;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class CerradorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cerradores.index');
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
            $cerrador = Cerrador::create([
                'nombre'        => $request->nombre,
                'comision'      => $request->comision,
                'iva'           => $request->iva,
                'direccion'     => $request->direccion,
                'telefono'      => $request->telefono
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($cerrador['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Cerrador  $cerrador
     * @return \Illuminate\Http\Response
     */
    public function show(Cerrador $cerrador = null)
    {
        if(is_null($cerrador)){
            $cerradores      = Cerrador::all();
            $cerradoresArray = [];
            foreach ($cerradores as $cerrador) {
                $cerradoresArray[] = [
                    'id'           => $cerrador->id,
                    'codigo'       => $cerrador->codigo,
                    'nombre'       => $cerrador->nombre,
                    'iva'          => $cerrador->iva,
                    'comision'     => $cerrador->comision,
                    'direccion'    => $cerrador->direccion,
                    'estatus'      => $cerrador->estatus,
                    'telefono'     => $cerrador->telefono
                ];
            }
            return json_encode(['data' => $cerradoresArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cerrador  $cerrador
     * @return \Illuminate\Http\Response
     */
    public function edit(Cerrador $cerrador)
    {
        return view('cerradores.edit',['cerrador' => $cerrador]);
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
            $cerrador           = Cerrador::find($id);
            $cerrador->nombre   = $request->nombre;
            $cerrador->comision = $request->comision;
            $cerrador->iva      = $request->iva;
            $cerrador->direccion = $request->direccion;
            $cerrador->telefono = $request->telefono;
            $cerrador->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return redirect()->route("cerradores.index")->with(["result" => "Cerrador actualizado"]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEstatus(Request $request, $id){
        try{
            $Alojamiento          = Cerrador::find($id);
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
     * Remove the specified resource from storage.
     *
     * @param  Cerrador  $cerrador
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cerrador $cerrador)
    {
        $result = $cerrador->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}