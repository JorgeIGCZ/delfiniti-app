<?php

namespace Database\Seeders;

use App\Models\CanalVenta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CanalVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CanalVenta::create([
            'id'       => 10,
            'nombre'   => 'CERRADORES'
        ]);
    }
}
