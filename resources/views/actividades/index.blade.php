@extends('layouts.app')
@section('scripts')
    <script>
        let actividadesTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar actividad?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyActividad(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyActividad(id){
            axios.delete(`/actividades/${id}`)
            .then(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro eliminado',
                    showConfirmButton: false,
                    timer: 1500
                })
                actividadesTable.ajax.reload();
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eliminacion fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
        }
        function createActividad(actividades){
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
            axios.post('/actividades', {
                '_token'         : '{{ csrf_token() }}',
                "clave"          : actividades.elements['clave'].value,
                "nombre"         : actividades.elements['nombre'].value,
                "precio"         : actividades.elements['precio'].getAttribute('value'),
                "capacidad"      : actividades.elements['capacidad'].value,
                "duracion"       : actividades.elements['duracion'].value,
                "fechaInicial"   : actividades.elements['rango'].getAttribute('fechainicial'),
                "fechaFinal"     : actividades.elements['rango'].getAttribute('fechafinal'),
                "horarioInicial" : horario_inicial,
                "horarioFinal"   : horario_final
            })
            .then(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro creado',
                    showConfirmButton: false,
                    timer: 1500
                })
                location.reload();
            })
            .catch(function (error) {
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
                    axios.get('/actividades/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'clave' },
                    { data: 'nombre' },
                    { data: 'precio' },
                    { data: 'capacidad' },
                    { data: 'duracion' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="actividades/${row.id}/edit/">Editar</a>
                                                ${removeRow}
                                            </small>`;
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
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Actividades</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
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
                                <input type="text" name="nombre" class="form-control" required="required">  
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

     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="actividades" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Capacidad</th>
                                        <th>Duración</th>
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