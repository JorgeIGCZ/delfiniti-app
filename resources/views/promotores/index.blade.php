@extends('layouts.app')
@section('scripts')
    <script>
        let promotoresTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar promotor?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyPromotor(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyPromotor(id){
            axios.get(`/configuracion/promotores/destroy/${id}`)
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
            promotoresTable.ajax.reload();
        }
        function createPromotor(promotores){
            axios.post('/configuracion/promotores/store', {
                '_token'  : '{{ csrf_token() }}',
                "codigo"  : promotores.elements['codigo'].value,
                "nombre"  : promotores.elements['nombre'].value,
                "comision": promotores.elements['comision'].value,
                "iva"     : promotores.elements['iva'].value
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
            promotores.reset();
            promotoresTable.ajax.reload();
        }
        $(function(){
            promotoresTable = new DataTable('#promotores', {
                ajax: function (d,cb,settings) {
                    axios.get('/configuracion/promotores/show')
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
                                                <a href="promotores/edit/${row.id}">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('promotores-form').addEventListener('submit', (event) =>{
                const promotores = document.getElementById('promotores-form');
                createPromotor(promotores);
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Promotores</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="promotores-form" action="{{ url('configuracion/promotores/create') }}">
                            @csrf
                            <div class="form-group col-2 mt-3">
                                <label for="new-codigo" class="col-form-label">Código</label>    
                                <input type="text" id="new-codigo" name="codigo" class="form-control">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="new-nombre" class="col-form-label">Promotor</label>    
                                <input type="text" id="new-nombre" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisión %</label>
                                <input type="number" id="new-nombre" name="comision" class="form-control" min="1" max="90">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-iva" class="col-form-label">Iva %</label>
                                <input type="number" id="new-iva" name="iva" class="form-control" min="1" max="90">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Crear promotor</button>
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
                            <table id="promotores" class="display table" style="width:100%">
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