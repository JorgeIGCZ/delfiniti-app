const actualizarEstatusReservacion = document.getElementById('actualizar-estatus-pedido');
const agregarProducto = document.getElementById('add-producto');
const autorizarPedido = document.getElementById('autorizar-pedido');

let impuestosProductosGeneralesArray = [];
let impuestosTotales = [];
let productosTable = new DataTable('#productosTable', {
    searching: false,
    paging: false,
    info: false
} );
 
window.onload = function() {
    // getDisponibilidad()
    const pedidoForm = document.getElementById('pedido-form').elements['proveedor'];
 
    if(pedidoForm !== undefined){
        pedidoForm.focus();
    } 

    document.getElementById('validar-verificacion').addEventListener('click', (event) =>{
        validarVerificacion();
    });
};

//jQuery
$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {

        if($(this).attr("id") == "cantidad"){
            if(productoIsValid() && validateProductos()){
                addProducto();
                resetProductoMeta();
                validateBotonGuardar();
                $('#codigo').focus();
                return false;
            }
        }

        if($(this).attr("id") == "codigo"){
            $('#cantidad').focus();
            return false;
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

$('#productosTable').on( 'click', '.eliminar-celda', function (event) {
    event.preventDefault(); 
    // if(env == 'edit'){
    //     Swal.fire({
    //         title: '¿Eliminiar?',
    //         text: "El producto será eliminada de la reservación, ¿desea proceder?",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#17a2b8',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Sí, eliminar!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             eliminarProductoPedido(this,$(this).parents('tr')[0].firstChild.innerText)
    //         }
    //     });
    // }else{
        removeProducto(this);
        displayImpuestos();
        calculateAndDisplaySubTotalTotal();
    // }
} );

$('#codigo').on('change', function (e) {
    changeProducto();
    displayImpuestos();
});

$('#productos').on('change', function (e) {
    changeCodigoProducto();
    displayImpuestos();
});

$('#proveedor').on('change', function (e) {
    // console.log(e.target.value);
    getProductos();
    resetProductoTabla();
    resetEstadoProducto();
    resetProductoMeta();
    calculateAndDisplaySubTotalTotal();
});

if(actualizarEstatusReservacion !== null){
    actualizarEstatusReservacion.addEventListener('click', (event) =>{
        event.preventDefault();
        if(document.getElementById('actualizar-estatus-pedido').getAttribute('accion') == 'cancelar'){
            validateCancelarPedido();
            return true;
        }
        validateActivarPedido();
    });
}

if(autorizarPedido !== null){
    autorizarPedido.addEventListener('click', (event) =>{
        event.preventDefault();
        validacionAutorizacion(autorizarPedido.getAttribute('id-pedido'));
    });
}

if(agregarProducto !== null){
    agregarProducto.addEventListener('click', (event) =>{
        event.preventDefault();
        if(productoIsValid() && validateProductos()){
            addProducto();
            resetProductoMeta();
            validateBotonGuardar();
        }
    });
}

function validacionAutorizacion(id){
    Swal.fire({
        title: `¿Desea autorizar el pedido con ID ${id}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '¡Si, autorizar!'
    }).then((result) => {
        if (result.isConfirmed) {
            autorizar(id,);
        }else{
            return false;
        }
    }) 
}

function autorizar(id){
    $('.loader').show();
    axios.post(`/pedidos/validate/${id}/update`, {
        '_token'  : token()
    })
    .then(function (response) {
        $('.loader').hide();

        if(response.data.result == "Success"){
            Swal.fire({
                icon: 'success',
                title: 'Productos registrados',
                showConfirmButton: false,
                timer: 1500
            });
            location.href = '/pedidos';
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

function processImpuestosProductos(){
    setimpuestosPU();
    productosArray.forEach(producto => { 
        const productoImpuestos = getProductoImpuestosId(producto.productoId);
        const impuestosPorUnidad = getImpuestosProducto(producto.costo, productoImpuestos);
        updateImpuestosProducto(producto.claveProducto, impuestosPorUnidad, producto.cantidad);
    });
}

function setimpuestosPU(){
    productosArray.forEach(producto => { 
        const productoImpuestos = getProductoImpuestosId(producto.productoId);
        const impuestosPorUnidad = getImpuestosProducto(producto.costo, productoImpuestos);
        producto.impuestosPU = impuestosPorUnidad;
    })
}

function validateCancelarPedido(){
    Swal.fire({
        title: '¿Cancelar?',
        text: "El pedido será cancelado, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('validar-verificacion').setAttribute('action','cancelar-pedido');
            $('#verificacion-modal').modal('show');
        }
    });
}

function validateActivarPedido(){
    Swal.fire({
        title: '¿Activar?',
        text: "El pedido será activado, ¿desea proceder?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, activar!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('validar-verificacion').setAttribute('action','activar-pedido');
            $('#verificacion-modal').modal('show');
        }
    });
}

function updateEstatusPedido(accion){
    const title = (accion === 'cancelar') ? 'cancelado' : 'reactivado';
    $('.loader').show();
    axios.post(`/pedidos/estatus/${pedidoId()}`, {
        '_token': token(),
        'estatus' : (accion === 'activar'),
        '_method' : 'PATCH'
    })
    .then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            Swal.fire({
                icon: 'success',
                title: `Pedido ${title}`,
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

async function eliminarProductoPedido(row,clave){
    $('.loader').show();
    result = await axios.post('/pedidos/removeProducto', {
        '_token': token(),
        'pedidoId': pedidoId(),
        'productoClave': clave
    });


    if(result.data.result == "Success"){
        $('.loader').hide();
        removeProducto(row);
        changeProducto();
        calculateAndDisplaySubTotalTotal();
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
    productosTable
        .row( $(row).parents('tr') )
        .remove()
        .draw();

    //remove clave from the array
    const clave   = $(row).parents('tr')[0].firstChild.innerText;
    // const horario = $(row).parents('tr')[0].childNodes[2].innerText;
    let updated   = 0;

    productosArray = productosArray.filter(function (productos) {
        let result = (productos.claveProducto !== clave && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });

    removeImpuesto(clave);
}

function removeImpuesto(clave){
    let updated   = 0;
    //Remueve impuestos de ese producto eliminado
    impuestosProductosGeneralesArray = impuestosProductosGeneralesArray.filter(function (impuestos) {
        let result = (impuestos.claveProducto !== clave && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
    recalculateImpuestosTotales();
}

function resetEstadoProducto(){
    //remove clave from the array
    productosArray = [];
    impuestosProductosGeneralesArray = [];
    constructorImpuestosTotales();
}

function resetProductoTabla(){
    productosTable
        .rows()
        .remove()
        .draw();
}

function addProducto(){ 
    // debugger;
    const productoDetalle = document.getElementById('productos').value;
    const productoId = document.getElementById('producto-id').value;
    const codigoProducto = document.getElementById('codigo').value;
    const claveProducto = document.getElementById('clave').value;
    const producto = document.getElementById('productos').value;
    const cantidad = document.getElementById('cantidad').value;
    const costo = document.getElementById('costo').value;
    const productoImpuestos = document.getElementById('producto-impuestos').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;
    const impuestosPorUnidad = getImpuestosProducto(costo, productoImpuestos);
    const impuestosTotalesPorUnidad = getImpuestosTotalesProducto(impuestosPorUnidad);
    const totalCosto = (parseFloat(costo) + impuestosTotalesPorUnidad) * parseFloat(cantidad);

    productosTable.row.add([
        claveProducto,
        productoDetalle,
        cantidad,
        costo,
        impuestosTotalesPorUnidad.toFixed(2),
        totalCosto.toFixed(2),
        acciones
    ]).draw(false);

    productosArray = [...productosArray, {
        'codigoProducto': codigoProducto,
        'productoId': productoId,
        'claveProducto': claveProducto,
        'cantidad': cantidad,
        'impuestosPU': impuestosPorUnidad,
        'costo': costo
    }];

    updateImpuestosProducto(claveProducto, impuestosPorUnidad, cantidad);
    displayImpuestos();
    calculateAndDisplaySubTotalTotal();
}

function displayImpuestos(){
    let impuestoTotal = 0;
    
    if(impuestosTotales.length > 0){
        impuestosTotales.forEach(impuesto => {
            impuestoTotal = parseFloat(impuesto.impuesto).toFixed(2);
            const impuestoElement = document.getElementById(`impuesto_${impuesto.impuestoId}`);
            impuestoElement.setAttribute('value', impuestoTotal);
            impuestoElement.value = formatter.format(impuestoTotal);
        })
        return;
    }

    impuestos.forEach( function (impuesto) {
        const impuestoElement = document.getElementById(`impuesto_${impuesto.impuestoId}`);
        impuestoElement.setAttribute('value', impuestoTotal);
        impuestoElement.value = formatter.format(impuestoTotal);
    });
}

function updateImpuestosProducto(claveProducto, impuestosPorUnidad, cantidad){
    setImpuestosProductoGenerales(claveProducto, impuestosPorUnidad, cantidad);
    recalculateImpuestosTotales();
}

function recalculateImpuestosTotales(){
    constructorImpuestosTotales();

    if(impuestosProductosGeneralesArray.length > 0){
        impuestosProductosGeneralesArray.forEach(impuestoGeneral => {
            impuestoGeneral.impuesto.forEach(impuestoGeneralImpuesto => {
                const impuestoIndex = impuestosTotales.findIndex((impuestoTotal) => impuestoTotal.impuestoId === impuestoGeneralImpuesto.impuestoId);

                if(impuestoIndex >= 0){
                    impuestosTotales[impuestoIndex].impuesto = (parseFloat(impuestosTotales[impuestoIndex].impuesto) + parseFloat(impuestoGeneralImpuesto.impuesto)).toFixed(2);
                }
            })
        });
    }
}

function setImpuestosProductoGenerales(claveProducto, impuestosPorUnidad, cantidad){
    let impuestosTotalProducto = impuestosPorUnidad.map(function(impuestoProducto){
        return {
            'impuestoId' : impuestoProducto.impuestoId,
            'impuesto' : (parseFloat(impuestoProducto.impuesto) * parseFloat(cantidad)).toFixed(2)
        };
    });

    impuestosProductosGeneralesArray.push({
        'claveProducto': claveProducto,   
        'impuesto': impuestosTotalProducto
    });
}

function getImpuestosTotalesProducto(impuestosPorUnidad){
    let impuestosTotalesProducto = 0;

    impuestosPorUnidad.forEach( function (impuesto) {
        impuestosTotalesProducto += parseFloat(impuesto.impuesto);
    });

    return impuestosTotalesProducto;
}

function getImpuestosProducto(costo, productoImpuestos){
    const productoImpuestosArray = productoImpuestos.split(',');
    let impuestosP = []; 
    impuestos.forEach( function (impuesto) {
        if(productoImpuestosArray.includes(String(impuesto.id))){
            impuestosP.push({
                'impuestoId': impuesto.id,   
                'impuesto': (costo * (impuesto.impuesto/100)).toFixed(2)             
            });
        }
    });

    return impuestosP;
}

function isProductoDuplicado(nuevoProducto){ 
    let duplicado = 0;
    productosArray.forEach( function (producto) {
        if(producto.codigoProducto == nuevoProducto.codigoProducto){
            duplicado += 1;
        }
    });
    return duplicado;
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

function constructorImpuestosTotales(){
    impuestosTotales = [];
    impuestos.forEach( function (impuesto) {
        impuestosTotales.push({
            'impuestoId' : impuesto.id,
            'impuesto' : 0
        });
    });
}

async function validarVerificacion(){
    const action      = document.getElementById('validar-verificacion').getAttribute('action');
    if(await validateUsuario(document.getElementById('password').value)){
        if(action === 'cancelar-pedido'){
            updateEstatusPedido('cancelar');
        }else if(action === 'activar-pedido'){
            updateEstatusPedido('activar');
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

async function validateUsuario($password){
    result = await axios.post('/usuarios/validateUsuario', {
        '_token': token(),
        'email': userEmail(),
        'password': $password
    });
    $('#verificacion-modal').modal('hide');
    return (result.data.result == 'Autorized') ? true : false;
}

function validateProductos() {
    const codigoProducto = document.getElementById('codigo').value;

    if (isProductoDuplicado({'codigoProducto': codigoProducto})) {
        Swal.fire({
            icon: 'warning',
            title: 'El producto ya se encuenta agregado.',
            showConfirmButton: false,
            timer: 900
        });
        resetProductoMeta()
        return false;
    }
    // if(!isDisponible()){
    //     return false;
    // }
    // enableBtn('reservar', productosArray.length > 0);
    return true
}

function validateBotonGuardar(){
    if(env == 'create'){
        enableBtn('guardar',productosArray.length > 0);
    }else{
        enableBtn('actualizar',productosArray.length > 0);
    }
}

function getProductos(){
    $('.loader').show(); 
    
    let proveedor   = document.getElementById('proveedor');
    if(proveedor !== null){
        const proveedorId = proveedor.options[proveedor.selectedIndex].value;

        axios.post('/productos/getproductobyproveedor', {
            '_token': token(),
            'proveedorId': proveedorId
        })
        .then(function (response) {
            $('.loader').hide();
            showProductos(response.data.result);
            showCodigoProductos(response.data.result);
        })
        .catch(function (error) {
            Swal.fire({
                icon: 'error',
                title: `Autorización fallida E:${error.message}`,
                showConfirmButton: true
            })
            
        });
    }else{
        $('.loader').hide();
    }
}

function showProductos(productos){
    let productosList = document.getElementById('productos-list');
    let option;
    productosList.length = 0;
    while (productosList.hasChildNodes()) {
        productosList.removeChild(productosList.firstChild);
    }
    
    for (let i = 0; i < productos.length; i++) {
        option = document.createElement('option');
        option.value = productos[i].nombre;
        option.setAttribute('data-id', productos[i].id);
        option.setAttribute('data-codigo', productos[i].codigo);
        productosList.appendChild(option);
    }
}

function showCodigoProductos(productos){
    let codigosList = document.getElementById('codigos-list');
    let option;
    codigosList.length = 0;
    while (codigosList.hasChildNodes()) {
        codigosList.removeChild(codigosList.firstChild);
    }
    
    for (let i = 0; i < productos.length; i++) {
        option = document.createElement('option');
        option.value = productos[i].codigo;
        option.setAttribute('data-id', productos[i].id);
        option.setAttribute('data-value', productos[i].nombre);
        codigosList.appendChild(option);
    }
}

function fillPedidoDetallesTabla() {
    productosTable.rows.add(productosTableArray).draw(false);
}

function enableBtn(btnId,status){
    let reservar = document.getElementById(btnId);
    (status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
}
  
function calculateAndDisplaySubTotalTotal() {
    const subTotal = calculateSubTotal();
    const impuestoTotal = calculateImpuestosTotal();
    const total = calculateTotal(subTotal, impuestoTotal);
    updateDisplay(subTotal, total);
}

function calculateSubTotal() {
    let subTotal = 0;
    productosArray.forEach(venta => {
      subTotal += parseFloat(venta.cantidad) * parseFloat(venta.costo);
    });

    return subTotal.toFixed(2);
}

function calculateImpuestosTotal(){
    let impuestoTotal = 0;
    
    impuestosTotales.forEach(impuesto => {
        impuestoTotal = (parseFloat(impuestoTotal) + parseFloat(impuesto.impuesto));
    })

    return impuestoTotal.toFixed(2);
}
  
function calculateTotal(subTotal, impuestoTotal) {
    const total = parseFloat(subTotal) + parseFloat(impuestoTotal);

    return total.toFixed(2);;
}

function updateDisplay(subTotal, total) {
    subTotal = parseFloat(subTotal).toFixed(2)
    document.getElementById('subtotal').setAttribute('value', subTotal);
    document.getElementById('subtotal').value = formatter.format(subTotal);

    total = parseFloat(total).toFixed(2);
    document.getElementById('total').setAttribute('value', total);
    document.getElementById('total').value = formatter.format(total);
}

function getProductoImpuestosId(productoId){
    let productoImpuestos = [];
    
    for (var i = 0; i < productosImpuestos.length; i++) {
        if (productoId == productosImpuestos[i].producto_id) {
            productoImpuestos.push(productosImpuestos[i].impuesto_id);
        }
    }

    return productoImpuestos.join(',');
}