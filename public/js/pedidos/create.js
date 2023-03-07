let productosArray = [];


document.getElementById('cancelar').addEventListener('click', (event) => {
    event.preventDefault();
    resetReservaciones();
});

function createReservacion(estatus) {
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
        'alojamiento': venta.elements['alojamiento'].value,
        'origen': venta.elements['origen'].value,
        'agente': venta.elements['agente'].value,
        'comisionista': venta.elements['comisionista'].value,
        'comisionistaActividad': venta.elements['comisionista-actividad'].value,
        'cerrador': venta.elements['cerrador'].value,
        'total': venta.elements['total'].getAttribute('value'),
        'fecha': venta.elements['fecha'].value,
        'pagos': pagos,
        'cupon': {
            'cantidad': venta.elements['cupon'].getAttribute('value'),
            'tipo': venta.elements['cupon'].getAttribute('tipo')
        },
        'comentarios': venta.elements['comentarios'].value,
        'estatus': estatus,
        "comisionable"   : venta.elements['comisionable'].checked,
        'ventaArticulos': productosArray
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: 'Reservacion creada',
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
                title: `Reservacion fallida`,
                text: response.data.message,
                showConfirmButton: true
            })
        }
    }).catch(function (error) {
        Swal.fire({
            icon: 'error',
            title: `Reservacion fallida E:${error.message}`,
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

function setSubTotal() {
    let subTotal = 0;
    productosArray.forEach(venta => {
        subTotal += (venta.cantidad * venta.precio);
    });
    subTotal = parseFloat(subTotal).toFixed(2)
    document.getElementById('subtotal').setAttribute('value', subTotal);
    document.getElementById('subtotal').value = formatter.format(subTotal);
    setTotal();
}

function setTotal(){
    let total = 0;
    const subTotal = parseFloat(document.getElementById('subtotal').getAttribute('value'));
    const iva = parseFloat(document.getElementById('iva').getAttribute('value'));
    const descuento = parseFloat(document.getElementById('descuento').getAttribute('value'));
    const ieps = parseFloat(document.getElementById('ieps').getAttribute('value'));

    total = parseFloat((subTotal + iva + ieps) - descuento).toFixed(2);

    document.getElementById('total').setAttribute('value', total);
    document.getElementById('total').value = formatter.format(total);
}