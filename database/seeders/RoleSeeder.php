<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        Permission::truncate();

        $administrador = Role::create(['name' => 'Administrador']);
        $supervisor    = Role::create(['name' => 'Supervisor']);
        $recepcion     = Role::create(['name' => 'Recepcion']);
        $mercadotecnia = Role::create(['name' => 'mercadotecnia']);
        $contabilidad  = Role::create(['name' => 'contabilidad']);

        Permission::create(['name' => 'Reportes.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.CorteCaja.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Comisiones.index'])->assignRole($administrador);

        Permission::create(['name' => 'Checkin.index'])->assignRole($administrador);
        Permission::create(['name' => 'Checkin.create'])->assignRole($administrador);
        Permission::create(['name' => 'Checkin.update'])->assignRole($administrador);

        Permission::create(['name' => 'Disponibilidad.index'])->assignRole($administrador);

        Permission::create(['name' => 'Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.create'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Comisiones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Comisiones.create'])->assignRole($administrador);
        Permission::create(['name' => 'Comisiones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Actividades.index'])->assignRole($administrador);
        Permission::create(['name' => 'Actividades.create'])->assignRole($administrador);
        Permission::create(['name' => 'Actividades.update'])->assignRole($administrador);

        Permission::create(['name' => 'Alojamientos.index'])->assignRole($administrador);
        Permission::create(['name' => 'Alojamientos.create'])->assignRole($administrador);
        Permission::create(['name' => 'Alojamientos.update'])->assignRole($administrador);

        Permission::create(['name' => 'Comisionista.index'])->assignRole($administrador);
        Permission::create(['name' => 'Comisionista.create'])->assignRole($administrador);
        Permission::create(['name' => 'Comisionista.update'])->assignRole($administrador);

        Permission::create(['name' => 'CanalesVenta.index'])->assignRole($administrador);
        Permission::create(['name' => 'CanalesVenta.create'])->assignRole($administrador);
        Permission::create(['name' => 'CanalesVenta.update'])->assignRole($administrador);

        Permission::create(['name' => 'CodigosDescuento.index'])->assignRole($administrador);
        Permission::create(['name' => 'CodigosDescuento.create'])->assignRole($administrador);
        Permission::create(['name' => 'CodigosDescuento.update'])->assignRole($administrador);

        Permission::create(['name' => 'Usuarios.index'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.create'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.update'])->assignRole($administrador);

        Permission::create(['name' => 'Usuarios.Roles.index'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.Roles.update'])->assignRole($administrador);

        Permission::create(['name' => 'TipoCambio.index'])->assignRole($administrador);
        Permission::create(['name' => 'TipoCambio.update'])->assignRole($administrador);
    }
}
