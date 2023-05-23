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
        Schema::create('tienda_proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('razon_social');
            $table->string('RFC')->nullable();
            $table->string('nombre_contacto')->nullable();
            $table->string('cargo_contacto')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('cp')->nullable();
            $table->string('pais')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('comentarios')->nullable();
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
        Schema::dropIfExists('tienda_proveedores');
    }
};
