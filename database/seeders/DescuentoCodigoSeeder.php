<?php

namespace Database\Seeders;

use App\Models\DescuentoCodigo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DescuentoCodigoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DescuentoCodigo::create([
            'id'        => '1',
            'nombre'    => 'CORTESIA',
            'tipo'      => 'porcentaje',
            'descuento' => '100',
            'estatus'   => '1'
        ]);
    }
}
