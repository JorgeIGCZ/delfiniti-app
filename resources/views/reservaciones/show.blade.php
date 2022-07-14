@extends('layouts.app')
@section('scripts')
    <script>
        let reservacionesTabla; 
        let pagosTabla;
        let allActividades      = [];
        let reservacionesArray  = [];
        let pagosArray          = [];
        let cantidadPagada      = 0;

        window.onload = function() {
            getDisponibilidad();

            reservacionesTabla = new DataTable('#reservaciones', {
                searching: false,
                paging: false,
                info: false
            } );

            pagosTabla = new DataTable('#pagos', {
                searching: false,
                paging: false,
                info: false
            } );

            fillReservacionDetallesTabla();
            fillPagosTabla();

            document.getElementById('reservacion-form').elements['nombre'].focus();
            document.getElementById('verificacion-modal').addEventListener('blur', (event) =>{
                document.getElementById('password').value="";
            });

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
            document.getElementById('pagar').addEventListener('click', (event) =>{
                if(formValidity()){
                    createReservacion('pagar');
                }
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
            $('#reservaciones').on( 'click', '.eliminar-celda', function (event) {
                event.preventDefault();
                reservacionesTabla
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
            axios.post('/reservaciones/{{$reservacion->id}}', {
                '_token'       : '{{ csrf_token() }}',
                '_method'      : 'PATCH',
                'nombre'       : reservacion.elements['nombre'].value,
                'email'        : reservacion.elements['email'].value,
                'alojamiento'  : reservacion.elements['alojamiento'].value,
                'origen'       : reservacion.elements['origen'].value,
                'agente'       : reservacion.elements['agente'].value,
                'comisionista' : reservacion.elements['comisionista'].value,
                'cerrador'     : reservacion.elements['cerrador'].value,
                'total'        : reservacion.elements['subtotal'].getAttribute('value'),
                'pagosAnteriores': reservacion.elements['pagado'].getAttribute('value'),
                'pagos'        : estatus === 'pagar' ? pagos : {},
                
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
                        title: 'Reservacion actualizada',
                        showConfirmButton: false,
                        timer: 1500
                    })
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
            reservacionesTabla.row.add( [ 
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
        function fillReservacionDetallesTabla(){
            let actividadDetalle = '';
            let horarioDetalle   = '';
            let claveActividad   = '';
            let actividad      = '';
            let cantidad       = '';
            let precio         = '';
            let horario        = '';
            let fecha          = '';
            let acciones       = '';

            @forEach($reservacion->reservacionDetalle as $detalle)
                actividadDetalle = '{{$detalle->actividad->nombre}}';
                horarioDetalle   = '{{$detalle->horario->horario_inicial}}';
                claveActividad   = '{{$detalle->actividad->clave}}';
                actividad      = '{{$detalle->actividad_id}}';
                cantidad       = '{{$detalle->numero_personas}}';
                precio         = '{{$detalle->PPU}}';
                horario        = '{{$detalle->actividad_horario_id}}';
                fecha          = '{{$detalle->actividad_fecha}}';
                acciones       = `<a href="#reservaciones" class='eliminar-celda' class='eliminar'>Eliminar</a>`
                reservacionesTabla.row.add( [ 
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
            @endforeach
            setSubtotal();
        }

        function fillPagosTabla(){
            let pagoId       = '';
            let cantidad     = '';
            let tipoPago     = '';
            let tipoPagoId   = '';
            let fechaPago    = '';
            let acciones     = '';
            let tipoCambio   = 0;    
            //'1','efectivo'
            //'2','efectivoUsd'
            //'3','tarjeta'

            @forEach($reservacion->pagos as $pago)
                pagoId       = '{{$pago->id}}';
                tipoCambio   = '{{$pago->tipo_cambio_usd}}';
                cantidad     = '{{$pago->cantidad}}';
                tipoPago     = '{{$pago->tipoPago->nombre}}';
                tipoPagoId   = '{{$pago->tipo_pago_id}}';
                fechaPago    = '{{$pago->created_at}}';

                //acciones     = `<a href="#reservaciones" class='eliminar-celda' class='eliminar'>Eliminar</a>`
                pagosTabla.row.add( [ 
                    pagoId,
                    (pagoId == '2' ? `${cantidad}USD * ${tipoCambio}` : cantidad),
                    tipoPago,
                    fechaPago
                ] )
                .draw(false);
                pagosArray = [...pagosArray,{
                    'id': pagoId,
                    'cantidad': cantidad,
                    'tipoPagoId': tipoPagoId,
                    'fechaPago': fechaPago
                }];

                cantidadPagada += parseFloat(cantidad);
                blockDescuentos({{$pago->id}});
            @endforeach
            setCantidadPagada(cantidadPagada);
            setSubtotal();
        }

        function setCantidadPagada(cantidadPagada){
            document.getElementById('pagado').setAttribute('value',cantidadPagada);
            document.getElementById('pagado').value = formatter.format(cantidadPagada);
        }

        function blockDescuentos(pagoId){
            switch (pagoId){
                case 4:
                    document.getElementById('comisionista').setAttribute('disabled','disabled');
                    break;
                case 5:
                    document.getElementById('codigo-descuento').setAttribute('disabled','disabled');
                    break;
                case 6:
                    document.getElementById('add-descuento-personalizado').setAttribute('disabled','disabled');
                    break;
            }
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
            const pagado                 = parseFloat(document.getElementById('pagado').getAttribute('value'));
            

            let total = subtotal - (cantidadPersonalizado+cantidadCodigo+cupon+pagado);
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
            const pagado          = parseFloat(document.getElementById('pagado').getAttribute('value'));
            
            const resta           = subtotal-(cupon+efectivo+efectivoUsd+tarjeta+cantidadPersonalizado+cantidadCodigo+pagado);

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
            let pagarReservar = document.getElementById('pagar');
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
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">FOLIO: {{$reservacion->folio}}</h2>
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
                                <label for="nombre" class="col-form-label">Nombre:</label>    
                                <strong>{{$reservacion->nombre_cliente}}</strong>
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>   
                                <strong>{{$reservacion->email}}</strong> 
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="alojamiento" class="col-form-label">Hotel</label>
                                <strong>{{dd($reservacion->horario)}}</strong>
                            </div>  
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>

                                <input list="ciudades" name="origen" class="form-control" tabindex="4" value="{{$reservacion->origen}}"/>
                                <datalist id="ciudades">
                                    @foreach($estados as $estado)
                                        <option value="{{$estado->nombre}} ({{$estado->pais->nombre}})">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-12 mt-8 mb-2 bd-t">
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

                            <div class="col-12 mt-3">
                                <strong>Pagos</strong>
                            </div>
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="pagos" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Cantidad</th>
                                                    <th>Tipo de pago</th>
                                                    <th>Fecha pago</th>
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
                                                <p>{{Auth::user()->name}} ({{Auth::user()->email}})</p>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" id="comisionista" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistas as $comisionista)
                                                        <option value="{{$comisionista->id}}" tipo="{{$comisionista->tipo->nombre}}" {{$reservacion->comisionista_id === $comisionista->id ? 'selected="selected' : ""}}>{{$comisionista->nombre}} ({{$comisionista->tipo->nombre}})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="cerrador" class="col-form-label">Cerrador</label>
                                                <select name="cerrador" id="cerrador" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar cerrador</option>
                                                    @foreach($cerradores as $cerrador)
                                                        <option value="{{$cerrador->id}}" {{$reservacion->cerrador_id === $cerrador->id ? 'selected="selected' : ""}}>{{$cerrador->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                           
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <p>{{$reservacion->comentarios}}</p>
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
                                                        <label for="pagado" class="col-form-label">Pagado:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="pagado" id="pagado" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection