const validarVerificacionElement = document.getElementById('validar-verificacion');
const addCodigoDescuentoElement = document.getElementById('add-codigo-descuento');
const verificacionModalElement = document.getElementById('verificacion-modal');
const addActividadElement = document.getElementById('add-actividad');
const codigoDescuentoElement = document.getElementById('codigo-descuento');
const addDescuentoPersonalizadoElement = document.getElementById('add-descuento-personalizado');
const descuentoCodigoElement = document.getElementById('descuento-codigo');
const efectivoElement = document.getElementById('efectivo');
const efectivoUsdElement = document.getElementById('efectivo-usd');
const tarjetaElement = document.getElementById('tarjeta');
const depositoElement = document.getElementById('deposito');
const descuentoPersonalizadoElement = document.getElementById('descuento-personalizado');
const cuponElement = document.getElementById('cupon');
const numCuponElement = document.getElementById('num-cupon');
const fechaElement = document.getElementById('fecha');

function validateActivarReservacion(){
    Swal.fire({
        title: '¿Activar?',
        text: "La reservación será activada, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, activar!'
    }).then((result) => {
        if (result.isConfirmed) {
            if(validarVerificacionElement !== null){
                validarVerificacionElement.setAttribute('action','activar-reservacion');
            }
            if(verificacionModalElement !== null){
                $('#verificacion-modal').modal('show');
            }
        }
    });
}
function updateEstatusReservacion(accion){
    const title = (accion === 'cancelar') ? 'cancelada' : 'reactivada';
    $('.loader').show();
    axios.post('/reservaciones/updateestatusreservacion', {
        '_token': token(),
        'reservacionId': reservacionId(),
        'accion': accion,
    })
    .then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: `Reservación ${title}`,
                showConfirmButton: false,
                timer: 1500
            })
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: `Petición fallida`,
                showConfirmButton: true
            })
        }
    })
    .catch(function (error) {
        $('.loader').hide();
        Swal.fire({
            icon: 'error',
            title: `Autorización fallida E:${error.message}`,
            showConfirmButton: true
        })
    });
}
async function eliminarActividadReservacion(row,clave){
    $('.loader').show();
    result = await axios.post('/reservaciones/removeActividad', {
        '_token': token(),
        'reservacionId': reservacionId(),
        'actividadClave': clave
    });


    if(result.data.result == "Success"){
        $('.loader').hide();
        removeActividad(row);
        validateBotonGuardar();
        changeActividad();
        setTotal();
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
async function eliminarPagoReservacion(row,pagoId){
    $('.loader').show();
    result = await axios.post('/reservaciones/removeDescuento', {
        '_token': token(),
        'reservacionId': reservacionId(),
        'pagoId': pagoId
    });


    if(result.data.result == "Success"){
        $('.loader').hide();
        Swal.fire({
            icon: 'success',
            title: `Eliminado!`,
            showConfermButton: false,
            timer: 1000
        })
        location.reload();
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
function removeDescuento(row){
    pagosTabla
        .row( $(row).parents('tr') )
        .remove()
        .draw();

    //remove clave from the array
    const id   = $(row).parents('tr')[0].firstChild.innerText;
    let updated   = 0;
    pagosArray = pagosArray.filter(function (pagos) {
        let result = (pagos.id !== id);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
}
function removeActividad(row){
    reservacionesTable
        .row( $(row).parents('tr') )
        .remove()
        .draw();

    //remove clave from the array
    const clave   = $(row).parents('tr')[0].firstChild.innerText;
    const horario = $(row).parents('tr')[0].childNodes[2].innerText;
    let updated   = 0;
    actvidadesArray = actvidadesArray.filter(function (reservaciones) {
        let result = (reservaciones.claveActividad !== clave && reservaciones.horario !== horario && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
}
function addActividad(){
    const actividadDetalle = document.getElementById('actividades').options[document.getElementById('actividades').selectedIndex].text;
    const comisionesEspeciales = document.getElementById('actividades').options[document.getElementById('actividades').selectedIndex].getAttribute('comisiones_especiales');
    const horarioDetalle = document.getElementById('horarios').options[document.getElementById('horarios').selectedIndex].text;
    const claveActividad = document.getElementById('clave-actividad').options[document.getElementById('clave-actividad').selectedIndex].text;
    const actividad = document.getElementById('actividades').value;
    const cantidad = document.getElementById('cantidad').value;
    const precio = document.getElementById('precio').value;
    const horario = document.getElementById('horarios').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;
    const totalPrecio = precio * cantidad;

    reservacionesTable.row.add([
        claveActividad,
        actividadDetalle,
        horarioDetalle,
        cantidad,
        precio,
        totalPrecio.toFixed(2),
        acciones
    ]).draw(false);

    actvidadesArray = [...actvidadesArray, {
        'claveActividad': claveActividad,
        'actividadDetalle':actividadDetalle,
        'comisionesEspeciales':comisionesEspeciales,
        'actividad': actividad,
        'cantidad': cantidad,
        'precio': precio,
        'horario': horario
    }];
    setTotal();
}
function isLimite() {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const descuento = parseFloat(descuentoPersonalizadoElement.getAttribute('value'));
    const limite = parseFloat(descuentoPersonalizadoElement.getAttribute('limite'));
    //return ((total/100)*limite) >= descuento;//cantidad del porcentaje limite del total debe ser mayor o igual a la cantidad de descuento
    return limite >= descuento;
}

function validateBotonGuardar(){
    if(env == 'create'){
        enableBtn('reservar',actvidadesArray.length > 0);
    }else{
        enableBtn('actualizar',actvidadesArray.length > 0);
    }
}

function removeCupon(cupon) {
    cupon.setAttribute('disabled', 'disabled');
    cupon.setAttribute('value', 0);
    cupon.value = 0;
    setTimeout(setOperacionResultados(), 500);
}

function removeNumCupon(cupon) {
    cupon.setAttribute('disabled', 'disabled');
    cupon.value = '';
}

function resetReservaciones() {
    // const reservacion = document.getElementById('reservacion-form');
    // actvidadesArray = [];
    // reservacion.reset();
    // reservacionesTable.clear().draw();
    // document.getElementsByName('cantidad')[0].value = 1;
    // document.getElementsByName('disponibilidad')[0].value = 1;
    // document.getElementsByName('fecha')[0].value = new Date();

    // efectivoElement.setAttribute('value', 0);
    // efectivoUsdElement.setAttribute('value', 0);
    // tarjetaElement.setAttribute('value', 0);
    // depositoElement.setAttribute('value', 0);
    // //cuponElement.setAttribute('value',0);
    // cuponElement.setAttribute('value', 0);
    // descuentoPersonalizadoElement.setAttribute('value', 0);
    // descuentoCodigoElement.setAttribute('value', 0);

    // efectivoElement.value = 0;
    // efectivoUsdElement.value = 0;
    // tarjetaElement.value = 0;
    // depositoElement.value = 0;
    // //cuponElement.value        = 0;
    // cuponElement.value = 0;
    // descuentoPersonalizadoElement.value = 0;
    // descuentoCodigoElement.value = 0;

    // $('select[name="actividad"] option:nth-child(1)').attr('selected', 'selected');
    // $('select[name="actividad"]').trigger('change.select2');

    // $('select[name="alojamiento"] option:nth-child(1)').attr('selected', 'selected');
    // $('select[name="alojamiento"]').trigger('change.select2');

    // $('select[name="origen"] option:nth-child(1)').attr('selected', 'selected');
    // $('select[name="origen"]').trigger('change.select2');

    // $('select[name="comisionista"] option:nth-child(1)').attr('selected', 'selected');
    // $('select[name="comisionista"]').trigger('change.select2');

    // $('select[name="cerrador"] option:nth-child(1)').attr('selected', 'selected');


    // descuentoPersonalizadoElement.setAttribute('password', '');
    // descuentoCodigoElement.setAttribute('password', '');

    // changeClaveActividad();
    // changeActividad();
    // setOperacionResultados();
    // enableBtn('reservar', false);
}

function validateDescuentoPersonalizado() {
    $('.loader').show();
    axios.post('/reservaciones/getDescuentoPersonalizadoValidacion', {
        '_token': token(),
        'email': userEmail()
    })
        .then(function (response) {
            $('.loader').hide();
            if (response.data.result == 'Success') {
                if(verificacionModalElement !== null){
                    $('#verificacion-modal').modal('hide');
                }
                setLimiteDescuentoPersonalizado(response.data.limite);
                if(addDescuentoPersonalizadoElement !== null){
                    addDescuentoPersonalizadoElement.checked = true;
                }
                $('#descuento-personalizado').focus();
            } else {
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: `Petición fallida`,
                    showConfirmButton: true
                })
                if(addDescuentoPersonalizadoElement !== null){
                    addDescuentoPersonalizadoElement.checked = false;
                }
            }
        })
        .catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: `Autorización fallida E:${error.message}`,
                showConfirmButton: true
            })
            if(addDescuentoPersonalizadoElement !== null){
                addDescuentoPersonalizadoElement.checked = false;
            }
        });
}

function setLimiteDescuentoPersonalizado(limite) {
    descuentoPersonalizadoElement.removeAttribute('disabled');
    if (limite !== null) {
        descuentoPersonalizadoElement.setAttribute('limite', limite);
        document.getElementById('descuento-personalizado-container').classList.remove("hidden");
    } else {
        document.getElementById('descuento-personalizado-container').classList.add("hidden");
    }
    setOperacionResultados();
}

async function validateUsuario($password){
    result = await axios.post('/usuarios/validateUsuario', {
        '_token': token(),
        'email': userEmail(),
        'password': $password
    });
    
    if(verificacionModalElement !== null){
        $('#verificacion-modal').modal('hide');
    }
    return (result.data.result == 'Autorized') ? true : false;
}

function getCodigoDescuento() {
    const reservacion = document.getElementById('reservacion-form');
    const nombre = reservacion.elements['nombre'].value;
    const codigoDescuento = reservacion.elements['codigo-descuento'].value;
    if (!formValidity('reservacion-form')) {
        return false;
    }
    $('.loader').show();
    axios.post('/reservaciones/getCodigoDescuento', {
        '_token': token(),
        'codigoDescuento': reservacion.elements['codigo-descuento'].value
    })
        .then(function (response) {
            $('.loader').hide();
            if (response.data.result == 'Success') {
                if (response.data.descuento.descuento !== null) {
                    if(verificacionModalElement !== null){
                        $('#verificacion-modal').modal('hide');
                    }
                    setCodigoDescuento(response.data.descuento);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Codigo incorrecto',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    setCodigoDescuento(null);
                }
            } else {
                $('.loader').hide();
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
    let resta = getResta();
    if(resta < 0){
        resta = getResta('venta');
    }
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

function getResta(tipoUsd = 'compra') {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const pagos = getPagos(tipoUsd);
    const resta = parseFloat(total - pagos);
    return resta;
}

function getMXNFromUSD(usd) {
    const dolarPrecio = dolarPrecioCompra();

    return usd * dolarPrecio;
}

function getMXNFromVentaUSD(usd) {
    const dolarPrecio = dolarPrecioVenta();

    return usd * dolarPrecio;
}

function getUSDFromVentaMXN(usd) {
    const dolarPrecio = dolarPrecioVenta();

    return usd / dolarPrecio;
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
        option.setAttribute('comisiones_especiales',allActividades[i].actividad.comisiones_especiales);
        actividadesSelect.add(option);
        optionClave = document.createElement('option');
        optionClave.value = allActividades[i].actividad.id;
        optionClave.text = allActividades[i].actividad.clave;
        optionClave.actividadId = allActividades[i].actividad.id;
        optionClave.setAttribute('comisiones_especiales',allActividades[i].actividad.comisiones_especiales);
        actividadesClaveSelect.add(optionClave);
    }
}

function getActividadDisponibilidad(){
    const actividad   = document.getElementById('actividades').value;
    const fecha       = fechaElement.value;
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

    cantidadElement.setAttribute('maximo',disponibilidad);
    cantidadElement.setAttribute('minimo',(disponibilidad == 0 ? 0 : 1));
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
function cantidadIsValid() {
    const cantidad = document.getElementById('cantidad');
    
    if(cantidad.value < 1){
        Swal.fire({
            icon: 'warning',
            title: `¡Cantidad invalida!`
        });
        cantidad.value = 1;
        cantidad.focus()
        return false;
    }
    return true;
}
function cantidadActividadesIsValid() {
    if(actvidadesArray.length < 1){
        Swal.fire({
            icon: 'warning',
            title: `¡Es necesario agregar actividades!`
        });
        cantidad.value = 1;
        cantidad.focus()
        return false;
    }
    return true;
}
function cambioValidoIsValid(){
    if(getCambio() > 0){
        Swal.fire({
            icon: 'warning',
            title: `¡Es necesario verificar pagos!`,
            text: 'Cambio debe de ser $0.00'
        });
        return false;
    }
    return true;
}
function validateFecha() {
    const fecha = fechaElement;
    const horario = document.getElementById('horarios');
    const horarioOpcion = horario.options[horario.selectedIndex];
    const fechaValor = new Date(`${fecha.value} 23:59:000`);
    const now = new Date();
    
    if(fecha.value == ''){
        Swal.fire({
            icon: 'warning',
            title: `¡Fecha de reservación invalida!`
        });
        /*
        const year = (new Date()).toLocaleDateString('es-MX',{year: 'numeric'});
        const month = (new Date()).toLocaleDateString('es-MX',{month: '2-digit'});
        const day = (new Date()).toLocaleDateString('es-MX',{day: '2-digit'});
        fecha.value = `${year}-${month}-${day}`;
        fecha.focus()
        return false;
        */
    }
    /*
    if (fechaValor < now && !isAdmin()) {
        Swal.fire({
            icon: 'warning',
            title: `¡Fecha de reservación invalida!`
        })
        fecha.value = null;
        fecha.focus()
        return false;
    }
    */
    return true;
}

function isDisponible() {
    const disponibilidad = parseInt(document.getElementById('disponibilidad').value);
    const cantidad       = parseInt(document.getElementById('cantidad').value);
    if((disponibilidad > 0)&&(cantidad <= disponibilidad)){
        return true;
    }
    Swal.fire({
        title: '¿Agregar?',
        text: "La disponibilidad es menor a la cantidad solicitada, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, agregar!'
    }).then((result) => {
        if (result.isConfirmed) {
            if(validarVerificacionElement !== null){
                validarVerificacionElement.setAttribute('action','add-actividad-disponibilidad');
            }
            if(verificacionModalElement !== null){
                $('#verificacion-modal').modal('show');
            }
        }
    })
    return false;
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
    info: false,
    ordering: false
} );

window.onload = function() {
    getDisponibilidad()
    document.getElementById('reservacion-form').elements['nombre'].focus();

    //$('.to-uppercase').keyup(function() {
    //    this.value = this.value.toUpperCase();
    //});s

    if(verificacionModalElement !== null){
        verificacionModalElement.addEventListener('blur', (event) =>{
            document.getElementById('password').value="";
        });
    }

    if(validarVerificacionElement !== null){
        validarVerificacionElement.addEventListener('click', (event) =>{
            validarVerificacion();
        });
    }

    if(addActividadElement !== null){
        addActividadElement.addEventListener('click', (event) =>{
            event.preventDefault();
            if(cantidadIsValid()){
                validateFecha();
                addActividades();
                validateBotonGuardar();
            }
        });
    }

    if(codigoDescuentoElement !== null){
        $('#codigo-descuento').on('change', function (e) {
            const codigoDescuentoVal = codigoDescuentoElement.value;
            if(codigoDescuentoVal == 0){
                document.getElementById('descuento-codigo-container').classList.add("hidden");
                descuentoCodigoElement.setAttribute('value',0);
                descuentoCodigoElement.value = 0;
                descuentoCodigoElement.setAttribute('tipo','cantidad');
            }
            setOperacionResultados()
        });
    }

    if(addCodigoDescuentoElement !== null){
        addCodigoDescuentoElement.addEventListener('click', (event) =>{
            event.preventDefault();
            //resetDescuentos();

            if(validarVerificacionElement !== null){
                validarVerificacionElement.setAttribute('action','add-codigo-descuento');
            }
        });
    }

    if(addDescuentoPersonalizadoElement !== null){
        addDescuentoPersonalizadoElement.addEventListener('click', (event) =>{
            //resetDescuentos();
            if(addDescuentoPersonalizadoElement.checked){
                if(verificacionModalElement !== null){
                    $('#verificacion-modal').modal('show');
                }
                addDescuentoPersonalizadoElement.checked = false;
                if(validarVerificacionElement !== null){
                    validarVerificacionElement.setAttribute('action','add-descuento-personalizado');
                }
                document.getElementById('password').focus();
            }else{
                descuentoPersonalizadoElement.setAttribute('disabled','disabled');
                descuentoPersonalizadoElement.setAttribute('limite','0');
                document.getElementById('descuento-personalizado-container').classList.add("hidden");
                descuentoPersonalizadoElement.value = '0';
                descuentoPersonalizadoElement.setAttribute('value',0);
                setOperacionResultados();
            }
        });
    }
    
    if(descuentoCodigoElement !== null){
        descuentoCodigoElement.addEventListener('keyup', (event) =>{
            setTimeout(setOperacionResultados(),500);
        });
    }


    if(efectivoElement !== null){
        efectivoElement.addEventListener('keyup', (event) =>{
            //if(getResta() < 0){
            //    efectivoElement.value = '$0.00';
            //    efectivoElement.setAttribute('value',0);
            //}
            
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(efectivoUsdElement !== null){
        efectivoUsdElement.addEventListener('keyup', (event) =>{
            //if(getResta() < 0){
            //    efectivoUsdElement.value = '$0.00';
            //    efectivoUsdElement.setAttribute('value',0);
            //}
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(tarjetaElement !== null){
        tarjetaElement.addEventListener('keyup', (event) =>{
            //if(getResta() < 0){
            //    tarjetaElement.value = '$0.00';
            //    tarjetaElement.setAttribute('value',0);
            //}
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(depositoElement !== null){
        depositoElement.addEventListener('keyup', (event) =>{
            //if(getResta() < 0){
            //    depositoElement.value = '$0.00';
            //    depositoElement.setAttribute('value',0);
            //}
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(efectivoElement !== null){
        efectivoElement.addEventListener('focusout', (event) =>{
            setTimeout(applyValorSinCambio(event.target),300);
            setTimeout(setOperacionResultados(),600);
        });
    }

    if(efectivoUsdElement !== null){
        efectivoUsdElement.addEventListener('focusout', (event) =>{
            // setTimeout(applyValorSinCambio(event.target,true),300);
            setTimeout(setOperacionResultados(),600);
        });
    }

    if(tarjetaElement !== null){
        tarjetaElement.addEventListener('focusout', (event) =>{
            setTimeout(applyValorSinCambio(event.target),300);
            setTimeout(setOperacionResultados(),600);
        });
    }

    if(depositoElement !== null){
        depositoElement.addEventListener('focusout', (event) =>{
            setTimeout(applyValorSinCambio(event.target),300);
            setTimeout(setOperacionResultados(),600);
        });
    }

    function applyValorSinCambio(elemento,isUsd = false){
        if(getResta() < 0){
            const valor = (isUsd ? getMXNFromVentaUSD(parseFloat(elemento.getAttribute('value'))) : parseFloat(getValor(elemento)));
            const cambio   = parseFloat(getCambio());
            const subTotal = (isUsd ? parseFloat(getUSDFromVentaMXN(valor+cambio)) : parseFloat(valor+cambio)); 
 
            if(subTotal > 0){
                setValor(elemento,subTotal);
            }
        }
    }

    function setValor(elemento,valor){
        elemento.value = formatter.format(valor);
        elemento.setAttribute('value',valor);
    }

    function getValor(elemento){
        return elemento.getAttribute('value');
    }

    function getCambio(){
        return document.getElementById('cambio').getAttribute('value');
    }



    if(descuentoCodigoElement !== null){
        descuentoCodigoElement.addEventListener('keyup', (event) =>{
            if(getResta() < 0){
                descuentoCodigoElement.value = '0%';
                descuentoCodigoElement.setAttribute('value',0);
            }
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(descuentoPersonalizadoElement !== null){
        descuentoPersonalizadoElement.addEventListener('keyup', (event) =>{
            if(getResta() < 0){
                descuentoPersonalizadoElement.value = '0';
                descuentoPersonalizadoElement.setAttribute('value',0);
            }
            if(!isLimite()){
                descuentoPersonalizadoElement.value = '0';
                descuentoPersonalizadoElement.setAttribute('value',0);
            }
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(cuponElement !== null){
        cuponElement.addEventListener('keyup', (event) =>{
            if(getResta() < 0){
                cuponElement.value = '0';
                cuponElement.setAttribute('value',0);
            }
            setTimeout(setOperacionResultados(),500);
        });
    }

    if(fechaElement !== null){
        fechaElement.addEventListener('focusout', (event) =>{
            getActividadDisponibilidad();
            setTimeout(validateFecha(),500);
        });
    }
};

//jQuery
$('#reservaciones').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        Swal.fire({
            title: '¿Eliminiar?',
            text: "La actividad será eliminada de la reservación, ¿desea proceder?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarActividadReservacion(this,$(this).parents('tr')[0].firstChild.innerText)
            }
        });
    }else{
        removeActividad(this);
        validateBotonGuardar();
        setTotal();
    }
} );

//jQuery
$('#pagos').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        Swal.fire({
            title: '¿Eliminiar?',
            text: "El pago/descuento será eliminado de la reservación, ¿desea proceder?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarPagoReservacion(this,$(this).parents('tr')[0].firstChild.innerText)
            }
        });
    }
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
            validateFecha();
            addActividades();
            validateBotonGuardar();
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
    const cupon           = cuponElement;

    cuponElement.setAttribute('value',0);
    cuponElement.value = 0;
    document.getElementById('reservacion-form').elements['cupon'].focus();

    (cuponDescuento == '1') ? cupon.removeAttribute('disabled') : removeCupon(cupon);
    (cuponDescuento == '1') ? numCuponElement.removeAttribute('disabled') : removeNumCupon(numCuponElement);

    setOperacionResultados();
}

function changeClaveActividad() {
    const actividades = document.getElementById('actividades');
    document.getElementById('clave-actividad').value = actividades.value;
    document.getElementById('clave-actividad').text = actividades.value;

    //$('#clave-actividad').trigger('change.select2');
    getActividadHorario();
    getActividadDisponibilidad();
    getActividadPrecio();
    removeIncompatibleActividades();
}

function changeActividad() {
    const claveActividad = document.getElementById('clave-actividad');
    document.getElementById('actividades').value = claveActividad.value;
    document.getElementById('actividades').text = claveActividad.value;

    //$('#actividades').trigger('change.select2');
    getActividadHorario();
    getActividadDisponibilidad();
    getActividadPrecio();
    removeIncompatibleActividades();
}

function removeIncompatibleActividades(){
    const claveActividad = document.getElementById('clave-actividad');
    const actividades = document.getElementById('actividades');
    const actividadSeleccionada = claveActividad.options[claveActividad.selectedIndex].value;
    const isVisita = claveActividad.options[claveActividad.selectedIndex].getAttribute('comisiones_especiales');    

    for (var i = 0; i < claveActividad.length; i++) {
        if (claveActividad.options[i].getAttribute('comisiones_especiales') == 1) {
            if(isVisita == 1 && actividadSeleccionada == claveActividad.options[i].value){
                $(claveActividad.options[i]).removeAttr('disabled').show();
            }else{
                $(claveActividad.options[i]).attr('disabled', 'disabled').hide();
            }
        } else {
            if(isVisita == 1){
                $(claveActividad.options[i]).attr('disabled', 'disabled').hide();
            }else{
                $(claveActividad.options[i]).removeAttr('disabled').show();
            }
        }
    }

    for (var i = 0; i < actividades.length; i++) {
        if (actividades.options[i].getAttribute('comisiones_especiales') == 1) {
            if(isVisita == 1 && actividadSeleccionada == actividades.options[i].value){
                $(actividades.options[i]).removeAttr('disabled').show();
            }else{
                $(actividades.options[i]).attr('disabled', 'disabled').hide();
            }
        } else {
            if(isVisita == 1){
                $(actividades.options[i]).attr('disabled', 'disabled').hide();
            }else{
                $(actividades.options[i]).removeAttr('disabled').show();
            }
        }
    }
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
        descuentoCodigoElement.setAttribute('value',cantidadDescuento);
        descuentoCodigoElement.value = `${cantidadDescuento}%`;
        descuentoCodigoElement.setAttribute('tipo','porcentaje');
    }else{
        document.getElementById('descuento-codigo-container').classList.remove("hidden");
        descuentoCodigoElement.setAttribute('value',descuento.descuento);
        descuentoCodigoElement.value  = `$${descuento.descuento}`;
        descuentoCodigoElement.setAttribute('tipo','cantidad');
    }
    setOperacionResultados()

}

function calculatePagoPersonalizado(descuentoPersonalizado,cantidadCodigo,cupon){
    const total    = parseFloat(document.getElementById('total').getAttribute('value'));
    const subTotal = total - ((cantidadCodigo));//+parseFloat(cupon));

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

async function validarVerificacion(){
    let action = "";
    if(validarVerificacionElement !== null){
        action = validarVerificacionElement.getAttribute('action');
    }
    if(formValidity('reservacion-form')){
        if(await validateUsuario(document.getElementById('password').value)){
            if(action === 'add-descuento-personalizado'){
                validateDescuentoPersonalizado();
            }else if(action === 'add-codigo-descuento'){
                getCodigoDescuento();
            }else if(action === 'add-actividad-disponibilidad'){
                addActividad();
                validateBotonGuardar();
            }else if(action === 'cancelar-reservacion'){
                updateEstatusReservacion('cancelar');
            }else if(action === 'activar-reservacion'){
                updateEstatusReservacion('activar');
            }
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Contraseña incorrecta!',
                showConfirmButton: false,
                timer: 1500
            })
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
