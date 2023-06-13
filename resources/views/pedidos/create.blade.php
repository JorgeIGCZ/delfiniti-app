@extends('layouts.app')
@section('scripts')
    <script>
        const modulo = 'pedidos';
        const env = 'create';
        const allProductos = @php echo(json_encode($productos)) @endphp;
        const logo = () =>{
            return '{{asset("assets/img/logo.png")}}';
        }
        const isAdmin = () => {
            return {{Auth::user()->hasRole('Administrador') ? 1 : 0}};
        }
        const detallePedido = () =>{
            const pedido   = document.getElementById('pedido-form');
            const proovedor = pedido.elements['proveedor'].html;

            return {
                'cajero'    : '{{Auth::user()->username}}',
                'cliente'   : proovedor
            };
        }
    </script>

    <script src="{{ asset('js/tiendaSeleccionProducto/select.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/main.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/create.js') }}"></script>
    <script src="{{ asset('js/tiendaPedido/ticket.js') }}"></script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Nuevo Pedido</h2>
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
                                                <option value="{{$proveedor->id}}">{{$proveedor->razon_social}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="fecha" class="col-form-label"><strong>Fecha de pedido</strong></label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" value="{{date('Y-m-d')}}" autocomplete="off" tabindex="2">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-2 mt-0 mb-0">
                                        <label for="codigo" class="col-form-label">Codigo</label>

                                        <input list="codigos-list" name="codigo" id="codigo" class="form-control to-uppercase" tabindex="1" autocomplete="off"/>
                                        <datalist id="codigos-list">
                                            {{-- @foreach($productos as $producto)
                                                <option data-value="{{$producto['nombre']}}" data-id="{{$producto['id']}}" value="{{$producto['codigo']}}">
                                            @endforeach --}}
                                        </datalist>
                                    </div>
                                    <div class="form-group col-8 mt-0 mb-0">
                                        <label for="actividad" class="col-form-label">Producto</label>
                                        <input list="productos-list" name="productos" id="productos" class="form-control to-uppercase" tabindex="2" value="{{@$pedido->producto}}" autocomplete="off"/>
                                        <datalist id="productos-list">
                                            {{-- @foreach($productos as $producto)
                                                <option data-codigo="{{$producto['codigo']}}" data-id="{{$producto['id']}}" value="{{$producto['nombre']}}">
                                            @endforeach --}}
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
                                                <textarea name="comentarios" class='to-uppercase' rows="10" style="width:100%;">{{@$pedido->comentarios}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-4 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
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

                                                    <div class="form-group col-12 mt-0 mb-0">
                                                        <button class="btn btn-info btn-block mt-3" id="guardar" disabled="disabled" tabindex="16">Guardar</button>
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
