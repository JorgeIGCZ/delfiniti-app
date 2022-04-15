@extends('layouts.app')
@section('scripts')

@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Agencias credito</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="agenciascredito-form" action="{{route("agenciascreditoUpdate",$agenciaCredito['id'])}}">
                            @csrf
                            <input type="hidden" name="id" class="form-control" value="{{$agenciaCredito['id']}}">  
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$agenciaCredito['codigo']}}">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="nombre" class="col-form-label">Nombre agencia</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$agenciaCredito['nombre']}}">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="1" max="90" value="{{$agenciaCredito['comision']}}">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="1" max="90" value="{{$agenciaCredito['iva']}}">
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3">
                                <label for="representante" class="col-form-label">Representante</label>
                                <input type="text" name="representante" class="form-control" value="{{$agenciaCredito['representante']}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{$agenciaCredito['direccion']}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$agenciaCredito['telefono']}}">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Editar agencia</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection