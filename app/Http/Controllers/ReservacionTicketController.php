<?php

namespace App\Http\Controllers;

use App\Models\ReservacionTicket;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class ReservacionTicketController extends Controller
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
            $reservacionTicket = ReservacionTicket::create([
                'reservacion_id' =>  $request->reservacionId,
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
     * @param  \App\Models\ReservacionTicket  $reservacionTicket
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reservacionTicket = ReservacionTicket::find($id);
        return json_encode($reservacionTicket);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReservacionTicket  $reservacionTicket
     * @return \Illuminate\Http\Response
     */
    public function edit(ReservacionTicket $reservacionTicket = null)
    {
        if(is_null($reservacionTicket)){
            $reservacionTicket = ReservacionTicket::find($reservacionTicket);
        }else{
            $reservacionTicket = ReservacionTicket::all();
        }
        return json_encode($reservacionTicket);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReservacionTicket  $reservacionTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReservacionTicket $reservacionTicket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReservacionTicket  $reservacionTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReservacionTicket $reservacionTicket)
    {
        //
    }
}
