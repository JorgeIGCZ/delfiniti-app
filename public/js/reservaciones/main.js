
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
    
};

//jQuery
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

function getDolarPrecioCompra(){
    return dolarPrecioCompra();
}

function enablePagar(status){
    let reservar = document.getElementById('reservar');
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
