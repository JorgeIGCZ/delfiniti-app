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
            <h2 class="az-dashboard-title">Fotógrafo</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionistas-form" action="{{route("fotovideocomisionistas.update",$comisionista['id'])}}">
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
