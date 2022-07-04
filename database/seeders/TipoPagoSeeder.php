<?php

namespace Database\Seeders;

use App\Models\TipoPago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoPago::create([
            'nombre'   => 'efectivo'
        ]);
        TipoPago::create([
            'nombre'   => 'efectivoUsd'
        ]);
        TipoPago::create([
            'nombre'   => 'tarjeta'
        ]);
        TipoPago::create([
            'nombre'   => 'descuentoAgencia'
        ]);
        TipoPago::create([
            'nombre'   => 'descuentoCodigo'
        ]);
        TipoPago::create([
            'nombre'   => 'descuentoPersonalizado'
        ]);
    }
}
