@extends('layouts.app')
@section('scripts')
    <script>
        let productosArray = [];
        let productosTableArray = [];

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
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Editar Pedido</h2>
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
