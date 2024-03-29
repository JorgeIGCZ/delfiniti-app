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
        $tienda        = Role::create(['name' => 'Tienda']);
        $supervisor    = Role::create(['name' => 'Supervisor']);
        $recepcion     = Role::create(['name' => 'Recepcion']);
        $mercadotecnia = Role::create(['name' => 'Mercadotecnia']);
        $contabilidad  = Role::create(['name' => 'Contabilidad']);
        $fotoVideo     = Role::create(['name' => 'FotoVideo']);

        Permission::create(['name' => 'SeccionReservaciones.index'])->assignRole($administrador);

        Permission::create(['name' => 'Reportes.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.CorteCaja.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.Comisiones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.CuponesAgenciaConcentrado.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reportes.CuponesAgenciaDetallado.index'])->assignRole($administrador);

        Permission::create(['name' => 'Checkin.index'])->assignRole($administrador);
        Permission::create(['name' => 'Checkin.create'])->assignRole($administrador);
        Permission::create(['name' => 'Checkin.update'])->assignRole($administrador);

        Permission::create(['name' => 'Disponibilidad.index'])->assignRole($administrador);
        
        Permission::create(['name' => 'Cupones.index'])->assignRole($administrador);

        Permission::create(['name' => 'Reservaciones.index'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.create'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.update'])->assignRole($administrador);
        Permission::create(['name' => 'Reservaciones.cancel'])->assignRole($administrador);

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

        Permission::create(['name' => 'TiendaComisionista.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaComisionista.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaComisionista.update'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaProductos.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProductos.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaProductos.update'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaAutorizacionPedidos.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaAutorizacionPedidos.update'])->assignRole($administrador);

        Permission::create(['name' => 'SeccionFotoVideo.index'])->assignRole($administrador);

        Permission::create(['name' => 'FotoVideoVentas.index'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoVentas.create'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoVentas.update'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoVentas.cancel'])->assignRole($administrador);

        Permission::create(['name' => 'FotoVideoProductos.index'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoProductos.create'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoProductos.update'])->assignRole($administrador);

        Permission::create(['name' => 'FotoVideoComisionistas.index'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoComisionistas.create'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoComisionistas.update'])->assignRole($administrador);

        Permission::create(['name' => 'FotoVideoComisiones.index'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoComisiones.create'])->assignRole($administrador);
        Permission::create(['name' => 'FotoVideoComisiones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Directivos.index'])->assignRole($administrador);
        Permission::create(['name' => 'Directivos.create'])->assignRole($administrador);
        Permission::create(['name' => 'Directivos.update'])->assignRole($administrador);

        Permission::create(['name' => 'Supervisores.index'])->assignRole($administrador);
        Permission::create(['name' => 'Supervisores.create'])->assignRole($administrador);
        Permission::create(['name' => 'Supervisores.update'])->assignRole($administrador);

        Permission::create(['name' => 'TiendaComisiones.index'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaComisiones.create'])->assignRole($administrador);
        Permission::create(['name' => 'TiendaComisiones.update'])->assignRole($administrador);

        Permission::create(['name' => 'Configuracion'])->assignRole($administrador);
    }
}
