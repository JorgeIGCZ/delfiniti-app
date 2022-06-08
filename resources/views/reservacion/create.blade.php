@extends('layouts.app')
@section('scripts')
    <script>
        let reservacionesTable; 
        let allActividades = [];
        let reservacionesArray  = [];
        window.onload = function() {
            getDisponibilidad();
            reservacionesTable = new DataTable('#reservaciones', {
                searching: false,
                paging: false,
                info: false
            } );
            document.getElementById('agregar-reservacion').addEventListener('click', (event) =>{
                event.preventDefault();
                addReservaciones();
            });
            document.getElementById('agregar-codigo').addEventListener('click', (event) =>{
                if(formValidity()){
                    getPeticionAutorizacionCodigo();
                }
            });
            document.getElementById('reservar').addEventListener('click', (event) =>{
                if(formValidity()){
                    createReservacion('reservar');
                }
            });
            document.getElementById('guardar').addEventListener('click', (event) =>{
                if(formValidity()){
                    createReservacion('guardar');
                }
            });
            document.getElementById('cancelar').addEventListener('click', (event) =>{
                event.preventDefault();
                resetReservaciones();
            });
            document.getElementById('efectivo').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('efectivo').value = '$0.00';
                    document.getElementById('efectivo').setAttribute('value',0);
                    return;
                }
                setTimeout(setResta(),500);
            });
            document.getElementById('efectivo-usd').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('efectivo-usd').value = '$0.00';
                    document.getElementById('efectivo-usd').setAttribute('value',0);
                    return;
                }
                setTimeout(setResta(),500);
            });
            document.getElementById('tarjeta').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('tarjeta').value = '$0.00';
                    document.getElementById('tarjeta').setAttribute('value',0);
                    return;
                }
                setTimeout(setResta(),500);
            });
            document.getElementById('cupon').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('cupon').value = '$0.00';
                    document.getElementById('cupon').setAttribute('value',0);
                    return;
                }
                setTimeout(setResta(),500);
            });
            //jQuery
            $('#clave-actividad').on('select2:select', function (e) {
                changeActividad();
            });
            $('#actividades').on('select2:select', function (e) {
                changeClaveActividad();
            });
        };
        function formValidity(){
            const reservacion = document.getElementById('reservacion-form');
            let response = true;
            if(reservacion.checkValidity()){
                event.preventDefault();
            }else{
                reservacion.reportValidity();
                response = false;
            }
            return response;
        }
        function createReservacion(estatus){
            const reservacion = document.getElementById('reservacion-form');
            axios.post('/reservacion/store', {
                '_token'       : '{{ csrf_token() }}',
                'nombre'       : reservacion.elements['nombre'].value,
                'email'        : reservacion.elements['email'].value,
                'localizacion' : reservacion.elements['localizacion'].value,
                'origen'       : reservacion.elements['origen'].value,
                'agente'       : reservacion.elements['agente'].value,
                'comisionista' : reservacion.elements['comisionista'].value,
                'total'        : reservacion.elements['total'].getAttribute('value'),
                'efectivo'     : reservacion.elements['efectivo'].getAttribute('value'),
                'efectioUsd'   : reservacion.elements['efectio-usd'].getAttribute('value'),
                'tarjeta'      : reservacion.elements['tarjeta'].getAttribute('value'),
                'cupon'        : reservacion.elements['cupon'].getAttribute('value'),
                'comentarios'  : reservacion.elements['comentarios'].value,
                'estatus'      : estatus,
                'reservacionArticulos'  : reservacionesArray
            })
            .then(function (response) {
                if(response.data.result == 'Success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Reservacion creada',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    resetReservaciones()
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: `Reservacion fallida`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: `Reservacion fallida E:${error.message}`,
                    showConfirmButton: true
                })
            });
        }

        function resetReservaciones(){
            const reservacion = document.getElementById('reservacion-form');
            reservacionesArray  = [];
            reservacion.reset();
            reservacionesTable.clear().draw();
            document.getElementsByName('cantidad')[0].value = 1;
            document.getElementsByName('disponibilidad')[0].value = 1;
            document.getElementsByName('fecha')[0].value = new Date();

            document.getElementById('efectivo').setAttribute("value",0);
            document.getElementById('efectivo-usd').setAttribute("value",0);
            document.getElementById('tarjeta').setAttribute("value",0);
            document.getElementById('cupon').setAttribute("value",0);
            document.getElementById('descuento').setAttribute("value",0);

            document.getElementById('efectivo').value     = 0;
            document.getElementById('efectivo-usd').value = 0;
            document.getElementById('tarjeta').value      = 0;
            document.getElementById('cupon').value        = 0;
            document.getElementById('descuento').value    = 0;

            $('select[name="actividad"] option:nth-child(1)').attr('selected','selected');
            $('select[name="actividad"]').trigger('change.select2');

            $('select[name="localizacion"] option:nth-child(1)').attr('selected','selected');
            $('select[name="localizacion"]').trigger('change.select2');

            $('select[name="origen"] option:nth-child(1)').attr('selected','selected');
            $('select[name="origen"]').trigger('change.select2');

            $('select[name="comisionista"] option:nth-child(1)').attr('selected','selected');
            $('select[name="comisionista"]').trigger('change.select2');

            changeClaveActividad();
            changeActividad();
            setResta();
        }
        function getPeticionAutorizacionCodigo(){
            const reservacion = document.getElementById('reservacion-form');
            const nombre          = reservacion.elements['nombre'].value;
            const codigoDescuento = reservacion.elements['codigo-descuento'].value;
            if(!formValidity()){
                return false;
            }
            axios.post('/reservacion/getPeticionAutorizacionCodigo', {
                '_token'       : '{{ csrf_token() }}',
                'nombre'       : reservacion.elements['nombre'].value,
                'codigoDescuento'  : reservacion.elements['codigo-descuento'].value
            })
            .then(function (response) {
                if(response.data.result == 'Success'){
                    switch (response.data.status) {
                        case 'authorized':
                            setCodigoDescuento(response.data.descuento);
                            break;
                        case 'waiting':
                            Swal.fire({
                                title: 'Esperando autorización...',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            setCodigoDescuento(null);
                            break;
                        case 'created':
                            Swal.fire({
                                icon: 'success',
                                title: 'Petición eviada',
                                message :'Esperando autorización...',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            setCodigoDescuento(null);
                            break;
                        default:
                            break;
                    }
                    changeBotonCodigoDescuento(response.data.id)
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: `Petición fallida`,
                        showConfirmButton: true
                    })
                    setCodigoDescuento(null);
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: `Autorización fallida E:${error.message}`,
                    showConfirmButton: true
                })
            });
        }
        function setCodigoDescuento(descuento){
            if(descuento == null){
                document.getElementById('descuento').value = `$0.00`;
                document.getElementById('descuento').setAttribute('value',0);
            }else{
                if(descuento.tipo == 'porcentaje'){
                    document.getElementById('descuento').value = `${descuento.descuento}%`;
                }else{
                    document.getElementById('descuento').value = descuento.descuento;
                }
                document.getElementById('descuento').setAttribute('value',descuento.descuento);
                document.getElementById('descuento').setAttribute('tipo',descuento.tipo);
            }
            setResta()
        }
        function changeBotonCodigoDescuento($codigoAutorizacion){
            document.getElementById('agregar-codigo').textContent = "Verificar";
        }
        function addReservaciones(){
            let actividadDetalle = document.getElementById('actividades');
            actividadDetalle     = actividadDetalle.options[actividadDetalle.selectedIndex].text;
            let horarioDetalle   = document.getElementById('horarios');
            horarioDetalle       = horarioDetalle.options[horarioDetalle.selectedIndex].text;
            let claveActividad   = document.getElementById('clave-actividad');
            claveActividad       = claveActividad.options[claveActividad.selectedIndex].text;
            const actividad      = document.getElementById('actividades').value;
            const cantidad       = document.getElementById('cantidad').value;
            const precio         = document.getElementById('precio').value;
            const horario        = document.getElementById('horarios').value;
            const fecha          = document.getElementById('fecha').value;
            const acciones       = `<a href='#' id='actividad-${claveActividad}' class='editar'>Editar</a> | <a href='#' id='actividad-${claveActividad}' class='eliminar'>Eliminar</a>`
            reservacionesTable.row.add( [ 
                claveActividad,
                actividadDetalle,
                horarioDetalle,
                cantidad,
                precio,
                precio*cantidad,
                acciones
            ] )
            .draw(false);
            reservacionesArray = [...reservacionesArray,{
                'claveActividad': claveActividad,
                'actividad': actividad,
                'cantidad': cantidad,
                'precio': precio,
                'horario': horario,
                'fecha': fecha
            }];
            setTotal();
        }
        function setTotal(){
            let total = 0;
            reservacionesArray.forEach(reservacion => {
                total += (reservacion.cantidad*reservacion.precio);
            });
            total = parseFloat(total).toFixed(2)
            document.getElementById('total').setAttribute('value',total);
            document.getElementById('total').value = formatter.format(total);
            setResta()
        }
        function getActividadPrecio(){
            const actividad = document.getElementById("actividades").value;
            let precio      = document.getElementById("precio");
            for (var i = 0; i < allActividades.length; i++) {
                if(actividad == allActividades[i].actividad.id){
                    precio.value = allActividades[i].actividad.precio;
                }
            }
        }
        function setResta(){
            const restaTotal = getResta();
            resta.setAttribute("value",restaTotal);
            resta.value = formatter.format(restaTotal);
            enableReservar((restaTotal == 0) ? true : false);
        }
        function getResta(){
            let resta                = document.getElementById("resta");
            const total              = parseFloat(document.getElementById('total').getAttribute('value'));
            const efectivo           = parseFloat(document.getElementById('efectivo').getAttribute('value'));
            const efectioUsd         = parseFloat(document.getElementById('efectivo-usd').getAttribute('value'));
            const tarjeta            = parseFloat(document.getElementById('tarjeta').getAttribute('value'));
            const cupon              = parseFloat(document.getElementById('cupon').getAttribute('value'));
            const descuento          = parseFloat(document.getElementById('descuento').getAttribute('value'));
            const cantidadDescuento  = (document.getElementById('descuento').getAttribute('tipo') == 'porcentaje') ? (total*(descuento/100)) : descuento;
            const restaTotal           = total-(efectivo+efectioUsd+tarjeta+cupon+cantidadDescuento);
            
            return restaTotal;
        }
        function enableReservar($status){
            let reservar = document.getElementById("reservar");
            ($status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
        }
        function getActividadHorario(){
            const actividad   = document.getElementById('actividades').value;
            let horarioSelect = document.getElementById('horarios');
            let option;
            horarioSelect.length = 0;
            for (let i = 0; i < allActividades.length; i++) {
                if(actividad == allActividades[i].actividad.id){
                    for (let ii = 0; ii < allActividades[i].horarios.length; ii++) {
                        option       = document.createElement('option');
                        option.value = allActividades[i].horarios[ii].id;
                        option.text  = allActividades[i].horarios[ii].horario_inicial;
                        horarioSelect.add(option);
                    }
                }
            }
        }
        function changeClaveActividad() {
            const actividades = document.getElementById('actividades');
            document.getElementById('clave-actividad').value = actividades.value;
            document.getElementById('clave-actividad').text = actividades.value;

            $('#clave-actividad').trigger('change.select2');
            getActividadHorario();
            getActividadPrecio();
        }
        function changeActividad() {
            const claveActividad = document.getElementById('clave-actividad');
            document.getElementById('actividades').value = claveActividad.value;
            document.getElementById('actividades').text = claveActividad.value;
        
            $('#actividades').trigger('change.select2');
            getActividadHorario();
            getActividadPrecio();
        }
        function getDisponibilidad(){
            axios.get('/api/disponibilidad')
            .then(function (response) {
                allActividades = response.data.disponibilidad;
                displayActividad()
                getActividadHorario()
                getActividadPrecio()
            })
            .catch(function (error) {
                actividades = [];
            });
        }
        function displayActividad(){
            let actividadesClaveSelect = document.getElementById('clave-actividad');
            let actividadesSelect      = document.getElementById('actividades');
            let optionNombre;
            let optionClave;
            let option;
            for (var i = 0; i < allActividades.length; i++) {
                option             = document.createElement('option');
                option.value       = allActividades[i].actividad.id;
                option.text        = allActividades[i].actividad.nombre;
                actividadesSelect.add(option);
                optionClave        = document.createElement('option');
                optionClave.value  = allActividades[i].actividad.id;
                optionClave.text   = allActividades[i].actividad.clave;
                optionClave.actividadId = allActividades[i].actividad.id;
                actividadesClaveSelect.add(optionClave);
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
                        <form class="row g-3 align-items-center f-auto" id="reservacion-form">
                            @csrf
                            <div class="col-12 mt-3">
                                <strong>Datos del ciente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" required="required" autocomplete="off">  
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>    
                                <input type="email" name="email" class="form-control" autocomplete="off">  
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
                                <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="disponibilidad" class="col-form-label">Disponibilidad</label>
                                <input type="number" name="disponibilidad" class="form-control" value="0" disabled="disabled">
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{date('Y-m-d')}}" autocomplete="off">
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
                                                    <th>Horario</th>
                                                    <th>Cantidad</th>
                                                    <th>Costo P/P</th>
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
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="agente" class="col-form-label">Reservado por</label>
                                                <select name="agente" class="form-control">
                                                    <option value="{{Auth::user()->id}}" selected="selected" disabled="disabled">
                                                        {{Auth::user()->name}} ({{Auth::user()->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistas as $comisionista)
                                                        <option value="{{$comisionista->id}}">{{$comisionista->nombre}} ({{$comisionista->tipo->nombre}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-4 mt-0 mb-0">
                                                <label for="codigo-descuento" class="col-form-label">Código descuento</label>
                                                <div class="input-button">
                                                    <input type="text" name="codigo-descuento" id="codigo-descuento"  class="form-control" autocomplete="off">
                                                    <button class="btn btn-info btn-block form-control" id="agregar-codigo">Agregar</button>
                                                </div>
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
                                                        <label for="descuento" class="col-form-label">Descuento</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="descuento" id="descuento" class="form-control" value="0%" disabled="disabled">
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
