@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Comisionistas</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionistas-form" action="{{route("comisionistas.update",$comisionista['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionista->codigo}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$comisionista->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" class="form-control">
                                    @foreach($tipos as $tipo)
                                        <option value="{{$tipo->id}}" {{$tipo->id === $comisionista->tipo_id ? 'selected="selected' : ""}} >{{$tipo->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="{{$comisionista->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{$comisionista->iva}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                <input type="number" name="descuento_impuesto" class="form-control" min="0" max="90" value="{{$comisionista->descuento_impuesto}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="descuentos" class="col-form-label">Puede recibir descuentos</label>
                                <input type="checkbox" name="descuentos" class="form-control" value="{{$comisionista->descuentos}}" @if($comisionista->descuentos) checked="checked" @endif>
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3">
                                <label for="representante" class="col-form-label">Representante</label>
                                <input type="text" name="representante" class="form-control" value="{{$comisionista->representante}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{$comisionista->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$comisionista->telefono}}">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-comisionista">Actualizar comisionista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
