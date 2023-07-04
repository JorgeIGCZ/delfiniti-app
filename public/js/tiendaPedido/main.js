const actualizarEstatusReservacion = document.getElementById('actualizar-estatus-pedido');
const agregarProducto = document.getElementById('add-producto');
 
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

if(agregarProducto !== null){
    agregarProducto.addEventListener('click', (event) =>{
        event.preventDefault();
        if(productoIsValid()){
            validateProductos();
            addProducto();
            resetProductoMeta();
            validateBotonGuardar();
        }
    });
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
        setSubTotal();
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
    const horario = $(row).parents('tr')[0].childNodes[2].innerText;
    let updated   = 0;
    productosArray = productosArray.filter(function (productos) {
        let result = (productos.claveProducto !== clave && productos.horario !== horario && updated == 0);
        updated > 0 ? result = true : '';
        !result ? updated++ : '';
        return result;
    });
}

function resetProductoTabla(){
    productosTable
        .rows()
        .remove()
        .draw();

    //remove clave from the array
    productosArray = [];
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
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;
    const totalCosto = costo * cantidad;

    productosTable.row.add([
        claveProducto,
        productoDetalle,
        cantidad,
        costo,
        totalCosto.toFixed(2),
        acciones
    ])
        .draw(false);
    productosArray = [...productosArray, {
        'codigoProducto': codigoProducto,
        'productoId': productoId,
        'producto': producto,
        'claveProducto': claveProducto,
        'cantidad': cantidad,
        'costo': costo
    }];
    setSubTotal();
}

// function resetVentas() {
//     location.reload();
// }

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

    // document.getElementById('validar-verificacion').addEventListener('click', (event) =>{
    //     validarVerificacion();
    // });
};

//jQuery
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
        setSubTotal();
    // }
} );


// $('#codigo').on('keydown', function (e) {
//     changeProducto();
// });
// $('#producto').on('keydown', function (e) {
//     changeCodigoProducto();
// });

$('#codigo').on('change', function (e) {
    changeProducto();
});
$('#productos').on('change', function (e) {
    changeCodigoProducto();
});

$('#proveedor').on('change', function (e) {
    // console.log(e.target.value);
    getProductos();
    resetProductoTabla();
    resetProductoMeta();
});

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
    let proveedorId = proveedor.options[proveedor.selectedIndex].value;

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




$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {

        if($(this).attr("id") == "cantidad"){
            if(productoIsValid()){
                validateProductos();
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