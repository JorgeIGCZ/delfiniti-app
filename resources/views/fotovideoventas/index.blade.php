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
            
            const ventasTable = new DataTable('#ventas', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    const ventas = document.getElementById('ventas-form');
                    axios.post('/fotovideoventas/get',{
                        "_token"  : '{{ csrf_token() }}',
                        "estatus" : ventas.elements['estatus'].value,
                        "fecha"   : ventas.elements['fecha'].value,
                        "fechaInicio"  : ventas.elements['start_date'].value,
                        "fechaFinal"  : ventas.elements['end_date'].value
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
                    { data: 'fotografo' },
                    { data: 'tiposPago' },
                    { defaultContent: 'total', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.total);
                        }
                    },
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
                            let viewRow = '';
                            let editRow = '';
                            let options = [];

                            @can('FotoVideoVentas.index')
                            viewRow = `<a href="fotovideoventas/${row.id}">Ver</a>`;
                            @endcan

                            @can('FotoVideoVentas.update') 
                                @role('Administrador')
                                    editRow = `<a href="fotovideoventas/${row.id}/edit?accion=edit">Editar</a>`;
                                @endrole

                                if(row.estatusPago !== 2){
                                    editRow = `<a href="fotovideoventas/${row.id}/edit?accion=edit">Editar</a>`;
                                }
                            @endcan

                            options = [viewRow, editRow];
                            options = options.filter(option => option != ""); 

                            let view    =   `<small>
                                                ${options.join(' | ')}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );

            document.getElementById('fecha_venta').addEventListener('change', (event) =>{
                const seleccion = event.target.value;
                const rangoFecha = document.getElementById('rango-fecha');

                $('#start_date').datepicker('setDate', null);
                $('#end_date').datepicker('setDate', null);

                rangoFecha.style.display = "none";
                if(seleccion !== "custom"){
                    ventasTable.ajax.reload();
                    return;
                }
                rangoFecha.style.display = "block";
            });

            document.getElementById('estatus_venta').addEventListener('change', (event) =>{
                const rangoFecha = document.getElementById('rango-fecha');

                $('#start_date').datepicker('setDate', null);
                $('#end_date').datepicker('setDate', null);

                rangoFecha.style.display = "block";
                
                ventasTable.ajax.reload();
                return;  
            });

            document.getElementById('start_date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    ventasTable.ajax.reload();
                    return;
                }
            });

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end_date').value;
                const fechaFinal = document.getElementById('start_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    ventasTable.ajax.reload();
                    return;
                }
            }
            
        } );
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Ventas</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <form class="row g-3 align-items-center f-auto" id="ventas-form" method="GET">
                
                <div class="form-group col-md-2">
                    <label for="fecha">Fecha</label>
                    <select class="form-control fecha" name="fecha" id="fecha_venta">
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

                <div class="form-group col-md-2">
                    <label for="estatus">Estatus</label>
                    <select class="form-control estatus" name="estatus" id="estatus_venta">
                        <option value="todos" selected="selected">Todos</option>
                        <option value="parcial">Parcial</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
            </form>
            
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <table id="ventas" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Folio</th>
                                        <th>Cliente</th>
                                        <th>Fotografo</th>
                                        <th>Tipo pago</th>
                                        <th>Total</th>
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
