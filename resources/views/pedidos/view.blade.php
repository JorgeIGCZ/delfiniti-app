@extends('layouts.app')
@section('scripts')
    <script>
        let productosArray = [];
        let productosTableArray = [];
        const pedidoId = () => {
            return {{$pedido->id}};
        }
        const userEmail = () =>{
            return  '{{Auth::user()->email}}';
        }

        @forEach($pedido->pedidoDetalle as $detalle)
            productosTableArray = [...productosTableArray,[
                '{{$detalle->producto->clave}}',
                '{{$detalle->producto->nombre}}',
                '{{$detalle->cantidad}}',
                formatter.format('{{$detalle->PPU}}'),
                formatter.format('{{$detalle->PPU}}'*'{{$detalle->cantidad}}')
            ]];
            productosArray = [...productosArray,{
                'claveProducto'   : '{{$detalle->producto->clave}}',
                'productoDetalle' : '{{$detalle->producto->nombre}}',
                'productoId'      : '{{$detalle->producto_id}}',
                'cantidad'        : '{{$detalle->cantidad}}',
                'costo'           : '{{$detalle->PPU}}'
            }];
        @endforeach
    </script>
    <script src="{{ asset('js/tiendaPedido/main.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/view.js') }}"></script>
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
            <h2 class="az-dashboard-title">Pedido #{{$pedido->id}}</h2>
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
                                        <label for="proveedor" class="col-form-label">
                                            <strong>Proveedor: </strong>
                                        </label>
                                        {{$pedido->proveedor->razon_social}}
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="fecha" class="col-form-label">
                                            <strong>Fecha de pedido: </strong>
                                        </label>
                                        {{date_format(date_create($pedido->fecha),'Y-m-d')}}
                                    </div>
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
                                                <label for="comentarios" class="col-form-label">
                                                    <strong>Comentarios: </strong>
                                                </label>
                                                <p>{{@$pedido->comentarios}}<p>
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
                                                        <input type="text" name="subtotal" id="subtotal" class="form-control amount not-editable height-auto" disabled="disabled" value="{{$subtotal}}">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="iva" class="col-form-label"><strong>I.V.A.:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="iva" id="iva" class="form-control amount not-editable height-auto" disabled="disabled" value="?">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="descuento" class="col-form-label"><strong>Descuento:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="descuento" id="descuento" class="form-control amount not-editable height-auto" disabled="disabled" value="?">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="ieps" class="col-form-label"><strong>IEPS:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="ieps" id="ieps" class="form-control amount not-editable height-auto" disabled="disabled" value="?">
                                                    </div>

                                                    <div class="form-group col-7 mt-0 mb-0">
                                                        <label for="total" class="col-form-label"><strong>Total:</strong></label>
                                                    </div>
                                                    <div class="form-group col-5 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount not-editable height-auto" disabled="disabled" value="{{$total}}">
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
