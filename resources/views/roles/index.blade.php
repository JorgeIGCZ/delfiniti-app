@extends('layouts.app')
@section('scripts')
<script>
    getPermisos();
    document.getElementById('tipo-usuario').addEventListener('change', (event) => {
        getPermisos();
    });
    document.getElementById('guardar-roles').addEventListener('click', (event) =>{
        event.preventDefault();
        const roles     = document.getElementById('roles-form');
        changeRoles(roles);
    });

    function getPermisos(){
        let tipoUsuario = document.getElementById("tipo-usuario");
        tipoUsuario     = tipoUsuario.options[tipoUsuario.selectedIndex].value;
        axios.get(`/roles/${tipoUsuario}`)
        .then(function (response) {
            const roles = document.getElementById('roles-form');
            const clist = document.getElementsByTagName("input");
            for (var i = 0; i < clist.length; ++i) { clist[i].checked = false; }

            response.data.forEach(element => {
                roles.elements[element.name].checked = true;
            });
            //$roles.elements['usuarios-ver'].checked = true;
        })
        .catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: `Error en obtención de roles E:${error.message}`,
                showConfirmButton: true
            })
        });
    }

    function changeRoles(roles){
        let tipoUsuario = document.getElementById("tipo-usuario");
        tipoUsuario     = tipoUsuario.options[tipoUsuario.selectedIndex].value;
        //const nombre          = reservacion.elements['reporte-corte-caja-ver'].value;
        //const codigoDescuento = reservacion.elements['codigo-descuento'].value;
        $('.loader').show();
        axios.post('/roles', {
            '_token'                   : '{{ csrf_token() }}',
            'tipoUsuario'              : tipoUsuario,
            'permisos'                 : {
                'SeccionReservaciones.index'  : roles.elements['SeccionReservaciones.index'    ].checked,

                'Reportes.index'                           : roles.elements['Reportes.index'                          ].checked,
                'Reportes.CorteCaja.index'                 : roles.elements['Reportes.CorteCaja.index'                ].checked,
                'Reportes.Reservaciones.index'             : roles.elements['Reportes.Reservaciones.index'            ].checked,
                'Reportes.Comisiones.index'                : roles.elements['Reportes.Comisiones.index'               ].checked,
                'Reportes.CuponesAgenciaConcentrado.index' : roles.elements['Reportes.CuponesAgenciaConcentrado.index'].checked,
                'Reportes.CuponesAgenciaDetallado.index'   : roles.elements['Reportes.CuponesAgenciaDetallado.index'  ].checked,

                'Checkin.index'               : roles.elements['Checkin.index'                 ].checked,
                'Checkin.create'              : roles.elements['Checkin.create'                ].checked,
                'Checkin.update'              : roles.elements['Checkin.update'                ].checked,

                'Disponibilidad.index'        : roles.elements['Disponibilidad.index'          ].checked,

                'Cupones.index'              : roles.elements['Cupones.index'                  ].checked,

                'Reservaciones.index'         : roles.elements['Reservaciones.index'           ].checked,
                'Reservaciones.create'        : roles.elements['Reservaciones.create'          ].checked,
                'Reservaciones.update'        : roles.elements['Reservaciones.update'          ].checked,
                'Reservaciones.cancel'        : roles.elements['Reservaciones.cancel'          ].checked,

                'Comisiones.index'            : roles.elements['Comisiones.index'              ].checked,
                'Comisiones.create'           : roles.elements['Comisiones.create'             ].checked,
                'Comisiones.update'           : roles.elements['Comisiones.update'             ].checked,

                'Actividades.index'           : roles.elements['Actividades.index'             ].checked,
                'Actividades.create'          : roles.elements['Actividades.create'            ].checked,
                'Actividades.update'          : roles.elements['Actividades.update'            ].checked,

                'Alojamientos.index'          : roles.elements['Alojamientos.index'            ].checked,
                'Alojamientos.create'         : roles.elements['Alojamientos.create'           ].checked,
                'Alojamientos.update'         : roles.elements['Alojamientos.update'           ].checked,

                'Comisionista.index'          : roles.elements['Comisionista.index'            ].checked,
                'Comisionista.create'         : roles.elements['Comisionista.create'           ].checked,
                'Comisionista.update'         : roles.elements['Comisionista.update'           ].checked,

                'CanalesVenta.index'          : roles.elements['CanalesVenta.index'            ].checked,
                'CanalesVenta.create'         : roles.elements['CanalesVenta.create'           ].checked,
                'CanalesVenta.update'         : roles.elements['CanalesVenta.update'           ].checked,

                'CodigosDescuento.index'      : roles.elements['CodigosDescuento.index'        ].checked,
                'CodigosDescuento.create'     : roles.elements['CodigosDescuento.create'       ].checked,
                'CodigosDescuento.update'     : roles.elements['CodigosDescuento.update'       ].checked,

                'Usuarios.index'              : roles.elements['Usuarios.index'                ].checked,
                'Usuarios.create'             : roles.elements['Usuarios.create'               ].checked,
                'Usuarios.update'             : roles.elements['Usuarios.update'               ].checked,

                'Usuarios.Roles.index'        : roles.elements['Usuarios.Roles.index'          ].checked,
                'Usuarios.Roles.update'       : roles.elements['Usuarios.Roles.update'         ].checked,

                'TipoCambio.index'            : roles.elements['TipoCambio.index'              ].checked,
                'TipoCambio.update'           : roles.elements['TipoCambio.update'             ].checked,

                'SeccionTienda.index'         : roles.elements['SeccionTienda.index'           ].checked,

                'TiendaVentas.index'          : roles.elements['TiendaVentas.index'            ].checked,
                'TiendaVentas.create'         : roles.elements['TiendaVentas.create'           ].checked,
                'TiendaVentas.update'         : roles.elements['TiendaVentas.update'           ].checked,
                'TiendaVentas.cancel'         : roles.elements['TiendaVentas.cancel'           ].checked,

                'TiendaPedidos.index'         : roles.elements['TiendaPedidos.index'           ].checked,
                'TiendaPedidos.create'        : roles.elements['TiendaPedidos.create'          ].checked,
                'TiendaPedidos.update'        : roles.elements['TiendaPedidos.update'          ].checked,
                'TiendaPedidos.cancel'        : roles.elements['TiendaPedidos.cancel'          ].checked,

                'TiendaProveedores.index'     : roles.elements['TiendaProveedores.index'       ].checked,
                'TiendaProveedores.create'    : roles.elements['TiendaProveedores.create'      ].checked,
                'TiendaProveedores.update'    : roles.elements['TiendaProveedores.update'      ].checked,

                'TiendaComisionista.index'     : roles.elements['TiendaComisionista.index'       ].checked,
                'TiendaComisionista.update'    : roles.elements['TiendaComisionista.update'      ].checked,

                'TiendaProductos.index'       : roles.elements['TiendaProductos.index'         ].checked,
                'TiendaProductos.create'      : roles.elements['TiendaProductos.create'        ].checked,
                'TiendaProductos.update'      : roles.elements['TiendaProductos.update'        ].checked,

                'TiendaAutorizacionPedidos.index'  : roles.elements['TiendaAutorizacionPedidos.index' ].checked,
                'TiendaAutorizacionPedidos.update' : roles.elements['TiendaAutorizacionPedidos.update'].checked,

                'TiendaComisiones.index'    : roles.elements['TiendaComisiones.index'      ].checked,
                'TiendaComisiones.create'   : roles.elements['TiendaComisiones.create'     ].checked,
                'TiendaComisiones.update'   : roles.elements['TiendaComisiones.update'     ].checked,

                'SeccionFotoVideo.index'      : roles.elements['SeccionFotoVideo.index'        ].checked,

                'FotoVideoVentas.index'       : roles.elements['FotoVideoVentas.index'         ].checked,
                'FotoVideoVentas.create'      : roles.elements['FotoVideoVentas.create'        ].checked,
                'FotoVideoVentas.update'      : roles.elements['FotoVideoVentas.update'        ].checked,
                'FotoVideoVentas.cancel'      : roles.elements['FotoVideoVentas.cancel'        ].checked,

                'FotoVideoProductos.index'    : roles.elements['FotoVideoProductos.index'      ].checked,
                'FotoVideoProductos.create'   : roles.elements['FotoVideoProductos.create'     ].checked,
                'FotoVideoProductos.update'   : roles.elements['FotoVideoProductos.update'     ].checked,

                'FotoVideoComisionistas.index'    : roles.elements['FotoVideoComisionistas.index'      ].checked,
                'FotoVideoComisionistas.create'   : roles.elements['FotoVideoComisionistas.create'     ].checked,
                'FotoVideoComisionistas.update'   : roles.elements['FotoVideoComisionistas.update'     ].checked,

                'FotoVideoComisiones.index'    : roles.elements['FotoVideoComisiones.index'      ].checked,
                'FotoVideoComisiones.create'   : roles.elements['FotoVideoComisiones.create'     ].checked,
                'FotoVideoComisiones.update'   : roles.elements['FotoVideoComisiones.update'     ].checked,

                'Directivos.index'    : roles.elements['Directivos.index'      ].checked,
                'Directivos.create'   : roles.elements['Directivos.create'     ].checked,
                'Directivos.update'   : roles.elements['Directivos.update'     ].checked,

                'Supervisores.index'    : roles.elements['Supervisores.index'      ].checked,
                'Supervisores.create'   : roles.elements['Supervisores.create'     ].checked,
                'Supervisores.update'   : roles.elements['Supervisores.update'     ].checked,

                'Configuracion' : roles.elements['Configuracion'     ].checked,
            }
        })
        .then(function (response) {
            $('.loader').hide();
            if(response.data.result == 'Success'){
                Swal.fire({
                    icon: 'success',
                    title: 'Roles actualizados',
                    showConfirmButton: false,
                    timer: 1500
                })
                location.reload()
            }else{
                Swal.fire({
                    icon: 'error',
                    title: `Actualización fallida`,
                    showConfirmButton: true
                })
            }
        })
        .catch(function (error) {
            $('.loader').hide();
            Swal.fire({
                icon: 'error',
                title: `Actualización fallida E:${error.message}`,
                showConfirmButton: true
            })
        });
    }
</script>
@endsection
@section('content')
<div class="az-content az-content-dashboard-two">
    <div class="az-content-body az-content-header d-block d-md-flex">
      <div>
        <h2 class="az-content-title tx-24 mg-b-5 mg-b-lg-8">Configuración de Permisos</h2>
      </div>
    </div><!-- az-content-header -->
    <div class="az-content-body">
      <div class="row row-sm mg-b-20 mg-lg-b-20">
        <div class="col-md-6 col-xl-12">
          <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="form-group col-2 mt-3">
                    <label for="tipo-usuario">
                        <strong>Rol</strong>
                    </label>
                    <select id="tipo-usuario" class="form-control card-title">
                        @foreach($roles as $rol)
                            <option value="{{$rol->id}}">{{$rol->name}}</option>
                        @endforeach
                    </select>
                </div>
                <form class="row g-3 f-auto" id="roles-form">
                    <div class="col-md-12 col-xl-12 mt-5 mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Sección configuración</strong>

                                <div class="checkbox checkbox-primary">
                                    <input name="Configuracion" type="checkbox">
                                    <label for="Configuracion">
                                        Ver
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <strong>Usuarios</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Usuarios.index" type="checkbox" >
                            <label for="Usuarios.index">
                                ver
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                        <input name="Usuarios.create" type="checkbox" >
                        <label for="Usuarios.create">
                            Crear 
                        </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Usuarios.update" type="checkbox" >
                            <label for="Usuarios.update">
                                Modificar 
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <strong>Roles</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Usuarios.Roles.index" type="checkbox" >
                            <label for="Usuarios.Roles.index">
                                Ver 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Usuarios.Roles.update" type="checkbox" >
                            <label for="Usuarios.Roles.update">
                                Modificar 
                            </label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <strong>Tipos de cambio</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="TipoCambio.index" type="checkbox" >
                            <label for="TipoCambio.index">
                                Ver 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="TipoCambio.update" type="checkbox" >
                            <label for="TipoCambio.update">
                                Modificar 
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <strong>Directivos</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Directivos.index" type="checkbox" >
                            <label for="Directivos.index">
                                Ver 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Directivos.create" type="checkbox" >
                            <label for="Directivos.create">
                                Crear 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Directivos.update" type="checkbox" >
                            <label for="Directivos.update">
                                Modificar 
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <strong>Supervisores</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Supervisores.index" type="checkbox" >
                            <label for="Supervisores.index">
                                Ver 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Supervisores.create" type="checkbox" >
                            <label for="Supervisores.create">
                                Crear 
                            </label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input name="Supervisores.update" type="checkbox" >
                            <label for="Supervisores.update">
                                Modificar 
                            </label>
                        </div>
                    </div>


                    <div class="col-md-12 col-xl-12 mt-5 mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Sección reportes</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.index" type="checkbox">
                                    <label for="Reportes.index">
                                        Ver
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-12 ml-5">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.CorteCaja.index" type="checkbox" >
                                    <label for="Reportes.CorteCaja.index">
                                    Corte caja
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.Reservaciones.index" type="checkbox" >
                                    <label for="Reportes.Reservaciones.index">
                                    Reservaciones
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.Comisiones.index" type="checkbox" >
                                    <label for="Reportes.Comisiones.index">
                                        Comisiones
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.CuponesAgenciaConcentrado.index" type="checkbox" >
                                    <label for="Reportes.CuponesAgenciaConcentrado.index">
                                        Cup. Agencia concentrado
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reportes.CuponesAgenciaDetallado.index" type="checkbox" >
                                    <label for="Reportes.CuponesAgenciaDetallado.index">
                                        Cup. Agencia Detallado
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="col-md-12 col-xl-12 mt-5 mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Sección reservaciones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="SeccionReservaciones.index" type="checkbox">
                                    <label for="SeccionReservaciones.index">
                                        Ver
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-12 ml-5">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Check-in</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Checkin.index" type="checkbox" >
                                    <label for="Checkin.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="Checkin.create" type="checkbox" >
                                <label for="Checkin.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Checkin.update" type="checkbox" >
                                    <label for="Checkin.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Disponibilidad</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Disponibilidad.index" type="checkbox" >
                                    <label for="Disponibilidad.index">
                                        Ver 
                                    </label>
                                </div>
                            </div> 

                            <div class="col-md-2">
                                <strong>Cupones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Cupones.index" type="checkbox" >
                                    <label for="Cupones.index">
                                        Ver 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Reservaciones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reservaciones.index" type="checkbox" >
                                    <label for="ver">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="Reservaciones.create" type="checkbox" >
                                <label for="Reservaciones.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reservaciones.update" type="checkbox" >
                                    <label for="Reservaciones.update">
                                        Modificar 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Reservaciones.cancel" type="checkbox" >
                                    <label for="Reservaciones.cancel">
                                        Cancelar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Comisiones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Comisiones.index" type="checkbox" >
                                    <label for="Comisiones.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="Comisiones.create" type="checkbox" >
                                <label for="Comisiones.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Comisiones.update" type="checkbox" >
                                    <label for="Comisiones.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <strong>Actividades</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Actividades.index" type="checkbox" >
                                    <label for="Actividades.index">
                                        Ver 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Actividades.create" type="checkbox" >
                                    <label for="Actividades.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Actividades.update" type="checkbox" >
                                    <label for="Actividades.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Alojamientos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Alojamientos.index" type="checkbox" >
                                    <label for="Alojamientos.index">
                                        Ver 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Alojamientos.create" type="checkbox" >
                                    <label for="Alojamientos.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Alojamientos.update" type="checkbox" >
                                    <label for="Alojamientos.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Comisionistas</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="Comisionista.index" type="checkbox" >
                                    <label for="Comisionista.index">
                                        Ver 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Comisionista.create" type="checkbox" >
                                    <label for="Comisionista.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="Comisionista.update" type="checkbox" >
                                    <label for="Comisionista.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Canales De Venta</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="CanalesVenta.index" type="checkbox" >
                                    <label for="CanalesVenta.index">
                                        Ver 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="CanalesVenta.create" type="checkbox" >
                                    <label for="CanalesVenta.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="CanalesVenta.update" type="checkbox" >
                                    <label for="CanalesVenta.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Codigos descuento</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="CodigosDescuento.index" type="checkbox" >
                                    <label for="CodigosDescuento.index">
                                        Ver 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="CodigosDescuento.create" type="checkbox" >
                                    <label for="CodigosDescuento.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="CodigosDescuento.update" type="checkbox" >
                                    <label for="CodigosDescuento.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="col-md-12 col-xl-12 mt-5 mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Sección tienda</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="SeccionTienda.index" type="checkbox">
                                    <label for="SeccionTienda.index">
                                        Ver
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-12 ml-5">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Ventas</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaVentas.index" type="checkbox" >
                                    <label for="TiendaVentas.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="TiendaVentas.create" type="checkbox" >
                                <label for="TiendaVentas.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaVentas.update" type="checkbox" >
                                    <label for="TiendaVentas.update">
                                        Modificar 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaVentas.cancel" type="checkbox" >
                                    <label for="TiendaVentas.cancel">
                                        Cancelar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Pedidos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaPedidos.index" type="checkbox" >
                                    <label for="TiendaPedidos.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="TiendaPedidos.create" type="checkbox" >
                                <label for="TiendaPedidos.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaPedidos.update" type="checkbox" >
                                    <label for="TiendaPedidos.update">
                                        Modificar 
                                    </label>
                                </div>

                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaPedidos.cancel" type="checkbox" >
                                    <label for="TiendaPedidos.cancel">
                                        Cancelar 
                                    </label>
                                </div>
                            </div> 

                            <div class="col-md-2">
                                <strong>Proveedores</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaProveedores.index" type="checkbox" >
                                    <label for="ver">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaProveedores.create" type="checkbox" >
                                    <label for="TiendaProveedores.create">
                                        Crear 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaProveedores.update" type="checkbox" >
                                    <label for="TiendaProveedores.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Comisionistas</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaComisionista.index" type="checkbox" >
                                    <label for="ver">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaComisionista.update" type="checkbox" >
                                    <label for="TiendaComisionista.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Productos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaProductos.index" type="checkbox" >
                                    <label for="TiendaProductos.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="TiendaProductos.create" type="checkbox" >
                                <label for="TiendaProductos.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaProductos.update" type="checkbox" >
                                    <label for="TiendaProductos.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Autorización pedidos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaAutorizacionPedidos.index" type="checkbox" >
                                    <label for="TiendaAutorizacionPedidos.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaAutorizacionPedidos.update" type="checkbox" >
                                    <label for="TiendaAutorizacionPedidos.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>



                            <div class="col-md-2">
                                <strong>Comisiones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaComisiones.index" type="checkbox" >
                                    <label for="TiendaComisiones.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="TiendaComisiones.create" type="checkbox" >
                                <label for="TiendaComisiones.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="TiendaComisiones.update" type="checkbox" >
                                    <label for="TiendaComisiones.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>




                    <div class="col-md-12 col-xl-12 mt-5 mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Sección foto y video</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="SeccionFotoVideo.index" type="checkbox">
                                    <label for="SeccionFotoVideo.index">
                                        Ver
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-12 ml-5">
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Ventas</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoVentas.index" type="checkbox" >
                                    <label for="FotoVideoVentas.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="FotoVideoVentas.create" type="checkbox" >
                                <label for="FotoVideoVentas.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoVentas.update" type="checkbox" >
                                    <label for="FotoVideoVentas.update">
                                        Modificar 
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoVentas.cancel" type="checkbox" >
                                    <label for="FotoVideoVentas.cancel">
                                        Cancelar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Productos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoProductos.index" type="checkbox" >
                                    <label for="FotoVideoProductos.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="FotoVideoProductos.create" type="checkbox" >
                                <label for="FotoVideoProductos.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoProductos.update" type="checkbox" >
                                    <label for="FotoVideoProductos.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Fotógrafos</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoComisionistas.index" type="checkbox" >
                                    <label for="FotoVideoComisionistas.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="FotoVideoComisionistas.create" type="checkbox" >
                                <label for="FotoVideoComisionistas.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoComisionistas.update" type="checkbox" >
                                    <label for="FotoVideoComisionistas.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <strong>Comisiones</strong>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoComisiones.index" type="checkbox" >
                                    <label for="FotoVideoComisiones.index">
                                        ver
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                <input name="FotoVideoComisiones.create" type="checkbox" >
                                <label for="FotoVideoComisiones.create">
                                    Crear 
                                </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input name="FotoVideoComisiones.update" type="checkbox" >
                                    <label for="FotoVideoComisiones.update">
                                        Modificar 
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @can('Usuarios.Roles.update')
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-2">
                                    <button class="btn btn-info btn-block mt-33" usuario="" id="guardar-roles">Guardar</button>
                                </div>
                            </div>
                        </div>
                    @endcan
                </form>
            </div>
          </div>
        </div>
        <!-- col -->
      </div>
    </div><!-- az-content-body -->  
  </div>
   
@endsection
