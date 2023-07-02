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
        Schema::create('directivo_comisiones_tienda', function (Blueprint $table) {
            $table->id();
            $table->integer('directivo_id');
            $table->integer('venta_id');
            $table->float('pago_total')->default(0);
            $table->float('pago_total_sin_iva')->default(0);
            $table->float('cantidad_comision_bruta')->default(0);
            $table->float('iva')->default(0);
            $table->float('descuento_impuesto')->default(0);
            $table->float('cantidad_comision_neta')->default(0);
            $table->boolean('estatus')->default(0);
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
        Schema::dropIfExists('directivo_comisiones_tienda');
    }
};
