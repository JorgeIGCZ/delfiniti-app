<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\FotoVideoVentaTicket;
use Illuminate\Http\Request;

class FotoVideoVentaTicketController extends Controller
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
            $reservacionTicket = FotoVideoVentaTicket::create([
                'venta_id'       =>  $request->ventaId,
                'ticket'         =>  $request->ticket
            ]);
            return json_encode(['result' => is_numeric($reservacionTicket['id']) ? 'Success' : 'Error']);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FotoVideoVentaTicketController  $fotoVideoTicket
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reservacionTicket = FotoVideoVentaTicket::find($id);
        return json_encode($reservacionTicket);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FotoVideoVentaTicketController  $fotoVideoTicket
     * @return \Illuminate\Http\Response
     */
    public function edit(FotoVideoVentaTicketController $fotoVideoVentaTicket = null)
    {
        if(is_null($fotoVideoVentaTicket)){
            $fotoVideoVentaTicket = FotoVideoVentaTicket::find($fotoVideoVentaTicket);
        }else{
            $fotoVideoVentaTicket = FotoVideoVentaTicket::all();
        }
        return json_encode($fotoVideoVentaTicket);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FotoVideoVentaTicketController  $fotoVideoTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FotoVideoVentaTicketController $fotoVideoTicket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FotoVideoVentaTicketController  $fotoVideoTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy(FotoVideoVentaTicketController $fotoVideoTicket)
    {
        //
    }
}
