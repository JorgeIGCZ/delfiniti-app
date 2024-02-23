let allActividades = [];
let actvidadesArray = [];

document.getElementById('add-alojamiento').addEventListener('click', (event) =>{
    event.preventDefault();
});

document.getElementById('pagar-reservar').addEventListener('click', (event) => {
    validateFecha();
    if (formValidity('reservacion-form') && comisionistaIsValid() && cantidadActividadesIsValid() && cambioValidoIsValid() && numCumponIsValid()) {
        createReservacion('pagar-reservar');
    }
});

document.getElementById('reservar').addEventListener('click', (event) => {
    validateFecha();
    if (formValidity('reservacion-form')) {
        createReservacion('reservar');
    }
});

document.getElementById('cancelar').addEventListener('click', (event) => {
    event.preventDefault();
    resetReservaciones();
});

document.getElementById('alojamientos-form').addEventListener('submit', (event) =>{
    event.preventDefault();
    const alojamientos = document.getElementById('alojamientos-form');
    createAlojamiento(alojamientos);
});

function createAlojamiento(alojamientos){
    $('.loader').show();
    axios.post('/alojamientos', {
        '_token'   : token(),
        "nombre"   : alojamientos.elements['nombre'].value
    })
    .then(function (response) {
        $('.loader').hide();
        if(response.data.result == "Success"){
            Swal.fire({
                icon: 'success',
                title: 'Registro creado',
                showConfirmButton: false,
                timer: 1500
            });
            addAlojamiento(response.data.id,alojamientos.elements['nombre'].value);
            document.getElementById('alojamientos-form').reset();
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Registro fallido',
                html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                showConfirmButton: true
            })
        }
    })
    .catch(function (error) {
        $('.loader').hide();
        Swal.fire({
            icon: 'error',
            title: 'Registro fallido',
            html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
            showConfirmButton: true
        })
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
    axios.post('/reservaciones', {
        '_token': token(),
        'nombre': reservacion.elements['nombre'].value,
        'email': reservacion.elements['email'].value,
        'alojamiento': reservacion.elements['alojamiento'].value,
        'origen': reservacion.elements['origen'].value,
        'usuario': reservacion.elements['usuario'].value,
        'comisionista': reservacion.elements['comisionista'].value,
        'comisionistaActividad': reservacion.elements['comisionista-actividad'].value,
        'cerrador': reservacion.elements['cerrador'].value,
        'total': reservacion.elements['total'].getAttribute('value'),
        'fecha': reservacion.elements['fecha'].value,
        'pagos': estatus === 'pagar-reservar' ? pagos : {},
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
        'numCupon': reservacion.elements['num-cupon'].value,
        'estatus': estatus,
        "comisionable"   : reservacion.elements['comisionable'].checked,
        'reservacionArticulos': actvidadesArray
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: 'Reservacion creada',
                showConfirmButton: false,
                footer: `<a href="/reservaciones/${response.data.id}/edit">Ver reservación</a>`,
                timer: 1500
            }).then(function() {
                if (estatus === 'pagar-reservar') {
                    if(getTicket(response.data.reservacion)){
                        location.reload();
                    } 
                }else{
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
    enableBtn('reservar', actvidadesArray.length > 0);
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

    const descuentoCodigo = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    const cantidadCodigo = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
        ? convertPorcentageCantidad(descuentoCodigo)
        : parseFloat(descuentoCodigo);

    const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));

    const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const cantidadPersonalizado = calculatePagoPersonalizado(descuentoPersonalizado, cantidadCodigo, cupon); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;

    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + deposito + cantidadPersonalizado + cantidadCodigo);

    return parseFloat(pagos);
}

function enableReservar(status) {
    let pagarReservar = document.getElementById('pagar-reservar');
    (status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled', 'disabled');
}