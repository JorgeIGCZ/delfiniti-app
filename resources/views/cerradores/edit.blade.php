@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Cerrador</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="cerradores-form" action="{{route("cerradores.update",$cerrador['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre cerrador</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$cerrador->nombre}}"  required="required">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="{{$cerrador->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{$cerrador->iva}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{$cerrador->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$cerrador->telefono}}">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-cerrador">Actualizar cerrador</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
