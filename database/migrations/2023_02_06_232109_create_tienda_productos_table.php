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
        Schema::create('tienda_productos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('codigo')->unique()->nullable();
            $table->integer('proveedor_id');
            $table->string('nombre');
            $table->double('costo');
            $table->double('precio_venta');
            $table->double('margen_ganancia');
            $table->integer('stock');
            $table->integer('stock_minimo');
            $table->integer('stock_maximo');
            $table->date('ultima_entrada');
            $table->date('ultima_salida');
            $table->text('comentarios');
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
        Schema::dropIfExists('tienda_productos');
    }
};
