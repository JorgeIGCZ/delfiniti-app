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
            'nombre'   => 'Efectivo'
        ]);
        TipoPago::create([
            'nombre'   => 'Efectivo USD'
        ]);
        TipoPago::create([
            'nombre'   => 'Tarjeta'
        ]);
        TipoPago::create([
            'nombre'   => 'Cupón'
        ]);
        TipoPago::create([
            'nombre'   => 'Código Descuento'
        ]);
        TipoPago::create([
            'nombre'   => 'Descuento Personalizado'
        ]);
        TipoPago::create([
            'nombre'   => 'Cambio'
        ]);
    }
}
