let actividades = '';
const format    = (pedido) => {

    const descuentos = '';//getDescuentos(pedido);
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
                        <p class="f-16 centrado ">ID: ${pedido.id}</p>
                        <br/>
                        <p class="f-11">LUGAR DE EXPEDICIÓN: IXTAPA - ZIHUATANEJO</p>
                        <p class="f-11">
                        FECHA DE EXPEDICIÓN: ${moment().format('YYYY-MM-DD hh:mm a')}
                        </p>
                        <br/>
                        NOMBRE: ${detallePedido.proveedor}
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
                            ${getTicketProductos()}
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

function getTicketProductos(){
    let productos = '';
    productosArray.forEach(producto => {
        productos += `
        <tr>
            <td class="clave">${producto.claveProducto}</td>
            <td class="centrado cantidad">${producto.cantidad}</td>
            <td class="descripcion">${producto.producto.slice(0,7)}...</td>
            <td class="derecha precio">${formatter.format(producto.costo)}</td>
            <td class="derecha importe">${formatter.format(producto.cantidad * producto.costo)}</td>
        </tr>
        `;
    });

    return productos;
}
function getTicketPagos(){
    const pedido   = document.getElementById('pedido-form');
    // const efectivo      = pedido.elements['efectivo'];
    // const efectivoUsd   = pedido.elements['efectio-usd'];
    // const tarjeta       = pedido.elements['tarjeta'];
    // const deposito       = pedido.elements['deposito'];
    // const total         = formatter.format(parseFloat(
    //     parseFloat(tarjeta.getAttribute('value'))+
    //     parseFloat(deposito.getAttribute('value'))+
    //     parseFloat(efectivoUsd.getAttribute('value'))+
    //     parseFloat(efectivo.getAttribute('value'))
    // ).toFixed(2));
    const total         = pedido.elements['total'].value;
    // const cambio        = pedido.elements['cambio'];

    let pagos = `
        <tr class="izq">
            <td class="vacio"></td>
            <td class="etiqueta">TOTAL</td>
            <td class="importe">${total}</td>
        </tr>
        `;

    return pagos;
}

// function getDescuentos(pedido){

//     let descuentosHTML  = "";
//     const pedidoForm   = document.getElementById('pedido-form');
//     const descuento     = pedidoForm.elements['descuento-personalizado'];
//     const codigo        = pedidoForm.elements['descuento-codigo'];
//     const codigoOpcion  = pedidoForm.elements['codigo-descuento'];
//     const cupon         = pedidoForm.elements['cupon'];

//     const descuentos = new Map([
//         ["descuento", descuento.getAttribute('value')],
//         ["codigo", codigo.getAttribute('value')],
//         ["cupon", cupon.getAttribute('value')]
//     ]);

//     for(let [key, descuento] of descuentos){
//         if(parseFloat(descuento) !== 0){
//             switch (key) {
//                 //case 'cupon':
//                 //    descuentosHTML = descuentosHTML.concat(`
//                 //        <tr class="centrado">
//                 //            <td class="vacio"></td>
//                 //            <td class="etiqueta">CUPÓN</td>
//                 //            <td class="importe">-$${descuento}</td>
//                 //        </tr>`);
//                 //    break;
//                 case 'descuento':
//                     descuentosHTML = descuentosHTML.concat(`
//                         <p class="centrado">
//                             ${descuento}% OFF
//                         </p>
//                     `);
//                     break;
//                 //case 'codigo':
//                 //    descuentosHTML = descuentosHTML.concat(`
//                 //        <tr class="centrado">
//                 //            <td class="vacio"></td>
//                 //            <td class="etiqueta">${codigoOpcion.options[codigoOpcion.selectedIndex].text}</td>
//                 //            <td class="importe">-${descuento}%</td>
//                 //        </tr>`);
//                 //    break;
//                 default:
//                     break;
//             }
//         }
//     }

//     return descuentosHTML;
// }

function getTicket(pedido){
    let result = true;
    try{
        let w = window.open();
        w.document.write(format(pedido));
        w.window.print();
        w.document.close();
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
    // saveTicket(pedido.id,format(pedido));
    return result;
}

function imprimirTicket(id){
    axios.get(`/pedidoticket/${id}`)
    .then(function (response) {
        let w = window.open();
        w.document.write(response.data.ticket);
        w.window.print();
        w.document.close();
        result = true;    
    })
    .catch(function (error) {

    });
}

function saveTicket(pedidoId,ticket){
    $('.loader').show();
    axios.post('/pedidoticket', {
        '_token': token(),
        'ticket': ticket,
        'pedidoId': pedidoId
    }).then(function (response) {
        $('.loader').hide();
        if (response.data.result == 'Success') {
            
        } else {
           
        }
    }).catch(function (error) {
        $('.loader').hide();
    });
}