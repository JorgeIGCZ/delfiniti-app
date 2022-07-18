@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Códigos de desucentos</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="descuentocodigos-form" action="{{route("descuentocodigos.update",$descuentocodigo['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$descuentocodigo->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" class="form-control">
                                    <option value="cantidad" {{'cantidad' === $descuentocodigo->tipo ? 'selected="selected' : ""}} >Cantidad</option>
                                    <option value="porcentaje" {{'porcentaje' === $descuentocodigo->tipo ? 'selected="selected' : ""}} >Porcentaje</option>
                                </select>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="descuento" class="col-form-label">descuento</label>
                                <input type="number" name="descuento" class="form-control" min="0" max="90" value="{{$descuentocodigo->descuento}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-descuentocodigo">Actualizar Código</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
