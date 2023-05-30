@extends('layouts.app')
@section('scripts')
    <script>
        const modulo = 'pedidos';
        const env = 'edit';
        const accion = '{{ (@$_GET["accion"] === "pago" ? "pago" : "edit"); }}';

        const pedidoId = () => {
            return {{$pedido->id}};
        }
        const logo = () =>{
            return '{{asset("assets/img/logo.png")}}';
        }
        const canEdit = () => {
            return {{Auth::user()->can('TiendaPedidos.update') ? 1 : 0}};
        }
        const allProductos = @php echo(json_encode($productos)) @endphp;
        const isAdmin = () => {
            return {{Auth::user()->hasRole('Administrador') ? 1 : 0}};
        }
        const userEmail = () =>{
            return  '{{Auth::user()->email}}';
        }

        let productosArray    = [];
        let productosTableArray = [];

        @forEach($pedido->pedidoDetalle as $detalle)
            productosTableArray = [...productosTableArray,[
                '{{$detalle->producto->clave}}',
                '{{$detalle->producto->nombre}}',
                '{{$detalle->cantidad}}',
                formatter.format('{{$detalle->PPU}}'),
                formatter.format('{{$detalle->PPU}}'*'{{$detalle->cantidad}}'),
                (canEdit() || isAdmin() 
                    ?  `<a href="#!" class='eliminar-celda' class='eliminar'>Eliminar</a>` 
                    : '')
            ]];
            productosArray = [...productosArray,{
                'codigoProducto'  : '{{$detalle->producto->codigo}}',
                'claveProducto'   : '{{$detalle->producto->clave}}',
                'productoDetalle' : '{{$detalle->producto->nombre}}',
                'productoId'      : '{{$detalle->producto_id}}',
                'cantidad'        : '{{$detalle->cantidad}}',
                'costo'           : '{{$detalle->PPU}}'
            }];
        @endforeach

    </script>
    <script src="{{ asset('js/tiendaSeleccionProducto/select.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/main.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/edit.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/ticket.js') }}"></script>
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
            <h2 class="az-dashboard-title">Editar Pedido</h2>   
        </div>
        <div class="az-content-header-right">
            @can('TiendaPedidos.cancel')
                @if($pedido->estatus)
                    <div class="media">
                        <div class="media-body">
                            <button class="btn btn-danger" id="actualizar-estatus-pedido" accion='cancelar'>Cancelar</button>
                        </div>
                    </div>
                @else
                    <div class="media">
                        <div class="media-body">
                            <button class="btn btn-success" id="actualizar-estatus-pedido" accion='reactivar'>Activar pedido</button>
                        </div>
                    </div>
                @endif
            @endcan  
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="pedido-form">
                            @csrf
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-10 mt-0 mb-0">
                                        <label for="proveedor" class="col-form-label">Proveedor</label>
                                        <select name="proveedor" id="proveedor" class="form-control" tabindex="1">
                                            @foreach($proveedores as $proveedor)
                                                <option value="{{$proveedor->id}}" {{$pedido->proveedor_id == $proveedor->id ? 'selected="selected"' : ""}}>{{$proveedor->razon_social}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="fecha" class="col-form-label"><strong>Fecha de pedido</strong></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{date_format(date_create($pedido->fecha),'Y-m-d')}}"  autocomplete="off" tabindex="2">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="codigo" class="col-form-label">Codigo</label>

                                        <input list="codigos-list" name="codigo" id="codigo" class="form-control to-uppercase" tabindex="1" autocomplete="off"/>
                                        <datalist id="codigos-list">
                                            @foreach($productos as $producto)
                                                <option data-value="{{$producto['nombre']}}" data-id="{{$producto['id']}}" value="{{$producto['codigo']}}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="form-group col-8 mt-0 mb-0">
                                        <label for="actividad" class="col-form-label">Producto</label>
                                        <input list="productos-list" name="productos" id="productos" class="form-control to-uppercase" tabindex="2" value="{{@$pedido->producto}}" autocomplete="off"/>
                                        <datalist id="productos-list">
                                            @foreach($productos as $producto)
                                                <option data-codigo="{{$producto['codigo']}}" data-id="{{$producto['id']}}" value="{{$producto['nombre']}}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="form-group col-1 mt-0 mb-0">
                                        <label for="cantidad" class="col-form-label">Cantidad</label>
                                        <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off" tabindex="3">
                                    </div>
                                    <div class="form-group col-1 mt-1 mb-0">
                                        <button class="btn btn-info btn-block mt-33" id="add-producto" tabindex="10">+</button>
                                    </div>

                                    <input type="hidden" name="costo" id="costo" value="0">
                                    <input type="hidden" name="clave" id="clave" value="0">
                                    <input type="hidden" name="producto-id" id="producto-id" value="0">
                                </div>
                            </div>
                            
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="productosTable" class="display" style="width:100%">
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
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" class='to-uppercase' rows="7" style="width:100%;">{{@$pedido->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0" id="detalle-pedido-contenedor">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle del pedido</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="subtotal" class="col-form-label"><strong>Subtotal:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="subtotal" id="subtotal" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="iva" class="col-form-label"><strong>I.V.A.:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="iva" id="iva" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="descuento" class="col-form-label"><strong>Descuento:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="descuento" id="descuento" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="ieps" class="col-form-label"><strong>IEPS:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="ieps" id="ieps" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total" class="col-form-label"><strong>Total:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount not-editable height-auto" disabled="disabled" value="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="actualizar" tabindex="17">Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
