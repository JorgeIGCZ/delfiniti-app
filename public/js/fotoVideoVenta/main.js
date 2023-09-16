function updateEstatusVenta(accion){
    const title = (accion === 'cancelar') ? 'cancelada' : 'reactivada';
    $('.loader').show();
    axios.post('/fotovideoventas/updateestatus', {
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
    result = await axios.post('/fotovideoventas/removeProducto', {
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
    productosArray = productosArray.filter(function (ventas) {
        let result = (ventas.claveProducto !== clave && ventas.horario !== horario && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
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

function getUSDFromVentaMXN(usd) {
    const dolarPrecio = dolarPrecioVenta();

    return usd / dolarPrecio;
}

function displayProducto() {
    let productosClaveSelect = document.getElementById('clave');
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

// function getProductoDisponibilidad(){
//     const producto   = document.getElementById('productos').value;
//     const horario     = document.getElementById('horarios').value;

//     axios.get(`/api/disponibilidad/productoDisponibilidad/${producto}/${fecha}/${horario}`)
//     .then(function (response) {
//         displayDisponibilidad(response.data.disponibilidad);
//     })
//     .catch(function (error) {
//         displayDisponibilidad(0);
//     });
// }

function displayDisponibilidad(disponibilidad){
    const cantidadElement       = document.getElementById('cantidad');
    const disponibilidadElement = document.getElementById('disponibilidad');

    cantidadElement.setAttribute('maximo',disponibilidad);
    cantidadElement.setAttribute('minimo',(disponibilidad == 0 ? 0 : 1));
    disponibilidadElement.value = disponibilidad;
}

function isProductoDuplicado(nuevoProducto){
    let duplicado = 0;
    productosArray.forEach( function (producto) {
        if(producto.claveProducto == nuevoProducto.claveProducto){
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
    if(productosArray.length < 1){
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

function fotografoIsValid() {
    const fotografo = document.getElementById('fotografo');
    if(fotografo.value === '0'){
        Swal.fire({
            icon: 'warning',
            title: `¡Es necesario agregar fotografo!`
        });
        fotografo.focus()
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
    const fechaValor = new Date(`${fecha.value} 23:59:000`);
    const now = new Date();
    
    if(fecha.value == ''){
        Swal.fire({
            icon: 'warning',
            title: `¡Fecha de venta invalida!`
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
    // Swal.fire({
    //     title: '¿Agregar?',
    //     text: "La disponibilidad es menor a la cantidad solicitada, ¿desea proceder?",
    //     icon: 'warning',
    //     showCancelButton: true,
    //     confirmButtonColor: '#17a2b8',
    //     cancelButtonColor: '#d33',
    //     confirmButtonText: 'Sí, agregar!'
    // }).then((result) => {
    //     if (result.isConfirmed) {
    //         document.getElementById('validar-verificacion').setAttribute('action','add-producto-disponibilidad');
    //     }
    // })
    return false;
}

let ventasTable = new DataTable('#ventas', {
    searching: false,
    paging: false,
    info: false
} );

window.onload = function() {
    // getDisponibilidad()

    const claveElement = document.getElementById('venta-form').elements['clave'];
    const addProductoElement = document.getElementById('add-producto');
    const fechaElement = document.getElementById('fecha');
    const descuentoPersonalizadoElement = document.getElementById('descuento-personalizado');
    const addDescuentoPersonalizadoElement = document.getElementById('add-descuento-personalizado');
    const validarVerificacionElement = document.getElementById('validar-verificacion');

    // if(claveElement !== undefined){
        // claveElement.focus();
    // }

    document.getElementById('venta-form').elements['nombre'].focus();

    if(addProductoElement !== null){
        addProductoElement.addEventListener('click', (event) =>{
            event.preventDefault();
            if(productoIsValid() && fotografoIsValid()){
                validateFecha();
                addProductos();
                document.getElementById('venta-form').elements['codigo'].focus();
            }
        });
    }
    if(fechaElement !== null){
        fechaElement.addEventListener('focusout', (event) =>{
            // getProductoDisponibilidad();
            setTimeout(validateFecha(),500);
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
    if(addDescuentoPersonalizadoElement !== null){
        addDescuentoPersonalizadoElement.addEventListener('click', (event) =>{
            //resetDescuentos();
            if(addDescuentoPersonalizadoElement.checked){
                $('#verificacion-modal').modal('show');
                addDescuentoPersonalizadoElement.checked = false;
                document.getElementById('validar-verificacion').setAttribute('action','add-descuento-personalizado');
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
    if(validarVerificacionElement !== null){
        validarVerificacionElement.addEventListener('click', (event) =>{
            validarVerificacion();
        });
    }

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
            // debugger;
            const valor = (isUsd ? getMXNFromVentaUSD(parseFloat(elemento.getAttribute('value'))) : parseFloat(getValor(elemento)));
            const cambio   = parseFloat(getCambio());
            const subTotal = (isUsd ? parseFloat(getUSDFromVentaMXN(valor+cambio)) : parseFloat(valor+cambio)); 
 
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



async function eliminarPagoReservacion(row,pagoId){
    $('.loader').show();
    result = await axios.post('/ventas/removePago', {
        '_token': token(),
        'ventaId': ventaId(),
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

 $('#clave').on('change', function (e) {
    // debugger;
    validateFecha();
    changeProducto();
});

$('#productos').on('change', function (e) {
    validateFecha();
    changeClaveProducto();
});

// $('#comisionista').on('change', function (e) {
//     changeCuponDetalle();
//     document.getElementById('venta-form').elements['cupon'].focus();
// });

$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {
        // debugger;
        e.preventDefault();
        const element = $(this).attr("id");
        if(element == "clave" || element == "productos" || element == "cantidad" || "add-producto"){
            if(element == "clave"){
                changeProducto();
            }else{
                changeClaveProducto();
            }
            if(productoIsValid()){
                validateFecha();
                addProductos();
                document.getElementById('venta-form').elements['clave'].focus();
                return true;
            }
        }

        var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
        focusable = form.find('input[tabindex],a[tabindex],select[tabindex],button[tabindex],textarea[tabindex]').filter(':visible');
        next = focusable.eq(focusable.index(this)+1);
        if (next.length) {
            next.focus();
        } else {
            // form.submit();
        }
        return false;
    }
});


async function validarVerificacion(){
    const action      = document.getElementById('validar-verificacion').getAttribute('action');
    if(formValidity('venta-form')){
        if(await validateUsuario(document.getElementById('password').value)){
            if(action === 'add-descuento-personalizado'){
                validateDescuentoPersonalizado();
            }else if(action === 'cancelar-venta'){
                updateEstatusReservacion('cancelar');
            }else if(action === 'activar-venta'){
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

function updateEstatusReservacion(accion){
    const title = (accion === 'cancelar') ? 'cancelada' : 'reactivada';
    $('.loader').show();
    axios.post('/fotovideoventas/updateestatusventa', {
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

function validateDescuentoPersonalizado() {
    $('.loader').show();
    axios.post('/ventas/getDescuentoPersonalizadoValidacion', {
        '_token': token(),
        'email': userEmail()
    })
        .then(function (response) {
            $('.loader').hide();
            if (response.data.result == 'Success') {
                $('#verificacion-modal').modal('hide');
                setLimiteDescuentoPersonalizado(response.data.limite);
                document.getElementById('add-descuento-personalizado').checked = true;
                $('#descuento-personalizado').focus();
            } else {
                $('.loader').hide();
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

function isLimite() {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const descuento = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const limite = parseFloat(document.getElementById('descuento-personalizado').getAttribute('limite'));
    //return ((total/100)*limite) >= descuento;//cantidad del porcentaje limite del total debe ser mayor o igual a la cantidad de descuento
    return limite >= descuento;
}

function productoIsValid() {
    const cantidad = document.getElementById('cantidad');
    const clave = document.getElementById('clave');
    
    if(cantidad.value < 1){
        Swal.fire({
            icon: 'warning',
            title: `¡Cantidad invalida!`
        });
        cantidad.value = 1;
        cantidad.focus()
        return false;
    }

    if(clave.value == "" || clave.value == "0"){
        Swal.fire({
            icon: 'warning',
            title: `¡Producto invalido!`
        });
        clave.focus()
        return false;
    }
    return true;
}

function addProductos() {
    const claveProducto = document.getElementById('clave').value;

    if (isProductoDuplicado({'claveProducto': claveProducto})) {
        Swal.fire({
            icon: 'warning',
            title: 'El prodducto ya se encuenta agregado.',
            showConfirmButton: false,
            timer: 900
        });

        resetProductoMeta();
        return false;
    }
    // if(!isDisponible()){
    //     return false;
    // }
    addProducto();
    resetProductoMeta();
    // enableBtn('reservar', productosArray.length > 0);
}

function calculatePagoPersonalizado(descuentoPersonalizado){
    const total    = parseFloat(document.getElementById('total').getAttribute('value'));
    const subTotal = total;// - ((cantidadClave));//+parseFloat(cupon));

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