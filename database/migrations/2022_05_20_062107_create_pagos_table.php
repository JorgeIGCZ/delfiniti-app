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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('factura_id');
            $table->integer('reservacion_id');
            $table->float('cantidad');
            $table->integer('tipo_pago_id');
            $table->float('tipo_cambio_usd')->nullable();
            $table->string('valor')->nullable();
            $table->string('tipo_valor')->nullable();
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
        Schema::dropIfExists('pagos');
    }
};
