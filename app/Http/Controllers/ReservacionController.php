<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use App\Models\Agencia;
use App\Models\Agenciacredito;
use App\Models\Comisionista;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Localizacion;
use App\Models\Promotor;
use Illuminate\Notifications\Action;
use Illuminate\Database\Eloquent\Builder;

class ReservacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reservacion.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $estados        = Estado::all();
        $localizaciones = Localizacion::all();
        $actividades    = Actividad::whereRaw('NOW() >= fecha_inicial')
                    ->whereRaw('NOW() <= fecha_final')
                    ->orWhere('duracion','indefinido')
                    ->get();
        $comisionsita   = Comisionista::all()->toArray();
        $promotor       = Promotor::all()->toArray();
        $agenciaCredito = Agenciacredito::all()->toArray();
        $agencia        = Agencia::all()->toArray();
        $agencias       = [...$comisionsita,...$promotor,...$agenciaCredito,...$agencia];
        
        return view('reservacion.create',['estados' => $estados,'actividades' => $actividades,'localizaciones' => $localizaciones,'agencias' => $agencias]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        /*
        $result = Promotor::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'comision' => $request->comision,
            'iva' => $request->iva,
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Promotor Guardado" : "Error"]);
        */
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {   
        /*
        if(is_null($id)){
            $promotores = Promotor::all();
            return json_encode(['data' => $promotores]);
        }
        */
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        /*
        if($id > 0){
            $promotores = Promotor::where('id', $id)->first();
            return view('promotores.edit',['promotor' => $promotores]);
        }
        */
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
        /*
        $promotor           = promotor::find($id);
        $promotor->codigo   = $request->codigo;
        $promotor->nombre   = $request->nombre;
        $promotor->comision = $request->comision;
        $promotor->iva      = $request->iva;
        $promotor->save();

        return redirect()->route("promotores")->with(["result" => "Promotor actualizado",]);
        */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /*
        $result = promotor::destroy($id);
        return json_encode(['result' => ($result) ? "Promotor eliminado" : "Error"]);
        */
    }
}
