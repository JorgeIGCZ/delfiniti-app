@extends('layouts.app')
@section('scripts')
    <script>
        function addTime(element,event){
            const horario = `
            <div class="form-group col-3 horario-container">
                <label for="new-time" class="col-form-label">Horario</label>
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <input type="time" name="horario_inicial[]" class="form-control" required="required" onclick="removeTime()">
                    </div>
                    A
                    <div class="col-auto">
                        <input type="time" name="horario_final[]" class="form-control" required="required">
                    </div>
                    <div class="action-time">
                        <span class="remove-time">
                            <i class="fa fa-minus-circle" aria-hidden="true"></i>
                        </span>
                        <span class="add-time" >
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>`;
            element.closest('.horario-container').insertAdjacentHTML('afterend',horario);
        }
        function removeTime(element,event){
            element.closest('.horario-container').remove();
        }
        function changeComisionesSettings(){
            const comisionesEspeciales = document.getElementById('comisiones_especiales');
            
            if(comisionesEspeciales.checked){
                $('#comisiones-especiales-container').show();
                return false;
            }
            $('#comisiones-especiales-container').hide();
            return false;       
        }
        $(function(){
            on("click", ".add-time", function(event) {
                addTime(this,event);
            });
            on("click", ".remove-time", function(event) {
                removeTime(this,event);
            });
            $('#comisiones_especiales').on('change', function (e) {
                changeComisionesSettings();
            });
        });
    </script>
@endsection
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
                        <form method="POST" class="row g-3 align-items-center f-auto" id="actividades-form" action="{{route("actividades.update",$actividad['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">

                            <input type="hidden" name="id" class="form-control" value="{{$actividad['id']}}"> 
                            
                            <div class="form-group col-1">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$actividad['clave']}}" disabled="disabled"> 
                            </div>

                            <div class="form-group col-4">
                                <label for="nombre" class="col-form-label">Nombre actividad</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$actividad['nombre']}}">  
                            </div>
 

                            <div class="form-group col-1">
                                <label for="precio" class="col-form-label">Precio</label>      
                                <input type="text" name="precio" class="form-control amount" value="{{$actividad['precio']}}">  
                            </div>
                            
                            <div class="form-group col-2">
                                <label for="capacidad" class="col-form-label">Capacidad</label>    
                                <input type="number" name="capacidad" min="1" max="500" class="form-control" value="{{$actividad['capacidad']}}">  
                            </div>

                            <div class="form-group col-1"> 
                                <label for="comisionable" class="col-form-label">Comisionable</label>
                                <input type="checkbox" name="comisionable" class="form-control" @if($actividad->comisionable) checked="checked" @endif style="display: block;">
                            </div>

                            <div class="form-group col-2"> 
                                <label for="comisiones_especiales" class="col-form-label">Comisiones especiales</label>    
                                <input type="checkbox" name="comisiones_especiales" id="comisiones_especiales" class="form-control" @if($actividad->comisiones_especiales) checked="checked" @endif style="display: block;">
                            </div>

                            <div class="form-group col-2"> 
                                <label for="exclusion_especial" class="col-form-label">Excluir de disponibilidad</label>    
                                <input type="checkbox" name="exclusion_especial" id="exclusion_especial" class="form-control" @if($actividad->exclusion_especial) checked="checked" @endif style="display: block;">
                            </div>

                            <div class="form-group col-12 mt-3" id="comisiones-especiales-container" @if($actividad->comisiones_especiales) style="display: block;" @else style="display: none;" @endif>
                                <strong>
                                    Comisiones especiales actividad
                                </strong>
                                <table class="mt-3">
                                    <tr>
                                        <th>Canal de venta</th>
                                        <th>Comisión directa P/Actividad %</th>
                                        <th>Descuentro por imp. %</th>
                                    </tr>
                                    @foreach($canales as $key => $canal)
                                        @php
                                            $cantidad = 0;
                                            $descuentoImpuesto = 0;
                                        @endphp
                                        @foreach($comisionesPersonalizadas as $actividadComisionDetalle)
                                            @if($actividadComisionDetalle->canal_venta_id == $canal->id)
                                                @php
                                                    $cantidad = $actividadComisionDetalle->comision;
                                                    $descuentoImpuesto = $actividadComisionDetalle->descuento_impuesto;
                                                @endphp
                                            @endif
                                        @endforeach
                                        <tr canalId="{{$canal->id}}" class="canales">
                                            <td>{{$canal->nombre}}</td>
                                            <td>
                                                <input type="text" step="0.01" name="canal_comision[{{$key}}][{{$canal->id}}][comision]" class="canal_comision form-control percentage" value="{{$cantidad}}">
                                            </td>
                                            <td>
                                                <input type="text" step="0.01" name="canal_comision[{{$key}}][{{$canal->id}}][descuento_impuesto]" class="canal_comision form-control percentage" value="{{$descuentoImpuesto}}">  
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="col-12">
                                <div class="row" id="horarios-container">
                                    @foreach($actividadHorarios as $actividadHorario)
                                        <div class="form-group col-3 horario-container">
                                            <label for="new-time" class="col-form-label">Horario</label>
                                            <div class="row g-3 align-items-center">
                                                <div class="col-auto">
                                                    <input type="time" name="horario_inicial[]" class="form-control" required="required" value="{{$actividadHorario->horario_inicial}}">
                                                </div>
                                                A
                                                <div class="col-auto">
                                                    <input type="time" name="horario_final[]" class="form-control" required="required" value="{{$actividadHorario->horario_final}}">
                                                </div>
                                                <div class="action-time">
                                                    <span class="add-time">
                                                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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