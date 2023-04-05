@extends('layouts.app')
@section('scripts')
    <script>
        let impuestosTable;
        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar el impuesto?',
                text: "El impuesto dejará de estar disponible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateImpuestoEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }
        function updateImpuestoEstatus(id,estatus){
            $('.loader').show();
            axios.post(`impuestos/estatus/${id}`, {
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
                    impuestosTable.ajax.reload();
                }else{
                    $('.loader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Actualización fallida',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
                productos.reset();
            });
        }
        function createImpuesto(productos){
            $('.loader').show();
            axios.post('/impuestos', {
                '_token'   : '{{ csrf_token() }}',
                "nombre"   : productos.elements['nombre'].value,
                "impuesto" : productos.elements['impuesto'].getAttribute('value')
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
            impuestosTable = new DataTable('#impuestos', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/impuestos/show')
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { data: 'impuesto' },
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
                            let view       = '';   
                            let estatusRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateImpuestoEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                            //}
                            // can('impuestos.update')
                            view    =   `<small> 
                                            <a href="impuestos/${row.id}/edit">Editar</a>
                                            ${estatusRow}
                                        </small>`;
                            // endcan
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('impuestos-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const impuesto = document.getElementById('impuestos-form');
                createImpuesto(impuesto);
            });
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Impuestos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    {{-- @can('impuestos.create') --}}
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="impuestos-form">
                                @csrf
                                <div class="form-group col-3 mt-3">
                                    <label for="nombre" class="col-form-label">Nombre del impuesto</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="1" required="required">  
                                </div>
                                <div class="form-group col-1 mt-2">
                                    <label for="impuesto" class="col-form-label">Impuesto</label>
                                    <input type="text" name="impuesto" id="impuesto" class="form-control percentage"  autocomplete="off" tabindex="2">
                                </div>
                                <div class="form-group col-2 mt-3">
                                    <button class="btn btn-info btn-block mt-33" id="crear-impuesto" tabindex="3">Crear impuesto</button>
                                </div> 
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- @endcan --}}
     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="impuestos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Impuesto</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
