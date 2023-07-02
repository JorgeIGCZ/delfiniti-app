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
        Schema::create('directivo_comisiones_reservacion_actividad_detalle', function (Blueprint $table) {
            $table->id();
            // $table->integer('directivo_id'); removido ya qeues general la comision para directivos en actividad
            $table->integer('actividad_id');
            $table->float('comision')->default(0)->comment('Solo porcentaje');
            $table->float('descuento_impuesto')->default(0)->comment('Solo porcentaje');
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
        Schema::dropIfExists('directivo_comisiones_reservacion_actividad_detalle');
    }
};
