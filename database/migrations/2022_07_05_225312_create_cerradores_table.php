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
        Schema::create('cerradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->float('comision');
            $table->float('iva');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
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
        Schema::dropIfExists('cerradores');
    }
};
