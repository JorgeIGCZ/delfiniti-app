@extends('layouts.app')
@section('scripts')
    <script>
        let comisionistasTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar comisionista?',
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
            axios.get(`/configuracion/comisionistas/destroy/${id}`)
            .then(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro eliminado',
                    showConfirmButton: false,
                    timer: 1500
                })
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eliminacion fallida',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
            comisionistasTable.ajax.reload();
        }
        function createComisionista(comisionistas){
            axios.post('/configuracion/comisionistas/store', {
                '_token'  : '{{ csrf_token() }}',
                "codigo"  : comisionistas.elements['codigo'].value,
                "nombre"  : comisionistas.elements['nombre'].value,
                "comision": comisionistas.elements['comision'].value,
                "iva"     : comisionistas.elements['iva'].value
            })
            .then(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro creado',
                    showConfirmButton: false,
                    timer: 1500
                })
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Registro fallido',
                    showConfirmButton: false,
                    timer: 1500
                })
            });
            event.preventDefault();
            comisionistas.reset();
            comisionistasTable.ajax.reload();
        }
        $(function(){
            comisionistasTable = new DataTable('#comisionistas', {
                ajax: function (d,cb,settings) {
                    axios.get('/configuracion/comisionistas/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'nombre' },
                    { data: 'comision' },
                    { data: 'iva' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="comisionistas/edit/${row.id}">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('comisionsistas-form').addEventListener('submit', (event) =>{
                const comisionistas = document.getElementById('comisionsistas-form');
                createComisionista(comisionistas);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Comisionistas</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionsistas-form" action="{{ url('configuracion/comisionistas/create') }}">
                            @csrf
                            <div class="form-group col-2 mt-3">
                                <label for="new-codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisionista</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="1" max="90">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="1" max="90">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Crear comisionista</button>
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
                            <table id="comisionistas" class="display table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
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
