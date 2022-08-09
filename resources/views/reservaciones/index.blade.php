@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            comisionistasTable = new DataTable('#reservaciones', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    axios.get('/reservaciones/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'folio' },
                    { data: 'cliente' },
                    { data: 'actividad' },
                    { data: 'personas' },
                    { data: 'horario' },
                    { data: 'fecha' },
                    { defaultContent: 'Estatus', 'render': function ( data, type, row )
                        {
                            let estatus = '';
                            switch (row.estatusPago) {
                                case 0:
                                    estatus = "<p class='pending'>Pendiente</p>";
                                    break;
                                case 1:
                                    estatus = "<p class='partial'>Parcial</p>";
                                    break;
                                case 2:
                                    estatus = "<p class='paid'>Pagado</p>";
                                    break;
                            }
                            return  estatus;
                        }
                    },
                    { data: 'fechaCreacion' },
                    { data: 'notas' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row )
                        {
                            let payRow = '';
                            let editRow   = '';
                            editRow = `<a href="reservaciones/${row.id}/edit?accion=edit">Editar</a>`;
                            if(row.estatusPago !== 2){
                                payRow = `| <a href="reservaciones/${row.id}/edit?accion=pago#detalle-reservacion-contenedor">Pagar</a>`;
                            }
                            let view    =   `<small>
                                                ${editRow}
                                                ${payRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
        } );
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Reservaciones</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <table id="reservaciones" class="stripe" style="width:100%">
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
                                        <th>Fecha creación</th>
                                        <th>Notas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
