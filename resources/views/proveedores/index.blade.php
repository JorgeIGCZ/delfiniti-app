@extends('layouts.app')
@section('scripts')
    <script>
        let proveedoresTable;
        
        function formValidity(formId){
            const reservacion = document.getElementById(formId);
            let response = true;
            if(reservacion.checkValidity()){
                event.preventDefault();
            }else{
                reservacion.reportValidity();
                response = false;
            }
            return response;
        }

        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar al proveedor?',
                text: "El proveedor dejará de estar disponible!!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateProveedorEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }

        function updateProveedorEstatus(id,estatus){
            $('.loader').show();
            axios.post(`proveedores/estatus/${id}`, {
                '_token'  : '{{ csrf_token() }}',
                'estatus' : estatus,
                '_method' : 'PATCH'
            })
            .then(function (response) {
                $('.loader').hide();
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro actualizado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    proveedoresTable.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Actualización fallida',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
                proveedores.reset();
            }); 
        }

        function createProveedor(proveedores){
            $('.loader').show();
            axios.post('/proveedores', {
                "_token"          : "{{ csrf_token() }}",
                "clave"           : proveedores.elements['clave'].value,
                "razonSocial"     : proveedores.elements['razon_social'].value,
                "rfc"             : proveedores.elements['rfc'].value,
                "nombreContacto"  : proveedores.elements['nombre_contacto'].value,
                "cargoContacto"   : proveedores.elements['cargo_contacto'].value,
                "direccion"       : proveedores.elements['direccion'].value,
                "telefono"        : proveedores.elements['telefono'].value,
                "ciudad"          : proveedores.elements['ciudad'].checked,
                "estado"          : proveedores.elements['estado'].value,
                "cp"              : proveedores.elements['cp'].value,
                "pais"            : proveedores.elements['pais'].value,
                "email"           : proveedores.elements['email'].value,
                "comentarios"     : proveedores.elements['comentarios'].value,
            })
            .then(function (response) {
                $('.loader').hide();
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro creado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    location.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Registro fallido',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Registro fallido',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
        }
        $(function(){
            proveedoresTable = new DataTable('#proveedores', {
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5', 
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    footer: true,
                    text: 'Exportar Excel',
                    title: 'GUERRERO DOLPHIN S.A. DE C.V. - REPORTE PROVEEDORES',
                    exportOptions: {
                        columns: [0, 1, 3, 5, 7]
                    }
                }],
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/proveedores/show')
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                columns: [
                    { data: 'clave' },
                    { data: 'razon_social' },
                    { data: 'RFC' },
                    { data: 'nombre_contacto' },
                    { data: 'cargo_contacto' },
                    { data: 'direccion' },
                    { data: 'estado' },
                    { data: 'telefono' },
                    { data: 'email' },
                    { data: 'comentarios' }, 
                    { defaultContent: 'estatus', 'render': function ( data, type, row ) 
                        {
                            if(row.estatus){
                                    return 'Activo';
                            }
                            return 'Inactivo';
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let estatusRow = '';
                            let view       = '';  
                            @can('TiendaProveedores.update')
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateProveedorEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                                view    =   `<small> 
                                                <a href="proveedores/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            @endcan
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('proveedores-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const proveedores = document.getElementById('proveedores-form');
                if(formValidity('proveedores-form')){
                    createProveedor(proveedores);
                }
            });

            $('#tipo').on('change', function (e) {
                document.querySelectorAll("#tipo option").forEach(function(el) {
                    el.removeAttribute("selected");
                })

                changeComisionesSettings();
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Proveedores</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    @can('TiendaProveedores.create')
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body"> 
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="proveedores-form">
                                @csrf
                                <div class="form-group col-1 mt-3">
                                    <label for="clave" class="col-form-label">Clave</label>    
                                    <input type="text" name="clave" class="form-control" required="required">  
                                </div>
                                <div class="form-group col-3 mt-3">
                                    <label for="razon_social" class="col-form-label">Razon social</label>    
                                    <input type="text" name="razon_social" class="form-control to-uppercase" required="required">  
                                </div>
                                <div class="form-group col-3 mt-3">
                                    <label for="rfc" class="col-form-label">RFC</label>    
                                    <input type="text" name="rfc" class="form-control to-uppercase">  
                                </div>

                                <div class="col-12 mt-3 general-settings">
                                    <strong>Datos Representante</strong>
                                </div>
                                <div class="form-group col-5 mt-3 general-settings">
                                    <label for="nombre_contacto" class="col-form-label">Nombre</label>
                                    <input type="text" id="nombre_contacto" class="form-control to-uppercase">
                                </div>

                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="cargo_contacto" class="col-form-label">Cargo</label>
                                    <input type="text" id="cargo_contacto" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-5 mt-3 general-settings">
                                    <label for="direccion" class="col-form-label">Dirección</label>
                                    <input type="text" id="direccion" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-5 mt-3 general-settings">
                                    <label for="ciudad" class="col-form-label">Ciudad</label>
                                    <input type="text" id="ciudad" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="estado" class="col-form-label">Estado</label>
                                    <input type="text" id="estado" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="cp" class="col-form-label">CP</label>
                                    <input type="text" id="cp" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="pais" class="col-form-label">Pais</label>
                                    <input type="text" id="pais" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-3 mt-3 general-settings">
                                    <label for="telefono" class="col-form-label">Teléfono</label>
                                    <input type="text" id="telefono" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="email" class="col-form-label">Email</label>
                                    <input type="text" id="email" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-5 mt-3 general-settings">
                                    <label for="comentarios" class="col-form-label">Comentarios</label>
                                    <input type="text" id="comentarios" class="form-control to-uppercase">
                                </div>
                            

                                <div class="form-group col-3 mt-3">
                                    <button class="btn btn-info btn-block mt-33" id="crear-proveedor">Crear proveedor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="proveedores" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Razón social</th>
                                        <th>RFC</th>
                                        <th>Contacto</th>
                                        <th>Cargo</th>
                                        <th>Dirección</th>
                                        <th>Estado</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Comentarios</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
