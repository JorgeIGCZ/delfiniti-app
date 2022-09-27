@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Canal de venta</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="canalesventa-form" action="{{route("canalesventa.update",$canalVenta['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Id</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$canalVenta->id}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre de canal</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$canalVenta->nombre}}">  
                            </div>
                            <!--div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Comisiones sobre tipo</label>
                                <input type="checkbox" name="comisionista_canal" {{($canalVenta->comisionista_canal) ? 'checked' : ''}} class="form-control" style="display: block;"> 
                            </div-->
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-tipo-comisionista">Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
