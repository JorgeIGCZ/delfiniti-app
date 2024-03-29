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
        Schema::create('tienda_movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->integer('producto_id');
            $table->string('movimiento');
            $table->integer('cantidad');
            $table->integer('usuario_id');
            $table->text('comentarios');
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
        Schema::dropIfExists('tienda_movimientos_inventario');
    }
};
