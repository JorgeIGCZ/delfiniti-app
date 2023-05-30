let productosArray = [];
getProductos();


document.getElementById('cancelar').addEventListener('click', (event) => {
    event.preventDefault();
    resetReservaciones();
});


document.getElementById('guardar').addEventListener('click', (event) => {
    event.preventDefault();
    if (formValidity('pedido-form')) {
        createPedido('guardar');
    }
});

function createPedido(estatus) {
    const pedido = document.getElementById('pedido-form');
    $('.loader').show();
    axios.post('/pedidos', {
        '_token': token(),
        'proveedor': pedido.elements['proveedor'].value,
        'fecha': pedido.elements['fecha'].value,
        'comentarios': pedido.elements['comentarios'].value,
        'estatus': estatus,
        'pedidoProductos': productosArray
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: 'Pedido creado',
                showConfirmButton: false,
                footer: `<a href="/pedidos/${response.data.id}/edit">Ver pedido</a>`,
                timer: 1500
            }).then(function() {
                if(getTicket(response.data.pedido)){
                    location.reload();
                }
            });
        } else {
            $('.loader').hide();
            Swal.fire({
                icon: 'error',
                title: `Pedido fallido`,
                text: response.data.message,
                showConfirmButton: true
            })
        }
    }).catch(function (error) {
        Swal.fire({
            icon: 'error',
            title: `Pedido fallido E:${error.message}`,
            showConfirmButton: true
        })
    });
}

function setSubTotal() {
    let subTotal = 0;
    productosArray.forEach(venta => {
        subTotal += (venta.cantidad * venta.costo);
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