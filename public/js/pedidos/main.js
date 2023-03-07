function updateEstatusPedido(accion){
    const title = (accion === 'cancelar') ? 'cancelado' : 'reactivado';
    $('.loader').show();
    axios.post('/pedidos/updateestatus', {
        '_token': token(),
        'pedidoId': pedidoId(),
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
    pedidosTable
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

function addProducto(){
    // debugger;
    const productoDetalle = document.getElementById('productos').value;
    const codigoProducto = document.getElementById('codigo').value;
    const claveProducto = document.getElementById('clave').value;
    const producto = document.getElementById('productos').value;
    const cantidad = document.getElementById('cantidad').value;
    const precio = document.getElementById('precio').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;

    pedidosTable.row.add([
        claveProducto,
        productoDetalle,
        cantidad,
        precio,
        precio * cantidad,
        acciones
    ])
        .draw(false);
    productosArray = [...productosArray, {
        'codigoProducto': codigoProducto,
        'claveProducto': claveProducto,
        'producto': producto,
        'cantidad': cantidad,
        'precio': precio
    }];
    setSubTotal();
}

function clearSeleccion(){
    document.getElementById('productos').value = "";
    document.getElementById('codigo').value = "";
    document.getElementById('clave').value = "";
    document.getElementById('productos').value = "";
    document.getElementById('cantidad').value = "";
    document.getElementById('precio').value = "";
}

function resetVentas() {
    location.reload();
}



function displayProducto() {
    let productosClaveSelect = document.getElementById('codigo');
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

function isProductoDuplicado(nuevoProducto){
    let duplicado = 0;
    productosArray.forEach( function (producto) {
        if(producto.codigoProducto == nuevoProducto.codigoProducto){
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

let pedidosTable = new DataTable('#ventas', {
    searching: false,
    paging: false,
    info: false
} );

window.onload = function() {
    // getDisponibilidad()
    document.getElementById('pedido-form').elements['proveedor'].focus();

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
                eliminarProductoPedido(this,$(this).parents('tr')[0].firstChild.innerText)
            }
        });
    }else{
        removeProducto(this);
        setSubTotal();
    }
} );


 $('#codigo').on('change', function (e) {
    changeProducto();
});
$('#productos').on('change', function (e) {
    changeCodigoProducto();
});

document.getElementById('add-producto').addEventListener('click', (event) =>{
    event.preventDefault();
    if(cantidadIsValid()){
        addProductos();
        validateBotonGuardar();
    }
});

function validateBotonGuardar(){
    if(env == 'create'){
        enableBtn('guardar',productosArray.length > 0);
    }else{
        enableBtn('actualizar',productosArray.length > 0);
    }
}

function changeCodigoProducto() {
    var value = document.getElementById('productos').value;
    const productos = document.querySelector(`#productos-list [value="${value}"]`);

    document.getElementById('codigo').value = productos.getAttribute('data-id');
    document.getElementById('codigo').setAttribute('nombreProducto',productos.value);

    //$('#codigo').trigger('change.select2');
    // getProductoDisponibilidad();
    getProductoMeta();
}

function changeProducto() {
    var value = document.getElementById('codigo').value;
    const codigos = document.querySelector(`#codigos-list [value="${value}"]`);

    document.getElementById('productos').value = codigos.getAttribute('data-value');
    // document.getElementById('codigo').setAttribute('nombreProducto',productos.value);

    //$('#productos').trigger('change.select2');
    // getProductoDisponibilidad();
    getProductoMeta();
}


function enableBtn(btnId,status){
    let reservar = document.getElementById(btnId);
    (status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
}

function getProductoMeta() {
    const codigo = document.getElementById('codigo').value;
    let costo = document.getElementById('costo');
    let clave = document.getElementById('clave');
    for (var i = 0; i < allProductos.length; i++) {
        if (codigo == allProductos[i].codigo) {
            precio.value = allProductos[i].costo;
            clave.value = allProductos[i].clave;
        }
    }
}