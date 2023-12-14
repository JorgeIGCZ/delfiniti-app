<?php

namespace App\Http\Controllers;

use App\Services\ReporteComisionesService;
use App\Services\ReporteCorteCajaService;
use App\Services\ReporteReservacionesService;
use App\Services\ReporteCuponesAgenciaConcentradoService;
use App\Services\ReporteCuponesAgenciaDetalladoService;
use App\Models\User;
use App\Models\CanalVenta;
use App\Models\Comisionista;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    use HasRoles;

    protected $reporteComisionesService, $reporteCorteCajaService, $reporteReservacionesService, $reporteCuponesAgenciaConcentradoService, $reporteCuponesAgenciaDetalladoService;

    public function __construct(
        ReporteComisionesService $reporteComisionesService, 
        ReporteCorteCajaService $reporteCorteCajaService, 
        ReporteReservacionesService $reporteReservacionesService, 
        ReporteCuponesAgenciaConcentradoService $reporteCuponesAgenciaConcentradoService,
        ReporteCuponesAgenciaDetalladoService $reporteCuponesAgenciaDetalladoService
    ) {
        $this->middleware('permission:Reportes.index')->only('index');
        $this->middleware('permission:Reportes.CorteCaja.index')->only('reporteCorteCaja'); 
        $this->middleware('permission:Reportes.Reservaciones.index')->only('reporteReservaciones'); 
        $this->middleware('permission:Reportes.Comisiones.index')->only('reporteComisiones'); 
        $this->middleware('permission:Reportes.CuponesAgenciaConcentrado.index')->only('reporteCuponesAgenciaConcentrado'); 
        $this->middleware('permission:Reportes.CuponesAgenciaDetallado.index')->only('reporteCuponesAgenciaDetallado'); 

        $this->reporteComisionesService = $reporteComisionesService;
        $this->reporteCorteCajaService = $reporteCorteCajaService;
        $this->reporteReservacionesService = $reporteReservacionesService;
        $this->reporteCuponesAgenciaConcentradoService = $reporteCuponesAgenciaConcentradoService;
        $this->reporteCuponesAgenciaDetalladoService = $reporteCuponesAgenciaDetalladoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userRole = Auth::user()->roles->pluck("name")->first();
        $canales = CanalVenta::get();

        if($userRole == 'Administrador' || $userRole == 'Supervisor' || $userRole == 'Mercadotecnia' || $userRole == 'Contabilidad'){
          $usuarios = User::get();
        }elseif($userRole == 'Recepcion'){
          $usuarios = User::role(['Recepcion', 'Tienda'])->get();
        }else{
          $role = Auth::user()->roles->pluck('name');
          $usuarios = User::role($role)->get();
        }
        
        $modulosCorteCaja = [];
        $modulosComisiones = [];

        if($userRole == 'Administrador' || $userRole == 'Supervisor' || $userRole == 'Mercadotecnia' || $userRole == 'Contabilidad'){
          $modulosComisiones = [
            "Reservaciones" => "RESERVACIONES",
            "Tienda" => "TIENDA",
            "FotoVideo" => "FOTO Y VIDEO",
          ];
          $modulosCorteCaja = [
            "Reservaciones" => "RESERVACIONES",
            "Tienda" => "TIENDA",
            "Fotos" => "FOTO",
            "Videos" => "VIDEO",
          ];
        }elseif($userRole == 'Recepcion'){
          $modulosComisiones = [
            "Reservaciones" => "RESERVACIONES",
            "FotoVideo" => "FOTO Y VIDEO",
          ];
          $modulosCorteCaja = [
            "Reservaciones" => "RESERVACIONES",
            "Fotos" => "FOTO",
            "Videos" => "VIDEO",
          ];
        }elseif($userRole == 'Tienda'){
          $modulosComisiones = [
            "Tienda" => "TIENDA",
          ];
          $canales = [];
        }elseif($userRole == 'FotoVideo'){
          $modulosComisiones = [
            "FotoVideo" => "FOTO Y VIDEO",
          ];
          $canales = [];
        }

        $agenciasCupon = Comisionista::where('estatus', 1)->where('cupon', 1)->get();

        return view('reportes.index', [
            'canales' => $canales, 
            'usuarios' => $usuarios, 
            'modulosComisiones' => $modulosComisiones, 
            'modulosCorteCaja' => $modulosCorteCaja,
            'agenciasCupon' => $agenciasCupon
        ]);
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

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function reporteCuponesAgenciaConcentrado(Request $request){
        return $this->reporteCuponesAgenciaConcentradoService->getReporte($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function reporteCuponesAgenciaDetallado(Request $request){
        return $this->reporteCuponesAgenciaDetalladoService->getReporte($request);
    }
}