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
    <script>
      const token = () =>{
        return  '{{ csrf_token() }}';
      }
    </script>
  </head>
  <body>
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
                      <form class="row g-3 align-items-center f-auto">
                          <div class="form-group col-6 mt-3">
                            <label for="nombre" class="col-form-label">Fecha inicio</label>    
                            <input type="date" name="fecha-inicio" id="report-fecha-inicio" class="form-control" required="required" value="{{date('Y-m-d')}}">  
                          </div>
                          <div class="form-group col-6 mt-3">
                              <label for="nombre" class="col-form-label">Fecha final</label>    
                              <input type="date" name="fecha-inicio" id="report-fecha-final" class="form-control" required="required" value="{{date('Y-m-d')}}">  
                          </div>
                          <div class="form-group col-4 mt-3">
                              <button class="btn btn-info btn-block mt-33" id="crear-reporte">Exportar</button>
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