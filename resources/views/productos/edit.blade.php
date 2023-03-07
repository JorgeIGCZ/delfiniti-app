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
                            <div class="form-group col-2 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control"  value="{{$producto->clave}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="precio" class="col-form-label">Precio</label>    
                                <input type="text" name="precio" class="form-control" value="{{$producto->precio}}" required="required">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="nombre" class="col-form-label">nombre</label>
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$producto->nombre}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="precioVenta" class="col-form-label">Precio venta</label>    
                                <input type="text" name="precioVenta" class="form-control" value="{{$producto->precio_venta}}" required="required">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-producto">Actualizar producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection