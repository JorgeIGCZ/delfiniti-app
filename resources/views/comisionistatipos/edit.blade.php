@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Tipo de comisionista</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionistatipos-form" action="{{route("comisionistatipos.update",$comisionistaTipo['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Id</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionistaTipo->id}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre de tipo de comisionista</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$comisionistaTipo->nombre}}">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-tipo-comisionista">Actualizar tipo de comisionista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
