@extends('layouts.app')
@section('scripts')
    <script>
        let comisionistasTable;
        
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

        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar al comisionista?',
                text: "El comisionista dejará de estar disponible!!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateComisionistaEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }
        function updateComisionistaEstatus(id,estatus){
            axios.post(`comisionistas/estatus/${id}`, {
                '_token'  : '{{ csrf_token() }}',
                'estatus' : estatus,
                '_method' : 'PATCH'
            })
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro actualizado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    comisionistasTable.ajax.reload();
                }else{
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
                comisionistas.reset();
            }); 
        }
        function createComisionista(comisionistas){
            let tipo   = comisionistas.elements['tipo'];
            tipo       = tipo.options[tipo.selectedIndex].value;
            axios.post('/comisionistas', {
                '_token'  : '{{ csrf_token() }}',
                "codigo"  : comisionistas.elements['codigo'].value,
                "nombre"  : comisionistas.elements['nombre'].value,
                "tipo"  : tipo,
                "comision": comisionistas.elements['comision'].value,
                "iva"     : comisionistas.elements['iva'].value,
                "descuentoImpuesto" : comisionistas.elements['descuento-impuesto'].value,
                "descuentos": comisionistas.elements['descuentos'].checked,
                "representante"  : comisionistas.elements['representante'].value,
                "direccion": comisionistas.elements['direccion'].value,
                "telefono"     : comisionistas.elements['telefono'].value
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
            comisionistasTable = new DataTable('#comisionistas', {
                ajax: function (d,cb,settings) {
                    axios.get('/comisionistas/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'nombre' },
                    { data: 'tipo_id' },
                    { defaultContent: 'comision', 'render': function ( data, type, row ) 
                        {
                            return  `${row.comision}%`;
                        }
                    },
                    { defaultContent: 'iva', 'render': function ( data, type, row ) 
                        {
                            return  `${row.iva}%`;
                        }
                    },
                    { defaultContent: 'descuentoImpuesto', 'render': function ( data, type, row ) 
                        {
                            return  `${row.descuentoImpuesto}%`;
                        }
                    },
                    { defaultContent: 'descuentos', 'render': function ( data, type, row ) 
                        {
                            return  row.descuentos ? 'Si' : 'No';
                        }
                    },
                    { data: 'representante' },
                    { data: 'direccion' },
                    { data: 'telefono' },
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
                            let estatusRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateComisionistaEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                            //}
                            let view    =   `<small> 
                                                <a href="comisionistas/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('comisionistas-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const comisionistas = document.getElementById('comisionistas-form');
                if(formValidity('comisionistas-form')){
                    createComisionista(comisionistas);
                }
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
                        <form class="row g-3 align-items-center f-auto" id="comisionistas-form">
                            @csrf
                            <div class="form-group col-1 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                <input type="text" name="nombre" class="form-control">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" class="form-control">
                                    @foreach($tipos as $tipo)
                                        <option value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="0">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="0">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="descuento-impuesto" class="col-form-label">Descuentro por imp. %</label>
                                <input type="number" name="descuento-impuesto" class="form-control" min="0" max="90" value="0">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="descuentos" class="col-form-label">Puede recibir descuentos</label>
                                <input type="checkbox" name="descuentos" class="form-control" >
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
                                <button class="btn btn-info btn-block mt-33" id="crear-comisionista">Crear comisionista</button>
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
                            <table id="comisionistas" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                        <th>Descuento impuesto</th>
                                        <th>Descuentos</th>
                                        <th>Representante</th>
                                        <th>Dirección</th>
                                        <th>Teléfono</th>
                                        <th>Estatus</th>
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
