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
        addActividades();
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
        if(getResta() < 0){
            document.getElementById('tarjeta').value = '$0.00';
            document.getElementById('tarjeta').setAttribute('value',0);
        }
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
    if(env == 'create'){
        enableBtn('reservar',actvidadesArray.length > 0);
    }else{
        enableBtn('actualizar',actvidadesArray.length > 0);
    }
    setTotal();
} );

 $('#clave-actividad').on('change', function (e) {
    changeActividad();
});
$('#actividades').on('change', function (e) {
    changeClaveActividad();
});
$('#horarios').on('change', function (e) {
    getActividadDisponibilidad();
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
    const total    = document.getElementById('total').getAttribute('value');
    const subTotal = total - (cantidadCodigo+cupon);

    return (subTotal/100) * descuentoPersonalizado;
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
        applyVariables()
    })
    .catch(function (error) {
        actividades = [];
    });
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
