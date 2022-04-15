@extends('layouts.app')
@section('scripts')
    <script>
        let agenciasTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar agencia?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyAgencia(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyAgencia(id){
            axios.get(`/configuracion/agencias/destroy/${id}`)
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
            agenciasTable.ajax.reload();
        }
        function createAgencia(agencias){
            axios.post('/configuracion/agencias/store', {
                '_token'  : '{{ csrf_token() }}',
                "codigo"  : agencias.elements['codigo'].value,
                "nombre"  : agencias.elements['nombre'].value,
                "comision": agencias.elements['comision'].value,
                "iva"     : agencias.elements['iva'].value,
                "representante"  : agencias.elements['representante'].value,
                "direccion": agencias.elements['direccion'].value,
                "telefono"     : agencias.elements['telefono'].value
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
            agencias.reset();
            agenciasTable.ajax.reload();
        }
        $(function(){
            agenciasTable = new DataTable('#agencias', {
                ajax: function (d,cb,settings) {
                    axios.get('/configuracion/agencias/show')
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
                    { data: 'representante' },
                    { data: 'direccion' },
                    { data: 'telefono' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="agencias/edit/${row.id}">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('agencias-form').addEventListener('submit', (event) =>{
                const agencias = document.getElementById('agencias-form');
                createAgencia(agencias);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Agencias</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="agencias-form">
                            @csrf
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="nombre" class="col-form-label">Nombre agencia</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="1" max="90">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="1" max="90">
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3">
                                <label for="representante" class="col-form-label">Representante</label>
                                <input type="text" id="representante" class="form-control">
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
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Crear agencia</button>
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
                            <table id="agencias" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                        <th>Representante</th>
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
