<?php

namespace App\Http\Controllers;

use App\Models\Comisionista;
use App\Models\ComisionistaTipo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class ComisionistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tipos = ComisionistaTipo::all();
        return view('comisionistas.index',['tipos' => $tipos]);
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
                'tipo_id'            => $request->tipo,
                'comision'           => $request->comision,
                'iva'                => $request->iva,
                'descuento_impuesto' => $request->descuentoImpuesto,
                'descuentos'         => $request->descuentos,
                'representante'      => $request->representante,
                'direccion'          => $request->direccion,
                'telefono'           => $request->telefono
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($comisionista['id']) ? 'Success' : 'Error']);
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
                    'tipo_id'           => $comisionista->tipo->nombre,
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
        $tipos        = ComisionistaTipo::all();
        return view('comisionistas.edit',['comisionista' => $comisionista,'tipos' => $tipos]);
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
            $comisionista->comision           = $request->comision;
            $comisionista->iva                = $request->iva;
            $comisionista->descuento_impuesto = $request->descuento_impuesto;
            $comisionista->descuentos         = $request->has('descuentos');
            $comisionista->tipo_id            = $request->tipo;
            $comisionista->representante      = $request->representante;
            $comisionista->direccion          = $request->direccion;
            $comisionista->telefono           = $request->telefono;
            $comisionista->save();
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