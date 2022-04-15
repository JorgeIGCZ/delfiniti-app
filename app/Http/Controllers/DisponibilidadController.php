<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("configuracion.disponibilidad");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function show(Disponibilidad $disponibilidad)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function edit(Disponibilidad $disponibilidad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Disponibilidad $disponibilidad)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Disponibilidad  $disponibilidad
     * @return \Illuminate\Http\Response
     */
    public function destroy(Disponibilidad $disponibilidad)
    {
        //
    }
}
