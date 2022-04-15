<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotor;

class PromotorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('promotores.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = Promotor::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'comision' => $request->comision,
            'iva' => $request->iva,
        ]);        
        return json_encode(['result' => is_numeric($result['id']) ? "Promotor Guardado" : "Error"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {   
        if(is_null($id)){
            $promotores = Promotor::all();
            return json_encode(['data' => $promotores]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if($id > 0){
            $promotores = Promotor::where('id', $id)->first();
            return view('promotores.edit',['promotor' => $promotores]);
        }
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
        $promotor           = promotor::find($id);
        $promotor->codigo   = $request->codigo;
        $promotor->nombre   = $request->nombre;
        $promotor->comision = $request->comision;
        $promotor->iva      = $request->iva;
        $promotor->save();

        return redirect()->route("promotores")->with(["result" => "Promotor actualizado",]);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = promotor::destroy($id);
        return json_encode(['result' => ($result) ? "Promotor eliminado" : "Error"]);
    }
}
