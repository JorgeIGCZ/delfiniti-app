@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Alojamiento</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="alojamientos-form" action="{{route("alojamientos.update",$alojamiento['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control"  value="{{$alojamiento->codigo}}">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Lugar de alojamiento</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$alojamiento->nombre}}" required="required">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{$alojamiento->direccion}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$alojamiento->telefono}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-localizacion">Actualizar localización</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection