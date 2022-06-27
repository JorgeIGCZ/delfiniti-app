<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoCambio;

class TipoCambioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoCambio::create([
            'seccion_uso'   => 'general',
            'divisa'        => 'USD',
            'precio_compra' => 17,
            'precio_venta'  => 20
        ]);
        TipoCambio::create([
            'seccion_uso'   => 'reportes',
            'divisa'        => 'USD',
            'precio_compra' => 20,
            'precio_venta'  => 20
        ]);
    }
}
