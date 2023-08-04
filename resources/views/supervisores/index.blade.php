@extends('layouts.app')
@section('scripts')
    <script>
        const supervisores = document.getElementById('supervisores-form');
        let supervisoresTable;
        
        // function formValidity(formId){
        //     const reservacion = document.getElementById(formId);
        //     let response = true;
        //     if(reservacion.checkValidity()){
        //         event.preventDefault();
        //     }else{
        //         reservacion.reportValidity();
        //         response = false;
        //     }
        //     return response;
        // }

        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar al supervisor?',
                text: "El supervisor dejará de estar disponible!!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateSupervisorEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }

        function updateSupervisorEstatus(id,estatus){
            $('.loader').show();
            axios.post(`supervisores/estatus/${id}`, {
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
                    supervisoresTable.ajax.reload();
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
                supervisores.reset();
            }); 
        }
        
        function createSupervisor(){
            $('.loader').show();
            axios.post('/supervisores', {
                "_token"                       : "{{ csrf_token() }}",
                "clave"                        : supervisores.elements['clave'].value,
                "comisionesSobreTienda"        : getComisionesSobreTienda(),
                "comisionesSobreFotoVideo"     : getComisionesSobreFotoVideo(),
                "nombre"                       : supervisores.elements['nombre'].value,
                "direccion"                    : supervisores.elements['direccion'].value,
                "telefono"                     : supervisores.elements['telefono'].value
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

        function getComisionesSobreTienda(){
            return {
                'comision' : supervisores.elements['tienda_comision'].value,
                'iva' : supervisores.elements['tienda_iva'].value,
                'descuentoImpuesto' : supervisores.elements['tienda_descuento_impuesto'].value
            };
        }
        
        function getComisionesSobreFotoVideo(){
            return {
                'comision' : supervisores.elements['foto_video_comision'].value,
                'iva' : supervisores.elements['foto_video_iva'].value,
                'descuentoImpuesto' : supervisores.elements['foto_video_descuento_impuesto'].value
            };
        }


        $(function(){
            supervisoresTable = new DataTable('#supervisores', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/supervisores/show')
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
                    { data: 'nombre' },
                    { data: 'direccion' },
                    { data: 'telefono' },
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
                            @can('Supervisores.update')
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateSupervisorEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                                view    =   `<small> 
                                                <a href="supervisores/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            @endcan
                            return  view;
                        }
                    }
                ]
            } );
            
            if(supervisores !== null){
                supervisores.addEventListener('submit', (event) =>{
                    event.preventDefault();
                    if(formValidity('supervisores-form')){
                        createSupervisor();
                    }
                });
            }
        });

    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Supervisores</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    @can('Supervisores.create')
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body"> 
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="supervisores-form">
                                @csrf
                                <div class="form-group col-1 mt-3">
                                    <label for="clave" class="col-form-label">Código</label>    
                                    <input type="text" name="clave" class="form-control" required="required">  
                                </div>
                                <div class="form-group col-3 mt-3">
                                    <label for="nombre" class="col-form-label">Nombre</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" required="required">  
                                </div>
                                <div class="form-group col-4 mt-3 general-settings">
                                    <label for="direccion" class="col-form-label">Dirección</label>
                                    <input type="text" name="direccion" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-3 mt-3 general-settings">
                                    <label for="telefono" class="col-form-label">Teléfono</label>
                                    <input type="text" name="telefono" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <div class="card wrapper">

                                        <div class="card-header buttonWrapper">
                                            <ul class="nav nav-pills">
                                                <li class="nav-item">
                                                    <a class="nav-link tab-button active" href="#" data-id="tienda">Tienda</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link tab-button" href="#" data-id="foto_video">Foto y video</a>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <div class="card-body">
                                            <div class="tab-content contentWrapper">
                                                <div class="tab-panel content active" id="tienda">
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_comision" class="col-form-label">Comisión %</label>
                                                        <input type="number" step="0.01" name="tienda_comision" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_iva" class="col-form-label">Iva %</label>
                                                        <input type="number" name="tienda_iva" class="form-control" min="0" max="90" value="0">
                                                    </div>
                    
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                        <input type="number" step="0.01" name="tienda_descuento_impuesto" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                </div>
                                                <div class="tab-panel content" id="foto_video">
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_comision" class="col-form-label">Comisión %</label>
                                                        <input type="number" step="0.01" name="foto_video_comision" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_iva" class="col-form-label">Iva %</label>
                                                        <input type="number" name="foto_video_iva" class="form-control" min="0" max="90" value="0">
                                                    </div>
                    
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                        <input type="number" step="0.01" name="foto_video_descuento_impuesto" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-3 mt-3">
                                    <button class="btn btn-info btn-block mt-33" id="crear-supervisor">Crear supervisor</button>
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
                            <table id="supervisores" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
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
