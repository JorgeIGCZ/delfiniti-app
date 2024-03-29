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
        Schema::create('tienda_pedidos', function (Blueprint $table) {
            $table->id();
            $table->integer('proveedor_id');
            $table->date('fecha_pedido');
            $table->date('fecha_autorizacion')->nullable();
            $table->longText('comentarios')->nullable();
            $table->boolean('estatus')->default(1);
            $table->boolean('estatus_proceso')->default(0)->comment('0 es pedido pendiente de aprovacion 1 es pedido aprovado');;
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
        Schema::dropIfExists('tienda_pedidos');
    }
};
