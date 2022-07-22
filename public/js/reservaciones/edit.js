        let pagosTabla;
        let allActividades = [];

            if((isReservacionPagada())){
                bloquearPagos();
            }


            pagosTabla = new DataTable('#pagos', {
                searching: false,
                paging: false,
                info: false
            } );

            fillReservacionDetallesTabla();
            fillPagosTabla();


            /*
            document.getElementById('add-descuento-cupon').addEventListener('click', (event) =>{
                resetDescuentos();
                $('#verificacion-modal').modal('show');
                document.getElementById('validar-verificacion').setAttribute('action','add-descuento-cupon');
            });
            */
           
            document.getElementById('add-descuento-personalizado').addEventListener('click', (event) =>{
                //resetDescuentos();
                if(document.getElementById('add-descuento-personalizado').checked){
                    $('#verificacion-modal').modal('show');
                    document.getElementById('add-descuento-personalizado').checked = false;
                    document.getElementById('validar-verificacion').setAttribute('action','add-descuento-personalizado');
                    document.getElementById('password').focus();
                }
            });

            document.getElementById('pagar').addEventListener('click', (event) =>{
                if(formValidity('reservacion-form')){
                    createReservacion('pagar');
                }
            });
            document.getElementById('actualizar').addEventListener('click', (event) =>{
                if(formValidity('reservacion-form')){
                    createReservacion('actualizar');
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
        $('#reservaciones').on( 'click', '.eliminar-celda', function (event) {
            event.preventDefault();
            reservacionesTable
                .row( $(this).parents('tr') )
                .remove()
                .draw();

            //remove clave from the array
            const clave   = $(this).parents('tr')[0].firstChild.innerText;
            const horario = $(this).parents('tr')[0].childNodes[2].innerText;
            let updated   = 0;
            reservacionesArray = reservacionesArray.filter(function (reservaciones) {
                let result = (reservaciones.claveActividad !== clave && reservaciones.horario !== horario && updated == 0);
                updated > 0 ? result = true : '';
                !result ? updated++ : '';
                return result;
            });
            enableBtn('actualizar',reservacionesArray.length > 0);
            setTotal();
        } );

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
            const total     = parseFloat(document.getElementById('total').getAttribute('value'));
            const descuento = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
            const limite    = parseFloat(document.getElementById('descuento-personalizado').getAttribute('limite'));
            //return ((total/100)*limite) >= descuento;//cantidad del porcentaje limite del total debe ser mayor o igual a la cantidad de descuento
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
            const comisionista    = document.getElementById('comisionista');
            const cuponDescuento  = comisionista.options[comisionista.selectedIndex].getAttribute('cuponDescuento');
            const cupon           = document.getElementById('cupon');
    
            document.getElementById('cupon').setAttribute('value',0);
            document.getElementById('cupon').value = 0;
            document.getElementById('reservacion-form').elements['cupon'].focus();

            (cuponDescuento == '1') ? cupon.removeAttribute('disabled') : removeCupon(cupon);
        }

        function removeCupon(cupon){
            cupon.setAttribute('disabled','disabled');
            cupon.setAttribute('value',0);
            cupon.value = 0;
            setTimeout(setOperacionResultados(),500);
        }
        
        function createReservacion(estatus){
            const reservacion = document.getElementById('reservacion-form');
            const pagos       = {
                'efectivo'     : reservacion.elements['efectivo'].getAttribute('value'),
                'efectivoUsd'  : reservacion.elements['efectio-usd'].getAttribute('value'),
                'tarjeta'      : reservacion.elements['tarjeta'].getAttribute('value'),
                'cambio'       : reservacion.elements['cambio'].getAttribute('value'),
            };
            axios.post(`/reservaciones/${reservacionId()}`, {
                '_token'       : token(),
                '_method'      : 'PATCH',
                'nombre'       : reservacion.elements['nombre'].value,
                'email'        : reservacion.elements['email'].value,
                'alojamiento'  : reservacion.elements['alojamiento'].value,
                'origen'       : reservacion.elements['origen'].value,
                'agente'       : reservacion.elements['agente'].value,
                'comisionista' : reservacion.elements['comisionista'].value,
                'cerrador'     : reservacion.elements['cerrador'].value,
                'total'        : reservacion.elements['total'].getAttribute('value'),
                'pagosAnteriores': reservacion.elements['anticipo'].getAttribute('value'),
                'fecha'        : reservacion.elements['fecha'].value,
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

        function validateDescuentoPersonalizado(){
            axios.post('/reservaciones/getDescuentoPersonalizadoValidacion', {
                '_token'          : token(),
                'email'           : userEmail(),
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
            if(!formValidity('reservacion-form')){
                return false;
            }
            axios.post('/reservaciones/getCodigoDescuento', {
                '_token'          : token(),
                'email'           : userEmail(),
                'password'        : document.getElementById('descuento-codigo').getAttribute('password'),
                'codigoDescuento' : reservacion.elements['codigo-descuento'].value
            })
            .then(function (response) {
                if(response.data.result == 'Success'){
                    switch (response.data.status) {
                        case 'authorized':
                            if(response.data.descuento.descuento !== null){
                                
                                $('#verificacion-modal').modal('hide');
                                setCodigoDescuento(response.data.descuento);
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

        function addActividades(){
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
            const acciones       = `<a href="#reservaciones" class='eliminar-celda' class='eliminar'>Eliminar</a>`
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
                'horario': horario
            }];
            //enableBtn('enableBtn',reservacionesArray.length > 0);
            setTotal();
        }
        
        function fillReservacionDetallesTabla(){
            reservacionesTable.rows.add(reservacionesTableArray).draw(false);
            setTotal();
        }

        function setCantidadPagada(cantidadPagada){
            document.getElementById('anticipo').setAttribute('value',cantidadPagada);
            document.getElementById('anticipo').value = formatter.format(cantidadPagada);
        }

        function fillPagosTabla(){ 
            //'1','efectivo'
            //'2','efectivoUsd'
            //'3','tarjeta'

            nombreTipoPagoArray.forEach(function(nombre) { 
                blockDescuentos(nombre);
            });

            pagosArray.forEach(function(pago) { 
                cantidadPagada += parseFloat(pago.cantidad);;
            });

            pagosTabla.rows.add(pagosTablaArray).draw(false);

            setCantidadPagada(cantidadPagada);
            setTotal();
        }

        function blockDescuentos(nombre){
            switch (nombre){
                case 'cupon':
                    document.getElementById('comisionista').setAttribute('disabled','disabled');
                    break;
                case 'descuentoCodigo':
                    document.getElementById('codigo-descuento').setAttribute('disabled','disabled');
                    document.getElementById('add-codigo-descuento').setAttribute('disabled','disabled');
                    break;
                case 'descuentoPersonalizado':
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
        function setTotal(){
            let total = 0;
            reservacionesArray.forEach(reservacion => {
                total += (reservacion.cantidad*reservacion.precio);
            });
            total = parseFloat(total).toFixed(2)
            document.getElementById('total').setAttribute('value',total);
            document.getElementById('total').value = formatter.format(total);
            
            setOperacionResultados();
        }
        function setTotalRecibido(){
            let totalRecibido = getPagos();
            totalRecibido     = parseFloat(totalRecibido).toFixed(2);

            document.getElementById('total-recibido').setAttribute('value',totalRecibido);
            document.getElementById('total-recibido').value = formatter.format(totalRecibido);
        }
        function setOperacionResultados(){
            const total = document.getElementById('total').getAttribute('value');
            setResta();
            setCambio();
            //document.getElementById('reservacion-form').elements['descuento-general'].focus();

            enableFinalizar((getResta() < total) ? true : false);
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

            setTotalRecibido();
        }
        function getResta(){
            const total = parseFloat(document.getElementById('total').getAttribute('value'));
            const pagos    = getPagos();
            const resta    = parseFloat(total-pagos);
            return resta;
        }

        function getPagos(){
            const total              = parseFloat(document.getElementById('total').getAttribute('value'));
            const efectivo           = parseFloat(document.getElementById('efectivo').getAttribute('value'));
            const efectivoUsd        = getMXNFromUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')));
            const tarjeta            = parseFloat(document.getElementById('tarjeta').getAttribute('value'));
            
            const descuentoCodigo   = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
            const cantidadCodigo    = convertPorcentageCantidad(descuentoCodigo); //(document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoCodigo/100)) : descuentoCodigo;
            
            const cupon           = parseFloat(document.getElementById('cupon').getAttribute('value'));
            
            const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
            const cantidadPersonalizado  = calculatePagoPersonalizado(descuentoPersonalizado,cantidadCodigo,cupon); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;

            const anticipo        = parseFloat(document.getElementById('anticipo').getAttribute('value'));

            const pagos           = (cupon+efectivo+efectivoUsd+tarjeta+cantidadPersonalizado+cantidadCodigo+anticipo);

            return parseFloat(pagos);
        }

        function getMXNFromUSD(usd){
            const dolarPrecio = dolarPrecioCompra();
            
            return usd*dolarPrecio;
        }

        function enableFinalizar($status){
            try {
                let pagarReservar = document.getElementById('pagar');
                ($status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled','disabled');
            } catch (error) {
                
            }
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
        function bloquearPagos(){
            const contenedorPagos = document.getElementById("detalle-reservacion-contenedor");
            const elementos       = contenedorPagos.querySelectorAll("input, select, checkbox, textarea")
            elementos.forEach( elemento => {
                elemento.classList.add('not-editable');
                elemento.setAttribute('disabled','disabled');
            })
            document.getElementById("pagar").remove();
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