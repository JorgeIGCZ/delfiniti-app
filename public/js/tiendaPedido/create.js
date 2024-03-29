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
        'pedidoProductos': productosArray,
        'impuestosTotales': impuestosTotales
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
                // if(getTicket(response.data.pedido)){
                    location.reload();
                // }
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