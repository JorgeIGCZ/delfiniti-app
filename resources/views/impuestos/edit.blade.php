@extends('layouts.app')
@section('scripts')
    <script>
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Impuestos a productos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p"> 
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="impuestos-form" action="{{route("impuestos.update",$impuesto['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre del impuesto</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" value="{{$impuesto->nombre}}" tabindex="1" required="required">  
                            </div>
                            <div class="form-group col-1 mt-2">
                                <label for="impuesto" class="col-form-label">Impuesto</label>
                                <input type="text" name="impuesto" id="impuesto" class="form-control percentage"  autocomplete="off" value="{{$impuesto->impuesto}}" tabindex="2">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-impuesto" tabindex="3">Actualizar impuesto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection