<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\FotoVideoComisionista;
use Illuminate\Http\Request;

class FotoVideoComisionistaController extends Controller
{

    public function __construct() {
        $this->middleware('permission:FotoVideoComisionistas.index')->only('index');
        $this->middleware('permission:FotoVideoComisionistas.update')->only('edit'); 
    }
    
   /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fotovideocomisionistas.index');
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
            if(count(FotoVideoComisionista::
                where('codigo',$request->codigo)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $comisionista = FotoVideoComisionista::create([
                'codigo'             => $request->codigo,
                'nombre'             => mb_strtoupper($request->nombre),
                'comision'           => $request->comision,
                'iva'                => $request->iva,
                'descuento_impuesto' => $request->descuentoImpuesto,
                'direccion'          => mb_strtoupper($request->direccion),
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
     * @param  \App\Models\FotoVideoComisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function show(FotoVideoComisionista $comisionista = null)
    {
        if(is_null($comisionista)){
            $comisionistas      = FotoVideoComisionista::all();
            $comisionistasArray = [];
            foreach ($comisionistas as $comisionista) {
                $comisionistasArray[] = [
                    'id'                => $comisionista->id,
                    'codigo'            => $comisionista->codigo,
                    'nombre'            => mb_strtoupper($comisionista->nombre),
                    'comision'          => $comisionista->comision,
                    'iva'               => $comisionista->iva,
                    'descuentoImpuesto' => $comisionista->descuento_impuesto,
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
     * @param  \App\Models\FotoVideoComisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function edit(FotoVideoComisionista $fotovideocomisionista)
    {
        return view('fotovideocomisionistas.edit',['comisionista' => $fotovideocomisionista]);
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
            $Alojamiento          = FotoVideoComisionista::find($id);
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
            $comisionista                     = FotoVideoComisionista::find($id);
            $comisionista->nombre             = mb_strtoupper($request->nombre);
            $comisionista->comision           = @$request->comision;
            $comisionista->iva                = @$request->iva;
            $comisionista->descuento_impuesto = @$request->descuento_impuesto;
            $comisionista->direccion          = mb_strtoupper($request->direccion);
            $comisionista->telefono           = $request->telefono;
            $comisionista->save();

        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("fotovideocomisionistas.index")->with(["result" => "Comisionista actualizado"]);
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
     * @param  FotoVideoComisionista  $comisionista
     * @return \Illuminate\Http\Response
     */
    public function destroy(FotoVideoComisionista $comisionista)
    {
        $result = $comisionista->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
