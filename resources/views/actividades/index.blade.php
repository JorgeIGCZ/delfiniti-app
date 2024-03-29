@extends('layouts.app')
@section('scripts')
    <script>
        let actividadesTable;
        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar la actividad?',
                text: "La actividad dejará de estar disponible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateActividadEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }
        function updateActividadEstatus(id,estatus){
            $('.loader').show();
            axios.post(`actividades/estatus/${id}`, {
                '_token'  : '{{ csrf_token() }}',
                'estatus' : estatus,
                '_method' : 'PATCH'
            })
            .then(function (response) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Registro actualizado',
                    showConfirmButton: false,
                    timer: 1500
                })
                actividadesTable.ajax.reload();
            })
            .catch(function (error) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
        }
        function getComisionesSobreActividades(actividades){
            let comisionesSobreActividades = [];
            $('.canales').each(function(index, value) {
                comisionesSobreActividades = {...comisionesSobreActividades,[$(value).attr('canalId')] :
                        {
                            'comision'          : $(value).find('.canal_comision').attr('value'),
                            'descuento_impuesto' : $(value).find('.descuento_impuesto').attr('value')
                        }
                    };
            });

            const directivoComisiones = {
                'comision'          : actividades.elements['directivo_comision'].getAttribute('value'),
                'descuento_impuesto' : actividades.elements['directivo_descuento_impuesto'].getAttribute('value')
            }

            return {
                'directivo_comisiones' : directivoComisiones,
                'canales_comisiones' : comisionesSobreActividades
            };
        }
        function createActividad(actividades){
            const comisionesSobreActividades = getComisionesSobreActividades(actividades);

            let horario_inicial = [],
                horario_final   = [];

            if(actividades.elements['horario_inicial[]'].length > 0){
                actividades.elements['horario_inicial[]'].forEach(element => {
                    horario_inicial.push(element.value);
                });
                actividades.elements['horario_final[]'].forEach(element => {
                    horario_final.push(element.value);
                });
            }else{
                horario_inicial.push(actividades.elements['horario_inicial[]'].value);
                horario_final.push(actividades.elements['horario_final[]'].value);
            }
            $('.loader').show();
            axios.post('/actividades', {
                '_token'         : '{{ csrf_token() }}',
                "comisionesSobreActividades" : comisionesSobreActividades,
                "clave"          : actividades.elements['clave'].value,
                "nombre"         : actividades.elements['nombre'].value,
                "precio"         : actividades.elements['precio'].getAttribute('value'),
                "capacidad"      : actividades.elements['capacidad'].value,
                "duracion"       : actividades.elements['duracion'].value,
                "comisionable"   : actividades.elements['comisionable'].checked,
                "comisionesEspeciales" : actividades.elements['comisiones-especiales'].checked,
                "exclusionEspecial" : actividades.elements['exclusion-especial'].checked,
                "fechaInicial"   : actividades.elements['rango'].getAttribute('fechainicial'),
                "fechaFinal"     : actividades.elements['rango'].getAttribute('fechafinal'),
                "horarioInicial" : horario_inicial,
                "horarioFinal"   : horario_final
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
                    showConfirmButton: false,
                    timer: 1500
                })
            });
        }
        function displayRangoPersonalizado(duracion){
            if(duracion == "personalizado"){
                document.getElementById('rango-personalizado').classList.remove("hidden");
            }else{
                document.getElementById('rango-personalizado').classList.add("hidden");
            }
        }
        function addTime(element,event){
            const horario = `
            <div class="form-group col-3 horario-container">
                <label for="new-time" class="col-form-label">Horario</label>
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <input type="time" name="horario_inicial[]" class="form-control" required="required" onclick="removeTime()">
                    </div>
                    A
                    <div class="col-auto">
                        <input type="time" name="horario_final[]" class="form-control" required="required">
                    </div>
                    <div class="action-time">
                        <span class="remove-time">
                            <i class="fa fa-minus-circle" aria-hidden="true"></i>
                        </span>
                        <span class="add-time" >
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>`;
            element.closest('.horario-container').insertAdjacentHTML('afterend',horario);
        }
        function removeTime(element,event){
            element.closest('.horario-container').remove();
        }
        function changeComisionesSettings(){
            const comisionesEspeciales = document.getElementById('comisiones-especiales');
            
            if(comisionesEspeciales.checked){
                $('#comisiones-especiales-container').show();
                return false;
            }
            $('#comisiones-especiales-container').hide();
            return false;       
        }
        $(function(){
            $('input[name="rango"]').daterangepicker({
                "autoApply": true,
                "locale": {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Del",
                    "toLabel": "Al",
                    "customRangeLabel": "Personalizado",
                    "weekLabel": "S",
                    "daysOfWeek": [
                        "Do",
                        "Lu",
                        "Ma",
                        "Mi",
                        "Ju",
                        "Vi",
                        "Sa"
                    ],
                    "monthNames": [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Augosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre"
                    ],
                    "firstDay": 1
                },
                opens: 'left'
            }, function(start, end, label) {
                document.getElementById('rango').setAttribute('fechaInicial',start.format('YYYY-MM-DD'))
                document.getElementById('rango').setAttribute('fechaFinal',end.format('YYYY-MM-DD'))
            });
            actividadesTable = new DataTable('#actividades', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/actividades/show')
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
                    { data: 'precio' },
                    { data: 'capacidad' },
                    { data: 'duracion' },
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
                            if(row.estatus){
                                estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                            }else{
                                estatusRow = `| <a href="#!" onclick="updateActividadEstatus(${row.id},1)" >Reactivar</a>`;
                            }
                            @can('Actividades.update')
                            view    =   `<small> 
                                            <a href="actividades/${row.id}/edit/">Editar</a>
                                            ${estatusRow}
                                        </small>`;
                            @endcan
                            return  view;
                        }
                    }
                ]
            } );
            document.getElementById('duracion').addEventListener('change', (event) =>{
                const duracion = document.getElementById('duracion').value;
                displayRangoPersonalizado(duracion);
            });
            document.getElementById('actividades-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const actividades = document.getElementById('actividades-form');
                createActividad(actividades);
            });
            on("click", ".add-time", function(event) {
                addTime(this,event);
            });
            on("click", ".remove-time", function(event) {
                removeTime(this,event);
            });

            $('#comisiones-especiales').on('change', function (e) {
                changeComisionesSettings();
            });
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Actividades</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    @can('Actividades.create')
        <div class="row row-sm mg-b-20">
            <div class="col-lg-12 ht-lg-100p">
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <form class="row g-3 align-items-center f-auto" id="actividades-form">
                                <div class="form-group col-1">
                                    <label for="clave" class="col-form-label">Clave</label>    
                                    <input type="text" name="clave" class="form-control" required="required">  
                                </div>
                                <div class="form-group col-4">
                                    <label for="nombre" class="col-form-label">Nombre actividad</label>    
                                    <input type="text" name="nombre" class="form-control to-uppercase" required="required">  
                                </div>
                                <div class="form-group col-1">
                                    <label for="precio" class="col-form-label">Precio</label>    
                                    <input type="text" name="precio" class="form-control amount" required="required">  
                                </div>
                                <div class="form-group col-1">
                                    <label for="capacidad" class="col-form-label">Capacidad</label>    
                                    <input type="number" name="capacidad" min="1" max="500" class="form-control" required="required">  
                                </div>
                                <div class="form-group col-2">
                                    <label for="duracion" class="col-form-label">Duracion</label> 
                                    <select name="duracion" class="form-control" id="duracion">
                                        <option value="indefinido" select="selected">Indefinido</option>
                                        <option value="personalizado">Personalizado</option>
                                    </select>
                                </div>
                                <div id="rango-personalizado" class="form-group col-3 hidden">
                                    <label for="rango" class="col-form-label">Rango personalizado</label> 
                                    <input type="text" id="rango" name="rango" class="form-control">
                                </div>
                                <div class="form-group col-1"> 
                                    <label for="comisionable" class="col-form-label">Comisionable</label>    
                                    <input type="checkbox" name="comisionable" class="form-control" checked="checked" style="display: block;">
                                </div>
                                <div class="form-group col-2"> 
                                    <label for="comisiones-especiales" class="col-form-label">Comisiones especiales</label>    
                                    <input type="checkbox" name="comisiones-especiales" id="comisiones-especiales" class="form-control" style="display: block;">
                                </div>
                                <div class="form-group col-2"> 
                                    <label for="exclusion-especial" class="col-form-label">Excluir de disponibilidad</label>    
                                    <input type="checkbox" name="exclusion-especial" id="exclusion-especial" class="form-control" style="display: block;">
                                </div>

                                <div class="form-group col-12 mt-3" id="comisiones-especiales-container" style="display: none;">
                                    <strong>
                                        Comisiones especiales actividad
                                    </strong>
                                    <table class="mt-3">
                                        <tr>
                                            <th>Canal de venta</th>
                                            <th>Comisión directa P/Actividad %</th>
                                            <th>Descuentro por imp. %</th>
                                        </tr>
                                        @foreach($canales as $canal)
                                            <tr canalId="{{$canal->id}}" class="canales">
                                                <td>{{$canal->nombre}}</td>
                                                <td>
                                                    <input type="text" step="0.01" name="canal_comision" class="canal_comision form-control percentage" value="0">
                                                </td>
                                                <td>
                                                    <input type="text" step="0.01" name="descuento_impuesto" class="descuento_impuesto form-control percentage" value="0">  
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- DIRECTIVO --}}
                                        <tr class="directivo">
                                            <td><b>DIRECTIVOS VENTAS</b></td>
                                            <td>
                                                <input type="text" step="0.01" name="directivo_comision" class="directivo_comision form-control percentage">
                                            </td>
                                            <td>
                                                <input type="text" step="0.01" name="directivo_descuento_impuesto" class="directivo_descuento_impuesto form-control percentage">  
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-12">
                                    <div class="row" id="horarios-container">
                                        <div class="form-group col-3 horario-container">
                                            <label for="new-time" class="col-form-label">Horario</label>
                                            <div class="row g-3 align-items-center">
                                                <div class="col-auto">
                                                    <input type="time" name="horario_inicial[]" class="form-control" required="required">
                                                </div>
                                                A
                                                <div class="col-auto">
                                                    <input type="time" name="horario_final[]" class="form-control" required="required">
                                                </div>
                                                <div class="action-time">
                                                    <span class="add-time">
                                                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-3">
                                    <button class="btn btn-info btn-block mt-33">Crear actividad</button>
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
                            <table id="actividades" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Capacidad</th>
                                        <th>Duración</th>
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