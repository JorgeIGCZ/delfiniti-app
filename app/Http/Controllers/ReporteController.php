<?php

namespace App\Http\Controllers;

use App\Services\ReporteComisionesService;
use App\Services\ReporteCorteCajaService;
use App\Services\ReporteReservacionesService;

use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected $reporteComisionesService, $reporteCorteCajaService, $reporteReservacionesService;

    public function __construct(ReporteComisionesService $reporteComisionesService, ReporteCorteCajaService $reporteCorteCajaService, ReporteReservacionesService $reporteReservacionesService) {
        $this->middleware('permission:Reportes.index')->only('index');
        $this->middleware('permission:Reportes.CorteCaja.index')->only('reporteCorteCaja'); 
        $this->middleware('permission:Reportes.Reservaciones.index')->only('reporteReservaciones'); 
        $this->middleware('permission:Reportes.Comisiones.index')->only('reporteComisiones'); 

        $this->reporteComisionesService = $reporteComisionesService;
        $this->reporteCorteCajaService = $reporteCorteCajaService;
        $this->reporteReservacionesService = $reporteReservacionesService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reportes.index');
    }
    
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function reporteComisiones(Request $request){
        return $this->reporteComisionesService->getReporte($request);
    }
    
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function reporteCorteCaja(Request $request)
    {
        return $this->reporteCorteCajaService->getReporte($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function reporteReservaciones(Request $request){
        return $this->reporteReservacionesService->getReporte($request);
    }
}