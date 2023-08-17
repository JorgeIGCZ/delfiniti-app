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
        Schema::create('tienda_venta_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('factura_id');
            $table->integer('venta_id');
            $table->float('cantidad');
            $table->integer('tipo_pago_id');
            $table->float('tipo_cambio_usd')->nullable();
            $table->string('valor')->nullable();
            $table->string('tipo_valor')->nullable();
            $table->boolean('comision_creada')->default(0);
            $table->integer('usuario_id');
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
        Schema::dropIfExists('tienda_venta_pagos');
    }
};
