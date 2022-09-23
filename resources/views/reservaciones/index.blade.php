@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            $('.input-daterange').datepicker({
                format: 'yyyy/mm/dd',
                language: 'es'
            }).on("change", function() {
                isFechaRangoValida();
            });
            
            const comisionistasTable = new DataTable('#reservaciones', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    const reservaciones = document.getElementById('reservaciones-form');
                    axios.post('/reservaciones/show',{
                        '_token'  : '{{ csrf_token() }}',
                        "fecha"   : reservaciones.elements['fecha'].value,
                        "fechaInicio"  : reservaciones.elements['start_date'].value,
                        "fechaFinal"  : reservaciones.elements['end_date'].value
                    })
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                rowCallback: function( row, data, index ) {
                    if ( data.cortesia == "Cortesia" ) {
                        $(row).addClass("highlight");
                    }
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
                    },{ defaultContent: 'Cortesias', 'render': function ( data, type, row )
                        {
                            return  (row.cortesia !== '' ? `<p class='paid'>${row.cortesia}</p>` : '');
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

            document.getElementById('fecha_reservacion').addEventListener('change', (event) =>{
                const seleccion = event.target.value;
                const rangoFecha = document.getElementById('rango-fecha');

                $('#start_date').datepicker('setDate', null);
                $('#end_date').datepicker('setDate', null);

                rangoFecha.style.display = "none";
                if(seleccion !== "custom"){
                    comisionistasTable.ajax.reload();
                    return;
                }
                rangoFecha.style.display = "block";
            });

            document.getElementById('start_date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    comisionistasTable.ajax.reload();
                    return;
                }
            });

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end_date').value;
                const fechaFinal = document.getElementById('start_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    comisionistasTable.ajax.reload();
                    return;
                }
            }
            
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
            <form class="row g-3 align-items-center f-auto" id="reservaciones-form" method="GET">
                
                <div class="form-group col-md-2">
                    <label for="fecha">Fecha</label>
                    <select class="form-control fecha" name="fecha" id="fecha_reservacion">
                        <option value="dia" selected="selected">Día Actual</option>
                        <option value="mes">Mes Actual</option>
                        <option value="custom">Rango</option>
                    </select>
                </div>
                <div class="form-group col-md-3" id="rango-fecha" style="display: none;">
                    <label for="fecha">Mes</label>
                    <div class="input-group input-daterange">
                        <input id="start_date" name="start_date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa"> 
                        <span class="input-group-addon" style="padding: 0 8px;align-self: center;background: none;border: none;">Al</span> 
                        <input id="end_date" name="end_date" type="text" class="form-control" readonly="readonly" placeholder="dd/mm/aaaa">
                    </div>
                </div>
            </form>
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
                                        <th>Cortesia</th>
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
