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
        Schema::create('canales_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('comisionista_canal')->default(0);
            $table->boolean('comisionista_actividad')->default(0);
            $table->boolean('comisionista_cerrador')->default(0);
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
        Schema::dropIfExists('canales_ventas');
    }
};
