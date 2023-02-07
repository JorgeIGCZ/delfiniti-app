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
        const detalleVenta = () =>{
            const venta   = document.getElementById('venta-form');
            const nombreCliente = venta.elements['nombre'].value;
            const direccion     = venta.elements['alojamiento'].value;
            const ciudad        = venta.elements['origen'].value;

            return {
                'cajero'    : '{{Auth::user()->username}}',
                'cliente'   : nombreCliente,
                'dirección' : direccion,
                'ciudad'    : ciudad
            };
        }
    </script>
    <script src="{{ asset('js/ventas/create.js') }}"></script>
    <script src="{{ asset('js/ventas/main.js') }}"></script>
    <script src="{{ asset('js/ventas/ticket.js') }}"></script>
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
            <h2 class="az-dashboard-title">Nueva Venta</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="venta-form">
                            @csrf
                            <div class="form-group col-6 mt-0 mb-0">
                                <div class="row">
                                    <div class="col-12 mt-3">
                                        <strong>Datos de la venta</strong>
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="clave" class="col-form-label">Clave</label>
                                        <input type="text" name="clave" id="clave" class="form-control" tabindex="1">
                                    </div>
                                    <div class="form-group col-10 mt-0 mb-0">
                                        <label for="actividad" class="col-form-label">Producto</label>
                                        <input list="productos" name="origen" class="form-control to-uppercase" tabindex="2" value="{{@$venta->prducto}}"/>
                                        <datalist id="productos">
                                            @foreach($estados as $estado)
                                                <option value="{{$estado->nombre}}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="cantidad" class="col-form-label">Cantidad</label>
                                        <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off" tabindex="3">
                                    </div>
                                    <div class="form-group col-3 mt-0 mb-0">
                                        <label for="fecha" class="col-form-label"><strong>Fecha</strong></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control to-uppercase" value="{{date('Y-m-d')}}"  @if(!Auth::user()->hasRole('Administrador')) min="{{date('Y-m-d')}}" @endif  autocomplete="off" tabindex="4">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <div class="row">
                                    <div class="col-12 mt-3">
                                        <strong>Datos del ciente</strong>
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="nombre" class="col-form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control to-uppercase" required="required" autocomplete="off" tabindex="6" value="{{@$venta->nombre_cliente}}">
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="email" class="col-form-label">Email</label>
                                        <input type="email" name="email" class="form-control to-uppercase" autocomplete="off" tabindex="7" value="{{@$venta->email}}">
                                    </div>
                                    
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="rfc" class="col-form-label">RFC</label>
                                        <input type="text" name="rfc" class="form-control to-uppercase" autocomplete="off" tabindex="8" value="{{@$venta->rfc}}">
                                    </div>
                                    <div class="form-group col-6 mt-0 mb-0">
                                        <label for="origen" class="col-form-label">Lugar de origen</label>

                                        <input list="ciudades" name="origen" class="form-control to-uppercase" tabindex="9" value="{{@$venta->origen}}"/>
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
                                                <label for="agente" class="col-form-label">Vendido por</label>
                                                <select name="agente" class="form-control" tabindex="10">
                                                    <option value="{{Auth::user()->id}}" usuario="{{Auth::user()->username}}" selected="selected" disabled="disabled">
                                                        {{Auth::user()->name}} ({{Auth::user()->email}})
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" class='to-uppercase' rows="11" style="width:100%;">{{@$venta->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
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
                                                        <input type="text" name="efectivo" id="efectivo" class="form-control amount height-auto" value="0.00" tabindex="12">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="efectivo-usd" class="col-form-label">Efectivo USD:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount height-auto" value="0.00" tabindex="13">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="tarjeta" class="col-form-label">Tarjeta crédito:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="tarjeta" id="tarjeta" class="form-control amount height-auto" value="0.00" tabindex="14">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="deposito" class="col-form-label">Depósito / transferencia:</label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="deposito" id="deposito" class="form-control amount height-auto" value="0.00" tabindex="15">
                                                    </div>

                                                    <div class="form-group col-12 mt-0 mb-0">
                                                        <button class="btn btn-info btn-block mt-3" id="pagar" disabled="disabled" tabindex="16">Pagar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="mt-33 btn btn-gray-700 btn-block" id="cancelar" tabindex="17">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
