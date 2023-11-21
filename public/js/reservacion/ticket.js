let actividades = '';
const format    = (reservacion) => {

    const descuentos = getDescuentos(reservacion);
    return `
        <!DOCTYPE html>
        <html>
            <head>
                <style>
                    * {
                        font-size: 13px;
                        font-family: monospace;
                    }

                    .f-16{
                        font-size: 16px;
                    }

                    .f-11{
                        font-size: 11px;
                        margin: 0;
                    }

                    .vacio{
                        width:40%;
                    }

                    table{
                        width:100%;
                    }

                    .border{
                        border-top: 1px solid black;
                    }

                    td,
                    th,
                    tr,
                    table {
                        border-collapse: collapse;
                    }

                    td.producto,
                    th.producto {
                        width: 75px;
                        max-width: 75px;
                    }

                    td.cantidad,
                    th.cantidad {
                        word-break: break-all;
                    }

                    td.precio,
                    th.precio {
                        word-break: break-all;
                    }

                    .centrado {
                        text-align: center;
                        align-content: center;
                    }

                    .derecha{
                        text-align: right;
                    }

                    .ticket {
                        width: 320px;
                        max-width: 320px;
                    }

                    .image-container{
                        text-align:center;
                    }

                    img {
                        max-width: inherit;
                        width: 150px;
                        filter: grayscale(1);
                    }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <div class="image-container">
                        <img
                        src="${logo()}"
                        alt="Logotipo">
                    </div>
                    <p class="centrado">
                        <strong>GUERRERO DOLPHIN S.A. DE C.V.</strong>
                        <br/>
                        RFC: GDO211105KA5
                        <br/>
                        LOTE ANEXO 6-B
                        <br/>
                        TEL. (755) 553-2707
                        <br/>
                        IXTAPA - ZIHUATANEJO, GUERRERO MEXICO.
                        <br/>
                        C.P. 40884
                        <br/>
                        <p class="f-16 centrado ">FOLIO: ${reservacion.folio}</p>
                        <br/>
                        <p class="f-11">LUGAR DE EXPEDICIÓN: IXTAPA - ZIHUATANEJO</p>
                        <p class="f-11">
                        FECHA DE EXPEDICIÓN: ${moment().format('YYYY-MM-DD hh:mm a')}
                        </p>
                        <br/>
                        CAJERO: ${detalleReservacion().cajero}
                        <br/>
                        NOMBRE: ${detalleReservacion().cliente}
                        <br/>
                        CIUDAD: ${(detalleReservacion().ciudad === undefined ? '' : detalleReservacion().ciudad)}
                        <br/>
                    </p>
                    <table>
                        <thead class="border">
                            <tr>
                                <th class="clave">CLAVE</th>
                                <th class="cantidad">CANT</th>
                                <th class="descripcion">DESC.</th>
                                <th class="precio">PRECIO</th>
                                <th class="importe">IMPORTE</th>
                            </tr>
                        </thead>
                        <tbody class="border">
                            ${getTicketActividades()}
                        </tbody>
                    </table>
                    <br/>
                    <table>
                        <thead>
                            <tr>
                                <th class="vacio"></th>
                                <th class="etiqueta"></th>
                                <th class="importe"></th>
                            </tr>
                        </thead>
                        <tbody class="border">
                            ${getTicketPagos()}
                        </tbody>
                    </table>
                    <p class="border">
                        <br/>
                        ESTE COMPROBANTE FORMA PARTE DE
                        <br/>
                        LA FACTURA GLOBAL A PUBLICO EN GENERAL.
                        <br/>
                    </p>
                    <p class="border centrado">
                        <strong>
                            <br/>
                            SI SE REQUIERE FACTURA FAVOR DE
                            <br/>
                            COLICITARLA EN RECEPCIÓN EN EL
                            <br/>
                            MOMENTO, YA QUE NO SE PODRÁ
                            <br/>
                            FACTURAR DIAS ANTERIORES.
                            <br/>
                            <br/>
                        </strong>
                    </p>
                    ${descuentos}
                    <p class="centrado">
                        <strong>DELFINITI</strong>
                    </p>
                </div>
            </body>
        </html>
    `;
}

function getTicketActividades(){
    let actividades = '';
    actvidadesArray.forEach(actividad => {
        actividades += `
        <tr>
            <td class="clave">${actividad.claveActividad}</td>
            <td class="centrado cantidad">${actividad.cantidad}</td>
            <td class="descripcion">${actividad.actividadDetalle.slice(0,7)}...</td>
            <td class="derecha precio">${formatter.format(actividad.precio)}</td>
            <td class="derecha importe">${formatter.format(actividad.cantidad * actividad.precio)}</td>
        </tr>
        `;
    });
    return actividades;
}
function getTicketPagos(){
    const reservacion   = document.getElementById('reservacion-form');
    const efectivo      = reservacion.elements['efectivo'];
    const efectivoUsd   = reservacion.elements['efectio-usd'];
    const tarjeta       = reservacion.elements['tarjeta'];
    const deposito       = reservacion.elements['deposito'];
    const total         = formatter.format(parseFloat(
        parseFloat(tarjeta.getAttribute('value'))+
        parseFloat(deposito.getAttribute('value'))+
        parseFloat(efectivoUsd.getAttribute('value'))+
        parseFloat(efectivo.getAttribute('value'))
    ).toFixed(2));
    const cambio        = reservacion.elements['cambio'];

    let pagos = `
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">EFECTIVO M.N.</td>
            <td class="importe">${efectivo.value}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">EFECTIVO USD</td>
            <td class="importe">${efectivoUsd.value}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">TARJ. CRÉDITO</td>
            <td class="importe">${tarjeta.value}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">DEP. / TRANSF.</td>
            <td class="importe">${deposito.value}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">TOTAL</td>
            <td class="importe">${total}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">CAMBIO</td>
            <td class="importe">${cambio.value}</td>
        </tr>
        `;

    return pagos;
}

function getDescuentos(reservacion){

    let descuentosHTML  = "";
    const reservacionForm   = document.getElementById('reservacion-form');
    const descuento     = reservacionForm.elements['descuento-personalizado'];
    const codigo        = reservacionForm.elements['descuento-codigo'];
    const codigoOpcion  = reservacionForm.elements['codigo-descuento'];
    const cupon         = reservacionForm.elements['cupon'];

    const descuentos = new Map([
        ["descuento", descuento.getAttribute('value')],
        ["codigo", codigo.getAttribute('value')],
        ["cupon", cupon.getAttribute('value')]
    ]);

    for(let [key, descuento] of descuentos){
        if(parseFloat(descuento) !== 0){
            switch (key) {
                //case 'cupon':
                //    descuentosHTML = descuentosHTML.concat(`
                //        <tr class="centrado">
                //            <td class="vacio"></td>
                //            <td class="etiqueta">CUPÓN</td>
                //            <td class="importe">-$${descuento}</td>
                //        </tr>`);
                //    break;
                case 'descuento':
                    descuentosHTML = descuentosHTML.concat(`
                        <p class="centrado">
                            ${descuento}% OFF
                        </p>
                    `);
                    break;
                //case 'codigo':
                //    descuentosHTML = descuentosHTML.concat(`
                //        <tr class="centrado">
                //            <td class="vacio"></td>
                //            <td class="etiqueta">${codigoOpcion.options[codigoOpcion.selectedIndex].text}</td>
                //            <td class="importe">-${descuento}%</td>
                //        </tr>`);
                //    break;
                default:
                    break;
            }
        }
    }

    return descuentosHTML;
}

function getTicket(reservacion){
    let result = true;
    try{
        openWindowWithPost("/ticket", {
            venta: JSON.stringify(format(reservacion))
        });
        result = true;    
    }catch(err) {
        Swal.fire({
            icon: 'warning',
            title: `Pago guardado, error en impresión de ticket`,
            text: err,
            showConfirmButton: true
        });
        result = false;
    }
    saveTicket(reservacion.id,format(reservacion));
    return result;
}

function openWindowWithPost(url, data) {
    var form = document.createElement("form");
    form.target = "_blank";
    form.method = "POST";
    form.action = url;
    form.style.display = "none";

    for (var key in data) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function imprimirTicket(id){
    axios.get(`/reservacionticket/${id}`)
    .then(function (response) {
        openWindowWithPost("/ticket", {
            venta: JSON.stringify(response.data.ticket)
        });
        result = true;    
    })
    .catch(function (error) {
        Swal.fire({
            icon: 'warning',
            title: `Error en impresión de ticket`,
            text: err,
            showConfirmButton: true
        });
    });
}

function saveTicket(reservacionId,ticket){
    $('.loader').show();
    axios.post('/reservacionticket', {
        '_token': token(),
        'ticket': ticket,
        'reservacionId': reservacionId
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            
        } else {
           
        }
    }).catch(function (error) {
        $('.loader').hide();
    });
}