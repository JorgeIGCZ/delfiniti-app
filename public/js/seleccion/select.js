function changeProducto() {
    var value = document.getElementById('codigo').value;
    const codigos = document.querySelector(`#codigos-list [value="${value}"]`);

    if(codigos !== null){
        document.getElementById('productos').value = codigos.getAttribute('data-value');
        // document.getElementById('codigo').setAttribute('nombreProducto',productos.value);
        //$('#productos').trigger('change.select2');
        // getProductoDisponibilidad();
        getProductoMeta();
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

        getProductoMeta();
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

    document.getElementById('clave').value = "";
    document.getElementById('producto-id').value = "";
}

function getProductoMeta() {
    const codigo = document.getElementById('codigo').value;

	if(modulo === 'ventas'){
		let precio = document.getElementById('precio'); 
	}else{
		let costo = document.getElementById('costo'); 
	}

    
    let clave = document.getElementById('clave');
    let productoId = document.getElementById('producto-id');

    for (var i = 0; i < allProductos.length; i++) {
        if (codigo == allProductos[i].codigo) {

			if(modulo === 'ventas'){
				precio.value = allProductos[i].precio_venta;
			}else{
				costo.value = allProductos[i].costo;
			}

            clave.value = allProductos[i].clave;
            productoId.value = allProductos[i].id;
        }
    }
}

function getProductoMeta() {
    const codigo = document.getElementById('codigo').value;

	if(modulo === 'ventas'){
		let precio = document.getElementById('precio');
	}else{
		let costo = document.getElementById('costo');
	}

    
    let clave = document.getElementById('clave');
    let productoId = document.getElementById('producto-id');
    
    for (var i = 0; i < allProductos.length; i++) {
        if (codigo == allProductos[i].codigo) {

			if(modulo === 'ventas'){
				precio.value = allProductos[i].precio_venta;
			}else{
				costo.value = allProductos[i].costo;
			}

            clave.value = allProductos[i].clave;
            productoId.value = allProductos[i].id;
        }
    }
}