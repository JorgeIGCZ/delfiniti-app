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
        Schema::create('codigo_autorizacion_peticiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservacion_id');
            $table->unsignedBigInteger('codigo_descuento_id');
            $table->string('nombre_cliente');
            $table->date('fecha_peticion');
            $table->timestamps();
            $table->boolean('estatus')->default(0)->comment('0 es pendiente 1 autorizado');
            $table->foreign('reservacion_id')->nullable()->references('id')->on('reservaciones');
            $table->foreign('codigo_descuento_id')->references('id')->on('descuento_codigos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codigo_autorizacion_peticiones');
    }
};
