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
        Schema::create('reservacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('factura_id');
            $table->integer('reservacion_id');
            $table->integer('actividad_id');
            $table->integer('actividad_horario_id');
            $table->date('actividad_fecha');
            $table->integer('numero_personas');
            $table->double('PPU');
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
        Schema::dropIfExists('reservacion_detalles');
    }
};
