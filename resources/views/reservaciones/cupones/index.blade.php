@extends('layouts.app')
@section('scripts')
    <script>
        let cuponesTable;
        
        $(function(){
            $('.input-daterange').datepicker({
                format: 'yyyy/mm/dd',
                language: 'es'
            }).on("change", function() {
                isFechaRangoValida();
            });

            const cuponesTable = new DataTable('#cupones-table', {
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5', 
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    footer: true,
                    text: 'Exportar Excel',
                    title: 'DELFINITI IXTAPA S.A. DE C.V. - REPORTE CUPONES',
                    exportOptions: {
                        columns: [1, 2, 3, 4]
                    }
                }],
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    const cupones = document.getElementById('cupones-form');
                    axios.post('/cupones/get',{
                        '_token'  : '{{ csrf_token() }}',
                        "fecha"   : cupones.elements['fecha'].value,
                        "fechaInicio"  : cupones.elements['start_date'].value,
                        "fechaFinal"  : cupones.elements['end_date'].value
                    })
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'folio' },
                    { data: 'cupon' },
                    { defaultContent: 'cantidad', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.cantidad);
                        }
                    },
                    { data: 'fecha' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row )
                        {
                            let viewRow = '';
                            let options = [];
                            @can('Reservaciones.index')
                                viewRow = `<a href="reservaciones/${row.reservacionId}" target="_blank">Ver</a>`;
                            @endcan

                            options = [viewRow];
                            options = options.filter(option => option != ""); 

                            let view    =   `<small>
                                                ${options.join(' | ')}
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
                    cuponesTable.ajax.reload();
                    return;
                }
                rangoFecha.style.display = "block";
            });

            document.getElementById('start_date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    cuponesTable.ajax.reload();
                    return;
                }
            });

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end_date').value;
                const fechaFinal = document.getElementById('start_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    cuponesTable.ajax.reload();
                    return;
                }
            }
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Cupones</h2>
        </div>
    </div><!-- az-dashboard-one-title -->

     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
             <form class="row g-3 align-items-center f-auto" id="cupones-form" method="GET">
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
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="cupones-table" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Reservación</th>
                                        <th>Cupón</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
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
