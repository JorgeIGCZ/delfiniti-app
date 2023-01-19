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
        Schema::create('actividad_comision_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_id');
            $table->integer('canal_venta_id');
            $table->float('comision')->default(0)->comment('Solo porcentaje');
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
        Schema::dropIfExists('actividad_comision_detalle');
    }

    
};
