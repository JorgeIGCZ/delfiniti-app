@extends('layouts.app')
@section('scripts')
    <script>
        const pedidosTable = new DataTable('#pedidos', {
            order: [[0, 'desc']],
            ajax: function (d,cb,settings) {
                $('.loader').show();
                const pedidos = document.getElementById('pedidos-form');
                axios.post('/pedidos/get',{
                    "_token"  : '{{ csrf_token() }}',
                    "view" : "validate"
                })
                .then(function (response) {
                    $('.loader').hide();
                    cb(response.data)
                })
                .catch(function (error) {
                    $('.loader').hide();
                });
            },
            rowCallback: function( row, data, index ) {
                if ( data.cortesia == "Cortesia" ) {
                    $(row).addClass("highlight");
                }
            },
            columns: [
                { data: 'id' },
                { data: 'proveedor' },
                { data: 'cantidad' },
                { data: 'fechaCreacion' },
                { data: 'comentarios' },
                { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row )
                    {
                        let validate = "";
                        let viewRow = "";

                        if(row.estatus && !row.estatus_proceso){
                            validate = `<button class="btn btn-outline-success btn-block form-control mt-1 mb-1" onclick="validacion(${row.id})" >Validar</button>`;
                        }

                        @can('TiendaPedidos.index')
                            viewRow = `<a href="/pedidos/${row.id}">Ver</a>`;
                        @endcan
                        
                        options = [viewRow,validate];
                        options = options.filter(option => option != ""); 

                        let view    =   `<small>
                                            ${options.join('')}
                                        </small>`;
                        return  view;
                    }
                }
            ]
        } );

        function validar(id){
            $('.loader').show();
            axios.post(`/pedidos/validate/${id}/update`, {
                '_token'  : '{{ csrf_token() }}'
            })
            .then(function (response) {
                $('.loader').hide();

                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Productos registrados',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    pedidosTable.ajax.reload();
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

        function validacion(id){
            Swal.fire({
                title: `¿Desea validar el pedido con ID ${id}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, validar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    validar(id,);
                }else{
                    return false;
                }
            }) 
        }
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Validación Pedidos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <table id="pedidos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Proveedor</th>
                                        <th># Productos</th>
                                        <th>Fecha creación</th>
                                        <th>Comentarios</th>
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
