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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id(); 
            $table->string('folio');
            $table->string('nombre_cliente')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->string('origen')->nullable();
            $table->string('RFC')->nullable();
            $table->date('fecha');
            $table->date('fecha_creacion');
            $table->integer('usuario_id');
            $table->text('comentarios');
            $table->boolean('estatus')->default(1)->comment('0 es orden cancelada 1 es orden activa');
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
        Schema::dropIfExists('ventas');
    }
};
