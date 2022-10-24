let pagosTabla;
let allActividades = [];
const actualizarEstatusReservacion = document.getElementById('actualizar-estatus-reservacion');
const actualizar = document.getElementById('actualizar');
const pagar = document.getElementById('pagar');
const contenedorPagos = document.getElementById("detalle-reservacion-contenedor");
const elementoPagos = contenedorPagos.querySelectorAll("input, select, checkbox, textarea");
const detallePagoContainer = document.getElementById('detallePagoContainer');
const anticipoContainer = document.getElementById('anticipo-container');

setReservacionesTipoAccion();
changeCuponDetalle();
 
if(actualizarEstatusReservacion !== null){
    actualizarEstatusReservacion.addEventListener('click', (event) =>{
        event.preventDefault();
        if(document.getElementById('actualizar-estatus-reservacion').getAttribute('accion') == 'cancelar'){
            validateCancelarReservacion();
            return true;
        }
        validateActivarReservacion();
    });
}

if(actualizar !== null){
    actualizar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('reservacion-form')) {
            createReservacion('actualizar');
        }
    });
}  

if(pagar !== null){
    pagar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('reservacion-form') && cantidadActividadesIsValid()) {
            createReservacion('pagar');
        }
    });
}
if ((isReservacionPagada())) {
    bloquearPagos();
}

//jQuery
$('#pagos').on( 'click', '.editar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        $(this).closest('tr').find('.fecha-pago').removeClass('not-editable');
        $(this).closest('tr').find('.fecha-pago').removeAttribute('disabled');
    }
} );

$('#pagos').on( 'focusout', '.fecha-pago', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        Swal.fire({
            title: '¿Editar?',
            text: "La fecha de pago será actualizada, ¿desea proceder?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, actualizar!'
        }).then((result) => {
            if (result.isConfirmed) {
                editarPagoReservacion($(this))
            }
        });
    }
} );


pagosTabla = new DataTable('#pagos', {
    searching: false,
    paging: false,
    info: false
});

fillReservacionDetallesTabla();
fillPagosTabla();

async function editarPagoReservacion(row){
    const pagoId = row.parents('tr')[0].firstChild.innerText;
    const fecha =  row.closest('tr').find('.fecha-pago').val()

    $('.loader').show();
    result = await axios.post('/reservaciones/editPago', {
        '_token': token(),
        'reservacionId': reservacionId(),
        'fecha': fecha,
        'pagoId': pagoId
    });


    if(result.data.result == "Success"){
        $('.loader').hide();
        Swal.fire({
            icon: 'success',
            title: 'Pago actualizado',
            showConfirmButton: false,
            timer: 1000
        }).then(function() {
            location.reload();
        });
    }else{
        $('.loader').hide();
        Swal.fire({
            icon: 'error',
            title: `Petición fallida`,
            showConfirmButton: true
        })
    }

    return true;
}

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
    let hiddenFields = [];
    if (accion === 'pago') {
        hiddenFields = ['add-actividad', 'actualizar','actividad-container'];
        disabledFields = ['nombre', 'email', 'alojamiento', 'origen', 'clave', 'actividad', 'horario', 'fecha', 'cantidad', 'agente', 'cerrador'];
    } else {
        hiddenFields = ['pagar', 'detallePagoContainer', 'add-descuento-personalizado', 'add-codigo-descuento'];
        disabledFields = ['codigo-descuento'];
    }
    disabledFields.forEach((disabledField) => {
        let campoDeshabilitado = reservacion.elements[disabledField];
        if(campoDeshabilitado !== null){
            campoDeshabilitado.setAttribute('disabled', 'disabled');
            campoDeshabilitado.classList.add('not-editable');
        }
    });
    hiddenFields.forEach((hideField) => {
        let campoOcultar = document.getElementById(hideField);
        if(campoOcultar !== null){
            campoOcultar.style.setProperty('display', 'none', 'important');
        }
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
        'deposito': reservacion.elements['deposito'].getAttribute('value'),
        'cambio': reservacion.elements['cambio'].getAttribute('value'),
    };
    $('.loader').show();
    axios.post(`/reservaciones/${reservacionId()}`, {
        '_token': token(),
        '_method': 'PATCH',
        'nombre': reservacion.elements['nombre'].value,
        'email': reservacion.elements['email'].value,
        'alojamiento': reservacion.elements['alojamiento'].value,
        'origen': reservacion.elements['origen'].value,
        'agente': reservacion.elements['agente'].value,
        'comisionista': reservacion.elements['comisionista'].value,
        'comisionistaActividad': reservacion.elements['comisionista-actividad'].value,
        'cerrador': reservacion.elements['cerrador'].value,
        'total': reservacion.elements['total'].getAttribute('value'),
        'pagosAnteriores': reservacion.elements['anticipo'].getAttribute('value'),
        'fecha': reservacion.elements['fecha'].value,
        'pagos': estatus === 'pagar' ? pagos : {},
        'cupon': {
            'cantidad': reservacion.elements['cupon'].getAttribute('value'),//convertPorcentageCantidad(reservacion.elements['cupon'].getAttribute('value'))
            'tipo': reservacion.elements['cupon'].getAttribute('tipo')
        },
        'descuentoCodigo': {
            'cantidad': codigoDescuentoCantidad,
            'password': document.getElementById('descuento-codigo').getAttribute('password'),
            'valor': document.getElementById('descuento-codigo').value,
            'tipoValor': document.getElementById('descuento-codigo').getAttribute('tipo'),
            'descuentoCodigoId': document.getElementById('codigo-descuento').value
        },
        'descuentoPersonalizado': {
            'cantidad': calculatePagoPersonalizado(descuentoPersonalizadoCantidad, codigoDescuentoCantidad, cuponCantidad),
            'password': document.getElementById('descuento-personalizado').getAttribute('password'),
            'valor': document.getElementById('descuento-personalizado').value,
            'tipoValor': document.getElementById('descuento-personalizado').getAttribute('tipo')
        },
        'comentarios': reservacion.elements['comentarios'].value,
        "comisionable"   : reservacion.elements['comisionable'].checked,
        'estatus': estatus,
        'reservacionArticulos': actvidadesArray
    })
        .then(function (response) {
            $('.loader').hide();
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
                    title: `Reservacion fallida E:${response.data.message}`,
                    showConfirmButton: true
                })
            }
        })
        .catch(function (error) {
            $('.loader').hide();
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
    //'8','deposito'

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

function getPagos(tipoUsd = 'compra') {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const efectivo = parseFloat(document.getElementById('efectivo').getAttribute('value'));
    const efectivoUsd = (
        tipoUsd == 'compra'
            ? getMXNFromUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')))
            : getMXNFromVentaUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')))
    );
    const tarjeta = parseFloat(document.getElementById('tarjeta').getAttribute('value'));

    const deposito = parseFloat(document.getElementById('deposito').getAttribute('value'));

    const descuentoCodigo = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    const cantidadCodigo = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
        ? convertPorcentageCantidad(descuentoCodigo)
        : parseFloat(descuentoCodigo);

    const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));

    const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const cantidadPersonalizado = calculatePagoPersonalizado(descuentoPersonalizado, cantidadCodigo, cupon); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;

    const anticipo = parseFloat(document.getElementById('anticipo').getAttribute('value'));

    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + deposito + cantidadPersonalizado + cantidadCodigo + anticipo);

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
    elementoPagos.forEach(elemento => {
        elemento.classList.add('not-editable');
        elemento.setAttribute('disabled', 'disabled');
    })
    if(pagar !== null){
        pagar.remove();
    }
    if(detallePagoContainer !== null){
        detallePagoContainer.style.display = 'none';
    }
    if(anticipoContainer !== null){
        anticipoContainer.style.display = 'none';
    }
}