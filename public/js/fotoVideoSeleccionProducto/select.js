function changeProducto() {
    const value = document.getElementById('clave').value;
    const claves = document.querySelector(`#claves-list [value="${value}"]`);

    if(claves !== null){
        document.getElementById('productos').value = claves.getAttribute('data-value');
        // document.getElementById('clave').setAttribute('nombreProducto',productos.value);
        //$('#productos').trigger('change.select2');
        // getProductoDisponibilidad();
        getProductoMeta();
        return;
    }

    resetProductoMeta();
}

function changeClaveProducto() {
    const value = document.getElementById('productos').value;
    const productos = document.querySelector(`#productos-list [value="${value}"]`);

    if(productos !== null){
        document.getElementById('clave').value = productos.getAttribute('data-clave');
        document.getElementById('clave').setAttribute('nombreProducto',productos.value);

        getProductoMeta();
        return;
    }

    resetProductoMeta();
}

function resetProductoMeta() {
    document.getElementById('productos').value = "";
    document.getElementById('clave').value = "";

	if(modulo === 'ventas'){
		document.getElementById('precio').value = "";
	}else{
		document.getElementById('costo').value = "";
	}

    document.getElementById('cantidad').value = 1;

    document.getElementById('producto-id').value = "";
}

function getProductoMeta() {
    const clave = document.getElementById('clave').value;

	if(modulo === 'ventas'){
		const precio = document.getElementById('precio'); 
	}else{
		const costo = document.getElementById('costo'); 
	}

    const productoId = document.getElementById('producto-id');

    for (var i = 0; i < allProductos.length; i++) {
        if (clave == allProductos[i].clave) {

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