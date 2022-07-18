@extends('layouts.app')
@section('scripts')
    <script>
        let descuentocodigosTable;
        
        function formValidity(formId){
            const reservacion = document.getElementById(formId);
            let response = true;
            if(reservacion.checkValidity()){
                event.preventDefault();
            }else{
                reservacion.reportValidity();
                response = false;
            }
            return response;
        }

        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar codigod escuento?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyDescuentoCodigo(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyDescuentoCodigo(id){
            axios.delete(`/descuentocodigos/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    descuentocodigosTable.ajax.reload();
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
                descuentocodigos.reset();
            }); 
        }
        function createDescuentoCodigo(descuentocodigos){
            let tipo   = descuentocodigos.elements['tipo'];
            tipo       = tipo.options[tipo.selectedIndex].value;
            axios.post('/descuentocodigos', {
                '_token'  : '{{ csrf_token() }}',
                "nombre"  : descuentocodigos.elements['nombre'].value,
                "tipo"  : tipo,
                "descuento": descuentocodigos.elements['descuento'].value,
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
            descuentocodigosTable = new DataTable('#descuentocodigos', {
                ajax: function (d,cb,settings) {
                    axios.get('/descuentocodigos/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { data: 'tipo' },
                    { data: 'descuento' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="descuentocodigos/${row.id}/edit">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('descuentocodigos-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const descuentocodigos = document.getElementById('descuentocodigos-form');
                if(formValidity('descuentocodigos-form')){
                    createDescuentoCodigo(descuentocodigos);
                }
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Codigos Descuento</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body"> 
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="descuentocodigos-form">
                            @csrf
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" class="form-control">
                                    <option value="cantidad">Cantidad</option>
                                    <option value="porcentaje">Porcentaje</option>
                                </select>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="descuento" class="col-form-label">Descuento</label>
                                <input type="number" name="descuento" class="form-control" value="0">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-descuentocodigo">Crear código</button>
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
                            <table id="descuentocodigos" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Descuento</th>
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
