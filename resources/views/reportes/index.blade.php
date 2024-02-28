@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            $('.input-daterange').datepicker({
                format: 'dd/mm/yyyy',
                // format: 'yyyy/mm/dd',
                language: 'es'
            }).on("change", function() {
                isFechaRangoValida();
            });

            // document.getElementById('fecha_reporte').addEventListener('change', (event) =>{
            //     const seleccion = event.target.value;
            //     const rangoFecha = document.getElementById('rango-fecha');

            //     $('#start-date').datepicker('setDate', null);
            //     $('#end-date').datepicker('setDate', null);

            //     rangoFecha.style.display = "none";
            //     if(seleccion !== "custom"){
            //         // reservacionesTable.ajax.reload();
            //         return;
            //     }
            //     rangoFecha.style.display = "block";
            // });

            // document.getElementById('estatus_reporte').addEventListener('change', (event) =>{
            //     const rangoFecha = document.getElementById('rango-fecha');

            //     $('#start_date').datepicker('setDate', null);
            //     $('#end_date').datepicker('setDate', null);

            //     rangoFecha.style.display = "block";
                
            //     // reservacionesTable.ajax.reload();
            //     return;
            // });

            document.getElementById('start-date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end-date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    // reservacionesTable.ajax.reload();
                    return;
                }
            });

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end-date').value;
                const fechaFinal = document.getElementById('start-date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    // reservacionesTable.ajax.reload();
                    return;
                }
            }
            
        } );
    </script>
    <script src="{{asset('js/reportes/main.js') }}"></script>
@endsection
@section('content')
    <style>
      .az-content-dashboard > .container{
        place-content: center;
      }
      .az-content-body{
        max-width: 800px;
      }
      /* .filter-multi-select > .viewbar {
        padding: 3px 10px;
      } */
    </style>
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Exportación de reportes</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <form class="row g-3 align-items-center f-auto" id="reportes-form" method="GET">
              <div class="col-md-12">
                <div class="row">
                  <div class="form-group col-md-4">
                    <label for="reporte-select">Reporte</label>
                    <select class="form-control fecha" name="reporte-select" id="reporte-select">
                        <option value="" selected="selected">Seleccionar reporte</option>
              
                        @can('Reportes.CorteCaja.index')
                          <option value="corte-caja">Corte de caja</option>
                        @endcan

                        {{-- @if(session('modulo') == 'reservaciones') --}}
                        @can('Reportes.Reservaciones.index')
                          <option value="reservaciones">Reservaciones</option>
                        @endcan
                        {{-- @endif --}}

                        @can('Reportes.Comisiones.index')
                          <option value="comisiones">Comisiones</option>
                        @endcan
                        
                        @can('Reportes.CuponesAgenciaConcentrado.index')
                          <option value="cupones-agencia-concentrado">Cupones Agencia concentrado</option>
                        @endcan

                        @can('Reportes.CuponesAgenciaDetallado.index')
                          <option value="cupones-agencia-detallado">Cupones Agencia detallado</option>
                        @endcan
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group col-md-12" id="rango-fecha">
                <label for="fecha">Fecha</label>
                <div class="input-group input-daterange">
                    <input id="start-date" name="start-date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa"> 
                    <span class="input-group-addon" style="padding: 0 8px;align-self: center;background: none;border: none;">Al</span> 
                    <input id="end-date" name="end-date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa">
                </div>
              </div>

              {{-- <div class="form-group col-md-4">
                  <label for="fecha">Fecha</label>
                  <select class="form-control fecha" name="fecha" id="fecha_reporte">
                      <option value="dia" selected="selected">Día Actual</option>
                      <option value="mes">Mes Actual</option>
                      <option value="custom">Rango</option>
                  </select>
              </div>
              <div class="form-group col-md-4" id="rango-fecha" style="display: none;">
                  <label for="fecha">Mes</label>
                  <div class="input-group input-daterange">
                      <input id="start-date" name="start-date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa"> 
                      <span class="input-group-addon" style="padding: 0 8px;align-self: center;background: none;border: none;">Al</span> 
                      <input id="end-date" name="end-date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa">
                  </div>
              </div> --}}

      
              <div id="filtro-cajero" class="form-group col-6 mt-0 mb-0">
                <label for="cajero" class="col-form-label">Seleccionar el cajero.</label>
                <select id="corte-usuario" class="form-control" >
                    <option value="0" selected="selected">
                      TODOS
                    </option>
                    @foreach($usuarios as $usuario)
                      <option value="{{$usuario->id}}" >{{$usuario->name}}</option>
                    @endforeach
                </select>
              </div>

              <div id="filtro-cupones"class="form-group col-md-4">
                <label for="cupones">Incluir cupones?</label>
                <input type="checkbox" name="corte-cupones" id="corte-cupones" class="form-control" style="display: block;" tabindex="15">
              </div>

              <div id="filtros-comisiones" class="form-group col-12 mt-0 mb-0" style="display: none">
                <div class="row">
                    <div class="form-group col-12 mt-0 mb-0">
                        <label for="comisiones_canales_venta" class="col-form-label">Categorias</label>
                        <select multiple id="filtro-comisiones-canales-venta" name="comisiones_canales_venta"  class="form-control filter-multi-select" >
                          @foreach($canales as $canal)
                            <option value="{{$canal->id}}" >{{$canal->nombre}}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
              </div>

              <div id="filtros-modulo-corte-caja" class="form-group col-12 mt-0 mb-0">
                <div class="row">
                    <div class="form-group col-12 mt-0 mb-0">
                        <label for="filtro-modulo-corte-caja" class="col-form-label">Módulo</label>
                        <select multiple id="filtro-modulo-corte-caja" name="filtro_modulo_corte_caja"  class="form-control filter-multi-select" >
                          @foreach($modulosCorteCaja as $key => $modulo)
                            <option value="{{$key}}">{{$modulo}}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
              </div>

              <div id="filtros-modulo-comisiones" class="form-group col-12 mt-0 mb-0">
                <div class="row">
                    <div class="form-group col-12 mt-0 mb-0">
                        <label for="filtro-modulo-comisiones" class="col-form-label">Módulo</label>
                        <select multiple id="filtro-modulo-comisiones" name="filtro_modulo_comisiones"  class="form-control filter-multi-select" >
                          @foreach($modulosComisiones as $key => $modulo)
                            <option value="{{$key}}">{{$modulo}}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
              </div>

              <div id="filtros-agencia-cupon" class="form-group col-12 mt-0 mb-0">
                <div class="row">
                    <div class="form-group col-12 mt-0 mb-0">
                        <label for="filtro-agencia-cupon" class="col-form-label">Agencias cupón</label>
                        <select multiple id="filtro-agencia-cupon" name="filtro_agencia_cupon"  class="form-control filter-multi-select" >
                          @foreach($agenciasCupon as $agencia)
                            <option value="{{$agencia->id}}">{{$agencia->nombre}}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
              </div>

              <div class="form-group col-12 mt-3">
                <div class="row">
                  <div class="form-group col-4">
                    <button class="btn btn-info btn-block mt-33" action="" id="crear-reporte">Exportar</button>
                  </div>
                </div>
              </div>
            </form>
            
            {{-- <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <table id="reservaciones" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Actividad</th>
                                        <th>Personas</th>
                                        <th>Horario</th>
                                        <th>Fecha Actividad</th>
                                        <th>Estatus</th>
                                        <th>Cortesía</th>
                                        <th>Fecha creación</th>
                                        <th>Notas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endsection
