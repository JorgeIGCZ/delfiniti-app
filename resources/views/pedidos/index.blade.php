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

            // document.getElementById('estatus_pedido').addEventListener('change', (event) =>{
            //     const rangoFecha = document.getElementById('rango-fecha');

            //     $('#start_date').datepicker('setDate', null);
            //     $('#end_date').datepicker('setDate', null);

            //     rangoFecha.style.display = "block";
                
            //     pedidosTable.ajax.reload();
            //     return;
            // });

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

        const pedidosTable = new DataTable('#pedidos', {
            order: [[0, 'desc']],
            ajax: function (d,cb,settings) {
                $('.loader').show();
                const pedidos = document.getElementById('pedidos-form');
                axios.post('/pedidos/get',{
                    "_token"  : '{{ csrf_token() }}',
                    "fecha"   : pedidos.elements['fecha'].value,
                    "fechaInicio"  : pedidos.elements['start_date'].value,
                    "fechaFinal"  : pedidos.elements['end_date'].value,
                    "view" : "index"
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
                { data: 'proveedor' },
                { data: 'cantidad' },
                { data: 'fechaCreacion' },
                { data: 'comentarios' },
                { defaultContent: 'estatus_proceso', 'render': function ( data, type, row ) 
                    {
                        if(row.estatusProceso){
                            return "<p class='paid'>Validado</p>";
                        }
                        return "<p class='partial'>Pendiente</p>";
                    }
                }, 
                { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row )
                    {
                        let editStatusRow = '';
                        let payRow = '';
                        let editRow = '';
                        let viewRow = '';
                        let options = [];

                        @can('TiendaPedidos.update')
                            if(!row.estatusProceso){
                                editRow = `<a href="/pedidos/${row.id}/edit?accion=edit">Editar</a>`;
                            }
                        @endcan

                        // @can('TiendaPedidos.cancel')
                        //     if(row.estatus){
                        //         editStatusRow = `<a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                        //     }else{
                        //         editStatusRow = `<a href="#!" onclick="updateActividadEstatus(${row.id},1)" >Reactivar</a>`;
                        //     }
                        // @endcan

                        @can('TiendaPedidos.index')
                            viewRow = `<a href="/pedidos/${row.id}">Ver</a>`;
                        @endcan
                        
                        options = [viewRow,editRow,editStatusRow];
                        options = options.filter(option => option != ""); 
                        let view    =   `<small>
                                            ${options.join(' | ')}
                                        </small>`;
                        return  view;
                    }
                }
            ]
        } );

        function verificacionInactivar(id){
            Swal.fire({
                title: '¿Desea inactivar el pedido?',
                text: "Los productos dejarán de estar disponibles!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Inactivar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateActividadEstatus(id,0);
                }else{
                    return false;
                }
            }) 
        }

        function updateActividadEstatus(id,estatus){
            $('.loader').show();
            axios.post(`pedidos/estatus/${id}`, {
                '_token'  : '{{ csrf_token() }}',
                'estatus' : estatus,
                '_method' : 'PATCH'
            })
            .then(function (response) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Registro actualizado',
                    showConfirmButton: false,
                    timer: 1500
                })
                pedidosTable.ajax.reload();
            })
            .catch(function (error) {
                $('.loader').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Actualización fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            });
        }
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
                                        <th>Proveedor</th>
                                        <th># Productos</th>
                                        <th>Fecha creación</th>
                                        <th>Comentarios</th>
                                        <th>Estatus validación</th>
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
