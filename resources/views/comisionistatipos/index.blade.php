@extends('layouts.app')
@section('scripts')
    <script>
        let comisionistaTiposTable;
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
            const comisionistaTipos = document.getElementById('comisionista-tipos-form');
            axios.delete(`/comisionistatipos/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    comisionistaTiposTable.ajax.reload();
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
            comisionistaTiposTable.ajax.reload();
        }
        function createComisionista(comisionistaTipos){
            axios.post('/comisionistatipos', {
                '_token'  : '{{ csrf_token() }}',
                "nombre"  : comisionistaTipos.elements['nombre'].value,
                "comisionista_canal"  : comisionistaTipos.elements['comisionista_canal'].value
            })
            .then(function (response) {
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
                Swal.fire({
                    icon: 'error',
                    title: 'Registro fallido',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
        }
        $(function(){
            comisionistaTiposTable = new DataTable('#comisionista-tipos', {
                ajax: function (d,cb,settings) {
                    axios.get('/comisionistatipos/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { defaultContent: 'comisionista_canal', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            
                            let view    =   (row.comisionista_canal) ? 'Sí' : 'No';
                            return  view;
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="comisionistatipos/${row.id}/edit">Editar</a>
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('comisionista-tipos-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const comisionistaTipos = document.getElementById('comisionista-tipos-form');
                createComisionista(comisionistaTipos);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Tipos de comisionista</h2>
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
                                <label for="nombre" class="col-form-label">Nombre de tipo de comisionista</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Comisiones sobre canal</label>
                                <input type="checkbox" name="comisionista_canal" class="form-control" style="display: block;"> 
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-comisionista">Crear tipo de comisionista</button>
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
                                        <th>Comisiones sobre tipo</th>
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
