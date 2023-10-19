let pagosTabla = new DataTable('#pagos', {
    searching: false,
    paging: false,
    info: false
});

fillReservacionDetallesTabla();
fillPagosTabla();

function fillReservacionDetallesTabla() {
    reservacionesTable.rows.add(reservacionesTableArray).draw(false);
    setTotal();
}

function fillPagosTabla() {
    let cantidadPago = 0;
    let cantidadPagada = 0;
    
    pagosArray.forEach(function (pago) {
        cantidadPago = parseFloat(getCantiodadPago(pago));
        cantidadPagada += parseFloat(cantidadPago);
    });
    pagosTabla.rows.add(pagosTablaArray).draw(false);
    setCantidadPagada(cantidadPagada);
    setTotal();
}

function getCantiodadPago(pago){
    let cantidadPago = pago.cantidad;
    if(pago.tipoPagoId == 2){
        cantidadPago = parseFloat(parseFloat(cantidadPago) * parseFloat(pago.tipoCambioUSD)).toFixed(2);
    }
    return cantidadPago;
}

function setTotal() {
    let total = 0;
    actvidadesArray.forEach(reservacion => {
        total += (reservacion.cantidad * reservacion.precio);
    });
    total = parseFloat(total).toFixed(2)
    document.getElementById('total').setAttribute('value', total);
    document.getElementById('total').value = formatter.format(total);
    setOperacionResultados();
}

function setOperacionResultados() {
    const total = document.getElementById('total').getAttribute('value');
    setResta();
    setCambio();
}

function setCantidadPagada(cantidadPagada) {
    document.getElementById('anticipo').setAttribute('value', cantidadPagada);
    document.getElementById('anticipo').value = formatter.format(cantidadPagada);
}

function getPagos(tipoUsd = 'compra') {
    const total = parseFloat(document.getElementById('total').getAttribute('value'));
    const efectivo = parseFloat(document.getElementById('efectivo').getAttribute('value'));
    const efectivoUsd = (
        tipoUsd == 'compra'
            ? getMXNFromUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')))
            : getMXNFromVentaUSD(parseFloat(document.getElementById('efectivo-usd').getAttribute('value')))
    );
    const tarjeta = parseFloat(document.getElementById('tarjeta').getAttribute('value'));
    const deposito = parseFloat(document.getElementById('deposito').getAttribute('value'));
    const descuentoCodigo = parseFloat(document.getElementById('descuento-codigo').getAttribute('value'));
    const cantidadCodigo = (document.getElementById('descuento-codigo').getAttribute('tipo') == 'porcentaje')
        ? convertPorcentageCantidad(descuentoCodigo)
        : parseFloat(descuentoCodigo);
    const cupon = parseFloat(document.getElementById('cupon').getAttribute('value'));
    const descuentoPersonalizado = parseFloat(document.getElementById('descuento-personalizado').getAttribute('value'));
    const cantidadPersonalizado = calculatePagoPersonalizado(descuentoPersonalizado, cantidadCodigo, cupon); //(document.getElementById('descuento-personalizado').getAttribute('tipo') == 'porcentaje') ? (total*(descuentoPersonalizado/100)) : descuentoPersonalizado;
    const anticipo = parseFloat(document.getElementById('anticipo').getAttribute('value'));
    const pagos = (cupon + efectivo + efectivoUsd + tarjeta + deposito + cantidadPersonalizado + cantidadCodigo + anticipo);
    return parseFloat(pagos);
}