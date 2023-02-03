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
        Schema::create('comisionistas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('nombre');
            $table->float('comision');
            $table->float('iva');
            $table->float('descuento_impuesto')->default(0);
            $table->boolean('descuentos')->default(0);
            $table->string('representante')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->integer('canal_venta_id');
            $table->boolean('comisiones_canal')->default(1)->comment('Generalmente usado en comisionistas de grupos que son directivos');
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
        Schema::dropIfExists('comisionistas');
    }
};
