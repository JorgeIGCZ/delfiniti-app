// let pagosTabla;
let allActividades = [];
const actualizar = document.getElementById('actualizar');
const pagar = document.getElementById('pagar');
// const contenedorPagos = document.getElementById("detalle-pedido-contenedor");
// const elementoPagos = contenedorPagos.querySelectorAll("input, select, checkbox, textarea");
const detallePagoContainer = document.getElementById('detallePagoContainer');
const anticipoContainer = document.getElementById('anticipo-container');

setReservacionesTipoAccion();
getProductos();
// changeCuponDetalle();

if(actualizar !== null){
    actualizar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('pedido-form')) {
            createReservacion('actualizar');
        }
    });
}  

if(pagar !== null){
    pagar.addEventListener('click', (event) => {
        event.preventDefault();
        if (formValidity('pedido-form') && cantidadActividadesIsValid()) {
            createReservacion('pagar');
        }
    });
}
// if ((isReservacionPagada())) {
//     bloquearPagos();
// }

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
                editarPagoReservacion($(this))
            }
        });
    }
} );


// pagosTabla = new DataTable('#pagos', {
//     searching: false,
//     paging: false,
//     info: false
// });

fillPedidoDetallesTabla();
setSubTotal();
// fillPagosTabla();

async function editarPagoReservacion(row){
    const pagoId = row.parents('tr')[0].firstChild.innerText;
    const fecha =  row.closest('tr').find('.fecha-pago').val()

    $('.loader').show();
    result = await axios.post('/pedidos/editPago', {
        '_token': token(),
        'pedidoId': pedidoId(),
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

function setReservacionesTipoAccion() {
    // const pedido = document.getElementById('pedido-form');
    // let disabledFields = [];
    // let hiddenFields = [];
    // if (accion === 'pago') {
    //     hiddenFields = ['add-actividad', 'actualizar','actividad-container'];
    //     disabledFields = ['nombre', 'email', 'alojamiento', 'origen', 'clave', 'actividad', 'horario', 'fecha', 'cantidad', 'usuario', 'cerrador'];
    // } else {
    //     hiddenFields = ['pagar', 'detallePagoContainer', 'add-descuento-personalizado', 'add-codigo-descuento'];
    //     disabledFields = ['codigo-descuento'];
    // }
    // disabledFields.forEach((disabledField) => {
    //     let campoDeshabilitado = pedido.elements[disabledField];
    //     if(campoDeshabilitado !== null){
    //         campoDeshabilitado.setAttribute('disabled', 'disabled');
    //         campoDeshabilitado.classList.add('not-editable');
    //     }
    // });
    // hiddenFields.forEach((hideField) => {
    //     let campoOcultar = document.getElementById(hideField);
    //     if(campoOcultar !== null){
    //         campoOcultar.style.setProperty('display', 'none', 'important');
    //     }
    // });
}

function createReservacion(estatus) {
    const pedido = document.getElementById('pedido-form');
    // const codigoDescuentoCantidad = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
    //     ? convertPorcentageCantidad(pedido.elements['descuento-codigo'].getAttribute('value'))
    //     : parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    // const cuponCantidad = pedido.elements['cupon'].getAttribute('value');
    // const descuentoPersonalizadoCantidad = pedido.elements['descuento-personalizado'].getAttribute('value');
    // const pagos = {
    //     'efectivo': pedido.elements['efectivo'].getAttribute('value'),
    //     'efectivoUsd': pedido.elements['efectio-usd'].getAttribute('value'),
    //     'tarjeta': pedido.elements['tarjeta'].getAttribute('value'),
    //     'deposito': pedido.elements['deposito'].getAttribute('value'),
    //     'cambio': pedido.elements['cambio'].getAttribute('value'),
    // };
    $('.loader').show();
    axios.post(`/pedidos/${pedidoId()}`, {
        '_token': token(),
        '_method': 'PATCH',
        'proveedor': pedido.elements['proveedor'].value,
        'fecha': pedido.elements['fecha'].value,
        'comentarios': pedido.elements['comentarios'].value,
        'estatus': estatus,
        'pedidoProductos': productosArray
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
                    // if(getTicket(response.data.pedido)){
                        location.reload();
                    // } 
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

function setCantidadPagada(cantidadPagada) {
    document.getElementById('anticipo').setAttribute('value', cantidadPagada);
    document.getElementById('anticipo').value = formatter.format(cantidadPagada);
}

// function fillPagosTabla() {
//     let cantidadPago = 0;
//     let cantidadPagada = 0;
//     //'1','efectivo'
//     //'2','efectivoUsd'
//     //'3','tarjeta'
//     //'8','deposito'

//     nombreTipoPagoArray.forEach(function (nombre) {
//         blockDescuentos(nombre);
//     });

//     pagosArray.forEach(function (pago) {
//         cantidadPago = parseFloat(getCantiodadPago(pago));
//         cantidadPagada += parseFloat(cantidadPago);
//         ;
//     });

//     pagosTabla.rows.add(pagosTablaArray).draw(false);

//     setCantidadPagada(cantidadPagada);
//     setTotal();
// }

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

function setSubTotal() {
    let subTotal = 0;
    productosArray.forEach(producto => {
        subTotal += (producto.cantidad * producto.costo);
    });
    subTotal = parseFloat(subTotal).toFixed(2)
    document.getElementById('subtotal').setAttribute('value', subTotal);
    document.getElementById('subtotal').value = formatter.format(subTotal);
    setTotal();
}

function setTotal() {
    // let total = 0;
    // productosArray.forEach(producto => {
    //     total += (producto.cantidad * producto.costo);
    // });
    // total = parseFloat(total).toFixed(2)
    // document.getElementById('total').setAttribute('value', total);
    // document.getElementById('total').value = formatter.format(total);
    let total = 0;
    const subTotal = parseFloat(document.getElementById('subtotal').getAttribute('value'));
    const iva = parseFloat(document.getElementById('iva').getAttribute('value'));
    const ieps = parseFloat(document.getElementById('ieps').getAttribute('value'));

    total = parseFloat((subTotal + iva + ieps)).toFixed(2);

    document.getElementById('total').setAttribute('value', total);
    document.getElementById('total').value = formatter.format(total);

    setOperacionResultados();
}

function setOperacionResultados() {
    // const total = document.getElementById('total').getAttribute('value');
    // setResta();
    // setCambio();
    //document.getElementById('pedido-form').elements['descuento-general'].focus();

    // enableFinalizar((getResta() < total) ? true : false);
    enableFinalizar(true);
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

    const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));

    const anticipo = parseFloat(document.getElementById('anticipo').getAttribute('value'));

    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + deposito + anticipo);

    return parseFloat(pagos);
}

function enableFinalizar($status) {
    try {
        let pagarReservar = document.getElementById('pagar');
        ($status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled', 'disabled');
    } catch (error) {

    }
}

// function bloquearPagos() {
//     elementoPagos.forEach(elemento => {
//         elemento.classList.add('not-editable');
//         elemento.setAttribute('disabled', 'disabled');
//     })
//     if(pagar !== null){
//         pagar.remove();
//     }
//     if(detallePagoContainer !== null){
//         detallePagoContainer.style.display = 'none';
//     }
//     if(anticipoContainer !== null){
//         anticipoContainer.style.display = 'none';
//     }
// }