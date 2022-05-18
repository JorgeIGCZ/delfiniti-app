@extends('layouts.app')
@section('scripts')
    <script>
        let reservacionesTable; 
        let allActividades = [];
        let reservaciones  = [];
        const formatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        });
        window.onload = function() {
            getDisponibilidad();
            reservacionesTable = new DataTable('#reservaciones', {
                searching: false,
                paging: false,
                info: false,
                /*
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
                */
            } );
            document.getElementById('agregar-reservacion').addEventListener('click', (event) =>{
                addReservaciones();
            });
            
            /*
            document.getElementById('actividad-form').addEventListener('submit', (event) =>{
                const agencias = document.getElementById('agencias-form');
                createAgencia(agencias);
            });
            */
            document.getElementById('total').addEventListener('keyup', (event) =>{
                setTimeout(getResta(),500);
            });
            document.getElementById('efectivo').addEventListener('keyup', (event) =>{
                setTimeout(getResta(),500);
            });
            document.getElementById('efectivo-usd').addEventListener('keyup', (event) =>{
                setTimeout(getResta(),500);
            });
            document.getElementById('tarjeta').addEventListener('keyup', (event) =>{
                setTimeout(getResta(),500);
            });
            document.getElementById('cupon').addEventListener('keyup', (event) =>{
                setTimeout(getResta(),500);
            });
            //jQuery
            $('#clave-actividad').on('select2:select', function (e) {
                const claveActividad = document.getElementById('clave-actividad');
                changeActividad(claveActividad);
                $('#actividades').trigger('change.select2');
                getActividadHorario();
                getPrecio();
            });
            $('#actividades').on('select2:select', function (e) {
                const actividad = document.getElementById('actividades');
                changeClaveActividad(actividad);
                $('#clave-actividad').trigger('change.select2');
                getActividadHorario();
                getPrecio();
            });
        };
        function addReservaciones(){
            const claveActividad = document.getElementById("clave-actividad").value;
            const actividad      = document.getElementById("actividades").value;
            const cantidad       = document.getElementById("cantidad").value;
            const precio         = document.getElementById("precio").value;
            const horario        = document.getElementById("horarios").value;
            const fecha          = document.getElementById("fecha").value;
            const acciones       = `<a href="#" id="actividad-${claveActividad}" class="editar">Editar</a> | <a href="#" id="actividad-${claveActividad}" class="eliminar">Eliminar</a>`
            reservacionesTable.row.add( [ 
                claveActividad,
                actividad,
                precio,
                cantidad,
                precio*cantidad,
                acciones
            ] )
            .draw(false);
            reservaciones = [...reservaciones,{
                "claveActividad": claveActividad,
                "actividad": actividad,
                "cantidad": cantidad,
                "precio": precio,
                "horario": horario,
                "fecha": fecha
            }];
            getTotal();
        }
        function getTotal(){
            let total = 0;
            reservaciones.forEach(reservacion => {
                total += (reservacion.cantidad*reservacion.precio);
            });
            total = parseFloat(total).toFixed(2)
            document.getElementById("total").setAttribute("value",total);
            document.getElementById("total").value = formatter.format(total);
            getResta()
        }
        function getPrecio(){
            const actividad = document.getElementById("actividades").value;
            let precio      = document.getElementById("precio");
            for (var i = 0; i < allActividades.length; i++) {
                if(actividad == allActividades[i].actividad.clave){
                    precio.value = allActividades[i].actividad.precio;
                }
            }
        }
        function getResta(){
            let resta        = document.getElementById("resta");
            const total      = parseFloat(document.getElementById("total").getAttribute('value'));
            const efectivo   = parseFloat(document.getElementById("efectivo").getAttribute('value'));
            const efectioUsd = parseFloat(document.getElementById("efectivo-usd").getAttribute('value'));
            const tarjeta    = parseFloat(document.getElementById("tarjeta").getAttribute('value'));
            const cupon      = parseFloat(document.getElementById("cupon").getAttribute('value'));
            let restaTotal   = total-(efectivo+efectioUsd+tarjeta+cupon);

            resta.setAttribute("value",restaTotal);
            resta.value = formatter.format(restaTotal);
            enableReservar((restaTotal == 0) ? true : false);
        }
        function enableReservar($status){
            let reservar = document.getElementById("reservar");
            ($status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
        }
        function getActividadHorario(){
            const actividad   = document.getElementById("actividades").value;
            let horarioSelect = document.getElementById("horarios");
            let option;
            horarioSelect.length = 0;
            for (let i = 0; i < allActividades.length; i++) {
                if(actividad == allActividades[i].actividad.clave){
                    for (let ii = 0; ii < allActividades[i].horarios.length; ii++) {
                        option       = document.createElement("option");
                        option.value = allActividades[i].horarios[ii].id;
                        option.text  = allActividades[i].horarios[ii].horario_inicial;
                        horarioSelect.add(option);
                    }
                }
            }
        }
        function changeClaveActividad(actividad) {
            document.getElementById("clave-actividad").value = actividad.value;
            document.getElementById("clave-actividad").text = actividad.value;
        }
        function changeActividad(actividad) {
            document.getElementById("actividades").value = actividad.value;
            document.getElementById("actividades").text = actividad.value;
        }
        function getDisponibilidad(){
            axios.get('/api/disponibilidad')
            .then(function (response) {
                allActividades = response.data.disponibilidad;
                displayActividad()
            })
            .catch(function (error) {
                actividades = [];
            });
        }
        function displayActividad(){
            let actividadesClaveSelect = document.getElementById("clave-actividad");
            let actividadesSelect      = document.getElementById("actividades");
            let optionNombre;
            let optionClave;
            let option;
            for (var i = 0; i < allActividades.length; i++) {
                option             = document.createElement("option");
                optionClave        = document.createElement("option");
                option.value       = allActividades[i].actividad.clave;
                option.text        = allActividades[i].actividad.nombre;
                optionClave.value  = allActividades[i].actividad.clave;
                optionClave.text   = allActividades[i].actividad.clave;
                actividadesClaveSelect.appendChild(optionClave);
                actividadesSelect.add(option);
            }
        }
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Nueva Reserva</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 mt-3">
                                <strong>Datos del ciente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" required="required">  
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>    
                                <input type="email" name="email" class="form-control">  
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="localizacion" class="col-form-label">Localización</label>
                                <select name="localizacion" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                    <option value='0' selected="true">Seleccionar localización</option>
                                    @foreach($localizaciones as $localizacion)
                                        <option value="{{$localizacion->id}}">{{$localizacion->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>
                                <select name="origen" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                    <option value='0' selected="true">Seleccionar origen</option>
                                    @foreach($estados as $estado)
                                        <option value="{{$estado->nombre}} ({{$estado->pais->nombre}})">{{$estado->nombre}} ({{$estado->pais->nombre}})</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="clave" class="col-form-label">Clave</label>
                                <select id="clave-actividad" name="clave" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                </select>
                            </div>
                            <div class="form-group col-3 mt-0 mb-0">
                                <label for="actividad" class="col-form-label">Actividad</label>
                                <select name="actividad" id="actividades"  class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                </select>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="horario" class="col-form-label">Horario</label>
                                <select name="horario" id="horarios" class="form-control">
                                </select>
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="cantidad" class="col-form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" value="1" min="1" max="200">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="disponibilidad" class="col-form-label">Disponibilidad</label>
                                <input type="number" name="disponibilidad" class="form-control" value="0" disabled="disabled">
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="{{date('Y-m-d')}}">
                            </div>

                            <input type="hidden" name="precio" id="precio" value="0">

                            <div class="form-group col-1 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="agregar-reservacion">+</button>
                            </div>

                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="reservaciones" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Clave</th>
                                                    <th>Actividad</th>
                                                    <th>Costo P/P</th>
                                                    <th>Cantidad</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-9 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-6 mt-0 mb-0">
                                                <label for="horario" class="col-form-label">Reservado por</label>
                                                <select name="horario" id="horarios" class="form-control">
                                                    <option value="{{Auth::user()->id}}" selected="selected" disabled="disabled">
                                                        {{Auth::user()->name}} ({{Auth::user()->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-6 mt-0 mb-0">
                                                <label for="agencia" class="col-form-label">Agencia</label>
                                                <select name="agencia" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                                    <option value='0' selected="true">Seleccionar agencia</option>
                                                    @foreach($agencias as $agencia)
                                                        <option value="{{$agencia['id']}}">{{$agencia['nombre']}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" rows="5" style="width:100%;"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-3 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle de la reservación</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="total" class="col-form-label">Total:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="efectivo" class="col-form-label">Efectivo M.N.:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="efectivo" id="efectivo" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="efectivo-usd" class="col-form-label">Efectivo USD.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="tarjeta" class="col-form-label">Tarjeta crédito.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="tarjeta" id="tarjeta" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="resta" class="col-form-label">Resta</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="resta" id="resta" class="form-control amount" disabled="disabled" value="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="guardar">Guardar</button>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="reservar" disabled="disabled">Reservar</button>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="mt-33 btn btn-gray-700 btn-block" id="cancelar">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
