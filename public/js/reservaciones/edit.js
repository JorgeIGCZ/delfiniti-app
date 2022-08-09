let pagosTabla;
let allActividades = [];

setReservacionesTipoAccion();

document.getElementById('actualizar-estatus-reservacion').addEventListener('click', (event) =>{
    event.preventDefault();
    if(document.getElementById('actualizar-estatus-reservacion').getAttribute('accion') == 'cancelar'){
        validateCancelarReservacion();
        return true;
    }
    validateActivarReservacion();
});

document.getElementById('actualizar').addEventListener('click', (event) => {
    event.preventDefault();
    if (formValidity('reservacion-form')) {
        createReservacion('actualizar');
    }
});

document.getElementById('pagar').addEventListener('click', (event) => {
    event.preventDefault();
    if (formValidity('reservacion-form')) {
        createReservacion('pagar');
    }
});

if ((isReservacionPagada())) {
    bloquearPagos();
}


pagosTabla = new DataTable('#pagos', {
    searching: false,
    paging: false,
    info: false
});

fillReservacionDetallesTabla();
fillPagosTabla();

function validateCancelarReservacion(){
    Swal.fire({
        title: '¿Cancelar?',
        text: "La reservación será cancelada, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('validar-verificacion').setAttribute('action','cancelar-reservacion');
            $('#verificacion-modal').modal('show');
        }
    });
}

function setReservacionesTipoAccion() {
    const reservacion = document.getElementById('reservacion-form');
    let disabledFields = [];
    let hideFields = [];
    if (accion === 'pago') {
        hideFields = ['add-actividad', 'actualizar','actividad-container'];
        disabledFields = ['nombre', 'email', 'alojamiento', 'origen', 'clave', 'actividad', 'horario', 'fecha', 'cantidad', 'agente', 'cerrador'];
    } else {
        hideFields = ['pagar', 'detallePagoContainer', 'add-descuento-personalizado', 'add-codigo-descuento','add-descuento-personalizado'];
        disabledFields = ['codigo-descuento'];
    }
    disabledFields.forEach((disabledField) => {
        reservacion.elements[disabledField].setAttribute('disabled', 'disabled');
        reservacion.elements[disabledField].classList.add('not-editable');
    });
    hideFields.forEach((hideField) => {
        document.getElementById(hideField).style.setProperty('display', 'none', 'important');
    });
}

function createReservacion(estatus) {
    const reservacion = document.getElementById('reservacion-form');
    const codigoDescuentoCantidad = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
        ? convertPorcentageCantidad(reservacion.elements['descuento-codigo'].getAttribute('value'))
        : parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    const cuponCantidad = reservacion.elements['cupon'].getAttribute('value');
    const descuentoPersonalizadoCantidad = reservacion.elements['descuento-personalizado'].getAttribute('value');
    const pagos = {
        'efectivo': reservacion.elements['efectivo'].getAttribute('value'),
        'efectivoUsd': reservacion.elements['efectio-usd'].getAttribute('value'),
        'tarjeta': reservacion.elements['tarjeta'].getAttribute('value'),
        'cambio': reservacion.elements['cambio'].getAttribute('value'),
    };
    axios.post(`/reservaciones/${reservacionId()}`, {
        '_token': token(),
        '_method': 'PATCH',
        'nombre': reservacion.elements['nombre'].value,
        'email': reservacion.elements['email'].value,
        'alojamiento': reservacion.elements['alojamiento'].value,
        'origen': reservacion.elements['origen'].value,
        'agente': reservacion.elements['agente'].value,
        'comisionista': reservacion.elements['comisionista'].value,
        'cerrador': reservacion.elements['cerrador'].value,
        'total': reservacion.elements['total'].getAttribute('value'),
        'pagosAnteriores': reservacion.elements['anticipo'].getAttribute('value'),
        'fecha': reservacion.elements['fecha'].value,
        'pagos': estatus === 'pagar' ? pagos : {},

        //'cupon'        : reservacion.elements['cupon'].getAttribute('value'),
        'cupon': {
            'cantidad': reservacion.elements['cupon'].getAttribute('value'),//convertPorcentageCantidad(reservacion.elements['cupon'].getAttribute('value'))
            'tipo': document.getElementById('descuento-codigo').getAttribute('tipo')
        },
        'descuentoCodigo': {
            'cantidad': codigoDescuentoCantidad,
            'password': document.getElementById('descuento-codigo').getAttribute('password'),
            'valor': document.getElementById('descuento-codigo').value,
            'tipoValor': document.getElementById('descuento-codigo').getAttribute('tipo')
        },
        'descuentoPersonalizado': {
            'cantidad': calculatePagoPersonalizado(descuentoPersonalizadoCantidad, codigoDescuentoCantidad, cuponCantidad),
            'password': document.getElementById('descuento-personalizado').getAttribute('password'),
            'valor': document.getElementById('descuento-personalizado').value,
            'tipoValor': document.getElementById('descuento-personalizado').getAttribute('tipo')
        },
        'comentarios': reservacion.elements['comentarios'].value,
        'estatus': estatus,
        'reservacionArticulos': actvidadesArray
    })
        .then(function (response) {
            if (response.data.result == 'Success') {
                if (estatus === 'pagar') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pago actualizado',
                        showConfirmButton: false,
                        timer: 1000
                    }).then(function() {
                        if(getTicket(response.data.reservacion)){
                            location.reload();
                        } 
                    });
                }else{
                    Swal.fire({
                        icon: 'success',
                        title: 'Reservacion actualizada',
                        showConfirmButton: false,
                        timer: 1000
                    }).then(function() {
                        location.reload();
                    });
                }
            } else {
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

function addActividades() {
    let claveActividad = document.getElementById('clave-actividad');
    claveActividad     = claveActividad.options[claveActividad.selectedIndex].text;
    const horario      = document.getElementById('horarios').value;

    if (isActividadDuplicada({'claveActividad': claveActividad, 'horario': horario})) {
        Swal.fire({
            icon: 'warning',
            title: 'La actividad ya se encuentra agregada.',
            showConfirmButton: false,
            timer: 900
        });
        return false;
    }
    if(!isDisponible()){
        return false;
    }
    addActividad();
}

function fillReservacionDetallesTabla() {
    reservacionesTable.rows.add(reservacionesTableArray).draw(false);
    setTotal();
}

function setCantidadPagada(cantidadPagada) {
    document.getElementById('anticipo').setAttribute('value', cantidadPagada);
    document.getElementById('anticipo').value = formatter.format(cantidadPagada);
}

function fillPagosTabla() {
    let cantidadPago = 0;
    let cantidadPagada = 0;
    //'1','efectivo'
    //'2','efectivoUsd'
    //'3','tarjeta'

    nombreTipoPagoArray.forEach(function (nombre) {
        blockDescuentos(nombre);
    });

    pagosArray.forEach(function (pago) {
        cantidadPago = parseFloat(getCantiodadPago(pago));
        cantidadPagada += parseFloat(cantidadPago);
        ;
    });

    pagosTabla.rows.add(pagosTablaArray).draw(false);

    setCantidadPagada(cantidadPagada);
    setTotal();
}

function getCantiodadPago(pago){
    let cantidadPago = pago.cantidad;
    if(pago.tipoPagoId == 2){
        cantidadPago = parseFloat(parseFloat(cantidadPago) * parseFloat(pago.tipoCambioUSD)).toFixed(2);
    }
    return cantidadPago;
}

function blockDescuentos(nombre) {
    switch (nombre) {
        case 'cupon':
            document.getElementById('comisionista').setAttribute('disabled', 'disabled');
            break;
        case 'descuentoCodigo':
            document.getElementById('codigo-descuento').setAttribute('disabled', 'disabled');
            document.getElementById('add-codigo-descuento').setAttribute('disabled', 'disabled');
            break;
        case 'descuentoPersonalizado':
            document.getElementById('add-descuento-personalizado').setAttribute('disabled', 'disabled');
            break;
    }
}

function setTotal() {
    let total = 0;
    actvidadesArray.forEach(reservacion => {
        total += (reservacion.cantidad * reservacion.precio);
    });
    total = parseFloat(total).toFixed(2)
    document.getElementById('total').setAttribute('value', total);
    document.getElementById('total').value = formatter.format(total);

    setOperacionResultados();
}

function setOperacionResultados() {
    const total = document.getElementById('total').getAttribute('value');
    setResta();
    setCambio();
    //document.getElementById('reservacion-form').elements['descuento-general'].focus();

    enableFinalizar((getResta() < total) ? true : false);
}

function getPagos() {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const efectivo = parseFloat(document.getElementById('efectivo').getAttribute('value'));
    const efectivoUsd = getMXNFromUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')));
    const tarjeta = parseFloat(document.getElementById('tarjeta').getAttribute('value'));

    const descuentoCodigo = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    const cantidadCodigo = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
        ? convertPorcentageCantidad(descuentoCodigo)
        : parseFloat(descuentoCodigo);

    const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));

    const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const cantidadPersonalizado = calculatePagoPersonalizado(descuentoPersonalizado, cantidadCodigo, cupon); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;

    const anticipo = parseFloat(document.getElementById('anticipo').getAttribute('value'));

    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + cantidadPersonalizado + cantidadCodigo + anticipo);

    return parseFloat(pagos);
}

function enableFinalizar($status) {
    try {
        let pagarReservar = document.getElementById('pagar');
        ($status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled', 'disabled');
    } catch (error) {

    }
}

function bloquearPagos() {
    const contenedorPagos = document.getElementById("detalle-reservacion-contenedor");
    const elementos = contenedorPagos.querySelectorAll("input, select, checkbox, textarea")
    elementos.forEach(elemento => {
        elemento.classList.add('not-editable');
        elemento.setAttribute('disabled', 'disabled');
    })
    document.getElementById('pagar').remove();
    document.getElementById('detallePagoContainer').style.display = 'none';
    document.getElementById('anticipo-container').style.display = 'none';
}