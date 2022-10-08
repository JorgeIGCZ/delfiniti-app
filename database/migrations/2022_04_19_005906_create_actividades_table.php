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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->double('precio');
            $table->string('nombre');
            $table->integer('capacidad');
            $table->date('fecha_inicial')->nullable();
            $table->date('fecha_final')->nullable();
            $table->string('duracion');
            $table->integer('reporte_orden');
            $table->boolean('comisionable')->default(1);
            $table->boolean('estatus')->default(1);
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
        Schema::dropIfExists('actividades');
    }
};
