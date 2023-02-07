let allProductos = [];
let actvidadesArray = [];

document.getElementById('pagar').addEventListener('click', (event) => {
    validateFecha();
    if (formValidity('venta-form') && cantidadProductosIsValid() && cambioValidoIsValid()) {
        createReservacion('pagar');
    }
});

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
    enableBtn('reservar', actvidadesArray.length > 0);
}

function setTotal() {
    let total = 0;
    actvidadesArray.forEach(venta => {
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

    enableReservar((getResta() < total) ? true : false);
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

function enableReservar(status) {
    let pagarReservar = document.getElementById('pagar-reservar');
    (status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled', 'disabled');
}