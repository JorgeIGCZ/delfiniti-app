<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrador = 'Administrador';

        Permission::create(['name' => 'SeccionReservaciones.index'])->assignRole($administrador);

        Permission::create(['name' => 'SeccionTienda.index'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaVentas.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaVentas.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaVentas.update'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaVentas.cancel'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaPedidos.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaPedidos.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaPedidos.update'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaPedidos.cancel'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaProveedores.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProveedores.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProveedores.update'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaProductos.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProductos.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProductos.update'])->assignRole($administrador);

        Permission::create(['name' => 'SeccionFotoVideo.index'])->assignRole($administrador);
    }
}
