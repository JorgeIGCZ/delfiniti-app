<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReservacionController extends Controller
{
    public function index(){
        return view("reservacion.index");
    }
    public function create(){
        return view("reservacion.create");
    }
}
