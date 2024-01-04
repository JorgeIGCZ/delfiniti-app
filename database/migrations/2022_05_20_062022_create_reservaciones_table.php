<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->string('folio');
            $table->string('nombre_cliente');
            $table->string('email')->nullable();
            $table->string('alojamiento')->nullable();
            $table->string('origen')->nullable();
            $table->integer('usuario_id');
            $table->integer('comisionista_id');
            $table->integer('comisionista_actividad_id');
            $table->integer('cerrador_id');
            $table->longText('comentarios')->nullable();
            $table->date('fecha');
            $table->date('fecha_creacion');
            $table->boolean('estatus_pago')->comment('0 es orden reservada 1 es orden parcialmente pagada 2 es orden pagada');
            $table->boolean('estatus')->default(1)->comment('0 es orden cancelada 1 es orden activa');
            $table->boolean('check_in')->default(0);
            $table->boolean('comisionable')->default(1);
            $table->boolean('comisiones_especiales')->default(0)->comment('Generalmente usado en visitas');
            $table->boolean('comisiones_canal')->default(1)->comment('Generalmente usado en comisionistas de grupos que son directivos');
            $table->string('num_cupon')->nullable()->comment('Campo usado para colocar el numero de cupón');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservaciones');
    }
};
