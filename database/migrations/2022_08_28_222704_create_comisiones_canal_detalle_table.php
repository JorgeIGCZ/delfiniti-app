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
        Schema::create('directivo_canal_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer('directivo_id');
            $table->integer('canal_venta_id');
            $table->float('comision')->default(0)->comment('Solo porcentaje');
            $table->float('iva')->default(0);
            $table->float('descuento_impuesto')->default(0);
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
        Schema::dropIfExists('directivo_canal_detalle');
    }
};
