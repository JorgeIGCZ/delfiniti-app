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
        $administrador = Role::create(['name' => 'Administrador']);
        $supervisor    = Role::create(['name' => 'Supervisor']);
        $responsableReservacionesInterno = Role::create(['name' => 'Responsable reservaciones interno']);
        $responsableReservacionesExterno = Role::create(['name' => 'Responsable reservaciones externo']);

        Permission::create(['name' => 'Usuarios.index'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.create'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.update'])->assignRole($administrador);

        Permission::create(['name' => 'Usuarios.Roles.index'])->assignRole($administrador);
        Permission::create(['name' => 'Usuarios.Roles.update'])->assignRole($administrador);

        Permission::create(['name' => 'Reportes.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.CorteCaja.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Comisiones.index'])->assignRole($administrador);

        Permission::create(['name' => 'Disponibilidad.index'])->assignRole($administrador);

        Permission::create(['name' => 'Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.create'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Actividades.index'])->assignRole($administrador);
        Permission::create(['name' => 'Actividades.create'])->assignRole($administrador);
        Permission::create(['name' => 'Actividades.update'])->assignRole($administrador);

        Permission::create(['name' => 'Comisiones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Comisiones.create'])->assignRole($administrador);
        Permission::create(['name' => 'Comisiones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Alojamientos.index'])->assignRole($administrador);
        Permission::create(['name' => 'Alojamientos.create'])->assignRole($administrador);
        Permission::create(['name' => 'Alojamientos.update'])->assignRole($administrador);

        Permission::create(['name' => 'TipoCambio.index'])->assignRole($administrador);
        Permission::create(['name' => 'TipoCambio.update'])->assignRole($administrador);

        Permission::create(['name' => 'TiposComisionista.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiposComisionista.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiposComisionista.update'])->assignRole($administrador);

        Permission::create(['name' => 'CodigosDescuento.index'])->assignRole($administrador);
        Permission::create(['name' => 'CodigosDescuento.create'])->assignRole($administrador);
        Permission::create(['name' => 'CodigosDescuento.update'])->assignRole($administrador);

        Permission::create(['name' => 'Cerradores.index'])->assignRole($administrador);
        Permission::create(['name' => 'Cerradores.create'])->assignRole($administrador);
        Permission::create(['name' => 'Cerradores.update'])->assignRole($administrador);
    }
}
