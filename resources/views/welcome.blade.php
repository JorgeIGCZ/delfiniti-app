@extends('layouts.app')
@section('content')

<div class="az-dashboard-one-title">
    <div>
      <h2 class="az-dashboard-title">Disponibilidad</h2>
      <p class="az-dashboard-text">.</p>
    </div>
</div><!-- az-dashboard-one-title -->
<div class="row row-sm mg-b-20">
    <div class="col-lg-12 ht-lg-100p">
      <div class="card">
        <div class="card-body">
          <div class="container">
            <div class="row">

              <div class="col-auto actividades mt-3">
                <div class="row g-3 align-items-center">
                  <div class="col-auto">
                    <label for="new-activity" class="col-form-label">Actividad</label>
                  </div>
                  <div class="col-auto">
                    <input type="text" id="new-activity" class="form-control" >
                  </div>
                </div>
              </div>

              <div class="col-auto actividades mt-3">
                <div class="row g-3 align-items-center">
                  <div class="col-auto">
                    <label for="new-capacidad" class="col-form-label">Capacidad</label>
                  </div>
                  <div class="col-auto">
                    <input type="number" id="new-capacidad" class="form-control" min="1" max="500">
                  </div>
                </div>
              </div>
              
              <div class="col-auto horario mt-3">
                <div class="row g-3 align-items-center">
                  <div class="col-auto">
                    <label for="new-time" class="col-form-label">Horario</label>
                  </div>
                  <div class="col-auto">
                    <input type="time" id="start-time" class="form-control" >
                  </div>
                  A
                  <div class="col-auto">
                    <input type="time" id="end-time" class="form-control" >
                  </div>
                </div>
              </div>

              <div class="col-auto mt-3">
                <button class="btn btn-info btn-block">Crear actividad</button>
              </div>
            </div>
          </div>
        </div><!-- card-body -->
      </div><!-- card -->

      <div class="program-container">
        <div class="card ">
          <div class="card-body">
            <div class="p-container">
              <div class="col-horario">
                <h3>10:30 AM</h3>
              </div>
              <div class="col-programas">
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="card">
          <div class="card-body">
            <div class="p-container">
              <div class="col-horario">
                <h3>10:30 AM</h3>
              </div>
              <div class="col-programas">
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
                <div class="programa">
                  <strong class="p-title">Show gpo castillo</strong>
                  <div class="p-detalles">
                    <div><p class="mg-b-0">Reserv. total: <span>12</span></p></div>
                    <div><p class="mg-b-0">Cupo: <span>200</span></p></div>
                  </div>
                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Reserva</th>
                            <th>Cliente</th>
                            <th>Personas</th>
                        </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
      
      </div>
    </div>
    </div><!-- col -->
</div><!-- row -->
@endsection
