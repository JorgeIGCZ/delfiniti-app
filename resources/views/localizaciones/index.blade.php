@extends('layouts.app')
@section('scripts')
    <script>
        let localizacionesTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar localizacion?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyLocalizacion(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyLocalizacion(id){
            axios.delete(`/localizaciones/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
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
                localizaciones.reset();
            });
            comisionistasTable.ajax.reload();
        }
        function createLocalizacion(localizaciones){
            axios.post('/localizaciones', {
                '_token'   : '{{ csrf_token() }}',
                "codigo"   : localizaciones.elements['codigo'].value,
                "nombre"   : localizaciones.elements['nombre'].value,
                "direccion": localizaciones.elements['direccion'].value,
                "telefono" : localizaciones.elements['telefono'].value
            })
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Localizacion creada',
                        showConfirmButton: false,
                        timer: 1500
                    })
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
                tipoCambio.reset();
            });
            localizacionesTable.ajax.reload();
        }
        $(function(){
            localizacionesTable = new DataTable('#localizaciones', {
                ajax: function (d,cb,settings) {
                    axios.get('/localizaciones/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'nombre' },
                    { data: 'direccion' },
                    { data: 'telefono' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="localizaciones/${row.id}/edit">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('comisionsistas-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const localizaciones = document.getElementById('comisionsistas-form');
                createLocalizacion(localizaciones);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Alojamientos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="comisionsistas-form">
                            @csrf
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre del alojamiento</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-localizacion">Crear localización</button>
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
                            <table id="localizaciones" class="display table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
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
