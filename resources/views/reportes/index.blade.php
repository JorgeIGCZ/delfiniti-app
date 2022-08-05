@extends('layouts.app')
@section('scripts')
<script src="https://bootstrapdash.com/demo/azia/v1.0.0/lib/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script>
        $(function(){
          $('#compositeline').sparkline('html', {
          lineColor: '#cecece',
          lineWidth: 2,
          spotColor: false,
          minSpotColor: false,
          maxSpotColor: false,
          highlightSpotColor: null,
          highlightLineColor: null,
          fillColor: '#f9f9f9',
          chartRangeMin: 0,
          chartRangeMax: 10,
          width: '100%',
          height: 20,
          disableTooltips: true
        });

            var ctx6 = document.getElementById('chartBar6').getContext('2d');
            new Chart(ctx6, {
            type: 'bar',
            data: {
                labels: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                datasets: [{
                data: [300000,150000,200000,180000,100000,80000],
                backgroundColor: '#2b91fe'
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                display: false,
                    labels: {
                    display: false
                    }
                },
                scales: {
                xAxes: [{
                    //stacked: true,
                    display: false,
                    barPercentage: 0.5,
                    ticks: {
                    beginAtZero:true,
                    fontSize: 11
                    }
                }],
                yAxes: [{
                    ticks: {
                    fontSize: 10,
                    color: '#eee'
                    }
                }]
                }
            }
            });
        } );
    </script>
@endsection
@section('content')
<div class="az-content az-content-dashboard-two">
    <div class="az-content-body az-content-header d-block d-md-flex">
      <div>
        <h2 class="az-content-title tx-24 mg-b-5 mg-b-lg-8">Hola {{Auth::user()->name}}!</h2>
        <p class="mg-b-0">Bienvenido a tu panel de administración.</p>
      </div>
      <div class="az-dashboard-header-right">
        <div>
          <label class="tx-13">All Sales (Online)</label>
          <h5>431,007</h5>
        </div>
        <div>
          <label class="tx-13">All Sales (Offline)</label>
          <h5>932,210</h5>
        </div>
          <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Reportes
              </button>
              <div class="dropdown-menu tx-13" aria-labelledby="dropdownMenuButton" style="">
                  <a class="dropdown-item" href="/reportes/cortecaja">Corte de caja</a>
                  <a class="dropdown-item" href="#">Another action</a>
                  <a class="dropdown-item" href="#">Something else here</a>
              </div>
          </div>
      </div><!-- az-dashboard-header-right -->
    </div><!-- az-content-header -->
    <div class="az-content-body">
      <div class="card card-dashboard-seven">
        <div class="card-header">
          <div class="row row-sm">
            <div class="col-6 col-md-4 col-xl mg-t-15 mg-md-t-0">
                <div class="media">
                  <div><i class="icon ion-logo-usd"></i></div>
                  <div class="media-body">
                    <label>Filtro de rango</label>
                    <div class="date">
                      <select name="comisionista" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                        <option value='0' selected="true">Hoy</option>
                        <option value='0' >Ayer</option>
                        <option value='0' >Semana actual</option>
                        <option value='0' >Mes actual</option>
                        <option value='0' >Año actual</option>
                        <option value='0' >Rango personalizado</option>
                      </select>
                    </div>
                  </div>
                </div><!-- media -->
            </div>
            <div class="col-6 col-md-4 col-xl filtro_fechas" style="display: none;">
              <div class="media">
                <div><i class="icon ion-ios-calendar"></i></div>
                <div class="media-body">
                  <label>Fecha Incio</label>
                  <div class="date">
                    <span>Sept 01, 2018</span> <a href=""><i class="icon ion-md-arrow-dropdown"></i></a>
                  </div>
                </div>
              </div><!-- media -->
            </div>
            <div class="col-6 col-md-4 col-xl filtro_fechas" style="display: none;">
              <div class="media">
                <div><i class="icon ion-ios-calendar"></i></div>
                <div class="media-body">
                  <label>Fecha Final</label>
                  <div class="date">
                    <span>Sept 30, 2018</span> <a href=""><i class="icon ion-md-arrow-dropdown"></i></a>
                  </div>
                </div>
              </div><!-- media -->
            </div>
            <div class="col-6 col-md-4 col-xl mg-t-15 mg-xl-t-0">
              <div class="media">
                <div><i class="icon ion-md-person"></i></div>
                <div class="media-body">
                  <label>Tipo de Actividad</label>
                  <div class="date">
                    <select name="comisionista" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                        <option value='0' selected="true">Todas</option>
                    </select>
                  </div>
                </div>
              </div><!-- media -->
            </div>
            <div class="col-md-4 col-xl mg-t-15 mg-xl-t-0">
              <div class="media">
                <div><i class="icon ion-md-stats"></i></div>
                <div class="media-body">
                  <label>Actividad Horario</label>
                  <div class="date">
                    <select name="comisionista" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                        <option value='0' selected="true">Todos</option>
                    </select>
                  </div>
                </div>
              </div><!-- media -->
            </div>
          </div><!-- row -->
        </div><!-- card-header -->
        <div class="card-body">
          <div class="row row-sm">
            <div class="col-6 col-lg-3">
              <label class="az-content-label">Ingreso Efectivo MXN</label>
              <h2><span>$</span>523,200</h2>
              <div class="desc up">
                <i class="icon ion-md-stats"></i>
                <span><strong>50%</strong> (40 Reservaciones)</span>
              </div>
              <span id="compositeline"><canvas width="264" height="20" style="display: inline-block; width: 264.5px; height: 20px; vertical-align: top;"></canvas></span>
            </div><!-- col -->
            <div class="col-6 col-lg-3 mg-t-20 mg-lg-t-0">
              <label class="az-content-label">Ingreso Efectivo USD</label>
              <h2><span>$</span>753,098</h2>
              <div class="desc down">
                <i class="icon ion-md-stats"></i>
                <span><strong>0.51%</strong> (10 Reservaciones)</span>
              </div>
              <span id="compositeline4"><canvas width="264" height="20" style="display: inline-block; width: 264.5px; height: 20px; vertical-align: top;"></canvas></span>
            </div><!-- col -->
            <div class="col-6 col-lg-3 mg-t-20 mg-lg-t-0">
              <label class="az-content-label">Ingreso Cuenta</label>
              <h2><span>$</span>331,886</h2>
              <div class="desc up">
                <i class="icon ion-md-stats"></i>
                <span><strong>5.32%</strong> (30 Reservaciones)</span>
              </div>
              <span id="compositeline3"><canvas width="264" height="20" style="display: inline-block; width: 264.5px; height: 20px; vertical-align: top;"></canvas></span>
            </div><!-- col -->
            <div class="col-6 col-lg-3 mg-t-20 mg-lg-t-0">
                <label class="az-content-label">Ingreso Total</label>
                <h2><span>$</span>331,886</h2>
              </div><!-- col -->
          </div><!-- row -->
        </div><!-- card-body -->
      </div><!-- card -->
      <div class="row row-sm mg-b-20 mg-lg-b-20">
        <div class="col-md-6 col-xl-12">
          <div class="card card-table-two">
            <h6 class="card-title">Reservaciones</h6>
            <span class="d-block mg-b-20">Lista de reservaciones creadas dentro del rango establecido.</span>
            <div class="table-responsive">
              <table class="table table-striped table-dashboard-two">
                <thead>
                  <tr>
                    <th class="wd-lg-25p">Date</th>
                    <th class="wd-lg-25p tx-right">Sales Count</th>
                    <th class="wd-lg-25p tx-right">Earnings</th>
                    <th class="wd-lg-25p tx-right">Tax Witheld</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>05 Oct 2018</td>
                    <td class="tx-right tx-medium tx-inverse">25</td>
                    <td class="tx-right tx-medium tx-inverse">$380.50</td>
                    <td class="tx-right tx-medium tx-danger">-$23.50</td>
                  </tr>
                  <tr>
                    <td>04 Oct 2018</td>
                    <td class="tx-right tx-medium tx-inverse">34</td>
                    <td class="tx-right tx-medium tx-inverse">$503.20</td>
                    <td class="tx-right tx-medium tx-danger">-$13.45</td>
                  </tr>
                  <tr>
                    <td>03 Oct 2018</td>
                    <td class="tx-right tx-medium tx-inverse">30</td>
                    <td class="tx-right tx-medium tx-inverse">$489.65</td>
                    <td class="tx-right tx-medium tx-danger">-$20.98</td>
                  </tr>
                  <tr>
                    <td>02 Oct 2018</td>
                    <td class="tx-right tx-medium tx-inverse">27</td>
                    <td class="tx-right tx-medium tx-inverse">$421.80</td>
                    <td class="tx-right tx-medium tx-danger">-$22.22</td>
                  </tr>
                  <tr>
                    <td>01 Oct 2018</td>
                    <td class="tx-right tx-medium tx-inverse">31</td>
                    <td class="tx-right tx-medium tx-inverse">$518.60</td>
                    <td class="tx-right tx-medium tx-danger">-$23.01</td>
                  </tr>
                </tbody>
              </table>
            </div><!-- table-responsive -->
          </div><!-- card-dashboard-five -->
        </div>
        <!-- col -->
      </div>
      <div class="row row-sm mg-b-15 mg-sm-b-20">
        <div class="col-lg-6 col-xl-7">
            <div class="card card-dashboard-twenty ht-md-100p">
                <div class="card-body">
                  <h6 class="az-content-label tx-13 mg-b-5">Ingresos <span>(Este año)</span></h6>
                  <p class="mg-b-25">Ingresos percibidos durante el año actual.</p>

                  <div class="chartjs-wrapper"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div><canvas id="chartBar6" width="1056" height="460" style="display: block; height: 230px; width: 528px;" class="chartjs-render-monitor"></canvas></div>
                </div><!-- card-body -->
              </div>
        </div><!-- col -->
        <div class="col-md-6 col-xl-5 mg-t-20 mg-md-t-0 mg-b-15 mg-sm-b-20">
            <div class="card card-dashboard-eight">
              <h6 class="card-title">Comisionistas</h6>
              <span class="d-block mg-b-20">Total de ventas ligadas a comisionistas</span>

              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Reservaciones</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong>Carlos Lope</strong></td>
                      <td><strong>6</strong></td>
                      <td>$1,671.10</td>
                    </tr>
                    <tr>
                        <td><strong>Aca Reservas</strong></td>
                        <td><strong>5</strong></td>
                        <td>$1,4 71.10</td>
                      </tr>
                      <tr>
                        <td><strong>Juan Perez</strong></td>
                        <td><strong>3</strong></td>
                        <td>$1,271.10</td>
                      </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
       </div><!-- row -->

    </div><!-- az-content-body -->
  </div>

@endsection
