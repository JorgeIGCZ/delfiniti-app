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
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cliente');
            $table->string('email')->nullable();
            $table->string('localizacion')->nullable();
            $table->string('origen')->nullable();
            $table->integer('agente_id');
            $table->integer('comisionista_id');
            $table->longText('comentarios')->nullable();
            $table->dateTime('fecha_creacion');
            $table->boolean('estatus')->comment('0 es orden guardada 1 es orden reservada');
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
        Schema::dropIfExists('reservaciones');
    }
};
