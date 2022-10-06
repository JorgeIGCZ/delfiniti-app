<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <!--script async src="https://www.googletagmanager.com/gtag/js?id=UA-90680653-2"></script-->
    <!--script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-90680653-2');
    </script-->

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Twitter -->
    <!-- <meta name="twitter:site" content="@bootstrapdash">
    <meta name="twitter:creator" content="@bootstrapdash">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Azia">
    <meta name="twitter:description" content="Responsive Bootstrap 4 Dashboard Template">
    <meta name="twitter:image" content="https://www.bootstrapdash.com/azia/img/azia-social.png"> -->

    <!-- Facebook -->
    <!-- <meta property="og:url" content="https://www.bootstrapdash.com/azia">
    <meta property="og:title" content="Azia">
    <meta property="og:description" content="Responsive Bootstrap 4 Dashboard Template">

    <meta property="og:image" content="https://www.bootstrapdash.com/azia/img/azia-social.png">
    <meta property="og:image:secure_url" content="https://www.bootstrapdash.com/azia/img/azia-social.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="600"> -->

    <!-- Meta -->
    <meta name="author" content="Ceusjic">

    <title>Delfiniti App</title>

    <!-- vendor css -->
    <link href="{{asset('assets/lib/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/lib/ionicons/css/ionicons.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/lib/typicons.font/typicons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/lib/flag-icon-css/css/flag-icon.min.css')}}" rel="stylesheet">

    <!-- azia CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/filter_multi_select.css')}}">
    <script>
      const token = () =>{
        return  '{{ csrf_token() }}';
      }
    </script>
  </head>
  <body>
    <div class="loader" style="display: none;"><img src="/assets/img/loader.svg" height="80" alt="loader"></div>
    @include('layouts.header')
    <div class="az-content az-content-dashboard">
      <div class="container">
        <div class="az-content-body">
          <div class="modal fade" id="reportes-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
            <div class="modal-dialog modal-m" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                  <h6 class="modal-title">Exportar Reporte</h6>
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
                  </div>
                  <div class="modal-body">
                      <form class="row g-3 align-items-center f-auto" id="reporte-form">
                          <div class="form-group col-6 mt-3">
                            <label for="nombre" class="col-form-label">Fecha inicio</label>    
                            <input type="date" name="fecha-inicio" id="report-fecha-inicio" class="form-control" required="required" value="{{date('Y-m-d')}}">  
                          </div>
                          <div class="form-group col-6 mt-3">
                              <label for="nombre" class="col-form-label">Fecha final</label>    
                              <input type="date" name="fecha-inicio" id="report-fecha-final" class="form-control" required="required" value="{{date('Y-m-d')}}">  
                          </div>

                          @php
                            use App\Models\User;
                            use App\Models\CanalVenta;
                            $agentes = User::role('Recepcion')->get();
                            $canales = CanalVenta::get();
                          @endphp
                          
                          <div id="filtros-corte-caja" class="form-group col-12 mt-0 mb-0" style="display: none">
                            <div class="row">
                                <div class="form-group col-6 mt-0 mb-0">
                                    <label for="cajero" class="col-form-label">Seleccionar el cajero.</label>
                                    <select id="corte-agente" class="form-control" >
                                        <option value="0" selected="selected">
                                          TODOS
                                        </option>
                                        @foreach($agentes as $agente)
                                          <option value="{{$agente->id}}" >{{$agente->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-6 mt-0 mb-0">
                                  <label for="corte-cupones" class="col-form-label">Incluir cupones?</label>
                                  <input type="checkbox" name="corte-cupones" id="corte-cupones" class="form-control" style="display: block;" tabindex="15">
                                </div>
                            </div>
                          </div>

                          <div id="filtros-comisiones" class="form-group col-12 mt-0 mb-0" style="display: none">
                            <div class="row">
                                <div class="form-group col-12 mt-0 mb-0">
                                    <label for="cajero" class="col-form-label">Categorias.</label>
                                    <select multiple id="comisiones-canales-venta" name="comisiones_canales_venta"  class="form-control filter-multi-select" >
                                        @foreach($canales as $canal)
                                          <option value="{{$canal->id}}" >{{$canal->nombre}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                          </div>
                          
                          <div class="form-group col-4 mt-3">
                              <button class="btn btn-info btn-block mt-33" action="" id="crear-reporte">Exportar</button>
                          </div>
                      </form>
                  </div>
              </div>
            </div><!-- modal-dialog -->
          </div>
          @yield('content')
        </div>
      </div>
    </div>
    @include('layouts.footer')
  </body>
</html>