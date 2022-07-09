@extends('layouts.app')
@section('scripts')
    <script>
        let reservacionesTable; 
        let allActividades = [];
        let reservacionesArray  = [];

        window.onload = function() {
            document.getElementById('reservacion-form').elements['nombre'].focus();

            $('body').on('keydown', 'input, select, button', function(e) {
                if (e.key === "Enter") {
                
                    if($(this).attr("id") == "agregar-reservacion"){
                        addReservaciones();
                    }
                    if($(this).attr("id") == "password"){
                        validarVerificacion();
                    }

                    var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
                    focusable = form.find('input[tabindex],a[tabindex],select[tabindex],button[tabindex],textarea[tabindex]').filter(':visible');
                    next = focusable.eq(focusable.index(this)+1);
                    if (next.length) {
                        next.focus();
                    } else {
                        form.submit();
                    }
                    return false;
                }
            });

            getDisponibilidad();

            document.getElementById('verificacion-modal').addEventListener('blur', (event) =>{
                document.getElementById('password').value="";
            });

            reservacionesTable = new DataTable('#reservaciones', {
                searching: false,
                paging: false,
                info: false
            } );
            document.getElementById('agregar-reservacion').addEventListener('click', (event) =>{
                event.preventDefault();
                addReservaciones();
            });
            document.getElementById('add-codigo-descuento').addEventListener('click', (event) =>{
                event.preventDefault();
                resetDescuentos();
                document.getElementById('validar-verificacion').setAttribute('action','add-codigo-descuento');
            });
            /*
            document.getElementById('add-descuento-cupon').addEventListener('click', (event) =>{
                resetDescuentos();
                $('#verificacion-modal').modal('show');
                document.getElementById('validar-verificacion').setAttribute('action','add-descuento-cupon');
            });
            */
            document.getElementById('add-descuento-personalizado').addEventListener('click', (event) =>{
                resetDescuentos();
                if(document.getElementById('add-descuento-personalizado').checked){
                    $('#verificacion-modal').modal('show');
                    document.getElementById('add-descuento-personalizado').checked = false;
                    document.getElementById('validar-verificacion').setAttribute('action','add-descuento-personalizado');
                    document.getElementById('password').focus();
                }
            });

            document.getElementById('validar-verificacion').addEventListener('click', (event) =>{
                validarVerificacion();
            });
            document.getElementById('pagar-reservar').addEventListener('click', (event) =>{
                if(formValidity()){
                    createReservacion('pagar-reservar');
                }
            });
            document.getElementById('reservar').addEventListener('click', (event) =>{
                if(formValidity()){
                    createReservacion('reservar');
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
                }
                setTimeout(setOperacionResultados(),500);
            });
            document.getElementById('efectivo-usd').addEventListener('keyup', (event) =>{
                //if(getResta() < 0){
                //    document.getElementById('efectivo-usd').value = '$0.00';
                //    document.getElementById('efectivo-usd').setAttribute('value',0);
                //}
                setTimeout(setOperacionResultados(),500);
            });
            document.getElementById('tarjeta').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('tarjeta').value = '$0.00';
                    document.getElementById('tarjeta').setAttribute('value',0);
                }
                setTimeout(setOperacionResultados(),500);
            });
            /*
            document.getElementById('cupon').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('cupon').value = '$0.00';
                    document.getElementById('cupon').setAttribute('value',0);
                }
                setTimeout(setOperacionResultados(),500);
            });
            */

            document.getElementById('descuento-codigo').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('descuento-codigo').value = '0%';
                    document.getElementById('descuento-codigo').setAttribute('value',0);
                }
                setTimeout(setOperacionResultados(),500);
            });

            document.getElementById('descuento-personalizado').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('descuento-personalizado').value = '0';
                    document.getElementById('descuento-personalizado').setAttribute('value',0);
                }
                if(!isLimite()){
                    document.getElementById('descuento-personalizado').value = '0';
                    document.getElementById('descuento-personalizado').setAttribute('value',0);
                }
                setTimeout(setOperacionResultados(),500);
            });

            document.getElementById('cupon').addEventListener('keyup', (event) =>{
                if(getResta() < 0){
                    document.getElementById('cupon').value = '0';
                    document.getElementById('cupon').setAttribute('value',0);
                }
                setTimeout(setOperacionResultados(),500);
            });

            //jQuery
            $('#reservaciones').on( 'click', '.eliminar-celda', function (event) {
                event.preventDefault();
                reservacionesTable
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();

                //remove clave from the array
                const clave = $(this).parents('tr')[0].firstChild.innerText;
                const fecha = $(this).parents('tr')[0].childNodes[3].innerText;
                let updated = 0;
                reservacionesArray = reservacionesArray.filter(function (reservaciones) {
                    let result = (reservaciones.claveActividad !== clave && reservaciones.fecha !== fecha && $updated === 0);
                    updated > 0 ? result = true : '';
                    !result ? updated++ : '';
                    return result;
                });
                setSubtotal();
            } );
            $('#clave-actividad').on('change', function (e) {
                changeActividad();
            });
            $('#actividades').on('change', function (e) {
                changeClaveActividad();
            });
            $('#comisionista').on('change', function (e) {
                changeCuponDetalle();
                document.getElementById('reservacion-form').elements['cupon'].focus();
            });
        };
        function validarVerificacion(){
            const action = document.getElementById('validar-verificacion').getAttribute('action');
                
            if(formValidity()){
                if(action === 'add-descuento-personalizado'){
                    applyDescuentoPassword('descuento-personalizado');
                    validateDescuentoPersonalizado();
                }else if(action === 'add-codigo-descuento'){
                    applyDescuentoPassword('descuento-codigo');
                    getCodigoDescuento();
                }
            }
        }
        function applyVariables(){
            const queryString = window.location.search;
            const urlParams   = new URLSearchParams(queryString);
            const fecha     = urlParams.get('f');
            const hora      = urlParams.get('h');
            const actividad = urlParams.get('id');
            if(actividad === null){
                return;
            }
            document.getElementsByName('fecha')[0].value = fecha;
            
            $('#actividades').val(actividad); 
            $('#actividades').trigger('change');   

            changeClaveActividad();

            $('#horarios').val(hora); 
            $('#horarios').trigger('change');
        }
        function isLimite(){
            const subtotal     = parseFloat(document.getElementById('subtotal').getAttribute('value'));
            const descuento = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
            const limite    = parseFloat(document.getElementById('descuento-personalizado').getAttribute('limite'));
            //return ((subtotal/100)*limite) >= descuento;//cantidad del porcentaje limite del total debe ser mayor o igual a la cantidad de descuento
            return limite >= descuento;
        }
        function resetDescuentos(){
            document.getElementById('descuento-personalizado-container').classList.add("hidden");
            document.getElementById('descuento-personalizado').setAttribute('limite','');
            document.getElementById('descuento-personalizado').setAttribute('password','');
            document.getElementById('descuento-personalizado').setAttribute('value',0);
            document.getElementById('descuento-personalizado').value = 0;

            document.getElementById('descuento-codigo-container').classList.add("hidden");
            document.getElementById('descuento-codigo').setAttribute('limite','');
            document.getElementById('descuento-codigo').setAttribute('password','');
            document.getElementById('descuento-codigo').setAttribute('value',0);
            document.getElementById('descuento-codigo').value = "0%";
            setTimeout(setOperacionResultados(),500);
        }
        function applyDescuentoPassword($elementId){
            document.getElementById($elementId).setAttribute('password',document.getElementById('password').value);
        }
        function changeCuponDetalle() {
            const comisionista = document.getElementById('comisionista');
            const tipoDetalle  = comisionista.options[comisionista.selectedIndex].getAttribute('tipo');
            const descuento    = document.getElementById('cupon');
    
            document.getElementById('cupon').setAttribute('value',0);
            document.getElementById('cupon').value = 0;
            document.getElementById('reservacion-form').elements['cupon'].focus();

            (tipoDetalle == 'Agencia') ? descuento.removeAttribute('disabled') : descuento.setAttribute('disabled','disabled');
        }

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
            const pagos       = {
                'efectivo'     : reservacion.elements['efectivo'].getAttribute('value'),
                'efectivoUsd'  : reservacion.elements['efectio-usd'].getAttribute('value'),
                'tarjeta'      : reservacion.elements['tarjeta'].getAttribute('value'),
                'cambio'       : reservacion.elements['cambio'].getAttribute('value'),
            };
            axios.post('/reservaciones', {
                '_token'       : '{{ csrf_token() }}',
                'nombre'       : reservacion.elements['nombre'].value,
                'email'        : reservacion.elements['email'].value,
                'alojamiento'  : reservacion.elements['alojamiento'].value,
                'origen'       : reservacion.elements['origen'].value,
                'agente'       : reservacion.elements['agente'].value,
                'comisionista' : reservacion.elements['comisionista'].value,
                'cerrador'     : reservacion.elements['cerrador'].value,
                'total'        : reservacion.elements['subtotal'].getAttribute('value')
                'pagos'        : estatus === 'pagar-reservar' ? pagos : {},
                
                
                //'cupon'        : reservacion.elements['cupon'].getAttribute('value'),
                'cupon'       : {
                    'cantidad': reservacion.elements['cupon'].getAttribute('value'),//convertPorcentageCantidad(reservacion.elements['cupon'].getAttribute('value'))
                },
                'descuentoCodigo'        : {
                    'cantidad': convertPorcentageCantidad(reservacion.elements['descuento-codigo'].getAttribute('value')),
                    'password': document.getElementById('descuento-codigo').getAttribute('password'),
                },
                'descuentoPersonalizado' : {
                    'cantidad': convertPorcentageCantidad(reservacion.elements['descuento-personalizado'].getAttribute('value')),
                    'password': document.getElementById('descuento-personalizado').getAttribute('password'),
                },

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
                    location.reload();
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

        function convertPorcentageCantidad(porcentaje){
            const subtotal = document.getElementById('subtotal').getAttribute('value');
            return (subtotal/100) * porcentaje;
        }

        function resetReservaciones(){
            const reservacion = document.getElementById('reservacion-form');
            reservacionesArray  = [];
            reservacion.reset();
            reservacionesTable.clear().draw();
            document.getElementsByName('cantidad')[0].value = 1;
            document.getElementsByName('disponibilidad')[0].value = 1;
            document.getElementsByName('fecha')[0].value = new Date();

            document.getElementById('efectivo').setAttribute('value',0);
            document.getElementById('efectivo-usd').setAttribute('value',0);
            document.getElementById('tarjeta').setAttribute('value',0);
            //document.getElementById('cupon').setAttribute('value',0);
            document.getElementById('cupon').setAttribute('value',0);
            document.getElementById('descuento-personalizado').setAttribute('value',0);
            document.getElementById('descuento-codigo').setAttribute('value',0);

            document.getElementById('efectivo').value     = 0;
            document.getElementById('efectivo-usd').value = 0;
            document.getElementById('tarjeta').value      = 0;
            //document.getElementById('cupon').value        = 0;
            document.getElementById('cupon').value         = 0;
            document.getElementById('descuento-personalizado').value   = 0;
            document.getElementById('descuento-codigo').value          = 0;
            
            $('select[name="actividad"] option:nth-child(1)').attr('selected','selected');
            $('select[name="actividad"]').trigger('change.select2');

            $('select[name="alojamiento"] option:nth-child(1)').attr('selected','selected');
            $('select[name="alojamiento"]').trigger('change.select2');

            $('select[name="origen"] option:nth-child(1)').attr('selected','selected');
            $('select[name="origen"]').trigger('change.select2');

            $('select[name="comisionista"] option:nth-child(1)').attr('selected','selected');
            $('select[name="comisionista"]').trigger('change.select2');

            $('select[name="cerrador"] option:nth-child(1)').attr('selected','selected');


            document.getElementById('descuento-personalizado').setAttribute('password','');
            document.getElementById('descuento-codigo').setAttribute('password','');

            changeClaveActividad();
            changeActividad();
            setOperacionResultados();
        }
        function validateDescuentoPersonalizado(){
            axios.post('/reservaciones/getDescuentoPersonalizadoValidacion', {
                '_token'          : '{{ csrf_token() }}',
                'email'           : '{{Auth::user()->email}}',
                'password'        : document.getElementById('descuento-personalizado').getAttribute('password')
            })
            .then(function (response) {
                if(response.data.result == 'Success'){
                    switch (response.data.status) {
                        case 'authorized':
                            $('#verificacion-modal').modal('hide');
                            setLimiteDescuentoPersonalizado(response.data.limite);
                            document.getElementById('add-descuento-personalizado').checked = true;
                            break;
                        default:
                            Swal.fire({
                                icon: 'error',
                                title: 'Codigo incorrecto',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            document.getElementById('add-descuento-personalizado').checked = false;
                            break;
                    }
                    $('#descuento-personalizado').focus();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: `Petición fallida`,
                        showConfirmButton: true
                    })
                    document.getElementById('add-descuento-personalizado').checked = false;
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: `Autorización fallida E:${error.message}`,
                    showConfirmButton: true
                })
                document.getElementById('add-descuento-personalizado').checked = false;
            });
        }
        function setLimiteDescuentoPersonalizado(limite){
            document.getElementById('descuento-personalizado').removeAttribute('disabled');
            if(limite !== null){
                document.getElementById('descuento-personalizado').setAttribute('limite',limite);
                document.getElementById('descuento-personalizado-container').classList.remove("hidden");
            }else{
                document.getElementById('descuento-personalizado-container').classList.add("hidden");
            }
            setOperacionResultados();
        }
        function getCodigoDescuento(){
            const reservacion = document.getElementById('reservacion-form');
            const nombre          = reservacion.elements['nombre'].value;
            const codigoDescuento = reservacion.elements['codigo-descuento'].value;
            if(!formValidity()){
                return false;
            }
            axios.post('/reservaciones/getCodigoDescuento', {
                '_token'          : '{{ csrf_token() }}',
                'email'           : '{{Auth::user()->email}}',
                'password'        : document.getElementById('descuento-codigo').getAttribute('password'),
                'codigoDescuento' : reservacion.elements['codigo-descuento'].value
            })
            .then(function (response) {
                if(response.data.result == 'Success'){
                    switch (response.data.status) {
                        case 'authorized':
                            if(response.data.descuento.descuento !== null){
                                
                                $('#verificacion-modal').modal('hide');
                                setCodigoDescuento(response.data.descuento.descuento);
                                break;
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Codigo incorrecto',
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            }
                        default:
                            Swal.fire({
                                icon: 'error',
                                title: 'Credenciales incorrectas',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            setCodigoDescuento(null);
                            break;
                    }
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
            document.getElementById('descuento-codigo').removeAttribute('disabled','disabled');
            if(descuento == null){
                document.getElementById('descuento-codigo').value = 0;
                document.getElementById('descuento-codigo').text  = 0;
                document.getElementById('descuento-codigo-container').classList.add("hidden");
            }else{
                $descuento = getDescuento(descuento);
                document.getElementById('descuento-codigo-container').classList.remove("hidden");
                document.getElementById('descuento-codigo').setAttribute('value',descuento);
                document.getElementById('descuento-codigo').value = `${descuento}%`;
            }
            setOperacionResultados()
            
        }
        function getDescuento(descuento){
            const subtotal = document.getElementById('subtotal').getAttribute('value');
            let cantidadDescuento = descuento.descuento;
            if(descuento.tipo == "porcentaje"){
                cantidadDescuento = descuento.descuento;//(subtotal/100) * descuento.descuento;
            }
            return cantidadDescuento;
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
            const acciones       = `<a href="#reservaciones" class='eliminar-celda' class='eliminar'>Eliminar</a>`
            reservacionesTable.row.add( [ 
                claveActividad,
                actividadDetalle,
                horarioDetalle,
                fecha,
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
            setSubtotal();
        }
        
        function getActividadPrecio(){
            const actividad = document.getElementById('actividades').value;
            let precio      = document.getElementById('precio');
            for (var i = 0; i < allActividades.length; i++) {
                if(actividad == allActividades[i].actividad.id){
                    precio.value = allActividades[i].actividad.precio;
                }
            }
        }
        function setSubtotal(){
            let subtotal = 0;
            reservacionesArray.forEach(reservacion => {
                subtotal += (reservacion.cantidad*reservacion.precio);
            });
            subtotal = parseFloat(subtotal).toFixed(2)
            document.getElementById('subtotal').setAttribute('value',subtotal);
            document.getElementById('subtotal').value = formatter.format(subtotal);
            
            setOperacionResultados();
        }
        function setTotal(){
            const subtotal               = parseFloat(document.getElementById('subtotal').getAttribute('value'));
            const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
            const cantidadPersonalizado  = (document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (subtotal*(descuentoPersonalizado/100)) : descuentoPersonalizado;
            const descuentoCodigo        = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
            const cantidadCodigo         = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje') ? (subtotal*(descuentoCodigo/100)) : descuentoCodigo;
            const cupon                  = parseFloat(document.getElementById('cupon').getAttribute('value'));
            

            let total = subtotal - (cantidadPersonalizado+cantidadCodigo+cupon);
            total     = parseFloat(total).toFixed(2);

            document.getElementById('total').setAttribute('value',total);
            document.getElementById('total').value = formatter.format(total);
        }
        function setOperacionResultados(){
            const subtotal = document.getElementById('subtotal').getAttribute('value');
            setResta();
            setCambio();
            //document.getElementById('reservacion-form').elements['descuento-general'].focus();

            enableFinalizar((getResta() < subtotal) ? true : false);
        }
        function setCambio(){
            const cambioCampo = document.getElementById('cambio');
            const resta       = getResta();
            const cambio      = getCambio(resta);
            cambioCampo.setAttribute('value',cambio);
            cambioCampo.value = formatter.format(cambio);
        }
        function getCambio(resta){
            return (resta < 0 ? resta : 0 );
        }
        function setResta(){
            const restaCampo  = document.getElementById('resta');
            const resta       = getResta();
            const restaTotal  = (resta >= 0 ? resta : 0 );
            restaCampo.setAttribute('value',restaTotal);
            restaCampo.value = formatter.format(restaTotal);

            setTotal();
        }
        function getResta(){
            const subtotal           = parseFloat(document.getElementById('subtotal').getAttribute('value'));
            const efectivo           = parseFloat(document.getElementById('efectivo').getAttribute('value'));
            const efectivoUsd        = getMXNFromUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')));
            const tarjeta            = parseFloat(document.getElementById('tarjeta').getAttribute('value'));
            
            const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
            const cantidadPersonalizado  = (document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (subtotal*(descuentoPersonalizado/100)) : descuentoPersonalizado;

            const descuentoCodigo = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
            const cantidadCodigo  = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje') ? (subtotal*(descuentoCodigo/100)) : descuentoCodigo;
            const cupon           = parseFloat(document.getElementById('cupon').getAttribute('value'));
            
            const resta           = subtotal-(cupon+efectivo+efectivoUsd+tarjeta+cantidadPersonalizado+cantidadCodigo);

            return resta;
        }
        function getMXNFromUSD(usd){
            const dolarPrecio = getDolarPrecioCompra();
            
            return usd*dolarPrecio;
        }
        function getDolarPrecioCompra(){
            return {{$dolarPrecioCompra->precio_compra}};
        }

        function enableFinalizar($status){
            let pagarReservar = document.getElementById('pagar-reservar');
            ($status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled','disabled');
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

            //$('#clave-actividad').trigger('change.select2');
            getActividadHorario();
            getActividadPrecio();
        }
        function changeActividad() {
            const claveActividad = document.getElementById('clave-actividad');
            document.getElementById('actividades').value = claveActividad.value;
            document.getElementById('actividades').text = claveActividad.value;
        
            //$('#actividades').trigger('change.select2');
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
                applyVariables()
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
    <div class="modal fade" id="verificacion-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h6 class="modal-title">Verificación</h6>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-12 mt-0 mb-0">
                    <label for="password" class="col-form-label">Contraseña</label>
                    <input type="password" id="password" class="form-control">
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button id="validar-verificacion" action="" class="btn btn-info btn-block form-control">Aplicar</button>
            </div>
        </div>
        </div><!-- modal-dialog -->
    </div>
    
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
                                <input type="text" name="nombre" class="form-control" required="required" autocomplete="off" tabindex="1">  
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>    
                                <input type="email" name="email" class="form-control" autocomplete="off" tabindex="2">  
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="alojamiento" class="col-form-label">Hotel</label>
                                <select name="alojamiento" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="3">
                                    <option value='0' selected="true">Seleccionar hotel</option>
                                    @foreach($alojamientos as $alojamiento)
                                        <option value="{{$alojamiento->id}}">{{$alojamiento->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>

                                <input list="ciudades" name="origen" class="form-control" tabindex="4"/>
                                <datalist id="ciudades">
                                    @foreach($estados as $estado)
                                        <option value="{{$estado->nombre}} ({{$estado->pais->nombre}})">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="clave" class="col-form-label">Clave</label>
                                <select id="clave-actividad" name="clave" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="5">
                                </select>
                            </div>
                            <div class="form-group col-3 mt-0 mb-0">
                                <label for="actividad" class="col-form-label">Actividad</label>
                                <select name="actividad" id="actividades"  class="form-control" data-show-subtext="true" data-live-search="true" tabindex="6">
                                </select>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="horario" class="col-form-label">Horario</label>
                                <select name="horario" id="horarios" class="form-control" tabindex="7">
                                </select>
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="cantidad" class="col-form-label">Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off" tabindex="8">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="disponibilidad" class="col-form-label">Disponibilidad</label>
                                <input type="number" name="disponibilidad" class="form-control" value="0" disabled="disabled" >
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{date('Y-m-d')}}" autocomplete="off" tabindex="9">
                            </div>
                            <input type="hidden" name="precio" id="precio" value="0">
                            <div class="form-group col-1 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="agregar-reservacion" tabindex="10">+</button>
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
                                                    <th>Fecha</th>
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
                                    <div class="form-group col-8 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="agente" class="col-form-label">Reservado por</label>
                                                <select name="agente" class="form-control" tabindex="11">
                                                    <option value="{{Auth::user()->id}}" selected="selected" disabled="disabled">
                                                        {{Auth::user()->name}} ({{Auth::user()->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" id="comisionista" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistas as $comisionista)
                                                        <option value="{{$comisionista->id}}" tipo="{{$comisionista->tipo->nombre}}">{{$comisionista->nombre}} ({{$comisionista->tipo->nombre}})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="cerrador" class="col-form-label">Cerrador</label>
                                                <select name="cerrador" id="cerrador" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar cerrador</option>
                                                    @foreach($cerradores as $cerrador)
                                                        <option value="{{$cerrador->id}}" >{{$cerrador->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-4 mt-0 mb-0">
                                                <label for="codigo-descuento" class="col-form-label">Código descuento</label>
                                                <div class="input-button">
                                                    <input type="text" name="codigo-descuento" id="codigo-descuento"  class="form-control" autocomplete="off" tabindex="13">
                                                    <button id="add-codigo-descuento" class="btn btn-info btn-block form-control" data-bs-toggle="modal" data-bs-target="#verificacion-modal">verificar</button>
                                                </div>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="add-descuento-personalizado" class="col-form-label">Agregar descuento</label>
                                                <input type="checkbox" name="add-descuento-personalizado" id="add-descuento-personalizado" class="form-control" style="display: block;" tabindex="14">
                                            </div>
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" rows="5" style="width:100%;"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle de la reservación</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="subtotal" class="col-form-label">SubTotal:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="subtotal" id="subtotal" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>


                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount height-auto" value="0" disabled="disabled" tipo='cantidad'>
                                                    </div>

                                                    <div id="descuento-personalizado-container" class="form-group col-12 mt-0 mb-0 hidden">
                                                        <div class="row ">
                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="descuento-personalizado" class="col-form-label">Descuento (Personalizado)</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="descuento-personalizado" id="descuento-personalizado" password="" limite="" class="form-control percentage height-auto" value="0" tipo='porcentaje'>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="descuento-codigo-container" class="form-group col-12 mt-0 mb-0 hidden">
                                                        <div class="row ">
                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="descuento-codigo" class="col-form-label">Descuento (Código)</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="descuento-codigo" id="descuento-codigo" password="" class="form-control percentage not-editable height-auto" disabled="disabled" value="0" tipo='porcentaje'>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total" class="col-form-label"><strong>Total:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="efectivo" class="col-form-label">Efectivo M.N.:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="efectivo" id="efectivo" class="form-control amount height-auto" value="0.00" tabindex="15">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="efectivo-usd" class="col-form-label">Efectivo USD.</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount height-auto" value="0.00" tabindex="16">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="tarjeta" class="col-form-label">Tarjeta crédito.</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="tarjeta" id="tarjeta" class="form-control amount height-auto" value="0.00" tabindex="17">
                                                    </div>

                                                    <!--div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount" value="0.00" disabled="disabled">
                                                    </div-->
                                                    
                                                    

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="resta" class="col-form-label">Resta</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="resta" id="resta" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cambio" class="col-form-label">Cambio</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cambio" id="cambio" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>


                                                    <div class="form-group col-12 mt-0 mb-0">
                                                        <button class="btn btn-info btn-block" id="pagar-reservar" disabled="disabled" tabindex="19">Pagar y reservar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="reservar" tabindex="18">Reservar</button>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="mt-33 btn btn-gray-700 btn-block" id="cancelar" tabindex="20">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
