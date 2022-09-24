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
        axios.post('/roles', {
            '_token'                   : '{{ csrf_token() }}',
            'tipoUsuario'              : tipoUsuario,
            'permisos'                 : {
                'Reportes.index'              : roles.elements['Reportes.index'                ].checked,
                'Reportes.CorteCaja.index'    : roles.elements['Reportes.CorteCaja.index'      ].checked,
                'Reportes.Reservaciones.index': roles.elements['Reportes.Reservaciones.index'  ].checked,
                'Reportes.Comisiones.index'   : roles.elements['Reportes.Comisiones.index'     ].checked,

                'Checkin.index'               : roles.elements['Checkin.index'                 ].checked,
                'Checkin.create'              : roles.elements['Checkin.create'                ].checked,
                'Checkin.update'              : roles.elements['Checkin.update'                ].checked,

                'Disponibilidad.index'        : roles.elements['Disponibilidad.index'          ].checked,

                'Reservaciones.index'         : roles.elements['Reservaciones.index'           ].checked,
                'Reservaciones.create'        : roles.elements['Reservaciones.create'          ].checked,
                'Reservaciones.update'        : roles.elements['Reservaciones.update'          ].checked,

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
            }
        })
        .then(function (response) {
            if(response.data.result == 'Success'){
                Swal.fire({
                    icon: 'success',
                    title: 'Roles actualizados',
                    showConfirmButton: false,
                    timer: 1500
                })
            }else{
                Swal.fire({
                    icon: 'error',
                    title: `Actualización fallida`,
                    showConfirmButton: true
                })
            }
        })
        .catch(function (error) {
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
        <h2 class="az-content-title tx-24 mg-b-5 mg-b-lg-8">Configuración de Permisos.</h2>
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

                    <div class="col-md-2">
                        <strong>Reportes</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Reportes.index" type="checkbox" >
                            <label for="Reportes.index">
                                Ver
                            </label>
                        </div>
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
                    </div>

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
                    </div>

                    <div class="col-md-2">
                        <strong>Comisiones</strong>
                        <div class="checkbox checkbox-primary">
                            <input name="Comisiones.index" type="checkbox" >
                            <label for="Comisiones.ver">
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
                            <input name="Comisiones.index" type="checkbox" >
                            <label for="Comisiones.index">
                                Ver 
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
                    <div class="col-md-12">
                        <button class="btn btn-primary " usuario="" id="guardar-roles">Guardar</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
        <!-- col -->
      </div>
    </div><!-- az-content-body -->  
  </div>
   
@endsection
