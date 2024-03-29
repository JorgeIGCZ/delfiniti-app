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
        Schema::create('tienda_pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('pedido_id');
            $table->integer('producto_id');
            $table->integer('cantidad');
            $table->float('CPU')->comment('Costo por unidad');
            $table->float('IPU_total')->comment('Impuesto total por unidad');
            $table->float('subtotal');
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
        Schema::dropIfExists('tienda_pedido_detalles');
    }
};
