@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">
                {{$promotor['nombre']}}
            </h2>
            <p class="az-dashboard-text">Edición de promotor</p>
        </div>
        <div class="az-content-header-right">
            <a href="/configuracion/promotores" class="btn btn-light btn-block mt-33">Promotores</a>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="promotores-form" action="{{route("promotoresUpdate",$promotor['id'])}}">
                            @csrf
                            <input type="hidden" name="id" class="form-control" value="{{$promotor['id']}}">  
                            <div class="form-group col-2 mt-3">
                                <label for="new-codigo" class="col-form-label">Código</label>    
                                <input type="text" id="new-codigo" name="codigo" class="form-control" value="{{$promotor['codigo']}}">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="new-nombre" class="col-form-label">Promotor</label>    
                                <input type="text" id="new-nombre" name="nombre" class="form-control" value="{{$promotor['nombre']}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisión %</label>
                                <input type="number" id="new-nombre" name="comision" class="form-control" min="1" max="90" value="{{$promotor['comision']}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-iva" class="col-form-label">Iva %</label>
                                <input type="number" id="new-iva" name="iva" class="form-control" min="1" max="90" value="{{$promotor['iva']}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Editar promotor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
