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
        Schema::create('comisionista_actividad_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer('comisionista_id');
            $table->integer('actividad_id');
            $table->float('comision')->comment('Solo efectivo');
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
        Schema::dropIfExists('comisionista_actividad_detalle');
    }
};
