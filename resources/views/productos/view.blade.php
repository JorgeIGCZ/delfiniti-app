@extends('layouts.app')
@section('scripts')
    <script>
        const productoId = "{{$producto->id}}";
        $(function(){

            $('.input-daterange').datepicker({
                format: 'yyyy/mm/dd',
                language: 'es'
            }).on("change", function() {
                isFechaRangoValida();
            });

            const productosTable = new DataTable('#movimientos', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    const movimientos = document.getElementById('movimientos-form');
                    axios.post(`/inventario/getMovimientosInventario/${productoId}`,{
                        "_token"  : '{{ csrf_token() }}',
                        "fecha"   : movimientos.elements['fecha'].value,
                        "fechaInicio"  : movimientos.elements['start_date'].value,
                        "fechaFinal"  : movimientos.elements['end_date'].value
                    })
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'movimiento' },
                    { data: 'cantidad' },
                    { data: 'comentarios' },
                    { data: 'usuario' },
                    { data: 'fechaMovimiento' },
                ]
            } );
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Producto</h2>
        </div>
        <div class="az-content-header-right">
            @role('Administrador')
            <div class="media">
                <div class="media-body">
                    <a href="/inventario/{{$producto->id}}/edit" class="btn btn-secondary btn-outline-warning" style="padding-top: 2px;">Modificar inventario</a>
                </div><!-- media-body -->
            </div><!-- media -->
            @endrole
            <div class="media">
                <div class="media-body">
                    <label>Fecha de última entrada</label>
                    <h6>{{date_format(date_create($producto->ultima_entrada),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->
            <div class="media">
                <div class="media-body">
                    <label>Fecha de última salida</label>
                    <h6>{{date_format(date_create($producto->ultima_salida),"d/m/Y")}}</h6>
                </div><!-- media-body -->
            </div><!-- media -->
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p"> 
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="productos-form" action="{{route("productos.update",$producto['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$producto->clave}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$producto->codigo}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre del producto</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="1" value="{{$producto->nombre}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-1 mt-2">
                                <label for="costo" class="col-form-label">Costo</label>
                                <input type="text" name="costo" id="costo" class="form-control amount" autocomplete="off" tabindex="2" value="{{$producto->costo}}" disabled="disabled">
                            </div>
                            <div class="form-group col-1 mt-2">
                                <label for="precioVenta" class="col-form-label">Precio venta</label>
                                <input type="text" name="precioVenta" id="precioVenta" class="form-control amount" autocomplete="off" tabindex="3" value="{{$producto->precio_venta}}" disabled="disabled">  
                            </div>

                            <div class="form-group col-2 mt-2">
                                <label for="margenGanancia" class="col-form-label">Margen de ganancia</label>
                                <input type="text" name="margenGanancia" id="margenGanancia" class="form-control percentage" value="{{$producto->margen_ganancia}}" readonly="readonly">
                            </div>
                            
                            <div class="form-group col-1 mt-2">
                                <label for="stockMinimo" class="col-form-label">Stock mín</label>
                                <input type="text" name="stockMinimo" class="form-control" autocomplete="off" tabindex="4" value="{{$producto->stock_minimo}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-1 mt-2">
                                <label for="stockMistockMaximonimo" class="col-form-label">Stock máx</label>
                                <input type="text" name="stockMaximo" class="form-control" autocomplete="off" tabindex="5" value="{{$producto->stock_maximo}}" disabled="disabled">  
                            </div>

                            <div class="form-group col-4 mt-2">
                                <label for="proveedor" class="col-form-label">Proveedor</label>
                                
                                <input type="text" class="form-control" value="{{$producto->proveedor->razon_social}}" disabled="disabled">
                            </div>

                            @foreach($impuestos as $impuesto)
                                @php
                                    $isChecked = '';
                                    foreach($productoImpuestos as $productoImpuesto){
                                        if($productoImpuesto->impuesto_id == $impuesto->id){
                                            $isChecked = 'checked = checked';
                                        }
                                    }
                                @endphp
                                <div class="form-group col-1 mt-3">
                                    <label for="descuentos" class="col-form-label" style="display: block;">{{$impuesto->nombre}}</label>
                                    <input type="checkbox" name="impuestos[]" value="{{$impuesto->id}}" {{$isChecked}} class="form-control" disabled="disabled">  
                                </div>
                            @endforeach

                            <div class="form-group col-4 mt-2">
                                <label for="stockMaximo" class="col-form-label">Comentarios</label>
                                <textarea name="comentarios" class="to-uppercase" rows="5" style="width:100%;" spellcheck="false" disabled="disabled">{{@$producto->comentarios}}</textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="az-dashboard-one-title mt-4">
        <div>
            <h2 class="az-dashboard-title">Movimientos de inventario</h2>
        </div>
    </div>
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <form class="row g-3 align-items-center f-auto" id="movimientos-form" method="GET">
                <div class="form-group col-md-2">
                    <label for="fecha">Fecha Movimiento</label>
                    <select class="form-control fecha" name="fecha">
                        <option value="year" selected="selected">Año Actual</option>
                        <option value="custom">Rango</option>
                    </select>
                </div>
                <div class="form-group col-md-3" id="rango-fecha" style="display: none;">
                    <label for="fecha">Mes</label>
                    <div class="input-group input-daterange">
                        <input id="start_date" name="start_date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa"> 
                        <span class="input-group-addon" style="padding: 0 8px;align-self: center;background: none;border: none;">Al</span> 
                        <input id="end_date" name="end_date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa">
                    </div>
                </div>
            </form>
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="movimientos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Movimiento</th>
                                        <th>Cantidad</th>
                                        <th>Comentarios</th>
                                        <th>Usuario</th>
                                        <th>Fecha Movimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection