<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaVenta;
use App\Models\TiendaVentaTicket;
use Illuminate\Http\Request;

class TiendaVentaTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        try{
            $tiendaVentaTicket = TiendaVentaTicket::create([
                'venta_id'       =>  $request->ventaId,
                'ticket'         =>  $request->ticket
            ]);
            return json_encode(['result' => is_numeric($tiendaVentaTicket['id']) ? 'Success' : 'Error']);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TiendaVentaTicket  $tiendaVentaTicket
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tiendaVentaTicket = TiendaVentaTicket::find($id);
        return json_encode($tiendaVentaTicket);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TiendaVentaTicket  $tiendaVentaTicket
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaVentaTicket $tiendaVentaTicket = null)
    {
        if(is_null($tiendaVentaTicket)){
            $tiendaVentaTicket = TiendaVentaTicket::find($tiendaVentaTicket);
        }else{
            $tiendaVentaTicket = TiendaVentaTicket::all();
        }
        return json_encode($tiendaVentaTicket);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TiendaVentaTicket  $tiendaVentaTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TiendaVentaTicket $tiendaVentaTicket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TiendaVentaTicket  $tiendaVentaTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy(TiendaVentaTicket $tiendaVentaTicket)
    {
        //
    }
}
