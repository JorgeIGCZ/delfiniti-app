let pagosTabla;
const actualizarEstatusVenta = document.getElementById('actualizar-estatus-venta');
const actualizar = document.getElementById('actualizar');
const pagar = document.getElementById('pagar');
const contenedorPagos = document.getElementById("detalle-venta-contenedor");
const elementoPagos = contenedorPagos.querySelectorAll("input, select, checkbox, textarea");
const detallePagoContainer = document.getElementById('detallePagoContainer');
const anticipoContainer = document.getElementById('anticipo-container');

setVentasTipoAccion();
// changeCuponDetalle();
 
if(actualizarEstatusVenta !== null){
    actualizarEstatusVenta.addEventListener('click', (event) =>{
        event.preventDefault();
        if(document.getElementById('actualizar-estatus-venta').getAttribute('accion') == 'cancelar'){
            validateCancelarVenta();
            return true;
        }
        validateActivarVenta();
    });
}

if(actualizar !== null){
    actualizar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('venta-form') && cantidadProductosIsValid() && cambioValidoIsValid() && fotografoIsValid()) {
            updateVenta('actualizar');
        }
    });
}  

if(pagar !== null){
    pagar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('venta-form') && cantidadProductosIsValid()) {
            updateVenta('pagar');
        }
    });
}
if ((isVentaPagada())) {
    bloquearPagos();
}

//jQuery
$('#pagos').on( 'click', '.editar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        $(this).hide();
        $(this).closest('tr').find('.fecha-pago').removeClass('not-editable');
        $(this).closest('tr').find('.fecha-pago').removeAttr('disabled');
    }
} );

$('#pagos').on( 'change', '.fecha-pago', function (event) {
    event.preventDefault();
    $(this).closest('tr').find('.editar-celda').show();
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
                editarPagoVenta($(this))
            }
        });
    }
} );


pagosTabla = new DataTable('#pagos', {
    searching: false,
    paging: false,
    info: false
});

fillVentaDetallesTabla();
fillPagosTabla();

async function editarPagoVenta(row){
    const pagoId = row.parents('tr')[0].firstChild.innerText;
    const fecha =  row.closest('tr').find('.fecha-pago').val()

    $('.loader').show();
    result = await axios.post('/fotovideoventas/editPago', {
        '_token': token(),
        'ventaId': ventaId(),
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

function validateCancelarVenta(){
    Swal.fire({
        title: '¿Cancelar?',
        text: "La venta será cancelada, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('validar-verificacion').setAttribute('action','cancelar-venta');
            $('#verificacion-modal').modal('show');
        }
    });
}

function validateActivarVenta(){
    Swal.fire({
        title: '¿Activar?',
        text: "La venta será activada, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, activar!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('validar-verificacion').setAttribute('action','activar-venta');
            $('#verificacion-modal').modal('show');
        }
    });
}

function setVentasTipoAccion() {
    const venta = document.getElementById('venta-form');
    let disabledFields = [];
    let hiddenFields = [];
    if (accion === 'pago') {
        hiddenFields = ['add-producto', 'actualizar','producto-container'];
        disabledFields = ['nombre', 'email', 'clave', 'productos', 'rfc', 'direccion', 'origen', 'cantidad', 'fecha', 'usuario'];
    } else {
        hiddenFields = ['pagar', 'detallePagoContainer'];
        disabledFields = [];
    }
    disabledFields.forEach((disabledField) => {
        let campoDeshabilitado = venta.elements[disabledField];
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

function updateVenta(estatus) {
    const venta = document.getElementById('venta-form');
    // const claveDescuentoCantidad = (document.getElementById('descuento-clave').getAttribute('tipo') == 'porcentaje')
    //     ? convertPorcentageCantidad(venta.elements['descuento-clave'].getAttribute('value'))
    //     : parseFloat(document.getElementById('descuento-clave').getAttribute('value'));
    // const cuponCantidad = venta.elements['cupon'].getAttribute('value');
    const descuentoPersonalizadoCantidad = venta.elements['descuento-personalizado'].getAttribute('value');

    const pagos = {
        'efectivo': venta.elements['efectivo'].getAttribute('value'),
        'efectivoUsd': venta.elements['efectio-usd'].getAttribute('value'),
        'tarjeta': venta.elements['tarjeta'].getAttribute('value'),
        'deposito': venta.elements['deposito'].getAttribute('value'),
        'cambio': venta.elements['cambio'].getAttribute('value'),
    };
    $('.loader').show();
    axios.post(`/fotovideoventas/${ventaId()}`, {
        '_token': token(),
        '_method': 'PATCH',
        'nombre': venta.elements['nombre'].value,
        'email': venta.elements['email'].value,
        'rfc': venta.elements['rfc'].value,
        'direccion': venta.elements['direccion'].value,
        'origen': venta.elements['origen'].value,
        'total': venta.elements['total'].getAttribute('value'),
        'fecha': venta.elements['fecha'].value,
        'pagos': estatus === 'pagar' ? pagos : {},
        'fotografo': venta.elements['fotografo'].value,
        'usuario': venta.elements['usuario'].value,
        'comentarios': venta.elements['comentarios'].value,
        'estatus': estatus,
        'ventaProductos': productosArray,
        'pagosAnteriores': venta.elements['anticipo'].getAttribute('value'),
        // 'cupon': {
        //     'cantidad': venta.elements['cupon'].getAttribute('value'),//convertPorcentageCantidad(venta.elements['cupon'].getAttribute('value'))
        //     'tipo': venta.elements['cupon'].getAttribute('tipo')
        // },
        // 'descuentoClave': {
        //     'cantidad': claveDescuentoCantidad,
        //     'password': document.getElementById('descuento-clave').getAttribute('password'),
        //     'valor': document.getElementById('descuento-clave').value,
        //     'tipoValor': document.getElementById('descuento-clave').getAttribute('tipo'),
        //     'descuentoClaveId': document.getElementById('clave-descuento').value
        // },
        'descuentoPersonalizado': {
            'cantidad': calculatePagoPersonalizado(descuentoPersonalizadoCantidad),
            'password': document.getElementById('descuento-personalizado').getAttribute('password'),
            'valor': document.getElementById('descuento-personalizado').value,
            'tipoValor': document.getElementById('descuento-personalizado').getAttribute('tipo')
        },
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
                        if(getTicket(response.data.venta)){
                            location.reload();
                        } 
                    });
                }else{
                    Swal.fire({
                        icon: 'success',
                        title: 'Venta actualizada',
                        showConfirmButton: false,
                        timer: 1000
                    }).then(function() {
                        location.reload();
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: `Venta fallida E:${response.data.message}`,
                    showConfirmButton: true
                })
            }
        })
        .catch(function (error) {
            $('.loader').hide();
            Swal.fire({
                icon: 'error',
                title: `Venta fallida E:${error.message}`,
                showConfirmButton: true
            })
        });
}

function addActividades() {
    let claveActividad = document.getElementById('clave-producto');
    claveActividad     = claveActividad.options[claveActividad.selectedIndex].text;
    const horario      = document.getElementById('horarios').value;

    if (isActividadDuplicada({'claveActividad': claveActividad, 'horario': horario})) {
        Swal.fire({
            icon: 'warning',
            title: 'La producto ya se encuentra agregada.',
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

function fillVentaDetallesTabla() {
    ventasTable.rows.add(ventasTableArray).draw(false);
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

    // nombreTipoPagoArray.forEach(function (nombre) {
    //     blockDescuentos(nombre);
    // });

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

// function blockDescuentos(nombre) {
//     switch (nombre) {
//         case 'cupon':
//             document.getElementById('comisionista').setAttribute('disabled', 'disabled');
//             break;
//         case 'descuentoClave':
//             document.getElementById('clave-descuento').setAttribute('disabled', 'disabled');
//             document.getElementById('add-clave-descuento').setAttribute('disabled', 'disabled');
//             break;
//         case 'descuentoPersonalizado':
//             document.getElementById('add-descuento-personalizado').setAttribute('disabled', 'disabled');
//             break;
//     }
// }

function setTotal() {
    let total = 0;
    productosArray.forEach(venta => {
        total += (venta.cantidad * venta.precio);
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
    //document.getElementById('venta-form').elements['descuento-general'].focus();

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

    // const descuentoClave = parseFloat(document.getElementById('descuento-clave').getAttribute('value'));
    // const cantidadClave = (document.getElementById('descuento-clave').getAttribute('tipo') == 'porcentaje')
    //     ? convertPorcentageCantidad(descuentoClave)
    //     : parseFloat(descuentoClave);

    // const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));

    const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const cantidadPersonalizado = calculatePagoPersonalizado(descuentoPersonalizado); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;

    const anticipo = parseFloat(document.getElementById('anticipo').getAttribute('value'));

    const pagos = (efectivo + efectivoUsd + tarjeta + deposito + anticipo + cantidadPersonalizado);// + cantidadClave + anticipo);

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