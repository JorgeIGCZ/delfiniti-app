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
        Schema::create('comisionista_canal_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer('comisionista_id');
            $table->integer('canal_venta_id');
            $table->float('comision')->comment('Solo porcentaje');
            $table->float('iva')->nullable();
            $table->float('descuento_impuesto')->nullable();
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
        Schema::dropIfExists('comisionista_canal_detalle');
    }
};
