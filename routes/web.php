<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\ComisionistaController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlojamientoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoCambioController;
use App\Http\Controllers\ComisionistaTipoController;

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
    return view('reportes.index');
})->middleware(['auth'])->name('dashboard');
Route::controller(ComisionistaController::class)->middleware(['auth'])->group(function () {
    Route::get('comisionistas/show/{comisionista?}', 'show');
    Route::resource('comisionistas',ComisionistaController::class, [
        'parameters' => [
            'comisionistas' => 'comisionista'
        ]
    ]);
    
});

Route::controller(ActividadController::class)->middleware(['auth'])->group(function () {
    Route::get('actividades/show/{actividad?}', 'show');
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

    Route::post('reservaciones/getCodigoDescuento', 'getCodigoDescuento');
    Route::post('reservaciones/getDescuentoPersonalizadoValidacion', 'getDescuentoPersonalizadoValidacion');
    Route::get('reservaciones/show/{reservacion?}', 'show');
    Route::resource('reservaciones',ReservacionController::class, [
        'parameters' => [
            'reservaciones' => 'reservacion'
        ]
    ]);
});
Route::controller(AlojamientoController::class)->middleware(['auth'])->group(function () {
    Route::get('/alojamientos/show/{alojamiento?}', 'show');
    Route::resource('alojamientos',AlojamientoController::class, [
        'parameters' => [
            'alojamientos' => 'alojamiento'
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

Route::controller(ComisionistaTipoController::class)->middleware(['auth'])->group(function () {
    Route::resource('comisionistatipos',ComisionistaTipoController::class, [
        'parameters' => [
            'comisionistatipos' => 'comisionistatipo'
        ]
    ]);
});

Route::get('/reportes',[ReporteController::class,'index'])->middleware(['auth'])->name('reportes');
require __DIR__.'/auth.php';