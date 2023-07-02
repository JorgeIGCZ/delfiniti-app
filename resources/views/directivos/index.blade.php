@extends('layouts.app')
@section('scripts')
    <script>
        const directivos = document.getElementById('directivos-form');
        let directivosTable;
        
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
                title: '¿Desea inactivar al directivo?',
                text: "El directivo dejará de estar disponible!!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateDirectivoEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }

        function updateDirectivoEstatus(id,estatus){
            $('.loader').show();
            axios.post(`directivos/estatus/${id}`, {
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
                    directivosTable.ajax.reload();
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
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
                directivos.reset();
            }); 
        }
        
        function createDirectivo(){
            $('.loader').show();
            axios.post('/directivos', {
                "_token"                       : "{{ csrf_token() }}",
                "clave"                        : directivos.elements['clave'].value,
                "comisionesSobreReservaciones" : getComisionesSobreReservaciones(),
                "comisionesSobreTienda"        : getComisionesSobreTienda(),
                "comisionesSobreFotoVideo"     : getComisionesSobreFotoVideo(),
                "nombre"                       : directivos.elements['nombre'].value,
                "direccion"                    : directivos.elements['direccion'].value,
                "telefono"                     : directivos.elements['telefono'].value
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

        function getComisionesSobreReservaciones(){
            let comisionesSobreCanales = [];
            $('.tipo_comisiones').each(function(index, value) {
                comisionesSobreCanales = {...comisionesSobreCanales,[$(value).attr('tipoid')] :
                        {
                            'comision'          : $(value).find('.tipo_comision').attr('value'),
                            'iva'               : $(value).find('.tipo_iva').attr('value'),
                            'descuentoImpuesto' : $(value).find('.tipo_descuento_impuesto').attr('value'),
                        }
                    };
            });

            return comisionesSobreCanales;
        }

        function getComisionesSobreTienda(){
            return {
                'comision' : directivos.elements['tienda_comision'].value,
                'iva' : directivos.elements['tienda_iva'].value,
                'descuentoImpuesto' : directivos.elements['tienda_descuento_impuesto'].value
            };
        }
        
        function getComisionesSobreFotoVideo(){
            return {
                'comision' : directivos.elements['foto_video_comision'].value,
                'iva' : directivos.elements['foto_video_iva'].value,
                'descuentoImpuesto' : directivos.elements['foto_video_descuento_impuesto'].value
            };
        }


        $(function(){
            directivosTable = new DataTable('#directivos', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/directivos/show')
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
                            let view       = '';  
                            @can('Directivos.update')
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateDirectivoEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                                view    =   `<small> 
                                                <a href="directivos/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            @endcan
                            return  view;
                        }
                    }
                ]
            } );
            
            if(directivos !== null){
                directivos.addEventListener('submit', (event) =>{
                    event.preventDefault();
                    if(formValidity('directivos-form')){
                        createDirectivo();
                    }
                });
            }
        });

    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Directivos</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    @can('Directivos.create')
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body"> 
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="directivos-form">
                                @csrf
                                <div class="form-group col-1 mt-3">
                                    <label for="clave" class="col-form-label">Código</label>    
                                    <input type="text" name="clave" class="form-control" required="required">  
                                </div>
                                <div class="form-group col-3 mt-3">
                                    <label for="nombre" class="col-form-label">Nombre</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" required="required">  
                                </div>
                                <div class="form-group col-4 mt-3 general-settings">
                                    <label for="direccion" class="col-form-label">Dirección</label>
                                    <input type="text" name="direccion" class="form-control to-uppercase">
                                </div>
                                <div class="form-group col-3 mt-3 general-settings">
                                    <label for="telefono" class="col-form-label">Teléfono</label>
                                    <input type="text" name="telefono" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <div class="card wrapper">
                                        <div class="card-header buttonWrapper">
                                            <ul class="nav nav-pills">
                                                <li class="nav-item">
                                                    <a class="nav-link tab-button active" href="#" data-id="reservaciones">Reservaciones</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link tab-button" href="#" data-id="tienda">Tienda</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link tab-button" href="#" data-id="foto_video">Foto y video</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="card-body">
                                            <div class="tab-content contentWrapper">
                                                <div class="tab-panel content active" id="reservaciones">
                                                    <div class="form-group col-12 mt-3 comisiones-sobre-canales">
                                                        <strong>
                                                            Comisiones sobre canales
                                                        </strong>
                                                        <table class="mt-3">
                                                            <tr>
                                                                <th>Canal de venta</th>
                                                                <th>Comisión %</th>
                                                                <th>Iva %</th>
                                                                <th>Descuentro por imp. %</th>
                                                            </tr>
                                                            @foreach($canalesVenta as $canalVenta)
                                                                <tr tipoid="{{$canalVenta->id}}" class="tipo_comisiones">
                                                                    <td>{{$canalVenta->nombre}}</td>
                                                                    <td>
                                                                        <input type="text" step="0.01" name="tipo_comision" class="tipo_comision form-control percentage" value="0">  
                                                                    </td>
                    
                                                                    <td>
                                                                        <input type="text" step="0.01" name="tipo_iva" class="tipo_iva form-control percentage" value="0">  
                                                                    </td>
                    
                                                                    <td>
                                                                        <input type="text" step="0.01" name="tipo_descuento_impuesto" class="tipo_descuento_impuesto form-control percentage" value="0">  
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-panel content" id="tienda">
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_comision" class="col-form-label">Comisión %</label>
                                                        <input type="number" step="0.01" name="tienda_comision" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_iva" class="col-form-label">Iva %</label>
                                                        <input type="number" name="tienda_iva" class="form-control" min="0" max="90" value="0">
                                                    </div>
                    
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="tienda_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                        <input type="number" step="0.01" name="tienda_descuento_impuesto" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                </div>
                                                <div class="tab-panel content" id="foto_video">
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_comision" class="col-form-label">Comisión %</label>
                                                        <input type="number" step="0.01" name="foto_video_comision" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_iva" class="col-form-label">Iva %</label>
                                                        <input type="number" name="foto_video_iva" class="form-control" min="0" max="90" value="0">
                                                    </div>
                    
                                                    <div class="form-group col-2 mt-3 general-settings">
                                                        <label for="foto_video_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                        <input type="number" step="0.01" name="foto_video_descuento_impuesto" class="form-control" min="0" max="90" value="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-3 mt-3">
                                    <button class="btn btn-info btn-block mt-33" id="crear-directivo">Crear directivo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="directivos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
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
