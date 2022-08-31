<?php

namespace App\Http\Controllers;

use App\Models\ComisionistaTipo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class ComisionistaTipoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comisionistatipos.index');
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
            $tipoComisionista = ComisionistaTipo::create([
                'id'     => $request->id,
                'nombre' => $request->nombre,
                'comisionista_canal' => ($request->comisionista_canal == 'on' ? 1 :0)
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($tipoComisionista['id']) ? 'Success' : 'Error']);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function show(ComisionistaTipo $comisionistatipo = null)
    {   
        if(is_null($comisionistatipo)){
            $comisionistaTipos      = ComisionistaTipo::all();
            $comisionistaTiposArray = [];
            foreach ($comisionistaTipos as $comisionistaTipo) {
                $comisionistaTiposArray[] = [
                    'id'           => $comisionistaTipo->id,
                    'nombre'       => $comisionistaTipo->nombre,
                    'comisionista_canal' => $comisionistaTipo->comisionista_canal
                ];
            }
            return json_encode(['data' => $comisionistaTiposArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function edit(ComisionistaTipo $comisionistatipo)
    {
        return view('comisionistatipos.edit',['comisionistaTipo' => $comisionistatipo]);
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
            $tipoComisionista                       = ComisionistaTipo::find($id);
            $tipoComisionista->nombre               = $request->nombre;
            $tipoComisionista->comisionista_canal = ($request->comisionista_canal == 'on' ? 1 :0);
            $tipoComisionista->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("comisionistatipos.index")->with(["result" => "Tipo de comisionista actualizado"]);
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
     * @param  ComisionistaTipo  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function destroy(ComisionistaTipo $comisionistatipo)
    {
        $result = $comisionistatipo->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}