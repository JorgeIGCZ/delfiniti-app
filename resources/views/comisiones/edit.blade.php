@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Comisiones</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisiones-form" action="{{route("comisiones.update",$comision['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Comisionista</label>    
                                <input type="text" name="comisionista" class="form-control" disabled="disabled" value="{{$comision->comisionista->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Reservacion</label>
                                <input type="text" name="comisionista-folio" class="form-control" disabled="disabled" value="{{$comision->reservacion->folio}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="descuento" class="col-form-label">Iva</label>
                                <input type="number" name="iva" id="iva" class="form-control" value="{{$comision->iva}}">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="descuento" class="col-form-label">Descuento impuesto</label>
                                <input type="number" name="descuento_impuesto" class="form-control" value="{{$comision->descuento_impuesto}}">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="descuento" class="col-form-label">Comision neta</label>
                                <input type="number" name="cantidad_comision_neta" class="form-control"  value="{{$comision->cantidad_comision_neta}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-comision">Actualizar Comision</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
