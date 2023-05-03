@extends('layouts.app')
@section('scripts')
    <script>
        let productosTable;
        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar el producto?',
                text: "El producto dejará de estar disponible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateProductoEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }
        function updateProductoEstatus(id,estatus){
            $('.loader').show();
            axios.post(`fotovideoproductos/estatus/${id}`, {
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
                    productosTable.ajax.reload();
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
        function createProducto(productos){
            $('.loader').show();

            axios.post('/fotovideoproductos', {
                '_token'   : '{{ csrf_token() }}',
                "clave"   : productos.elements['clave'].value,
                "nombre"   : productos.elements['nombre'].value,
                "precioVenta" : productos.elements['precioVenta'].getAttribute('value'),
                "comentarios" : productos.elements['comentarios'].value
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
            productosTable = new DataTable('#productos', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/fotovideoproductos/show')
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
                    { data: 'precio_venta' },
                    { data: 'comentarios' },
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
                                    estatusRow = `| <a href="#!" onclick="updateProductoEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                            //}
                            // can('Productos.update')
                            view    =   `<small> 
                                            <a href="fotovideoproductos/${row.id}/edit">Editar</a>
                                            ${estatusRow}
                                        </small>`;
                            // endcan
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('productos-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const productos = document.getElementById('productos-form');
                createProducto(productos);
            });
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Productos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    {{-- @can('Productos.create') --}}
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="productos-form">
                                @csrf
                                <div class="form-group col-1 mt-3">
                                    <label for="clave" class="col-form-label">Clave</label>    
                                    <input type="text" name="clave" class="form-control"  autocomplete="off" tabindex="1" required="required">  
                                </div>

                                <div class="form-group col-5 mt-3">
                                    <label for="nombre" class="col-form-label">Nombre del producto</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="3" required="required">  
                                </div>
                                <div class="form-group col-2 mt-2">
                                    <label for="precioVenta" class="col-form-label">Precio venta</label>
                                    <input type="text" name="precioVenta" id="precioVenta" class="form-control amount"  autocomplete="off" tabindex="5">
                                </div>

                                <div class="form-group col-6 mt-2">
                                    <label for="stockMaximo" class="col-form-label">Comentarios</label>
                                    <textarea name="comentarios" class="to-uppercase" rows="5" style="width:100%;" spellcheck="false"></textarea>
                                </div>
                                
                                <div class="form-group col-2 mt-3">
                                    <button class="btn btn-info btn-block mt-33" id="crear-producto" tabindex="8">Crear producto</button>
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
                            <table id="productos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>clave</th>
                                        <th>Nombre</th>
                                        <th>Precio venta</th>
                                        <th>Comentarios</th>
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
