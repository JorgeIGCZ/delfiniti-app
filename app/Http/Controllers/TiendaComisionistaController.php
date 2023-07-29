<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaComisionista;
use App\Models\User;
use Illuminate\Http\Request;

class TiendaComisionistaController extends Controller
{ 
    public function __construct() {
        $this->middleware('permission:TiendaComisionista.index')->only('index');
        $this->middleware('permission:TiendaComisionista.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tiendacomisionistas.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TiendaComisionista  $tiendaComisionista
     * @return \Illuminate\Http\Response
     */
    public function show(TiendaComisionista $tiendacomisionista)
    {
        $comisionistas      = User::role('Tienda')->get();
        $comisionistasArray = [];
        foreach ($comisionistas as $comisionista) {
            $comisionistasArray[] = [
                'id'                => $comisionista->id,
                'nombre'            => mb_strtoupper($comisionista->name),
                'iva'               => isset($comisionista->comisiones->iva) ? $comisionista->comisiones->iva : 0,
                'descuentoImpuesto' => isset($comisionista->comisiones->descuento_impuesto) ? $comisionista->comisiones->descuento_impuesto : 0,
                'descuentos'        => isset($comisionista->comisiones->descuentos) ? $comisionista->comisiones->descuentos : 0,
                'comision'          => isset($comisionista->comisiones->comision) ? $comisionista->comisiones->comision : 0
            ];
        }
        return json_encode(['data' => $comisionistasArray]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TiendaComisionista  $x
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaComisionista $tiendaComisionista, $id)
    {
        $comisionistas      = User::find($id);
        return view('tiendacomisionistas.edit',['comisionista' => $comisionistas]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TiendaComisionista  $tiendaComisionista
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $comisionista                     = TiendaComisionista::where('usuario_id', @$request->id)->first();
            if(!is_null($comisionista)){
                $comisionista->comision           = @$request->comision;
                $comisionista->iva                = @$request->iva;
                $comisionista->descuento_impuesto = @$request->descuento_impuesto;
                $comisionista->save();
            }else{
                $comisionista = TiendaComisionista::create([
                    'usuario_id'         => $request->id,
                    'comision'           => $request->comision,
                    'iva'                => $request->iva,
                    'descuento_impuesto' => $request->descuento_impuesto
                ]);
            }

            return redirect()->route("tiendacomisionistas.index")->with(["result" => "Comisionista actualizado"]);

        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TiendaComisionista  $tiendaComisionista
     * @return \Illuminate\Http\Response
     */
    public function destroy(TiendaComisionista $tiendaComisionista)
    {
        //
    }
}
