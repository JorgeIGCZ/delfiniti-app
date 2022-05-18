@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Actividades</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="actividades-form" action="{{route("actividadesUpdate",$actividad['id'])}}">
                            @csrf
                            <input type="hidden" name="id" class="form-control" value="{{$actividad['id']}}"> 
                            
                            <div class="form-group col-1">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$actividad['clave']}}" disabled="disabled"> 
                            </div>

                            <div class="form-group col-4">
                                <label for="nombre" class="col-form-label">Nombre actividad</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$actividad['nombre']}}">  
                            </div>


                            <div class="form-group col-2">
                                <label for="capacidad" class="col-form-label">Capacidad</label>    
                                <input type="number" name="capacidad" min="1" max="500" class="form-control" value="{{$actividad['capacidad']}}">  
                            </div>

                            <div class="form-group col-3">
                                <label for="new-time" class="col-form-label">Horario</label>
                                <div class="row g-3 align-items-center">
                                  <div class="col-auto">
                                    <input type="time" name="horario_inicial" class="form-control" value="{{$actividad['horario_inicial']}}">
                                  </div>
                                  A
                                  <div class="col-auto">
                                    <input type="time" name="horario_final" class="form-control" value="{{$actividad['horario_final']}}">
                                  </div>
                                </div>
                            </div>

                            <div class="form-group col-3">
                                <button class="btn btn-info btn-block mt-33">Actualizar actividad</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection