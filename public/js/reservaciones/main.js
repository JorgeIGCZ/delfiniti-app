function validateBotonGuardar(){
    if(env == 'create'){
        enableBtn('reservar',actvidadesArray.length > 0);
    }else{
        enableBtn('actualizar',actvidadesArray.length > 0);
    }
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

                            setCodigoDescuento(null);
                        }
                    default:
                        Swal.fire({
                            icon: 'error',
                            title: 'Credenciales incorrectas',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        setCodigoDescuento(null);
                        break;
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: `Petición fallida`,
                    showConfirmButton: true
                })
                setCodigoDescuento(null);
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

function setTotalRecibido() {
    let totalRecibido = getPagos();
    totalRecibido = parseFloat(totalRecibido).toFixed(2);

    document.getElementById('total-recibido').setAttribute('value', totalRecibido);
    document.getElementById('total-recibido').value = formatter.format(totalRecibido);
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

function getMXNFromUSD(usd) {
    const dolarPrecio = dolarPrecioCompra();

    return usd * dolarPrecio;
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

function getActividadDisponibilidad(){
    const actividad   = document.getElementById('actividades').value;
    const fecha       = document.getElementById('fecha').value;
    const horario     = document.getElementById('horarios').value;

    axios.get(`/api/disponibilidad/actividadDisponibilidad/${actividad}/${fecha}/${horario}`)
    .then(function (response) {
        displayDisponibilidad(response.data.disponibilidad);
    })
    .catch(function (error) {
        displayDisponibilidad(0);
    });
}
function displayDisponibilidad(disponibilidad){
    const cantidadElement       = document.getElementById('cantidad');
    const disponibilidadElement = document.getElementById('disponibilidad');

    cantidadElement.setAttribute('max',disponibilidad);
    disponibilidadElement.value = disponibilidad;
}
function isActividadDuplicada(nuevaActividad){
    let duplicado = 0;
    actvidadesArray.forEach( function (actividad) {
        if(actividad.claveActividad == nuevaActividad.claveActividad && actividad.horario == nuevaActividad.horario){
            duplicado += 1;
        }
    });
    return duplicado;
}
function validateFecha() {
    const fecha = document.getElementById('fecha');
    const horario = document.getElementById('horarios');
    const horarioOpcion = horario.options[horario.selectedIndex];
    const fechaValor = new Date(`${fecha.value} 23:59:000`);
    const now = new Date();

    if (fechaValor < now && !isAdmin()) {
        Swal.fire({
            icon: 'warning',
            title: `Fecha de reserva invalida`
        })
        fecha.value = null;
        fecha.focus()
    }
}

function isDisponible() {
    const disponibilidad = document.getElementById('disponibilidad').value;
    return (disponibilidad > 0);
}

function addAlojamiento(id,nombre){
    document.getElementById('alojamiento').selectedIndex = "-1";
    const alojamientos = document.getElementById('alojamiento');
    const opcion = document.createElement("option");
    opcion.value = id;
    opcion.text = nombre;
    opcion.setAttribute('selected', 'selected');
    alojamientos.add(opcion, null);
    $('#alojamiento-modal').modal('hide');
}

let reservacionesTable = new DataTable('#reservaciones', {
    searching: false,
    paging: false,
    info: false
} );

window.onload = function() {
    getDisponibilidad()
    document.getElementById('reservacion-form').elements['nombre'].focus();

    document.getElementById('verificacion-modal').addEventListener('blur', (event) =>{
        document.getElementById('password').value="";
    });

    document.getElementById('validar-verificacion').addEventListener('click', (event) =>{
        validarVerificacion();
    });

    document.getElementById('add-actividad').addEventListener('click', (event) =>{
        event.preventDefault();
        validateFecha();
        addActividades();
        validateBotonGuardar();
    });

    document.getElementById('add-alojamiento').addEventListener('click', (event) =>{
        event.preventDefault();
    });

    document.getElementById('add-codigo-descuento').addEventListener('click', (event) =>{
        event.preventDefault();
        //resetDescuentos();

        document.getElementById('validar-verificacion').setAttribute('action','add-codigo-descuento');
    });

    document.getElementById('add-descuento-personalizado').addEventListener('click', (event) =>{
        //resetDescuentos();
        if(document.getElementById('add-descuento-personalizado').checked){
            $('#verificacion-modal').modal('show');
            document.getElementById('add-descuento-personalizado').checked = false;
            document.getElementById('validar-verificacion').setAttribute('action','add-descuento-personalizado');
            document.getElementById('password').focus();
        }
    });
    document.getElementById('descuento-codigo').addEventListener('keyup', (event) =>{
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('efectivo').addEventListener('keyup', (event) =>{
        //if(getResta() < 0){
        //    document.getElementById('efectivo').value = '$0.00';
        //    document.getElementById('efectivo').setAttribute('value',0);
        //}
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('efectivo-usd').addEventListener('keyup', (event) =>{
        //if(getResta() < 0){
        //    document.getElementById('efectivo-usd').value = '$0.00';
        //    document.getElementById('efectivo-usd').setAttribute('value',0);
        //}
        setTimeout(setOperacionResultados(),500);
    });
    document.getElementById('tarjeta').addEventListener('keyup', (event) =>{
        //if(getResta() < 0){
        //    document.getElementById('tarjeta').value = '$0.00';
        //    document.getElementById('tarjeta').setAttribute('value',0);
        //}
        setTimeout(setOperacionResultados(),500);
    });



    document.getElementById('descuento-codigo').addEventListener('keyup', (event) =>{
        if(getResta() < 0){
            document.getElementById('descuento-codigo').value = '0%';
            document.getElementById('descuento-codigo').setAttribute('value',0);
        }
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('descuento-personalizado').addEventListener('keyup', (event) =>{
        if(getResta() < 0){
            document.getElementById('descuento-personalizado').value = '0';
            document.getElementById('descuento-personalizado').setAttribute('value',0);
        }
        if(!isLimite()){
            document.getElementById('descuento-personalizado').value = '0';
            document.getElementById('descuento-personalizado').setAttribute('value',0);
        }
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('cupon').addEventListener('keyup', (event) =>{
        if(getResta() < 0){
            document.getElementById('cupon').value = '0';
            document.getElementById('cupon').setAttribute('value',0);
        }
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('fecha').addEventListener('focusout', (event) =>{
        setTimeout(validateFecha(),500);
    });
};

//jQuery
$('#reservaciones').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault();
    reservacionesTable
        .row( $(this).parents('tr') )
        .remove()
        .draw();

    //remove clave from the array
    const clave   = $(this).parents('tr')[0].firstChild.innerText;
    const horario = $(this).parents('tr')[0].childNodes[2].innerText;
    let updated   = 0;
    actvidadesArray = actvidadesArray.filter(function (reservaciones) {
        let result = (reservaciones.claveActividad !== clave && reservaciones.horario !== horario && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
    validateBotonGuardar();
    setTotal();
} );

 $('#clave-actividad').on('change', function (e) {
    validateFecha();
    changeActividad();
});
$('#actividades').on('change', function (e) {
    validateFecha();
    changeClaveActividad();
});
$('#horarios').on('change', function (e) {
    getActividadDisponibilidad();
    validateFecha();
});
$('#comisionista').on('change', function (e) {
    changeCuponDetalle();
    document.getElementById('reservacion-form').elements['cupon'].focus();
});


$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {

        if($(this).attr("id") == "add-actividad"){
            addActividades();
        }
        if($(this).attr("id") == "password"){
            validarVerificacion();
        }

        var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
        focusable = form.find('input[tabindex],a[tabindex],select[tabindex],button[tabindex],textarea[tabindex]').filter(':visible');
        next = focusable.eq(focusable.index(this)+1);
        if (next.length) {
            next.focus();
        } else {
            form.submit();
        }
        return false;
    }
});

function changeCuponDetalle() {
    const comisionista    = document.getElementById('comisionista');
    const cuponDescuento  = comisionista.options[comisionista.selectedIndex].getAttribute('cuponDescuento');
    const cupon           = document.getElementById('cupon');

    document.getElementById('cupon').setAttribute('value',0);
    document.getElementById('cupon').value = 0;
    document.getElementById('reservacion-form').elements['cupon'].focus();

    (cuponDescuento == '1') ? cupon.removeAttribute('disabled') : removeCupon(cupon);
}

function changeClaveActividad() {
    const actividades = document.getElementById('actividades');
    document.getElementById('clave-actividad').value = actividades.value;
    document.getElementById('clave-actividad').text = actividades.value;

    //$('#clave-actividad').trigger('change.select2');
    getActividadHorario();
    getActividadDisponibilidad();
    getActividadPrecio();
}

function changeActividad() {
    const claveActividad = document.getElementById('clave-actividad');
    document.getElementById('actividades').value = claveActividad.value;
    document.getElementById('actividades').text = claveActividad.value;

    //$('#actividades').trigger('change.select2');
    getActividadHorario();
    getActividadDisponibilidad();
    getActividadPrecio();
}

function getDescuento(descuento){
    const total = document.getElementById('total').getAttribute('value');
    let cantidadDescuento = descuento;
    if(descuento.tipo == "porcentaje"){
        cantidadDescuento = descuento;//(total/100) * descuento.descuento;
    }
    return cantidadDescuento;
}

function setCodigoDescuento(descuento){
    if(descuento.tipo == 'porcentaje'){
        let cantidadDescuento = getDescuento(descuento.descuento);
        document.getElementById('descuento-codigo-container').classList.remove("hidden");
        document.getElementById('descuento-codigo').setAttribute('value',cantidadDescuento);
        document.getElementById('descuento-codigo').value = `${cantidadDescuento}%`;
        document.getElementById('descuento-codigo').setAttribute('tipo','porcentaje');
    }else{
        document.getElementById('descuento-codigo-container').classList.remove("hidden");
        document.getElementById('descuento-codigo').setAttribute('value',descuento.descuento);
        document.getElementById('descuento-codigo').value  = `$${descuento.descuento}`;
        document.getElementById('descuento-codigo').setAttribute('tipo','cantidad');
    }
    setOperacionResultados()

}

function calculatePagoPersonalizado(descuentoPersonalizado,cantidadCodigo,cupon){
    const total    = parseFloat(document.getElementById('total').getAttribute('value'));
    const subTotal = total - ((cantidadCodigo)+parseFloat(cupon));

    return (subTotal/100) * parseFloat(descuentoPersonalizado);
}

function convertPorcentageCantidad(porcentaje){
    const total = document.getElementById('total').getAttribute('value');
    return (total/100) * porcentaje;
}

function enableBtn(btnId,status){
    let reservar = document.getElementById(btnId);
    (status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
}

function validarVerificacion(){
    const action      = document.getElementById('validar-verificacion').getAttribute('action');
    if(formValidity('reservacion-form')){
        if(action === 'add-descuento-personalizado'){
            applyDescuentoPassword('descuento-personalizado');
            validateDescuentoPersonalizado();
        }else if(action === 'add-codigo-descuento'){
            applyDescuentoPassword('descuento-codigo');
            getCodigoDescuento();
        }
    }
}

function getDisponibilidad(){
    axios.get('/api/disponibilidad')
    .then(function (response) {
        allActividades = response.data.disponibilidad;
        displayActividad()
        getActividadHorario()
        getActividadPrecio()
        getActividadDisponibilidad()
        applyVariables()
    })
    .catch(function (error) {
        actividades = [];
    });
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

function formValidity(formId) {
    const form = document.getElementById(formId);
    let response = true;
    if (form.checkValidity()) {
        event.preventDefault();
    } else {
        form.reportValidity();
        response = false;
    }
    return response;
}
