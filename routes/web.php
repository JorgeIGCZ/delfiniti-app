<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\ComisionistaController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlojamientoController;
use App\Http\Controllers\CerradorController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\ComisionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\CanalVentaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ImprimirController;
use App\Http\Controllers\DescuentoCodigoController;
use App\Http\Controllers\ReservacionTicketController;
use App\Http\Controllers\VentaController;
use App\Models\Actividad;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route("disponibilidad")->with(["result" => ""]);
})->middleware(['auth'])->name('disp');

Route::controller(ComisionistaController::class)->middleware(['auth'])->group(function () {
    Route::get('comisionistas/show/{comisionista?}', 'show');
    Route::patch('comisionistas/estatus/{actividad}', 'updateEstatus');
    Route::resource('comisionistas',ComisionistaController::class, [
        'parameters' => [
            'comisionistas' => 'comisionista'
        ]
    ]);
    
});

Route::controller(ActividadController::class)->middleware(['auth'])->group(function () {
    Route::get('actividades/show/{actividad?}', 'show');
    Route::patch('actividades/estatus/{actividad}', 'updateEstatus');
    Route::resource('actividades',ActividadController::class, [
        'parameters' => [
            'actividades' => 'actividad'
        ]
    ]);
});

Route::controller(ReservacionController::class)->middleware(['auth'])->group(function () {
    /*
    Route::get('/reservacion', 'index')->name('reservaciones');
    Route::get('/reservacion/create', 'create')->name('reservacionesCreate');
    Route::post('/reservacion/store', 'store');
    Route::get('/reservacion/show/{reservacion?}', 'show');
    Route::get('/reservacion/get', 'get');
    Route::get('/reservacion/edit/{reservacion}', 'edit');
    Route::post('/reservacion/update/{reservacion}', 'update')->name('reservacionesUpdate');
    */
    Route::get('/reservaciones/create/{reservacion?}', 'create')->name('reservacionesCreate');
    Route::post('reservaciones/updateestatusreservacion', 'updateEstatusReservacion');
    Route::post('reservaciones/removeActividad', 'removeActividad');
    Route::post('reservaciones/editPago','editPago');
    Route::post('reservaciones/removeDescuento', 'removeDescuento');
    Route::post('reservaciones/getCodigoDescuento', 'getCodigoDescuento');
    Route::post('reservaciones/getDescuentoPersonalizadoValidacion', 'getDescuentoPersonalizadoValidacion');
    Route::post('reservaciones/show/{reservacion?}', 'show');
    Route::resource('reservaciones',ReservacionController::class, [
        'parameters' => [
            'reservaciones' => 'reservacion'
        ]
    ]);
});

Route::controller(VentaController::class)->middleware(['auth'])->group(function () {
    // Route::get('/reservaciones/create/{reservacion?}', 'create')->name('reservacionesCreate');
    // Route::post('reservaciones/updateestatusreservacion', 'updateEstatusReservacion');
    // Route::post('reservaciones/removeActividad', 'removeActividad');
    // Route::post('reservaciones/editPago','editPago');
    // Route::post('reservaciones/removeDescuento', 'removeDescuento');
    // Route::post('reservaciones/getCodigoDescuento', 'getCodigoDescuento');
    // Route::post('reservaciones/getDescuentoPersonalizadoValidacion', 'getDescuentoPersonalizadoValidacion');
    // Route::post('reservaciones/show/{reservacion?}', 'show');
    Route::resource('ventas',VentaController::class, [
        'parameters' => [
            'ventas' => 'venta'
        ]
    ]);
});

Route::controller(AlojamientoController::class)->middleware(['auth'])->group(function () {
    Route::get('/alojamientos/show/{alojamiento?}', 'show');
    Route::patch('alojamientos/estatus/{actividad}', 'updateEstatus');
    Route::resource('alojamientos',AlojamientoController::class, [
        'parameters' => [
            'alojamientos' => 'alojamiento'
        ]
    ]);
});

Route::controller(UsuarioController::class)->middleware(['auth'])->group(function () {
    Route::post('usuarios/validateUsuario', 'validateUsuario');
    Route::get('/usuarios/show/{usuario?}', 'show');
    Route::resource('usuarios',UsuarioController::class, [
        'parameters' => [
            'usuarios' => 'usuario'
        ]
    ]);
});

Route::controller(DisponibilidadController::class)->middleware(['auth'])->group(function () {
    Route::get('/disponibilidad', 'index')->name('disponibilidad');
    Route::post('/disponibilidad/show', 'show');
});

Route::controller(TipoCambioController::class)->middleware(['auth'])->group(function () {
    Route::resource('tiposcambio',TipoCambioController::class);
});

Route::controller(CanalVentaController::class)->middleware(['auth'])->group(function () {
    Route::get('/canalesventa/show/{canalVenta?}', 'show');
    Route::patch('canalesventa/estatus/{actividad}', 'updateEstatus');
    Route::resource('canalesventa',CanalVentaController::class, [
        'parameters' => [
            'canalesventa' => 'canalVenta'
        ]
    ]);
});

Route::get('/reportes',[ReporteController::class,'index'])->middleware(['auth'])->name('reportes');
Route::post('/reportes/cortecaja',[ReporteController::class,'reporteCorteCaja'])->middleware(['auth'])->name('reportecortecaja');
Route::post('/reportes/totalreservaciones',[ReporteController::class,'reporteReservaciones'])->middleware(['auth'])->name('reportereservaciones');
Route::post('/reportes/totalcomisiones',[ReporteController::class,'reporteComisiones'])->middleware(['auth'])->name('reportecomisiones');

Route::get('/roles',[RolController::class,'index'])->middleware(['auth'])->name('roles');
Route::post('/roles',[RolController::class,'store'])->middleware(['auth'])->name('rolesstore');
Route::get('/roles/{rol}',[RolController::class,'show'])->middleware(['auth'])->name('rolesupdate');

Route::get('/imprimir/{actividad?}',[ImprimirController::class,'imprimirTicket'])->middleware(['auth'])->name('imprimirticket');

Route::controller(CerradorController::class)->middleware(['auth'])->group(function () {
    Route::get('cerradores/show/{cerrador?}', 'show');
    Route::patch('cerradores/estatus/{actividad}', 'updateEstatus');
    Route::resource('cerradores',CerradorController::class, [
        'parameters' => [
            'cerradores' => 'cerrador'
        ]
    ]);
    
});

Route::controller(DescuentoCodigoController::class)->middleware(['auth'])->group(function () {
    Route::get('descuentocodigos/show/{descuentocodigo?}', 'show');
    Route::patch('descuentocodigos/estatus/{actividad}', 'updateEstatus');
    Route::resource('descuentocodigos',DescuentoCodigoController::class, [
        'parameters' => [
            'descuentocodigos' => 'descuentocodigo'
        ]
    ]);
    
});

Route::controller(ComisionController::class)->middleware(['auth'])->group(function () {
    Route::post('comisiones/recalculateComisiones', 'recalculateComisiones');
    Route::post('comisiones/show/{comision?}', 'show');
    Route::patch('comisiones/estatus/{actividad}', 'updateComisiones');
    Route::resource('comisiones',ComisionController::class, [
        'parameters' => [
            'comisiones' => 'comision'
        ]
    ]);
    
});

// Route::controller(CheckinController::class)->middleware(['auth'])->group(function () {
//     Route::post('checkin/show/{reservacion?}', 'show');
//     Route::patch('checkin/registro/{reservacion}', 'registroVisita');
//     Route::resource('checkin',CheckinController::class, [
//         'parameters' => [
//             'reservaciones' => 'reservacion'
//         ]
//     ]);
// });

Route::controller(ReservacionTicketController::class)->middleware(['auth'])->group(function () {
    Route::get('reservacionticket/show/{reservacionticket?}', 'show');
    Route::patch('reservacionticket/estatus/{reservacionticket}', 'updateEstatus');
    Route::resource('reservacionticket',ReservacionTicketController::class, [
        'parameters' => [
            'reservacionestickets' => 'reservacionTicket'
        ]
    ]);
});

require __DIR__.'/auth.php';