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
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->integer('comisionista_id');
            $table->integer('reservacion_id');
            $table->float('pago_total');
            $table->float('pago_total_sin_iva');
            $table->float('cantidad_comision_bruta');
            $table->float('iva')->nullable();
            $table->float('descuento_impuesto')->nullable();
            $table->float('cantidad_comision_neta');
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
        Schema::dropIfExists('comisiones');
    }
};
