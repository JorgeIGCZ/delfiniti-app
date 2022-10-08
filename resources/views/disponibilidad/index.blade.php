@extends('layouts.app')
@section('scripts')
    <script>
        $(function() {
            reloadInactividad();
            let reservaciones = new DataTable('.reservaciones-table', {
                order: [[0, 'desc']],
                ordering: false,
                searching: false,
                paging: false,
                info: false,
                columnDefs: [
                    {
                        targets: 0,
                        className: 'dt-body-center'
                    },
                    {
                        targets: 2,
                        className: 'dt-body-center'
                    },
                    {
                        targets: -1,
                        className: 'dt-body-center'
                    }
                ]
            });
            document.getElementById('toogle-info').addEventListener('click', (event) => {
                event.preventDefault();
                const reservacionesTable = document.querySelectorAll('.reservaciones-table');
                const toogleBtn = document.querySelectorAll('#toogle-info>i');
                reservacionesTable.forEach(reservacionTable => {
                    if (reservacionTable.style.display === "none") {
                        reservacionTable.style.display = "block";
                    } else {
                        reservacionTable.style.display = "none";
                    }
                });

                if (toogleBtn[0].className == 'fa fa-compress') {
                    toogleBtn[0].classList.remove('fa-compress');
                    toogleBtn[0].classList.add('fa-expand');
                } else {
                    toogleBtn[0].classList.remove('fa-expand');
                    toogleBtn[0].classList.add('fa-compress');
                }
            });

        });
        function reloadInactividad(){
            let time = new Date().getTime();
            const setActivityTime = (e) => {
            time = new Date().getTime();
            }
            document.body.addEventListener("mousemove", setActivityTime);
            document.body.addEventListener("keypress", setActivityTime);

            const refresh = () => {
                if (new Date().getTime() - time >= 20000) {
                    window.location.reload(true);
                } else {
                    setTimeout(refresh, 20000);
                }
            }

            setTimeout(refresh, 20000);
        }
    </script>
@endsection
@section('content')
    <style>
        .az-content .container {
            max-width: 100%;
            padding: 0 20px;
        }
    </style>
    <div class="az-dashboard-one-title disponibilidad-title">
        <div>
            <h2 class="az-dashboard-title">Disponibilidad</h2>
        </div> 
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20 disponibilidad-section">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container" id="disponibilidad-settings-container">
                        <form class="row g-3 align-items-center f-auto" action="disponibilidad" method="GET">
                            @csrf
                            <div class="col-auto actividades">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="fecha-actividades" class="col-form-label">fecha</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" id="fecha-actividades" name="fecha_actividades"
                                            class="form-control" value="{{ $fechaActividades }}" onChange="this.form.submit()">
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="contenedor-informacion-disponibilidad">
                            <div>
                                <label class="col-form-label">Total diario:</label>
                                <strong>{{$reservaciones}}</strong>
                            </div>
                            <div class="paid">
                                <label class="col-form-label">Pagados:</label>
                                <strong>{{$reservacionesPagadas}}</strong>
                            </div>
                            <div class="pending">
                                <label class="col-form-label">Pendientes:</label>
                                <strong>{{$reservacionesPendientes}}</strong>
                            </div>
                            <div>
                                <label class="col-form-label">Cortesias:</label>
                                <strong>{{$cortesias}}</strong>
                            </div>
                        </div>
                        <div>
                            <button id="toogle-info">
                                <i class="fa fa-expand" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div><!-- card-body -->
            </div><!-- card -->
            <div class="program-container">
                @foreach ($actividadesHorarios as $actividadesHorario)
                    <div class="card ">
                        <div class="card-body">
                            <div class="p-container">
                                <div class="col-horario">
                                    <h3>{{ $actividadesHorario[0]->horario_inicial }}</h3>
                                </div>
                                <div class="col-programas">
                                    @foreach ($actividadesHorario as $actividadHorario)
                                        @php
                                        if(count($actividadHorario->reservacion) < 1){
                                            continue;
                                        }
                                        @endphp
                                        <div class="programa">
                                            <strong class="p-title"><a
                                                    href="reservaciones/create/?id={{ $actividadHorario->actividad->id }}&h={{ $actividadHorario->id }}&f={{ $fechaActividades }}">{{ $actividadHorario->actividad->nombre }}</a></strong>
                                            <div class="p-detalles">
                                                <div>
                                                    <p class="mg-b-0">Reserv. total: <span>
                                                            @php
                                                                $numeroReservaciones = 0;
                                                                foreach ($actividadHorario->reservacion as $reservacion) {
                                                                    foreach ($reservacion->reservacionDetalle as $reservacionDetalle){
                                                                        if($reservacionDetalle->actividad_id == $actividadHorario->actividad->id){
                                                                            $numeroReservaciones += $reservacionDetalle->numero_personas;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            {{ $numeroReservaciones }}
                                                        </span></p>
                                                </div>
                                                <div>
                                                    <p class="mg-b-0">Disponibilidad:
                                                        <span>{{ $actividadHorario->actividad->capacidad - $numeroReservaciones }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <table class="display reservaciones-table" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Folio</th>
                                                        <th>Cliente</th>
                                                        <th>Per.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($actividadHorario->reservacion as $reservacion)
                                                        @php
                                                            $estatus = '';
                                                            switch ($reservacion->estatus_pago) {
                                                                case 0:
                                                                    $estatus = 'Pendiente';
                                                                    break;
                                                                case 1:
                                                                    $estatus = 'Parcial';
                                                                    break;
                                                                case 2:
                                                                    $estatus = 'Pagado';
                                                                    break;
                                                            }
                                                            $numeroPersonas = 0;
                                                            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                                                                if($reservacionDetalle->actividad_id == $actividadHorario->actividad->id){
                                                                    $numeroPersonas = $reservacionDetalle->numero_personas;
                                                                }
                                                            }
                                                        @endphp
                                                        <tr class={{$estatus}}>
                                                            <td class="folio-link">
                                                                <a href="
                                                                @can('Reservaciones.update') 
                                                                    @if($reservacion->estatusPago !== 2)
                                                                        {{ url('reservaciones/'.$reservacion->id.'/edit?accion=edit') }}
                                                                    @else
                                                                        @role('Administrador')
                                                                            {{ url('reservaciones/'.$reservacion->id.'/edit?accion=edit') }}
                                                                        @endrole
                                                                    @endif
                                                                @endcan
                                                                ">{{ $reservacion->folio }}</a>
                                                            </td>
                                                            <td>{{ strlen($reservacion->nombre_cliente) > 15 ? substr($reservacion->nombre_cliente, 0, 15) . '...' : $reservacion->nombre_cliente }}</td>
                                                            <td>{{ $numeroPersonas }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div><!-- col -->
    </div><!-- row -->
@endsection
