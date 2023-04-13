@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            const inventario = {{$producto->stock}};
            document.getElementById('accion').addEventListener('change', (event) =>{
                const numeroProductos = document.getElementById('numeroProductos');
                if(event.target.value == 'baja'){
                    numeroProductos.setAttribute('max',inventario);
                    return;
                }
                numeroProductos.removeAttribute('max');
            });

        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Movimientos de inventario</h2>
        </div>
        <div class="az-content-header-right">
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
                        <form method="POST" class="row g-3 align-items-center f-auto" id="inventario-form" action="{{route("inventario.update",$producto['id'])}}">
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
                                <input type="text" name="nombre" class="form-control" value="{{$producto->nombre}}" disabled="disabled">  
                            </div>

                            <div class="form-group col-2 mt-2">
                                <label for="accion" class="col-form-label">Movimiento</label>
                                <select name="accion" id="accion" class="form-control" data-show-subtext="true" tabindex="1">
                                    <option value="alta" selected="selected">Alta</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-1 mt-2">
                                <label for="numeroProductos" class="col-form-label">Productos</label>
                                <input type="number" name="numeroProductos" id="numeroProductos" class="form-control" min="1" tabindex="2"  value="1" required="required">  
                            </div>

                            <div class="form-group col-5 mt-2">
                                <label for="stockMaximo" class="col-form-label">Comentarios</label>
                                <textarea name="comentarios" class="to-uppercase" rows="3" style="width:100%;" spellcheck="false" required="required"></textarea>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-inventario" tabindex="6">Actualizar inventario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection