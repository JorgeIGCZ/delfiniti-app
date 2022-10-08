@extends('layouts.app')
@section('scripts')
    <script>
        const env = 'create';
        const dolarPrecioCompra = () =>{
            return  {{$dolarPrecio->precio_compra}};
        }
        const dolarPrecioVenta = () =>{
            return  {{$dolarPrecio->precio_venta}};
        }
        const isAdmin = () => {
            return {{Auth::user()->hasRole('Administrador') ? 1 : 0}};
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
    </script>
    <script src="{{ asset('js/reservaciones/create.js') }}"></script>
    <script src="{{ asset('js/reservaciones/main.js') }}"></script>
    <script src="{{ asset('js/reservaciones/ticket.js') }}"></script>
@endsection
@section('content')
    <div class="modal fade" id="alojamiento-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-m" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h6 class="modal-title">Nuevo Alojamiento</h6>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            </div>
            <div class="modal-body">
                <form class="row g-3 align-items-center f-auto" id="alojamientos-form">
                    <div class="form-group col-7 mt-3">
                        <label for="nombre" class="col-form-label">Nombre del alojamiento</label>    
                        <input type="text" name="nombre" id="nombre-alojamiento" class="form-control to-uppercase" required="required">  
                    </div>
                    <div class="form-group col-5 mt-3">
                        <button class="btn btn-info btn-block mt-33" id="crear-alojamiento">Crear alojamiento</button>
                    </div>
                </form>
            </div>
        </div>
        </div><!-- modal-dialog -->
    </div>
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
            <h2 class="az-dashboard-title">Nueva Reserva</h2>
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
                                <strong>Datos del ciente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="nombre" class="col-form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control to-uppercase" required="required" autocomplete="off" tabindex="1" value="{{@$reservacion->nombre_cliente}}">
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>
                                <input type="email" name="email" class="form-control to-uppercase" autocomplete="off" tabindex="2" value="{{@$reservacion->email}}">
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="alojamiento" class="col-form-label">Hotel
                                    @can('Reservaciones.create')
                                        <button id="add-alojamiento" class="btn btn-info to-uppercase" data-bs-toggle="modal" data-bs-target="#alojamiento-modal" style="
                                            height: 20px;
                                            min-height: 20px;
                                            font-size: 8px;
                                        ">+</button>
                                    @endcan
                                </label>
                                <select name="alojamiento" id="alojamiento" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="3">
                                    <option value='0' selected="true">Seleccionar hotel</option>
                                    @foreach($alojamientos as $alojamiento)
                                        <option value="{{$alojamiento->id}}" {{@$reservacion->alojamiento == $alojamiento->id ? 'selected="selected' : ""}} >{{$alojamiento->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>

                                <input list="ciudades" name="origen" class="form-control to-uppercase" tabindex="4" value="{{@$reservacion->origen}}"/>
                                <datalist id="ciudades">
                                    @foreach($estados as $estado)
                                        <option value="{{$estado->nombre}}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="clave" class="col-form-label">Clave</label>
                                <select id="clave-actividad" name="clave" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="5">
                                </select>
                            </div>
                            <div class="form-group col-3 mt-0 mb-0">
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
                                <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off" tabindex="8">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="disponibilidad" class="col-form-label">Disponibilidad</label>
                                <input type="number" name="disponibilidad" id="disponibilidad" class="form-control" value="0" disabled="disabled" >
                            </div>
                            <input type="hidden" name="precio" id="precio" value="0">
                            <div class="form-group col-1 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="add-actividad" tabindex="10">+</button>
                            </div>

                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label"><strong>Fecha</strong></label>
                                <input type="date" name="fecha" id="fecha" class="form-control to-uppercase" value="{{date('Y-m-d')}}"  @if(!Auth::user()->hasRole('Administrador')) min="{{date('Y-m-d')}}" @endif  autocomplete="off" tabindex="9">
                            </div>
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
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
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-8 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="agente" class="col-form-label">Reservado por</label>
                                                <select name="agente" class="form-control" tabindex="11">
                                                    <option value="{{Auth::user()->id}}" usuario="{{Auth::user()->username}}" selected="selected" disabled="disabled">
                                                        {{Auth::user()->name}} ({{Auth::user()->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" id="comisionista" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    @foreach($comisionistas as $comisionista)
                                                        <option value="{{$comisionista->id}}" cuponDescuento="{{$comisionista->descuentos}}">{{$comisionista->nombre}} ({{$comisionista->tipo->nombre}})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="cerrador" class="col-form-label">Cerrador</label>
                                                <select name="cerrador" id="cerrador" class="form-control" data-show-subtext="true" data-live-search="true" tabindex="12">
                                                    <option value='0' selected="true">Seleccionar cerrador</option>
                                                    @foreach($cerradores as $cerrador)
                                                        <option value="{{$cerrador->id}}" >{{$cerrador->nombre}}</option>
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
                                                        <option value="{{$comisionistaActividad->id}}" >{{$comisionistaActividad->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-2 mt-0 mb-0">
                                                <label for="add-descuento-personalizado" class="col-form-label">Agregar descuento</label>
                                                <input type="checkbox" name="add-descuento-personalizado" id="add-descuento-personalizado" class="form-control" style="display: block;" tabindex="15">
                                            </div>

                                            <div class="form-group col-2"> 
                                                <label for="comisionable" class="col-form-label">Comisionable</label>    
                                                <input type="checkbox" name="comisionable" class="form-control" checked="checked"  style="display: block;" tabindex="16">
                                            </div>
                                            
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" class='to-uppercase' rows="5" style="width:100%;">{{@$reservacion->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
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

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total-recibido" class="col-form-label"><strong>Total recibido:</strong></label>
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

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="efectivo" class="col-form-label">Efectivo M.N.:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="efectivo" id="efectivo" class="form-control amount height-auto" value="0.00" tabindex="17">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="efectivo-usd" class="col-form-label">Efectivo USD:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount height-auto" value="0.00" tabindex="18">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="tarjeta" class="col-form-label">Tarjeta crédito:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="tarjeta" id="tarjeta" class="form-control amount height-auto" value="0.00" tabindex="19">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="deposito" class="col-form-label">Depósito / transferencia:</label>
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
                                                                <input type="text" name="descuento-codigo" id="descuento-codigo" password="" class="form-control not-editable height-auto" disabled="disabled" value="0" >
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

                                                    <!--div class="form-group col-7 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount" value="0.00" disabled="disabled">
                                                    </div-->
                                                    <div class="form-group col-12 mt-0 mb-0">
                                                        <button class="btn btn-info btn-block mt-3" id="pagar-reservar" disabled="disabled" tabindex="21">Pagar y reservar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="reservar" disabled="disabled" tabindex="22">Reservar</button>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="mt-33 btn btn-gray-700 btn-block" id="cancelar" tabindex="23">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
