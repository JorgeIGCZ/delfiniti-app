let productosArray = [];

document.getElementById('pagar').addEventListener('click', (event) => {
    validateFecha();
    if (formValidity('venta-form') && cantidadProductosIsValid() && cambioValidoIsValid()) {
        createVenta('pagar');
    }
});

document.getElementById('cancelar').addEventListener('click', (event) => {
    event.preventDefault();
    resetReservaciones();
});

function createVenta(estatus) {
    const venta = document.getElementById('venta-form');
    const pagos = {
        'efectivo': venta.elements['efectivo'].getAttribute('value'),
        'efectivoUsd': venta.elements['efectio-usd'].getAttribute('value'),
        'tarjeta': venta.elements['tarjeta'].getAttribute('value'),
        'deposito': venta.elements['deposito'].getAttribute('value'),
        'cambio': venta.elements['cambio'].getAttribute('value'),
    };
    $('.loader').show();
    axios.post('/ventas', {
        '_token': token(),
        'nombre': venta.elements['nombre'].value,
        'email': venta.elements['email'].value,
        'rfc': venta.elements['rfc'].value,
        'direccion': venta.elements['direccion'].value,
        'origen': venta.elements['origen'].value,
        'total': venta.elements['total'].getAttribute('value'),
        'fecha': venta.elements['fecha'].value,
        'pagos': pagos,
        'comentarios': venta.elements['comentarios'].value,
        'estatus': estatus,
        'ventaProductos': productosArray
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: 'Venta creada',
                showConfirmButton: false,
                footer: `<a href="/ventas/${response.data.id}/edit">Ver venta</a>`,
                timer: 1500
            }).then(function() {
                if(getTicket(response.data.venta)){
                    location.reload();
                }
            });
        } else {
            $('.loader').hide();
            Swal.fire({
                icon: 'error',
                title: `Venta fallida`,
                text: response.data.message,
                showConfirmButton: true
            })
        }
    }).catch(function (error) {
        Swal.fire({
            icon: 'error',
            title: `Venta fallida E:${error.message}`,
            showConfirmButton: true
        })
    });
}

function addProductos() {
    const codigoProducto = document.getElementById('codigo').value;

    if (isProductoDuplicado({'codigoProducto': codigoProducto})) {
        Swal.fire({
            icon: 'warning',
            title: 'El prodducto ya se encuenta agregado.',
            showConfirmButton: false,
            timer: 900
        });
        clearSeleccion();
        return false;
    }
    // if(!isDisponible()){
    //     return false;
    // }
    addProducto();
    clearSeleccion();
    // enableBtn('reservar', productosArray.length > 0);
}

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

    enablePagar((getResta() < total) ? true : false);
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

    const pagos = (efectivo + efectivoUsd + tarjeta + deposito);

    return parseFloat(pagos);
}

function enablePagar(status) {
    let pagar = document.getElementById('pagar');
    (status) ? pagar.removeAttribute('disabled') : pagar.setAttribute('disabled', 'disabled');
}