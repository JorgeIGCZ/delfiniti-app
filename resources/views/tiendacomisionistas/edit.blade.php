@extends('layouts.app')
@section('scripts')
    <script>
    </script>
@endsection
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
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionistas-form" action="{{route("tiendacomisionistas.update",$comisionista->id)}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <input type="hidden" name="id" class="form-control" value="{{$comisionista->id}}">
                            <div class="form-group col-1 mt-3">
                                <label for="codigo" class="col-form-label">Id</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionista->id}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$comisionista->name}}"  disabled="disabled">  
                            </div>


                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" step="0.01" name="comision" class="form-control" min="0" max="90" value="{{@$comisionista->comisiones->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{@$comisionista->comisiones->iva}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                <input type="number" step="0.01" name="descuento_impuesto" class="form-control" min="0" max="90" value="{{@$comisionista->comisiones->descuento_impuesto}}">
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
