@extends('layouts.app')
@section('scripts')
    <script>
        let cerradoresTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar cerrador?',
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
            axios.delete(`/cerradores/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    cerradoresTable.ajax.reload();
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
        }
        function createComisionista(cerradores){
            axios.post('/cerradores', {
                '_token'  : '{{ csrf_token() }}',
                "nombre"  : cerradores.elements['nombre'].value,
                "comision": cerradores.elements['comision'].value,
                "iva"     : cerradores.elements['iva'].value,
                "direccion": cerradores.elements['direccion'].value,
                "telefono"     : cerradores.elements['telefono'].value
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
            cerradoresTable = new DataTable('#cerradores', {
                ajax: function (d,cb,settings) {
                    axios.get('/cerradores/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { data: 'comision' },
                    { data: 'iva' },
                    { data: 'direccion' },
                    { data: 'telefono' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="cerradores/${row.id}/edit">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('cerradores-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const cerradores = document.getElementById('cerradores-form');
                createComisionista(cerradores);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Cerradores</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body"> 
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="cerradores-form">
                            @csrf
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre cerrador</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="0">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="0">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" id="direccion" class="form-control">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" id="telefono" class="form-control">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-cerrador">Crear cerrador</button>
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
                            <table id="cerradores" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
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
