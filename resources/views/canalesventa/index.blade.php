@extends('layouts.app')
@section('scripts')
    <script>
        let canalesventaTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar tipo de comisionista?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyComisionista(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyComisionista(id){
            const canalesventa = document.getElementById('comisionista-tipos-form');
            axios.delete(`/canalesventa/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    canalesventaTable.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Eliminacion fallida',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eliminacion fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
            canalesventaTable.ajax.reload();
        }
        function createComisionista(canalesventa){
            $('.loader').show();
            axios.post('/canalesventa', {
                '_token'  : '{{ csrf_token() }}',
                "nombre"  : canalesventa.elements['nombre'].value,
                "comisionista_tipo"  : canalesventa.elements['comisionista_tipo'].value
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
            canalesventaTable = new DataTable('#comisionista-tipos', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/canalesventa/show')
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
                    { defaultContent: 'tipo', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let tipo = "COMISIONES GENERALES";
                            if(row.comisionista_canal == 1){
                                tipo = "COMISIONES SOBRE CANAL";
                            }else if(row.comisionista_actividad == 1){
                                tipo = "COMISIONES SOBRE ACTIVIDAD";
                            }else if(row.comisionista_cerrador == 1){
                                tipo = "CERRADOR";
                            }
	
                            return  tipo;
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="canalesventa/${row.id}/edit">Editar</a>
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('comisionista-tipos-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const canalesventa = document.getElementById('comisionista-tipos-form');
                createComisionista(canalesventa);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Canales de venta</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body"> 
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="comisionista-tipos-form">
                            @csrf
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre de canal</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" required="required">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Tipo</label>
                                <select name="comisionista_tipo" id="comisionista_tipo" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                    <option value="" selected="true">COMISIONES GENERALES</option>
                                    <option value="comisionesCanal" >COMISIONES SOBRE CANAL</option>
                                    <option value="comisionesActividad" >COMISIONES SOBRE ACTIVIDAD</option>
                                    <option value="comisionesCerrador" >CERRADOR</option>
                                </select>
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-comisionista">Crear canal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisionista-tipos" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
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
