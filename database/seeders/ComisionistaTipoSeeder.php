<?php

namespace Database\Seeders;

use App\Models\ComisionistaTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComisionistaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ComisionistaTipo::create([
            'id'       => 10,
            'nombre'   => 'CERRADORES'
        ]);
    }
}
