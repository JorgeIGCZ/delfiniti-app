@extends('layouts.app')
@section('scripts')
    <script>
        const modulo = 'ventas';

        const env = 'edit';

        const allProductos = @php echo(json_encode($productos)) @endphp;
        
        const accion = '{{ (@$_GET["accion"] === "pago" ? "pago" : "edit"); }}';
        const ventaId = () => {
            return {{$venta->id}};
        }
        const isVentaPagada = () => {
            return {{$venta->estatus_pago == 2 ? 1 : 0}};
        }
        const dolarPrecioCompra = () =>{
            return  {{$dolarPrecio->precio_compra}};
        }
        const dolarPrecioVenta = () =>{
            return  {{$dolarPrecio->precio_venta}};
        }
        const isAdmin = () => {
            return {{Auth::user()->hasRole('Administrador') ? 1 : 0}};
        }
        const canEdit = () => {
            return {{Auth::user()->can('TiendaVentas.update') ? 1 : 0}};
        }
        const userEmail = () =>{
            return  '{{Auth::user()->email}}';
        }
        const logo = () =>{
            return '{{asset("assets/img/logo.png")}}';
        }
        const detalleVenta = () =>{
            const venta   = document.getElementById('venta-form');
            const nombreCliente = venta.elements['nombre'].value;
            const direccion     = venta.elements['direccion'].value;
            const ciudad        = venta.elements['origen'].value;

            return {
                'cajero'    : '{{Auth::user()->username}}',
                'cliente'   : nombreCliente,
                'dirección' : direccion,
                'ciudad'    : ciudad
            };
        }
        const nombreCliente = () =>{
            return
        }
        const direccion = () =>{
            return
        }
        const ciudad = () =>{
            return
        }

        let productosArray          = [];
        let ventasTableArray        = [];
        let pagosArray              = [];
        let pagosTablaArray         = [];
        let nombreTipoPagoArray     = [];
        let cantidadPagada          = 0;

        @forEach($venta->ventaDetalle as $detalle)
            ventasTableArray = [...ventasTableArray,[
                '{{$detalle->producto->clave}}',
                '{{$detalle->producto->nombre}}',
                '{{$detalle->numero_productos}}',
                formatter.format('{{$detalle->PPU}}'),
                formatter.format('{{$detalle->PPU}}'*'{{$detalle->numero_productos}}')
            ]];
            productosArray = [...productosArray, {
                'claveProducto': '{{$detalle->producto->clave}}',
                'productoId': '{{$detalle->producto->id}}',
                'claveProducto': '{{$detalle->producto->clave}}',
                'producto': '{{$detalle->producto_id}}',
                'cantidad': '{{$detalle->numero_productos}}',
                'precio': '{{$detalle->PPU}}'
            }];
        @endforeach
        
        let eliminar = '';
        let editar   = '';
        @forEach($venta->pagos as $pago)

            pagosTablaArray = [...pagosTablaArray,[ 
                '{{$pago->id}}',
                ('{{$pago->tipo_pago_id}}' == '2' ? `${formatter.format('{{$pago->cantidad}}')} USD * ${'{{$pago->tipo_cambio_usd}}'}` : formatter.format('{{$pago->cantidad}}')),
                '{{@$pago->tipoPago->nombre}}',
                '<input class="fecha-pago not-editable" type="datetime-local" value="{{$pago->created_at}}" style="font-weight: 400;" disabled="disabled">'
            ]];
            pagosArray = [...pagosArray,{
                'id'            : '{{$pago->id}}',
                'cantidad'      : '{{$pago->cantidad}}',
                'tipoPagoId'    : '{{$pago->tipo_pago_id}}',
                'fechaPago'     : '{{$pago->created_at}}',
                'tipoCambioUSD' : '{{$pago->tipo_cambio_usd}}'
            }];

            nombreTipoPagoArray = [...nombreTipoPagoArray,'{{@$pago->tipoPago->nombre}}'];
        @endforeach

    </script>
    <script src="{{ asset('js/fotoVideoSeleccionProducto/select.js') }}"></script>
    <script src="{{ asset('js/fotoVideoVenta/main.js') }}"></script>
    <script src="{{ asset('js/fotoVideoVenta/edit.js') }}"></script>
    <script src="{{ asset('js/fotoVideoVenta/ticket.js') }}"></script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">FOLIO: {{$venta->folio}}</h2>
        </div>
        <div class="az-content-header-right">
            <div class="media">
                <div class="media-body">
                    <label>Fecha de creación</label>
                    <h6>{{date_format(date_create($venta->fecha_creacion),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->
            <div class="media">
                <div class="media-body">
                    <label>Fecha de producto</label>
                    <h6>{{date_format(date_create($venta->fecha),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->

            <div class="media">
                <div class="media-body">
                    @if(count($tickets)>0)
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Reimprimir
                            </button>
                            <div class="dropdown-menu tx-13" id="lista-tickets" aria-labelledby="dropdownMenuButton" style="">
                                @foreach($tickets as $ticket)
                                    <a href="#!" class="dropdown-item" onclick="imprimirTicket({{$ticket->id}})" ticket-id="{{$ticket->id}}">{{$ticket->created_at}}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div><!-- media-body -->
            </div><!-- media -->
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="venta-form">
                            @csrf
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="col-12 mt-3">
                                        <strong>Datos del ciente</strong>
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="nombre" class="col-form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="6" value="{{@$venta->nombre_cliente}}" disabled="disabled">
                                    </div>
                                    <div class="form-group col-3 mt-0 mb-0">
                                        <label for="email" class="col-form-label">Email</label>
                                        <input type="email" name="email" class="form-control to-uppercase" autocomplete="off" tabindex="7" value="{{@$venta->email}}" disabled="disabled">
                                    </div>
                                    <div class="form-group col-3 mt-0 mb-0">
                                        <label for="rfc" class="col-form-label">RFC</label>
                                        <input type="text" name="rfc" class="form-control to-uppercase" autocomplete="off" tabindex="8" value="{{@$venta->RFC}}" disabled="disabled">
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="rfc" class="col-form-label">Dirección</label>
                                        <input type="text" name="direccion" class="form-control to-uppercase" autocomplete="off" tabindex="9" value="{{@$venta->direccion}}" disabled="disabled">
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="origen" class="col-form-label">Lugar de origen</label>
        
                                        <input list="ciudades" name="origen" class="form-control to-uppercase" tabindex="4" value="{{$venta->origen}}"  disabled="disabled"/>
                                        <datalist id="ciudades">
                                            @foreach($estados as $estado)
                                                <option value="{{$estado->nombre}}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="ventas" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Clave</th>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Costo P/P</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <strong>Pagos</strong>
                            </div>
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="pagos" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Cantidad</th>
                                                    <th>Tipo de pago</th>
                                                    <th>Fecha pago</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-8 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="usuario" class="col-form-label">Vendido por</label>
                                                <select name="usuario" class="form-control" tabindex="10">
                                                    <option value="{{$venta->usuario->id}}" usuario="{{$venta->usuario->username}}" selected="selected" disabled="disabled">
                                                        {{$venta->usuario->name}} ({{$venta->usuario->email}})
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="fotografo" class="col-form-label">Fotógrafo</label>
                                                <select name="fotografo" id="fotografo" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12" disabled="disabled">
                                                    <option value='0' selected="true" disabled="disabled">Seleccionar fotógrafo</option>
                                                    @foreach($fotografos as $fotografo)
                                                        <option value="{{$fotografo->id}}" {{$venta->comisionista_id === $fotografo->id ? 'selected="selected"' : ""}}>{{$fotografo->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" class='to-uppercase' rows="11" style="width:100%;"  disabled="disabled">{{@$venta->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0" id="detalle-venta-contenedor">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle de la venta</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total" class="col-form-label"><strong>Total:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
                                                    <div class="col-12" id="anticipo-container">
                                                        <div class="row">
                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="anticipo" class="col-form-label"><strong>Anticipo:</strong></label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="anticipo" id="anticipo" class="form-control amount not-editable height-auto to-uppercase" disabled="disabled" value="0.00">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total-recibido" class="col-form-label"><strong>Total pagado:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="total-recibido" id="total-recibido" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="resta" class="col-form-label"><strong>Resta:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="resta" id="resta" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cambio" class="col-form-label"><strong>Cambio:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cambio" id="cambio" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
                                                    <div class="col-12" id="detallePagoContainer">
                                                        <div class="row">
                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="efectivo" class="col-form-label">Efectivo M.N.:</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="efectivo" id="efectivo" class="form-control amount height-auto" value="0.00" tabindex="17">
                                                            </div>

                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="efectivo-usd" class="col-form-label">Efectivo USD.</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount height-auto" value="0.00" tabindex="18">
                                                            </div>

                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="tarjeta" class="col-form-label">Tarjeta crédito.</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="tarjeta" id="tarjeta" class="form-control amount height-auto" value="0.00" tabindex="19">
                                                            </div>

                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="deposito" class="col-form-label">Depósito / transferencia.</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="deposito" id="deposito" class="form-control amount height-auto" value="0.00" tabindex="20">
                                                            </div>

                                                            <div id="descuento-personalizado-container" class="form-group col-12 mt-0 mb-0 hidden">
                                                                <div class="row ">
                                                                    <div class="form-group col-7 mt-0 mb-0">
                                                                        <label for="descuento-personalizado" class="col-form-label">Descuento (Personalizado):</label>
                                                                    </div>
                                                                    <div class="form-group col-5 mt-0 mb-0">
                                                                        <input type="text" name="descuento-personalizado" id="descuento-personalizado" password="" limite="" class="form-control percentage height-auto" value="0" tipo='porcentaje'>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
