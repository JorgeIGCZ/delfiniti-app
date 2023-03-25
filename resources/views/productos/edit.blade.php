@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Producto</h2>
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
                                <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="1" value="{{$producto->nombre}}" required="required">  
                            </div>
                            <div class="form-group col-2 mt-2">
                                <label for="costo" class="col-form-label">Costo</label>
                                <input type="text" name="costo" class="form-control amount" autocomplete="off" tabindex="2" value="{{$producto->costo}}">
                            </div>
                            <div class="form-group col-2 mt-2">
                                <label for="precioVenta" class="col-form-label">Precio venta</label>
                                <input type="text" name="precioVenta" class="form-control amount" autocomplete="off" tabindex="3" value="{{$producto->precio_venta}}" required="required">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-producto" tabindex="4">Actualizar producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection