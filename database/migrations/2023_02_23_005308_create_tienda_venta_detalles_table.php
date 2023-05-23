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
        Schema::create('tienda_venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('factura_id');
            $table->integer('venta_id');
            $table->integer('producto_id');
            $table->integer('numero_productos');
            $table->float('PPU');
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
        Schema::dropIfExists('tienda_venta_detalles');
    }
};
