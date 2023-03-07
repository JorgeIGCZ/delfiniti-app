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
            
            const pedidosTable = new DataTable('#pedidos', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    const pedidos = document.getElementById('pedidos-form');
                    axios.post('/pedidos/show',{
                        "_token"  : '{{ csrf_token() }}',
                        "estatus" : pedidos.elements['estatus'].value,
                        "fecha"   : pedidos.elements['fecha'].value,
                        "fechaInicio"  : pedidos.elements['start_date'].value,
                        "fechaFinal"  : pedidos.elements['end_date'].value
                    })
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
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
                    { data: 'productos' },
                    { data: 'numeroProductos' },
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
                            let cloneRow = '';
                            let payRow = '';
                            let editRow = '';
                            let options = [];
                            @can('Pedidos.update')
                                @role('Administrador')
                                    editRow = `<a href="pedidos/${row.id}/edit?accion=edit">Editar</a>`;
                                @endrole

                                if(row.estatusPago !== 2){
                                    editRow = `<a href="pedidos/${row.id}/edit?accion=edit">Editar</a>`;
                                }
                            @endcan

                            options = [editRow];
                            options = options.filter(option => option != ""); 

                            let view    =   `<small>
                                                ${options.join(' | ')}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );

            document.getElementById('fecha_pedido').addEventListener('change', (event) =>{
                const seleccion = event.target.value;
                const rangoFecha = document.getElementById('rango-fecha');

                $('#start_date').datepicker('setDate', null);
                $('#end_date').datepicker('setDate', null);

                rangoFecha.style.display = "none";
                if(seleccion !== "custom"){
                    pedidosTable.ajax.reload();
                    return;
                }
                rangoFecha.style.display = "block";
            });

            document.getElementById('estatus_pedido').addEventListener('change', (event) =>{
                const rangoFecha = document.getElementById('rango-fecha');

                $('#start_date').datepicker('setDate', null);
                $('#end_date').datepicker('setDate', null);

                rangoFecha.style.display = "block";
                
                pedidosTable.ajax.reload();
                return;
            });

            document.getElementById('start_date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    pedidosTable.ajax.reload();
                    return;
                }
            });

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end_date').value;
                const fechaFinal = document.getElementById('start_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    pedidosTable.ajax.reload();
                    return;
                }
            }
            
        } );
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Pedidos</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <form class="row g-3 align-items-center f-auto" id="pedidos-form" method="GET">
                
                <div class="form-group col-md-2">
                    <label for="fecha">Fecha</label>
                    <select class="form-control fecha" name="fecha" id="fecha_pedido">
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
                            <table id="pedidos" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Productos</th>
                                        <th># Productos</th>
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
