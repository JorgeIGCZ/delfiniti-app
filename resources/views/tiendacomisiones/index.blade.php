@extends('layouts.app')
@section('scripts')
    <script>
        let descuentocodigosTable;
        
        $(function(){
            $('.input-daterange').datepicker({
                format: 'yyyy/mm/dd',
                language: 'es'
            }).on("change", function() {
                isFechaRangoValida();
            });

            const descuentocodigosTable = new DataTable('#comisiones', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    const ventas = document.getElementById('ventas-form');
                    axios.post('/tiendacomisiones/show',{
                        '_token'  : '{{ csrf_token() }}',
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
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'comisionista' },
                    { data: 'venta' },
                    { defaultContent: 'total', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.total);
                        }
                    },
                    { defaultContent: 'iva', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.iva);
                        }
                    },
                    { defaultContent: 'comisionBruta', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.comisionBruta);
                        }
                    },
                    { defaultContent: 'descuentoImpuesto', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.descuentoImpuesto);
                        }
                    },
                    { defaultContent: 'comisionNeta', 'render': function ( data, type, row ) 
                        {
                            
                            return formatter.format(row.comisionNeta);
                        }
                    },
                    { data: 'fecha' },
                    { defaultContent: 'estatus', 'render': function ( data, type, row ) 
                        {
                            if(row.estatus == 1){
                                return 'Cobrado';
                            }else if(row.estatus == 2){
                                return 'Pagado';
                            }
                            return '';
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let view       = '';
                            let estatusRow = '';
                            let recalcular = '';

                            if(row.tipo === 'directivo'){
                                estatusRow = `<a href="/tiendacomisiones/${row.id}/edit">Editar</a> | `;
                            }

                            if(row.estatus == 1){
                                recalcular = `<a href="#!" class="recalcular-comisiones" ventaFolio="${row.venta}" ventaId="${row.ventaId}">Recalcular</a>`;
                            }

                            @can('TiendaComisiones.update')
                                view    =   `<small> 
                                        ${estatusRow}
                                        ${recalcular}
                                    </small>`;
                            @endcan
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
                    descuentocodigosTable.ajax.reload();
                    return;
                }
                rangoFecha.style.display = "block";
            });

            document.getElementById('start_date').addEventListener('change', (event) =>{
                const fechaInicio = event.target.value;
                const fechaFinal = document.getElementById('end_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    descuentocodigosTable.ajax.reload();
                    return;
                }
            });

            on("click", ".recalcular-comisiones", function(event) {
                const ventaFolio = this.getAttribute("ventaFolio");
                const ventaId = this.getAttribute("ventaId");
                Swal.fire({
                    title: '¿Recalcular comisiones?',
                    text: `Todas las comisiones serán recalculadas para la venta ${ventaFolio}, ¿desea proceder?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, recalcular!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        recalculateComisiones(ventaId)
                    }
                })
            });

            function recalculateComisiones(ventaId){
                $('.loader').show();
                axios.post('/tiendacomisiones/recalculateComisiones', {
                    '_token': token(),
                    'ventaId': ventaId
                })
                .then(function (response) {
                    $('.loader').hide();
                    if(response.data.result == "Success"){
                        Swal.fire({
                            icon: 'success',
                            title: 'Comisiones actualizadas',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        descuentocodigosTable.ajax.reload();
                    }else{
                        $('.loader').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Actualización fallida',
                            html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                            showConfirmButton: true
                        })
                    }
                })
                .catch(function (error) {
                    $('.loader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: `Actualización fallida E:${error.message}`,
                        showConfirmButton: true
                    })
                });
            }

            function isFechaRangoValida(){
                const fechaInicio = document.getElementById('end_date').value;
                const fechaFinal = document.getElementById('start_date').value;
                if(fechaInicio !== "" && fechaFinal !== ""){
                    descuentocodigosTable.ajax.reload();
                    return;
                }
            }
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Comisiones</h2>
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
            </form>
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisiones" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Comisionista</th>
                                        <th>Reservación</th>
                                        <th>Total</th>
                                        <th>Iva</th>
                                        <th>Comision bruta S/IVA</th>
                                        <th>Descuento impuesto</th>
                                        <th>Comision neta</th>
                                        <th>Fecha registro comisión</th>
                                        <th>Estatus</th>
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
