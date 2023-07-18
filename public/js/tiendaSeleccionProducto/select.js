function changeProducto() {
    var value = document.getElementById('codigo').value;
    const codigos = document.querySelector(`#codigos-list [value="${value}"]`);

    if(codigos !== null){
        document.getElementById('productos').value = codigos.getAttribute('data-value');
        // document.getElementById('codigo').setAttribute('nombreProducto',productos.value);
        //$('#productos').trigger('change.select2');
        // getProductoDisponibilidad();
        // getProductoMeta();

        displayProductoMeta(modulo);

        return;
    }

    resetProductoMeta();
}

function changeCodigoProducto() {
    var value = document.getElementById('productos').value;
    const productos = document.querySelector(`#productos-list [value="${value}"]`);

    if(productos !== null){
        document.getElementById('codigo').value = productos.getAttribute('data-codigo');
        document.getElementById('codigo').setAttribute('nombreProducto',productos.value);

        displayProductoMeta(modulo);
        return;
    }

    resetProductoMeta();
}

function resetProductoMeta() {
    document.getElementById('productos').value = "";
    document.getElementById('codigo').value = "";

	if(modulo === 'ventas'){
		document.getElementById('precio').value = "";
	}else{
		document.getElementById('costo').value = "";
	}
    
    document.getElementById('cantidad').value = 1;

    document.getElementById('clave').value = "";
    document.getElementById('producto-id').value = "";
    displayImpuestos();
}

function displayProductoMeta(modulo){
    const codigoElement = document.getElementById('codigo').value;
    const claveElement = document.getElementById('clave');
    const productoIdElement = document.getElementById('producto-id');
    const productoImpuestosElement = document.getElementById('producto-impuestos');
    const precio = (document.getElementById('precio') !== null ? document.getElementById('precio') : 0); 
    const costo = (document.getElementById('costo') !== null ? document.getElementById('costo') : 0); 

    for (var i = 0; i < allProductos.length; i++) {
        if (codigoElement == allProductos[i].codigo) {

			if(modulo === 'ventas'){
				precio.value = allProductos[i].precio_venta;
			}else{
				costo.value = allProductos[i].costo;
			}

            claveElement.value = allProductos[i].clave;
            productoIdElement.value = allProductos[i].id;
        }
    }

    if(modulo === 'pedidos'){
        const productoImpuestosId = getProductoImpuestosId(productoIdElement.value);
        productoImpuestosElement.value = productoImpuestosId;
    }
}

// function getProductoMeta(codigo, clave, productoId) {
//     const codigo = document.getElementById('codigo').value;
//     const clave = document.getElementById('clave');
//     const productoId = document.getElementById('producto-id');
//     const productoImpuestos = document.getElementById('producto-impuestos');

//     const precio = 0; 
//     const costo = 0;
// 	if(modulo === 'ventas'){
// 		precio = document.getElementById('precio'); 
// 	}else{
// 		costo = document.getElementById('costo'); 
// 	}


//     for (var i = 0; i < allProductos.length; i++) {
//         if (codigo == allProductos[i].codigo) {

// 			if(modulo === 'ventas'){
// 				precio.value = allProductos[i].precio_venta;
// 			}else{
// 				costo.value = allProductos[i].costo;
// 			}

//             clave.value = allProductos[i].clave;
//             productoId.value = allProductos[i].id;
//         }
//     }

    
//     if(modulo === 'pedidos'){
//         for (var i = 0; i < productosImpuestos.length; i++) {
//             if (productoId.value == productosImpuestos[i].producto_id) {
//                 productoImpuestos.value += productosImpuestos[i].impuesto_id+',';
//             }
//         }
//     }
// }