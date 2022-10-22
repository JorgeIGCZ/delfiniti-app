@extends('layouts.app')
@section('scripts')
    <script>
        const env = 'edit';
        const accion = '{{ (@$_GET["accion"] === "pago" ? "pago" : "edit"); }}';
        const reservacionId = () => {
            return {{$reservacion->id}};
        }
        const isReservacionPagada = () => {
            return {{$reservacion->estatus_pago == 2 ? 1 : 0}};
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
            return {{Auth::user()->can('Reservaciones.update') ? 1 : 0}};
        }
        const userEmail = () =>{
            return  '{{Auth::user()->email}}';
        }
        const logo = () =>{
            return '{{asset("assets/img/logo.png")}}';
        }
        const detalleReservacion = () =>{ 
            const reservacion   = document.getElementById('reservacion-form');
            const nombreCliente = reservacion.elements['nombre'].value;
            const direccion     = reservacion.elements['alojamiento'].value;
            const ciudad        = reservacion.elements['origen'].value;

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

        let actvidadesArray         = [];
        let reservacionesTableArray = [];
        let pagosArray              = [];
        let pagosTablaArray         = [];
        let nombreTipoPagoArray     = [];
        let cantidadPagada          = 0;

        @forEach($reservacion->reservacionDetalle as $detalle)
            reservacionesTableArray = [...reservacionesTableArray,[
                '{{$detalle->actividad->clave}}',
                '{{$detalle->actividad->nombre}}',
                '{{$detalle->horario->horario_inicial}}',
                '{{$detalle->numero_personas}}',
                formatter.format('{{$detalle->PPU}}'),
                formatter.format('{{$detalle->PPU}}'*'{{$detalle->numero_personas}}'),
                (canEdit && ((accion !== 'pago' && {{ ($reservacion->estatus_pago !== 2) ? 1 : 0 }}) || isAdmin) 
                    ?  `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>` 
                    : '')
            ]];
            actvidadesArray = [...actvidadesArray,{
                'claveActividad': '{{$detalle->actividad->clave}}',
                'actividadDetalle' : '{{$detalle->actividad->nombre}}',
                'actividad'     : '{{$detalle->actividad_id}}',
                'cantidad'      : '{{$detalle->numero_personas}}',
                'precio'        : '{{$detalle->PPU}}',
                'horario'       : '{{$detalle->actividad_horario_id}}'
            }];
        @endforeach
        
        let eliminar = '';
        let editar   = '';
        let accionesArray = [];
        @forEach($reservacion->pagos as $pago)
            accionesArray = [];
            accionesArray.push(isAdmin
                    ?  `<a href="#!" class='editar-celda'>Editar</a>` 
                    : '');
            accionesArray.push(canEdit && ((accion === 'edit' && ![1,2,3,8].includes({{$pago->tipo_pago_id}})) || isAdmin) 
                    ?  `<a href="#!" class='eliminar-celda'>Eliminar</a>` 
                    : '');

            pagosTablaArray = [...pagosTablaArray,[
                '{{$pago->id}}',
                ('{{$pago->tipo_pago_id}}' == '2' ? `${formatter.format('{{$pago->cantidad}}')} USD * ${'{{$pago->tipo_cambio_usd}}'}` : formatter.format('{{$pago->cantidad}}')),
                '{{@$pago->tipoPago->nombre}} {{@$pago->descuentoCodigo->nombre}}',
                '<input class="fecha-pago not-editable" type="datetime-local" value="{{$pago->created_at}}" style="font-weight: 400;">',
                accionesArray.join(" | ")
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

    <script src="{{ asset('js/reservaciones/main.js') }}"></script>
    <script src="{{ asset('js/reservaciones/edit.js') }}"></script>
    <script src="{{ asset('js/reservaciones/ticket.js') }}"></script>
@endsection
@section('content')
    <div class="modal fade" id="verificacion-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h6 class="modal-title">Verificación</h6>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-12 mt-0 mb-0">
                    <label for="password" class="col-form-label">Contraseña</label>
                    <input type="password" id="password" class="form-control">
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button id="validar-verificacion" action="" class="btn btn-info btn-block form-control">Aplicar</button>
            </div>
        </div>
        </div><!-- modal-dialog -->
    </div>
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">FOLIO: {{$reservacion->folio}}</h2>
        </div>
        <div class="az-content-header-right">
            <div class="media">
                <div class="media-body">
                    <label>Fecha de creación</label>
                    <h6>{{date_format(date_create($reservacion->fecha_creacion),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->
            <div class="media">
                <div class="media-body">
                    <label>Fecha de actividad</label>
                    <h6>{{date_format(date_create($reservacion->fecha),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->

            @can('Reservaciones.create')
                <div class="media">
                    <div class="media-body">
                        <a href="{{ url('reservaciones/create/'.$reservacion->id) }}" class="btn btn-secondary btn-outline-warning" style="padding-top: 2px;">Clonar</a>
                    </div>
                </div>
            @endcan

            @if($reservacion->estatus_pago !== 2 && @$_GET["accion"] !== "pago")
                <div class="media">
                    <div class="media-body">
                        <a href="{{ url('reservaciones/'.$reservacion->id.'/edit?accion=pago#detalle-reservacion-contenedor') }}" class="btn btn-secondary btn-block" style="padding-top: 5px;">Pagar</a>
                    </div>
                </div>
            @endif
            
            @can('Reservaciones.update')
                @if(@$_GET["accion"] === "pago")
                    <div class="media">
                        <div class="media-body">
                            <a href="{{ url('reservaciones/'.$reservacion->id.'/edit?accion=edit') }}"  class="btn btn-secondary btn-block" style="padding-top: 5px;">Editar</a>
                        </div>
                    </div>
                @endif
            @endcan

            @can('Reservaciones.cancel')
                @if($reservacion->estatus)
                    <div class="media">
                        <div class="media-body">
                            <button class="btn btn-danger" id="actualizar-estatus-reservacion" accion='cancelar'>Cancelar</button>
                        </div>
                    </div>
                @else
                    <div class="media">
                        <div class="media-body">
                            <button class="btn btn-success" id="actualizar-estatus-reservacion" accion='reactivar'>Activar reservación</button>
                        </div>
                    </div>
                @endif
            @endcan
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
                        <form class="row g-3 align-items-center f-auto" id="reservacion-form">
                            @csrf
                            <div class="col-12 mt-3">
                                <strong>Datos del cliente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="nombre" class="col-form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control to-uppercase" required="required" autocomplete="off" tabindex="1" value="{{$reservacion->nombre_cliente}}">
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>
                                <input type="email" name="email" class="form-control to-uppercase" autocomplete="off" tabindex="2">
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="alojamiento" class="col-form-label">Hotel</label>
                                <select name="alojamiento" class="form-control to-uppercase" data-show-subtext="true" data-live-search="true" tabindex="3" value="{{$reservacion->email}}">
                                    <option value='0' selected="true">Seleccionar hotel</option>
                                    @foreach($alojamientos as $alojamiento)
                                        <option value="{{$alojamiento->id}}" {{$reservacion->alojamiento == $alojamiento->id ? 'selected="selected' : ""}} >{{$alojamiento->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>

                                <input list="ciudades" name="origen" class="form-control to-uppercase" tabindex="4" value="{{$reservacion->origen}}"/>
                                <datalist id="ciudades">
                                    @foreach($estados as $estado)
                                        <option value="{{$estado->nombre}}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div id="actividad-container" class="form-group col-9 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="clave" class="col-form-label">Clave</label>
                                        <select id="clave-actividad" name="clave" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="5">
                                        </select>
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="actividad" class="col-form-label">Actividad</label>
                                        <select name="actividad" id="actividades"  class="form-control" data-show-subtext="true" data-live-search="true" tabindex="6">
                                        </select>
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="horario" class="col-form-label">Horario</label>
                                        <select name="horario" id="horarios" class="form-control" tabindex="7">
                                        </select>
                                    </div>
                                    <div class="form-group col-1 mt-0 mb-0">
                                        <label for="cantidad" class="col-form-label">Cantidad</label>
                                        <input type="number" name="cantidad" id="cantidad" class="form-control" value="1"  max="200" autocomplete="off" tabindex="8">
                                    </div>
                                    <div class="form-group col-1 mt-0 mb-0">
                                        <label for="disponibilidad" class="col-form-label">Disp.</label>
                                        <input type="text" name="disponibilidad" id="disponibilidad" class="form-control" value="0" disabled="disabled" >
                                    </div>
                                </div>
                            </div>


                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="{{date_format(date_create($reservacion->fecha),'Y-m-d')}}" required="required" autocomplete="off" tabindex="9">
                            </div>
                            <input type="hidden" name="precio" id="precio" value="0">
                            @can('Reservaciones.update') 
                                <div class="form-group col-1 mt-0 mb-0">
                                    <button class="btn btn-info btn-block mt-33" id="add-actividad" tabindex="10">+</button>
                                </div>
                            @endcan
                            <div class="form-group col-12 mt-8 mb-2 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="reservaciones" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Clave</th>
                                                    <th>Actividad</th>
                                                    <th>Horario</th>
                                                    <th>Cantidad</th>
                                                    <th>Costo P/P</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
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
                                                    <th>Acciones</th>
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
                                                <label for="agente" class="col-form-label">Reservado por</label>
                                                <select name="agente" class="form-control" tabindex="11">
                                                    <option value="{{$reservacion->agente->id}}" usuario="{{$reservacion->agente->username}}" selected="selected" disabled="disabled">
                                                        {{$reservacion->agente->name}} ({{$reservacion->agente->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" id="comisionista" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistas as $comisionista)
                                                        <option value="{{$comisionista->id}}" cuponDescuento="{{$comisionista->descuentos}}" {{$reservacion->comisionista_id === $comisionista->id ? 'selected="selected' : ""}}>{{$comisionista->nombre}} ({{$comisionista->tipo->nombre}})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="cerrador" class="col-form-label">Cerrador</label>
                                                <select name="cerrador" id="cerrador" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar cerrador</option>
                                                    @foreach($cerradores as $cerrador)
                                                        <option value="{{$cerrador->id}}" {{$reservacion->cerrador_id === $cerrador->id ? 'selected="selected' : ""}}>{{$cerrador->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-4 mt-0 mb-0">
                                                <label for="codigo-descuento" class="col-form-label">Código descuento</label>
                                                <div class="input-button">
                                                    <select name="codigo-descuento" id="codigo-descuento" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="13">
                                                        <option value='0' selected="true">Seleccionar codigo</option>
                                                        @foreach($descuentosCodigo as $descuentoCodigo)
                                                            <option value="{{$descuentoCodigo->id}}" >{{$descuentoCodigo->nombre}}</option>
                                                        @endforeach
                                                    </select>
                                                    <button id="add-codigo-descuento" class="btn btn-info btn-block form-control" data-bs-toggle="modal" data-bs-target="#verificacion-modal">verificar</button>
                                                </div>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista-actividad" class="col-form-label">Comisionista actividad</label>
                                                <select name="comisionista-actividad" id="comisionista-actividad" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="14">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistasActividad as $comisionistaActividad)
                                                        <option value="{{$comisionistaActividad->id}}" {{$reservacion->comisionista_actividad_id === $comisionistaActividad->id ? 'selected="selected' : ""}}>{{$comisionistaActividad->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-2 mt-0 mb-0">
                                                <label for="add-descuento-personalizado" class="col-form-label">Agregar descuento</label>
                                                <input type="checkbox" name="add-descuento-personalizado" id="add-descuento-personalizado" class="form-control" style="display: block;" tabindex="15">
                                            </div>

                                            <div class="form-group col-2"> 
                                                <label for="comisionable" class="col-form-label">Comisionable</label>
                                                <input type="checkbox" name="comisionable" id="comisionable" class="form-control" style="display: block;" @if($reservacion->comisionable) checked="checked" @endif tabindex="16">
                                            </div>

                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" class='to-uppercase' rows="5" style="width:100%;">{{$reservacion->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0" id="detalle-reservacion-contenedor">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle de la reservación</strong>
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

                                                            <div class="form-group col-7 mt-0 mb-0">
                                                                <label for="cupon" class="col-form-label">Cupón:</label>
                                                            </div>
                                                            <div class="form-group col-5 mt-0 mb-0">
                                                                <input type="text" name="cupon" id="cupon" class="form-control amount height-auto" value="0" disabled="disabled" tipo='cantidad'>
                                                            </div>

                                                            <div id="descuento-codigo-container" class="form-group col-12 mt-0 mb-0 hidden">
                                                                <div class="row ">
                                                                    <div class="form-group col-7 mt-0 mb-0">
                                                                        <label for="descuento-codigo" class="col-form-label">Descuento (Código):</label>
                                                                    </div>
                                                                    <div class="form-group col-5 mt-0 mb-0">
                                                                        <input type="text" name="descuento-codigo" id="descuento-codigo" password="" class="form-control percentage not-editable height-auto" disabled="disabled" value="0" tipo='porcentaje'>
                                                                    </div>
                                                                </div>
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

                                                    <!--div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount" value="0.00" disabled="disabled">
                                                    </div-->



                                                    @can('Reservaciones.update') 
                                                        @if($reservacion->estatus_pago !== 2)
                                                            <div class="form-group col-12 mt-0 mb-0">
                                                                <button class="btn btn-info btn-block" id="pagar" disabled="disabled" tabindex="20">Pagar</button>
                                                            </div>
                                                        @else
                                                            @role('Administrador')
                                                                <div class="form-group col-12 mt-0 mb-0">
                                                                    <button class="btn btn-info btn-block" id="pagar" disabled="disabled" tabindex="20">Pagar</button>
                                                                </div>
                                                            @endrole
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @can('Reservaciones.update') 
                                        @if($reservacion->estatus_pago !== 2)
                                            <div class="form-group col-2 mt-0 mb-0">
                                                <button class="btn btn-info btn-block mt-33" id="actualizar" tabindex="21">Actualizar</button>
                                            </div>
                                        @else
                                            @role('Administrador')
                                                <div class="form-group col-2 mt-0 mb-0">
                                                    <button class="btn btn-info btn-block mt-33" id="actualizar" tabindex="21">Actualizar</button>
                                                </div>
                                            @endrole
                                        @endif
                                    @endcan

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
