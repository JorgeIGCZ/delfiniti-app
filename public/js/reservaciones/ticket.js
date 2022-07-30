let actividades = '';
const format    = (folio = '') => {
    return `
        <!DOCTYPE html>
        <html>
            <head>
                <style>
                    * {
                        font-size: 12px;
                        font-family: monospace;
                    }

                    .f-16{
                        font-size: 16px;
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
                        width: 40px;
                        max-width: 40px;
                        word-break: break-all;
                    }

                    td.precio,
                    th.precio {
                        width: 40px;
                        max-width: 40px;
                        word-break: break-all;
                    }

                    .centrado {
                        text-align: center;
                        align-content: center;
                    }

                    .izquierda{
                        text-align: left;
                    }

                    .ticket {
                        width: 315px;
                        max-width: 315px;
                    }

                    img {
                        max-width: inherit;
                        width: inherit;
                        filter: invert(1);
                    }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <img
                        src="${logo()}"
                        alt="Logotipo">
                    <p class="centrado">
                        <strong>DELFINITI DE MEXICO S.A. DE C.V.</strong>
                        <br/>
                        RFC: DME-990323-PR7
                        <br/>
                        LOTE ANEXO 6-B
                        <br/>
                        TEL. (755) 553-2707
                        <br/>
                        IXTAPA - ZIHUATANEJO, GUERRERO MEXICO.
                        <br/>
                        C.P. 40884
                        <br/>
                        <p class="f-16 centrado ">FOLIO: ${folio}</p>
                        <br/>
                        LUGAR DE EXPEDICIÓN: IXTAPA - ZIHUATANEJO
                        <br/>
                        FECHA DE EXPEDICION: 00/MES/0000 hh:mm:ss A.M.
                        <br/>
                        CAJERO: ${detalleReservacion().cajero}
                        <br/>
                        NOMBRE: ${detalleReservacion().cliente}
                        <br/>
                        CIUDAD: ${detalleReservacion().ciudad}
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
                        LA FACTURA GLOBAL A PUBLICO
                        <br/>
                        LA FACTURA GLOBAL A PUBLICO EN GENERAL.
                        <br/>
                    </p>
                    <p class="border">
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
            <td class="izquierda cantidad">${actividad.cantidad}</td>
            <td class="descripcion">${actividad.actividad}</td>
            <td class="izquierda precio">${actividad.precio}</td>
            <td class="izquierda importe">${actividad.cantidad * actividad.precio}</td>
        </tr>
        `;
    });
    return actividades;
}
function getTicketPagos(){
    const reservacion   = document.getElementById('reservacion-form');
    const efectivo      = reservacion.elements['efectivo'].value;
    const efectivoUsd   = reservacion.elements['efectio-usd'].value;
    const tarjeta       = reservacion.elements['tarjeta'].value;
    const total         = reservacion.elements['total'].value;
    const cambio        = reservacion.elements['cambio'].value;

    let pagos = `
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">EFECTIVO M.N.</td>
            <td class="importe">${efectivo}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">EFECTIVO USD</td>
            <td class="importe">${efectivoUsd}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">TARJ. CREDITO</td>
            <td class="importe">${tarjeta}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">TOTAL</td>
            <td class="importe">${total}</td>
        </tr>
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">CAMBIO</td>
            <td class="importe">${cambio}</td>
        </tr>
        `;

    return pagos;
}

function getTicket(folio){
    let w = window.open();
    w.document.write(format(folio));
    w.window.print();
    w.document.close();
    return false;
}
