function validateActivarVenta(){
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
            document.getElementById('validar-verificacion').setAttribute('action','activar-venta');
            $('#verificacion-modal').modal('show');
        }
    });
}

function updateEstatusVenta(accion){
    const title = (accion === 'cancelar') ? 'cancelada' : 'reactivada';
    $('.loader').show();
    axios.post('/ventas/updateestatus', {
        '_token': token(),
        'ventaId': ventaId(),
        'accion': accion,
    })
    .then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: `Venta ${title}`,
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
async function eliminarProductoVenta(row,clave){
    $('.loader').show();
    result = await axios.post('/ventas/removeProducto', {
        '_token': token(),
        'ventaId': ventaId(),
        'productoClave': clave
    });


    if(result.data.result == "Success"){
        $('.loader').hide();
        removeProducto(row);
        changeProducto();
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
function removeProducto(row){
    ventasTable
        .row( $(row).parents('tr') )
        .remove()
        .draw();

    //remove clave from the array
    const clave   = $(row).parents('tr')[0].firstChild.innerText;
    const horario = $(row).parents('tr')[0].childNodes[2].innerText;
    let updated   = 0;
    actvidadesArray = actvidadesArray.filter(function (ventas) {
        let result = (ventas.claveProducto !== clave && ventas.horario !== horario && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
}
function addProducto(){
    let productoDetalle = document.getElementById('productos');
    productoDetalle = productoDetalle.options[productoDetalle.selectedIndex].text;
    let horarioDetalle = document.getElementById('horarios');
    horarioDetalle = horarioDetalle.options[horarioDetalle.selectedIndex].text;
    let claveProducto = document.getElementById('clave-producto');
    claveProducto = claveProducto.options[claveProducto.selectedIndex].text;
    const producto = document.getElementById('productos').value;
    const cantidad = document.getElementById('cantidad').value;
    const precio = document.getElementById('precio').value;
    const horario = document.getElementById('horarios').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;

    ventasTable.row.add([
        claveProducto,
        productoDetalle,
        horarioDetalle,
        cantidad,
        precio,
        precio * cantidad,
        acciones
    ])
        .draw(false);
    actvidadesArray = [...actvidadesArray, {
        'claveProducto': claveProducto,
        'productoDetalle':productoDetalle,
        'producto': producto,
        'cantidad': cantidad,
        'precio': precio,
        'horario': horario
    }];
    setTotal();
}

function resetVentas() {
    location.reload();
}

async function validateUsuario($password){
    result = await axios.post('/usuarios/validateUsuario', {
        '_token': token(),
        'email': userEmail(),
        'password': $password
    });
    $('#verificacion-modal').modal('hide');
    return (result.data.result == 'Autorized') ? true : false;
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

function displayProducto() {
    let productosClaveSelect = document.getElementById('clave-producto');
    let productosSelect = document.getElementById('productos');
    let optionNombre;
    let optionClave;
    let option;
    for (var i = 0; i < allProductos.length; i++) {
        option = document.createElement('option');
        option.value = allProductos[i].producto.id;
        option.text = allProductos[i].producto.nombre;
        productosSelect.add(option);
        optionClave = document.createElement('option');
        optionClave.value = allProductos[i].producto.id;
        optionClave.text = allProductos[i].producto.clave;
        optionClave.productoId = allProductos[i].producto.id;
        productosClaveSelect.add(optionClave);
    }
}

function getProductoDisponibilidad(){
    const producto   = document.getElementById('productos').value;
    const fecha       = document.getElementById('fecha').value;
    const horario     = document.getElementById('horarios').value;

    axios.get(`/api/disponibilidad/productoDisponibilidad/${producto}/${fecha}/${horario}`)
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
function isProductoDuplicada(nuevaProducto){
    let duplicado = 0;
    actvidadesArray.forEach( function (producto) {
        if(producto.claveProducto == nuevaProducto.claveProducto && producto.horario == nuevaProducto.horario){
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
function cantidadProductosIsValid() {
    if(actvidadesArray.length < 1){
        Swal.fire({
            icon: 'warning',
            title: `¡Es necesario agregar productos!`
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
    const fecha = document.getElementById('fecha');
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
            document.getElementById('validar-verificacion').setAttribute('action','add-producto-disponibilidad');
            $('#verificacion-modal').modal('show');
        }
    })
    return false;
}

let ventasTable = new DataTable('#ventas', {
    searching: false,
    paging: false,
    info: false
} );

window.onload = function() {
    getDisponibilidad()
    document.getElementById('venta-form').elements['nombre'].focus();

    //$('.to-uppercase').keyup(function() {
    //    this.value = this.value.toUpperCase();
    //});s

    document.getElementById('verificacion-modal').addEventListener('blur', (event) =>{
        document.getElementById('password').value="";
    });

    document.getElementById('validar-verificacion').addEventListener('click', (event) =>{
        validarVerificacion();
    });

    document.getElementById('add-producto').addEventListener('click', (event) =>{
        event.preventDefault();
        if(cantidadIsValid()){
            validateFecha();
            addProductos();
        }
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
    document.getElementById('deposito').addEventListener('keyup', (event) =>{
        //if(getResta() < 0){
        //    document.getElementById('deposito').value = '$0.00';
        //    document.getElementById('deposito').setAttribute('value',0);
        //}
        setTimeout(setOperacionResultados(),500);
    });

    document.getElementById('efectivo').addEventListener('focusout', (event) =>{
        setTimeout(applyValorSinCambio(event.target),300);
        setTimeout(setOperacionResultados(),600);
    });

    document.getElementById('efectivo-usd').addEventListener('focusout', (event) =>{
        setTimeout(applyValorSinCambio(event.target,true),300);
        setTimeout(setOperacionResultados(),600);
    });
    document.getElementById('tarjeta').addEventListener('focusout', (event) =>{
        setTimeout(applyValorSinCambio(event.target),300);
        setTimeout(setOperacionResultados(),600);
    });
    document.getElementById('deposito').addEventListener('focusout', (event) =>{
        setTimeout(applyValorSinCambio(event.target),300);
        setTimeout(setOperacionResultados(),600);
    });

    function applyValorSinCambio(elemento,isUsd = false){
        if(getResta() < 0){
            const valor = (isUsd ? getMXNFromVentaUSD(parseFloat(elemento.getAttribute('value'))) : parseFloat(getValor(elemento)));
            const cambio   = parseFloat(getCambio());
            const subTotal = parseFloat(valor+cambio);
 
            setValor(event.target,subTotal);
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

    document.getElementById('fecha').addEventListener('focusout', (event) =>{
        getProductoDisponibilidad();
        setTimeout(validateFecha(),500);
    });
};

//jQuery
$('#ventas').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        Swal.fire({
            title: '¿Eliminiar?',
            text: "La producto será eliminada de la reservación, ¿desea proceder?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarProductoVenta(this,$(this).parents('tr')[0].firstChild.innerText)
            }
        });
    }else{
        removeProducto(this);
        setTotal();
    }
} );

//jQuery
$('#pagos').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault();
    if(env == 'edit'){
        Swal.fire({
            title: '¿Eliminiar?',
            text: "El descuento será eliminado de la reservación, ¿desea proceder?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarDescuentoVenta(this,$(this).parents('tr')[0].firstChild.innerText)
            }
        });
    }
} );

 $('#clave-producto').on('change', function (e) {
    validateFecha();
    changeProducto();
});
$('#productos').on('change', function (e) {
    validateFecha();
    changeClaveProducto();
});
$('#horarios').on('change', function (e) {
    getProductoDisponibilidad();
    validateFecha();
});
$('#comisionista').on('change', function (e) {
    changeCuponDetalle();
    document.getElementById('venta-form').elements['cupon'].focus();
});


$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {

        if($(this).attr("id") == "clave" || $(this).attr("id") == "productos"){
            validateFecha();
            addProductos();
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

function changeClaveProducto() {
    const productos = document.getElementById('productos');
    document.getElementById('clave-producto').value = productos.value;
    document.getElementById('clave-producto').text = productos.value;

    //$('#clave-producto').trigger('change.select2');
    getProductoDisponibilidad();
    getProductoPrecio();
}

function changeProducto() {
    const claveProducto = document.getElementById('clave-producto');
    document.getElementById('productos').value = claveProducto.value;
    document.getElementById('productos').text = claveProducto.value;

    //$('#productos').trigger('change.select2');
    getProductoDisponibilidad();
    getProductoPrecio();
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
    const action      = document.getElementById('validar-verificacion').getAttribute('action');
    if(formValidity('venta-form')){
        if(await validateUsuario(document.getElementById('password').value)){
            if(action === 'add-descuento-personalizado'){
                validateDescuentoPersonalizado();
            }else if(action === 'add-codigo-descuento'){
                getCodigoDescuento();
            }else if(action === 'add-producto-disponibilidad'){
                addProducto();
            }else if(action === 'cancelar-venta'){
                updateEstatusVenta('cancelar');
            }else if(action === 'activar-venta'){
                updateEstatusVenta('activar');
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
        allProductos = response.data.disponibilidad;
        displayProducto()
        getProductoPrecio()
        getProductoDisponibilidad()
        applyVariables()
    })
    .catch(function (error) {
        productos = [];
    });
}

function getProductoPrecio() {
    const producto = document.getElementById('productos').value;
    let precio = document.getElementById('precio');
    for (var i = 0; i < allProductos.length; i++) {
        if (producto == allProductos[i].producto.id) {
            precio.value = allProductos[i].producto.precio;
        }
    }
}