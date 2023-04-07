@extends('layouts.app')
@section('scripts')
    <script>
        function changeComisionesSettings(){
            const tipo = document.getElementById('tipo');
            
            if(tipo.options[tipo.selectedIndex].getAttribute('comisionistaCanal') == '1'){
                $('.general-settings').hide();
                $('.comisiones-sobre-actividades').hide();
                $('.comisiones-sobre-canales').show();
                return false;
            }else if(tipo.options[tipo.selectedIndex].getAttribute('comisionistaActividad') == '1'){
                $('.general-settings').hide();
                $('.comisiones-sobre-actividades').show();
                $('.comisiones-sobre-canales').hide();
                return false;
            }
            $('.general-settings').show();
            $('.comisiones-sobre-actividades').hide();
            $('.comisiones-sobre-canales').hide();
            return true;
        }
        $(function(){
            changeComisionesSettings();

            $('#tipo').on('change', function (e) {
                document.querySelectorAll("#tipo option").forEach(function(el) {
                    el.removeAttribute("selected");
                })

                changeComisionesSettings();
            });
            
        });
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
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionistas-form" action="{{route("comisionistas.update",$comisionista['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionista->codigo}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$comisionista->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control">
                                    @foreach($tipos as $tipo)
                                        <option value="{{$tipo->id}}" {{$tipo->id === $comisionista->canal_venta_id ? 'selected="selected"' : ""}} comisionistaCanal={{$tipo->comisionista_canal}} comisionistaActividad={{$tipo->comisionista_actividad}}>{{$tipo->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-12 mt-3 comisiones-sobre-canales" style="display: none;">
                                <strong>
                                    Comisiones sobre canales
                                </strong>
                                <table class="mt-3">
                                    <tr>
                                        <th>Canal de venta</th>
                                        <th>Comisión %</th>
                                        <th>Iva %</th>
                                        <th>Descuentro por imp. %</th>
                                    </tr>
                                    @foreach($comisionistaGeneralTipos as $key => $comisionistaGeneralTipo)
                                        <tr tipoid="{{$comisionistaGeneralTipo->id}}" class="tipo_comisiones">
                                            <td>{{$comisionistaGeneralTipo->nombre}}</td>
                                            <td>
                                                <input type="number" step="0.01" name="comisionista_canal_detalles[{{$key}}][{{$comisionistaGeneralTipo->id}}][comision]" class="tipo_comision form-control" value="{{@$comisionista->comisionistaCanalDetalle->groupBy('canal_venta_id')[$comisionistaGeneralTipo->id][0]->comision}}">  
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="comisionista_canal_detalles[{{$key}}][{{$comisionistaGeneralTipo->id}}][iva]" class="tipo_iva form-control" value="{{@$comisionista->comisionistaCanalDetalle->groupBy('canal_venta_id')[$comisionistaGeneralTipo->id][0]->iva}}">  
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="comisionista_canal_detalles[{{$key}}][{{$comisionistaGeneralTipo->id}}][descuento_impuesto]" class="tipo_descuento_impuesto form-control" value="{{@$comisionista->comisionistaCanalDetalle->groupBy('canal_venta_id')[$comisionistaGeneralTipo->id][0]->descuento_impuesto}}">  
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            <div class="form-group col-12 mt-3 comisiones-sobre-actividades" style="display: none;">
                                <strong>
                                    Comisiones sobre actividad
                                </strong>
                                <table class="mt-3">
                                    <tr>
                                        <th>Actividad</th>
                                        <th>Comisión directa P/Actividad $</th>
                                        <th>Descuentro por imp. %</th>
                                    </tr>
                                    @foreach($actividades as $key => $actividad)
                                        @php
                                            $cantidad = 0;
                                            $descuentoImpuesto = 0;
                                        @endphp
                                        @foreach($comisionistaActividadesDetalle as $comisionistaActividadDetalle)
                                            @if($comisionistaActividadDetalle->actividad_id == $actividad->id)
                                                @php
                                                    $cantidad = $comisionistaActividadDetalle->comision;
                                                    $descuentoImpuesto = $comisionistaActividadDetalle->descuento_impuesto;
                                                @endphp
                                            @endif
                                        @endforeach

                                        <tr actividadid="{{$actividad->id}}" class="actividades">
                                            <td>{{$actividad->nombre}}</td>
                                            <td>
                                                <input type="text" name="comisionista_actividad_detalles[{{$key}}][{{$actividad->id}}][comision]" class="actividad_comision form-control" value="{{$cantidad}}">  
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="comisionista_actividad_detalles[{{$key}}][{{$actividad->id}}][descuento_impuesto]" class="actividad_descuento_impuesto form-control" value="{{$descuentoImpuesto}}">  
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            


                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" step="0.01" name="comision" class="form-control" min="0" max="90" value="{{$comisionista->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{$comisionista->iva}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                <input type="number" step="0.01" name="descuento_impuesto" class="form-control" min="0" max="90" value="{{$comisionista->descuento_impuesto}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="descuentos" class="col-form-label" style="display: block;">Puede recibir descuentos</label>
                                <input type="checkbox" name="descuentos" class="form-control" value="{{$comisionista->descuentos}}" @if($comisionista->descuentos) checked="checked" @endif>
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="comisiones_canal" class="col-form-label" style="display: block;">Incluir en comisiones de canal</label>
                                <input type="checkbox" name="comisiones_canal" class="form-control" value="{{$comisionista->comisiones_canal}}" @if($comisionista->comisiones_canal) checked="checked" @endif>
                            </div>

                            <div class="col-12 mt-3 general-settings">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3 general-settings">
                                <label for="representante" class="col-form-label">Representante</label>
                                <input type="text" name="representante" class="form-control to-uppercase" value="{{$comisionista->representante}}">
                            </div>
                            <div class="form-group col-4 mt-3 general-settings">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control to-uppercase" value="{{$comisionista->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3 general-settings">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$comisionista->telefono}}">
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
