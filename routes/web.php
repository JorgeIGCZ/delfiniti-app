<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\ComisionistaController;
use App\Http\Controllers\AgenciaController;
use App\Http\Controllers\AgenciacreditoController;
use App\Http\Controllers\PromotorController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\LocalizacionController;
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
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::controller(ComisionistaController::class)->middleware(['auth'])->group(function () {
    //Route::get('/orders/{id}', 'show');
    Route::get('/configuracion/comisionistas', 'index')->name('comisionistas');
    Route::post('/configuracion/comisionistas/store', 'store');
    Route::get('/configuracion/comisionistas/show/{comisionista?}', 'show');
    Route::get('/configuracion/comisionistas/edit/{comisionista}', 'edit');
    Route::post('/configuracion/comisionistas/update/{comisionista}', 'update')->name('comisionistasUpdate');
    Route::get('/configuracion/comisionistas/destroy/{comisionista}', 'destroy');
});

Route::controller(PromotorController::class)->middleware(['auth'])->group(function () {
    Route::get('/configuracion/promotores', 'index')->name('promotores');
    Route::post('/configuracion/promotores/store', 'store');
    Route::get('/configuracion/promotores/show/{promotor?}', 'show');
    Route::get('/configuracion/promotores/edit/{promotor}', 'edit');
    Route::post('/configuracion/promotores/update/{promotor}', 'update')->name('promotoresUpdate');
    Route::get('/configuracion/promotores/destroy/{promotor}', 'destroy');
});

Route::controller(AgenciaController::class)->middleware(['auth'])->group(function () {
    Route::get('/configuracion/agencias', 'index')->name('agencias');
    Route::post('/configuracion/agencias/store', 'store');
    Route::get('/configuracion/agencias/show/{agencia?}', 'show');
    Route::get('/configuracion/agencias/edit/{agencia}', 'edit');
    Route::post('/configuracion/agencias/update/{agencia}', 'update')->name('agenciasUpdate');
    Route::get('/configuracion/agencias/destroy/{agencia}', 'destroy');
});

Route::controller(AgenciacreditoController::class)->middleware(['auth'])->group(function () {
    Route::get('/configuracion/agenciascredito', 'index')->name('agenciascredito');
    Route::post('/configuracion/agenciascredito/store', 'store');
    Route::get('/configuracion/agenciascredito/show/{agenciacredito?}', 'show');
    Route::get('/configuracion/agenciascredito/edit/{agenciacredito}', 'edit');
    Route::post('/configuracion/agenciascredito/update/{agenciacredito}', 'update')->name('agenciascreditoUpdate');
    Route::get('/configuracion/agenciascredito/destroy/{agenciacredito}', 'destroy');
});

Route::controller(ActividadController::class)->middleware(['auth'])->group(function () {
    Route::get('/configuracion/actividades', 'index')->name('actividades');
    Route::post('/configuracion/actividades/store', 'store');
    Route::get('/configuracion/actividades/show/{actividad?}', 'show');
    Route::get('/configuracion/actividades/edit/{actividad}', 'edit');
    Route::post('/configuracion/actividades/update/{actividad}', 'update')->name('actividadesUpdate');
    Route::get('/configuracion/actividades/destroy/{actividad}', 'destroy');
});

Route::controller(ReservacionController::class)->middleware(['auth'])->group(function () {
    Route::get('/reservacion', 'index')->name('reservaciones');
    Route::get('/reservacion/create', 'create')->name('reservacionesCreate');
    Route::post('/reservacion/store', 'store');
    Route::get('/reservacion/show/{reservacion?}', 'show');
    Route::get('/reservacion/edit/{reservacion}', 'edit');
    Route::post('/reservacion/update/{reservacion}', 'update')->name('reservacionesUpdate');
});

Route::controller(LocalizacionController::class)->middleware(['auth'])->group(function () {
    Route::get('/configuracion/localizaciones', 'index')->name('localizaciones');
    Route::get('/configuracion/localizaciones/create', 'create')->name('localizacionesCreate');
    Route::post('/configuracion/localizaciones/store', 'store');
    Route::get('/configuracion/localizaciones/show/{localizacion?}', 'show');
    Route::get('/configuracion/localizaciones/edit/{localizacion}', 'edit');
    Route::post('/configuracion/localizaciones/update/{localizacion}', 'update')->name('localizacionesUpdate');
});

Route::get('/disponibilidad',[DisponibilidadController::class,'index'])->middleware(['auth'])->name('disponibilidad');

require __DIR__.'/auth.php';

