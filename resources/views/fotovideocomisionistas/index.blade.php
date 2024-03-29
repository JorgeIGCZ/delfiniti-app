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
            $('.loader').show();
            axios.post(`fotovideocomisionistas/estatus/${id}`, {
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
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
                comisionistas.reset();
            }); 
        }
        function tipoComisionista(){
            let tipoComisionista = "comisionistaGeneral";
            let tipo = document.getElementById('tipo');
            tipo = tipo.options[tipo.selectedIndex];

            if(tipo.getAttribute("comisionistaCanal") == "1"){
                tipoComisionista = "comisionistaCanal";
            }else if(tipo.getAttribute("comisionistaActividad") == "1"){
                tipoComisionista = "comisionistaActividad";
            }

            return tipoComisionista;
        }
        function createComisionista(comisionistas){
            $('.loader').show();
            axios.post('/fotovideocomisionistas', {
                "_token"  : "{{ csrf_token() }}",
                "codigo"  : comisionistas.elements['codigo'].value,
                "nombre"            : comisionistas.elements['nombre'].value,
                "comision"          : comisionistas.elements['comision'].value,
                "iva"               : comisionistas.elements['iva'].value,
                "descuentoImpuesto" : comisionistas.elements['descuento-impuesto'].value,
                "direccion"         : comisionistas.elements['direccion'].value,
                "telefono"          : comisionistas.elements['telefono'].value
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
        function getComisionesSobreActividades(){
            let comisionesSobreActividades = [];
            $('.actividades').each(function(index, value) {
                comisionesSobreActividades = {...comisionesSobreActividades,[$(value).attr('actividadid')] :
                        {
                            'comision'          : $(value).find('.actividad_comision').attr('value'),
                            'descuentoImpuesto' : $(value).find('.actividad_descuento_impuesto').attr('value'),
                        }
                    };
            });

            return comisionesSobreActividades;
        }
        function getComisionesSobreCanales(){
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
        function changeComisionesSettings(){
            const tipo = document.getElementById('tipo');
            
            if(tipo.options[tipo.selectedIndex].getAttribute('comisionistaCanal') == '1'){
                $('.general-settings').hide();
                $('.comisiones-sobre-actividades').hide();
                $('.comisiones-sobre-canales').show();
                return false;
            }else if(tipo.options[tipo.selectedIndex].getAttribute('comisionistaActividad') == '1'){
                $('.general-settings').hide();
                $('.comisiones-sobre-actividades').show();
                $('.comisiones-sobre-canales').hide();
                return false;
            }
            $('.general-settings').show();
            $('.comisiones-sobre-actividades').hide();
            $('.comisiones-sobre-canales').hide();
            return true;
        }
        $(function(){
            comisionistasTable = new DataTable('#comisionistas', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/fotovideocomisionistas/show')
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'nombre' },
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
                            @can('FotoVideoComisionistas.update')
                                if(row.estatus){
                                    estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                }else{
                                    estatusRow = `| <a href="#!" onclick="updateComisionistaEstatus(${row.id},1)" >Reactivar</a>`;
                                }
                                view    =   `<small> 
                                                <a href="fotovideocomisionistas/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            @endcan
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

            $('#tipo').on('change', function (e) {
                document.querySelectorAll("#tipo option").forEach(function(el) {
                    el.removeAttribute("selected");
                })

                changeComisionesSettings();
            });
            
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Fotógrafos</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    @can('Comisionista.create')
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body"> 
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="comisionistas-form">
                                @csrf
                                <div class="form-group col-1 mt-3">
                                    <label for="codigo" class="col-form-label">Código</label>    
                                    <input type="text" name="codigo" class="form-control" required="required">  
                                </div>

                                <div class="form-group col-3 mt-3">
                                    <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" required="required">  
                                </div>
                                
                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="comision" class="col-form-label">Comisión %</label>
                                    <input type="number" step="0.01" name="comision" class="form-control" min="0" max="100" value="0" required="required">
                                </div>

                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="iva" class="col-form-label">Iva %</label>
                                    <input type="number" name="iva" class="form-control" min="0" max="100" value="0" required="required">
                                </div>

                                <div class="form-group col-2 mt-3 general-settings">
                                    <label for="descuento-impuesto" class="col-form-label">Descuentro por imp. %</label>
                                    <input type="number" step="0.01" name="descuento-impuesto" class="form-control" min="0" max="100" value="0" required="required">
                                </div>

                                <div class="form-group col-4 mt-3 general-settings">
                                    <label for="direccion" class="col-form-label">Dirección</label>
                                    <input type="text" id="direccion" class="form-control to-uppercase">
                                </div>

                                <div class="form-group col-3 mt-3 general-settings">
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
    @endcan
     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisionistas" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                        <th>Descuento impuesto</th>
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
