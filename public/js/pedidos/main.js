getProductos();

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
    const productoDetalle = document.getElementById('producto').value;
    const productoId = document.getElementById('producto-id').value;
    const codigoProducto = document.getElementById('codigo').value;
    const claveProducto = document.getElementById('clave').value;
    const producto = document.getElementById('producto').value;
    const cantidad = document.getElementById('cantidad').value;
    const costo = document.getElementById('costo').value;
    const acciones = `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>`;

    productosTable.row.add([
        claveProducto,
        productoDetalle,
        cantidad,
        costo,
        costo * cantidad,
        acciones
    ])
        .draw(false);
    productosArray = [...productosArray, {
        'codigoProducto': codigoProducto,
        'productoId': productoId,
        'claveProducto': claveProducto,
        'cantidad': cantidad,
        'costo': costo
    }];
    setSubTotal();
}

function resetVentas() {
    location.reload();
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

let productosTable = new DataTable('#productosTable', {
    searching: false,
    paging: false,
    info: false
} );

window.onload = function() {
    // getDisponibilidad()
    document.getElementById('pedido-form').elements['proveedor'].focus();

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
$('#producto').on('change', function (e) {
    changeCodigoProducto();
});

$('#proveedor').on('change', function (e) {
    // console.log(e.target.value);
    getProductos();
    resetProductoTabla();
    resetProductoMeta();
});

document.getElementById('add-producto').addEventListener('click', (event) =>{
    event.preventDefault();
    if(productoIsValid()){
        addProductos();
        validateBotonGuardar();
    }
});

function addProductos() {
    const codigoProducto = document.getElementById('codigo').value;

    if (isProductoDuplicado({'codigoProducto': codigoProducto})) {
        Swal.fire({
            icon: 'warning',
            title: 'El prodducto ya se encuenta agregado.',
            showConfirmButton: false,
            timer: 900
        });
        resetProductoMeta()
        return false;
    }
    // if(!isDisponible()){
    //     return false;
    // }
    addProducto();
    resetProductoMeta()
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

function changeCodigoProducto() {
    var value = document.getElementById('producto').value;
    const productos = document.querySelector(`#productos-list [value="${value}"]`);

    if(productos !== null){
        document.getElementById('codigo').value = productos.getAttribute('data-codigo');
        document.getElementById('codigo').setAttribute('nombreProducto',productos.value);

        getProductoMeta();
        return;
    }

    resetProductoMeta();
}

function changeProducto() {
    var value = document.getElementById('codigo').value;
    const codigos = document.querySelector(`#codigos-list [value="${value}"]`);

    if(codigos !== null){
        document.getElementById('producto').value = codigos.getAttribute('data-value');
        // document.getElementById('codigo').setAttribute('nombreProducto',productos.value);

        //$('#producto').trigger('change.select2');
        // getProductoDisponibilidad();
        getProductoMeta();
        return;
    }

    resetProductoMeta();
}


function enableBtn(btnId,status){
    let reservar = document.getElementById(btnId);
    (status) ? reservar.removeAttribute('disabled') : reservar.setAttribute('disabled','disabled');
}

function getProductoMeta() {
    const codigo = document.getElementById('codigo').value;
    let costo = document.getElementById('costo');
    let clave = document.getElementById('clave');
    let productoId = document.getElementById('producto-id');
    
    for (var i = 0; i < allProductos.length; i++) {
        if (codigo == allProductos[i].codigo) {
            costo.value = allProductos[i].costo;
            clave.value = allProductos[i].clave;
            productoId.value = allProductos[i].id;
        }
    }
}

function resetProductoMeta() {
    document.getElementById('producto').value = "";
    document.getElementById('codigo').value = "";
    document.getElementById('costo').value = "";
    document.getElementById('clave').value = "";
    document.getElementById('producto-id').value = "";
}

$('body').on('keydown', 'input, select, button', function(e) {
    if (e.key === "Enter") {

        if($(this).attr("id") == "cantidad"){
            if(productoIsValid()){
                addProductos();
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