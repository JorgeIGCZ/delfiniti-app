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
                    const reservaciones = document.getElementById('reservaciones-form');
                    axios.post('/comisiones/show',{
                        '_token'  : '{{ csrf_token() }}',
                        "fecha"   : reservaciones.elements['fecha'].value,
                        "fechaInicio"  : reservaciones.elements['start_date'].value,
                        "fechaFinal"  : reservaciones.elements['end_date'].value
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
                    { data: 'tipo' },
                    { data: 'reservacion' },
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
                                return 'Cobrado (pendiente pago)';
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
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                // if(row.estatus){
                                //     estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                // }else{
                                //     estatusRow = `| <a href="#!" onclick="updateDescuentoCodigoEstatus(${row.id},1)" >Reactivar</a>`;
                                // }
                            //}
                            if(row.estatus == 1){
                                if(row.tipo === 'comisionista'){
                                    estatusRow = `<a href="comisiones/${row.id}/edit">Editar</a> | `;
                                }
                                recalcular = `<a href="#!" class="recalcular-comisiones" reservacionFolio="${row.reservacion}" reservacionId="${row.reservacionId}">Recalcular</a>`;
                                //comisiones/recalculateComisiones
                            }
 
                            @can('Comisiones.update')
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
            document.getElementById('fecha_reservacion').addEventListener('change', (event) =>{
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
                const reservacionFolio = this.getAttribute("reservacionFolio");
                const reservacionId = this.getAttribute("reservacionId");
                Swal.fire({
                    title: '¿Recalcular comisiones?',
                    text: `Todas las comisiones serán recalculadas para la reservacion ${reservacionFolio}, ¿desea proceder?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, recalcular!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        recalculateComisiones(reservacionId)
                    }
                })
            });

            function recalculateComisiones(reservacionId){
                $('.loader').show();
                axios.post('/comisiones/recalculateComisiones', {
                    '_token': token(),
                    'reservacionId': reservacionId
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
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisiones" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Comisionista</th>
                                        <th>Canal</th>
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
