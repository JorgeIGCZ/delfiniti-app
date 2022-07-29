let allActividades = [];
let actvidadesArray = [];


document.getElementById('pagar-reservar').addEventListener('click', (event) => {
    if (formValidity('reservacion-form')) {
        createReservacion('pagar-reservar');
    }
});

document.getElementById('reservar').addEventListener('click', (event) => {
    if (formValidity('reservacion-form')) {
        createReservacion('reservar');
    }
});

document.getElementById('cancelar').addEventListener('click', (event) => {
    event.preventDefault();
    resetReservaciones();
});


function applyVariables() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const fecha = urlParams.get('f');
    const hora = urlParams.get('h');
    const actividad = urlParams.get('id');
    if (actividad === null) {
        return;
    }
    document.getElementsByName('fecha')[0].value = fecha;

    $('#actividades').val(actividad);
    $('#actividades').trigger('change');

    changeClaveActividad();

    $('#horarios').val(hora);
    $('#horarios').trigger('change');
}

function isLimite() {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const descuento = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const limite = parseFloat(document.getElementById('descuento-personalizado').getAttribute('limite'));
    //return ((total/100)*limite) >= descuento;//cantidad del porcentaje limite del total debe ser mayor o igual a la cantidad de descuento
    return limite >= descuento;
}

function resetDescuentos() {
    document.getElementById('descuento-personalizado-container').classList.add("hidden");
    document.getElementById('descuento-personalizado').setAttribute('limite', '');
    document.getElementById('descuento-personalizado').setAttribute('password', '');
    document.getElementById('descuento-personalizado').setAttribute('value', 0);
    document.getElementById('descuento-personalizado').value = 0;

    document.getElementById('descuento-codigo-container').classList.add("hidden");
    document.getElementById('descuento-codigo').setAttribute('limite', '');
    document.getElementById('descuento-codigo').setAttribute('password', '');
    document.getElementById('descuento-codigo').setAttribute('value', 0);
    document.getElementById('descuento-codigo').value = "0%";
    setTimeout(setOperacionResultados(), 500);
}

function applyDescuentoPassword($elementId) {
    document.getElementById($elementId).setAttribute('password', document.getElementById('password').value);
}

function removeCupon(cupon) {
    cupon.setAttribute('disabled', 'disabled');
    cupon.setAttribute('value', 0);
    cupon.value = 0;
    setTimeout(setOperacionResultados(), 500);
}

function createReservacion(estatus) {
    const reservacion = document.getElementById('reservacion-form');
    const pagos = {
        'efectivo': reservacion.elements['efectivo'].getAttribute('value'),
        'efectivoUsd': reservacion.elements['efectio-usd'].getAttribute('value'),
        'tarjeta': reservacion.elements['tarjeta'].getAttribute('value'),
        'cambio': reservacion.elements['cambio'].getAttribute('value'),
    };
    axios.post('/reservaciones', {
        '_token': token(),
        'nombre': reservacion.elements['nombre'].value,
        'email': reservacion.elements['email'].value,
        'alojamiento': reservacion.elements['alojamiento'].value,
        'origen': reservacion.elements['origen'].value,
        'agente': reservacion.elements['agente'].value,
        'comisionista': reservacion.elements['comisionista'].value,
        'cerrador': reservacion.elements['cerrador'].value,
        'total': reservacion.elements['total'].getAttribute('value'),
        'fecha': reservacion.elements['fecha'].value,
        'pagos': estatus === 'pagar-reservar' ? pagos : {},
        'cupon': {
            'cantidad': reservacion.elements['cupon'].getAttribute('value'),//convertPorcentageCantidad(reservacion.elements['cupon'].getAttribute('value'))
            'tipo': document.getElementById('descuento-codigo').getAttribute('tipo')
        },
        'descuentoCodigo': {
            'cantidad': (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
                ? convertPorcentageCantidad(reservacion.elements['descuento-codigo'].getAttribute('value'))
                : parseFloat(document.getElementById('descuento-codigo').getAttribute('value')),
            'password': document.getElementById('descuento-codigo').getAttribute('password'),
            'valor': document.getElementById('descuento-codigo').value,
            'tipoValor': document.getElementById('descuento-codigo').getAttribute('tipo')
        },
        'descuentoPersonalizado': {
            'cantidad': convertPorcentageCantidad(reservacion.elements['descuento-personalizado'].getAttribute('value')),
            'password': document.getElementById('descuento-personalizado').getAttribute('password'),
            'valor': document.getElementById('descuento-personalizado').value,
            'tipoValor': document.getElementById('descuento-personalizado').getAttribute('tipo')
        },

        'comentarios': reservacion.elements['comentarios'].value,
        'estatus': estatus,
        'reservacionArticulos': actvidadesArray
    }).then(function (response) {
        if (response.data.result == 'Success') {
            if (estatus === 'pagar-reservar') {
                getTicket(response.data.reservacionFolio);
            }
            Swal.fire({
                icon: 'success',
                title: 'Reservacion creada',
                showConfirmButton: true,
                footer: `<a href="/reservaciones/${response.data.id}/edit">Ver reservación</a>`
            }).then((result) => {
                if (result.isConfirmed) {
                    resetReservaciones();
                    location.reload();
                }
            })
        } else {
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

function resetReservaciones() {
    const reservacion = document.getElementById('reservacion-form');
    actvidadesArray = [];
    reservacion.reset();
    reservacionesTable.clear().draw();
    document.getElementsByName('cantidad')[0].value = 1;
    document.getElementsByName('disponibilidad')[0].value = 1;
    document.getElementsByName('fecha')[0].value = new Date();

    document.getElementById('efectivo').setAttribute('value', 0);
    document.getElementById('efectivo-usd').setAttribute('value', 0);
    document.getElementById('tarjeta').setAttribute('value', 0);
    //document.getElementById('cupon').setAttribute('value',0);
    document.getElementById('cupon').setAttribute('value', 0);
    document.getElementById('descuento-personalizado').setAttribute('value', 0);
    document.getElementById('descuento-codigo').setAttribute('value', 0);

    document.getElementById('efectivo').value = 0;
    document.getElementById('efectivo-usd').value = 0;
    document.getElementById('tarjeta').value = 0;
    //document.getElementById('cupon').value        = 0;
    document.getElementById('cupon').value = 0;
    document.getElementById('descuento-personalizado').value = 0;
    document.getElementById('descuento-codigo').value = 0;

    $('select[name="actividad"] option:nth-child(1)').attr('selected', 'selected');
    $('select[name="actividad"]').trigger('change.select2');

    $('select[name="alojamiento"] option:nth-child(1)').attr('selected', 'selected');
    $('select[name="alojamiento"]').trigger('change.select2');

    $('select[name="origen"] option:nth-child(1)').attr('selected', 'selected');
    $('select[name="origen"]').trigger('change.select2');

    $('select[name="comisionista"] option:nth-child(1)').attr('selected', 'selected');
    $('select[name="comisionista"]').trigger('change.select2');

    $('select[name="cerrador"] option:nth-child(1)').attr('selected', 'selected');


    document.getElementById('descuento-personalizado').setAttribute('password', '');
    document.getElementById('descuento-codigo').setAttribute('password', '');

    changeClaveActividad();
    changeActividad();
    setOperacionResultados();
    enableBtn('reservar', false);
}

function validateDescuentoPersonalizado() {
    axios.post('/reservaciones/getDescuentoPersonalizadoValidacion', {
        '_token': token(),
        'email': userEmail(),
        'password': document.getElementById('descuento-personalizado').getAttribute('password')
    })
        .then(function (response) {
            if (response.data.result == 'Success') {
                switch (response.data.status) {
                    case 'authorized':
                        $('#verificacion-modal').modal('hide');
                        setLimiteDescuentoPersonalizado(response.data.limite);
                        document.getElementById('add-descuento-personalizado').checked = true;
                        break;
                    default:
                        Swal.fire({
                            icon: 'error',
                            title: 'Codigo incorrecto',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        document.getElementById('add-descuento-personalizado').checked = false;
                        break;
                }
                $('#descuento-personalizado').focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: `Petición fallida`,
                    showConfirmButton: true
                })
                document.getElementById('add-descuento-personalizado').checked = false;
            }
        })
        .catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: `Autorización fallida E:${error.message}`,
                showConfirmButton: true
            })
            document.getElementById('add-descuento-personalizado').checked = false;
        });
}

function setLimiteDescuentoPersonalizado(limite) {
    document.getElementById('descuento-personalizado').removeAttribute('disabled');
    if (limite !== null) {
        document.getElementById('descuento-personalizado').setAttribute('limite', limite);
        document.getElementById('descuento-personalizado-container').classList.remove("hidden");
    } else {
        document.getElementById('descuento-personalizado-container').classList.add("hidden");
    }
    setOperacionResultados();
}

function getCodigoDescuento() {
    const reservacion = document.getElementById('reservacion-form');
    const nombre = reservacion.elements['nombre'].value;
    const codigoDescuento = reservacion.elements['codigo-descuento'].value;
    if (!formValidity('reservacion-form')) {
        return false;
    }
    axios.post('/reservaciones/getCodigoDescuento', {
        '_token': token(),
        'email': userEmail(),
        'password': document.getElementById('descuento-codigo').getAttribute('password'),
        'codigoDescuento': reservacion.elements['codigo-descuento'].value
    })
        .then(function (response) {
            if (response.data.result == 'Success') {
                switch (response.data.status) {
                    case 'authorized':
                        if (response.data.descuento.descuento !== null) {

                            $('#verificacion-modal').modal('hide');
                            setCodigoDescuento(response.data.descuento);
                            break;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Codigo incorrecto',
                                showConfirmButton: false,
                                timer: 1500
                            })

                            document.getElementById('descuento-codigo').value = 0;
                            document.getElementById('descuento-codigo').text = 0;
                            document.getElementById('descuento-codigo-container').classList.add("hidden");
                        }
                    default:
                        Swal.fire({
                            icon: 'error',
                            title: 'Credenciales incorrectas',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        document.getElementById('descuento-codigo').value = 0;
                        document.getElementById('descuento-codigo').text = 0;
                        document.getElementById('descuento-codigo-container').classList.add("hidden");
                        break;
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: `Petición fallida`,
                    showConfirmButton: true
                })
                document.getElementById('descuento-codigo').value = 0;
                document.getElementById('descuento-codigo').text = 0;
                document.getElementById('descuento-codigo-container').classList.add("hidden");
            }
        })
        .catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: `Autorización fallida E:${error.message}`,
                showConfirmButton: true
            })
        });
}

function addActividades() {
    let actividadDetalle = document.getElementById('actividades');
    actividadDetalle = actividadDetalle.options[actividadDetalle.selectedIndex].text;
    let horarioDetalle = document.getElementById('horarios');
    horarioDetalle = horarioDetalle.options[horarioDetalle.selectedIndex].text;
    let claveActividad = document.getElementById('clave-actividad');
    claveActividad = claveActividad.options[claveActividad.selectedIndex].text;
    const actividad = document.getElementById('actividades').value;
    const cantidad = document.getElementById('cantidad').value;
    const precio = document.getElementById('precio').value;
    const horario = document.getElementById('horarios').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`
    if (isActividadDuplicada({'claveActividad': claveActividad, 'horario': horario})) {
        Swal.fire({
            icon: 'warning',
            title: 'La actividad ya se encuentra agregada.',
            showConfirmButton: false,
            timer: 900
        });
        return false;
    }
    reservacionesTable.row.add([
        claveActividad,
        actividadDetalle,
        horarioDetalle,
        cantidad,
        precio,
        precio * cantidad,
        acciones
    ])
        .draw(false);
    actvidadesArray = [...actvidadesArray, {
        'claveActividad': claveActividad,
        'actividad': actividad,
        'cantidad': cantidad,
        'precio': precio,
        'horario': horario
    }];
    enableBtn('reservar', actvidadesArray.length > 0);
    setTotal();
}

function getActividadPrecio() {
    const actividad = document.getElementById('actividades').value;
    let precio = document.getElementById('precio');
    for (var i = 0; i < allActividades.length; i++) {
        if (actividad == allActividades[i].actividad.id) {
            precio.value = allActividades[i].actividad.precio;
        }
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

function setTotalRecibido() {
    let totalRecibido = getPagos();
    totalRecibido = parseFloat(totalRecibido).toFixed(2);

    document.getElementById('total-recibido').setAttribute('value', totalRecibido);
    document.getElementById('total-recibido').value = formatter.format(totalRecibido);
}

function setOperacionResultados() {
    const total = document.getElementById('total').getAttribute('value');
    setResta();
    setCambio();
    //document.getElementById('reservacion-form').elements['descuento-general'].focus();

    enableReservar((getResta() < total) ? true : false);
}

function setCambio() {
    const cambioCampo = document.getElementById('cambio');
    const resta = getResta();
    const cambio = getCambio(resta);
    cambioCampo.setAttribute('value', cambio);
    cambioCampo.value = formatter.format(cambio);
}

function getCambio(resta) {
    return (resta < 0 ? resta : 0);
}

function setResta() {
    const restaCampo = document.getElementById('resta');
    const resta = getResta();
    const restaTotal = (resta >= 0 ? resta : 0);
    restaCampo.setAttribute('value', restaTotal);
    restaCampo.value = formatter.format(restaTotal);

    setTotalRecibido();
}

function getResta() {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const pagos = getPagos();
    const resta = parseFloat(total - pagos);
    return resta;
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

    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + cantidadPersonalizado + cantidadCodigo);

    return parseFloat(pagos);
}

function getMXNFromUSD(usd) {
    const dolarPrecio = dolarPrecioCompra();

    return usd * dolarPrecio;
}

function enableReservar(status) {
    let pagarReservar = document.getElementById('pagar-reservar');
    (status) ? pagarReservar.removeAttribute('disabled') : pagarReservar.setAttribute('disabled', 'disabled');
}

function getActividadHorario() {
    const actividad = document.getElementById('actividades').value;
    let horarioSelect = document.getElementById('horarios');
    let option;
    horarioSelect.length = 0;
    for (let i = 0; i < allActividades.length; i++) {
        if (actividad == allActividades[i].actividad.id) {
            for (let ii = 0; ii < allActividades[i].horarios.length; ii++) {
                option = document.createElement('option');
                option.value = allActividades[i].horarios[ii].id;
                option.text = allActividades[i].horarios[ii].horario_inicial;
                horarioSelect.add(option);
            }
        }
    }
}

function displayActividad() {
    let actividadesClaveSelect = document.getElementById('clave-actividad');
    let actividadesSelect = document.getElementById('actividades');
    let optionNombre;
    let optionClave;
    let option;
    for (var i = 0; i < allActividades.length; i++) {
        option = document.createElement('option');
        option.value = allActividades[i].actividad.id;
        option.text = allActividades[i].actividad.nombre;
        actividadesSelect.add(option);
        optionClave = document.createElement('option');
        optionClave.value = allActividades[i].actividad.id;
        optionClave.text = allActividades[i].actividad.clave;
        optionClave.actividadId = allActividades[i].actividad.id;
        actividadesClaveSelect.add(optionClave);
    }
}

function validateFecha() {
    const fecha = document.getElementById('fecha');
    const horario = document.getElementById('horarios');
    const horarioOpcion = horario.options[horario.selectedIndex];
    const fechaValor = new Date(`${fecha.value} ${horarioOpcion.text}`);
    const now = new Date();

    if (now > fechaValor && isAdmin()) {
        Swal.fire({
            icon: 'warning',
            title: `Fecha de reserva invalida`
        })
        fecha.value = null;
        fecha.focus()
    }
}
