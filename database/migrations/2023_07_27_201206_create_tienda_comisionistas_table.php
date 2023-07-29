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
        Schema::create('tienda_comisionistas', function (Blueprint $table) {
            $table->id(); 
            $table->integer('usuario_id');
            $table->float('comision');
            $table->float('iva');
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
        Schema::dropIfExists('tienda_comisionistas');
    }
};
