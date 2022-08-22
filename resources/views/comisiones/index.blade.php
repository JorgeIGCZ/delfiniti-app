@extends('layouts.app')
@section('scripts')
    <script>
        let descuentocodigosTable;
        
        $(function(){
            descuentocodigosTable = new DataTable('#comisiones', {
                ajax: function (d,cb,settings) {
                    axios.get('/comisiones/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'comisionista' },
                    { data: 'reservacion' },
                    { data: 'comisionBruta' },
                    { data: 'iva' },
                    { data: 'descuentoImpuesto' },
                    { data: 'comisionNeta' },
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
                            let estatusRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                // if(row.estatus){
                                //     estatusRow = `| <a href="#!" onclick="verificacionInactivar(${row.id})" >Inactivar</a>`;
                                // }else{
                                //     estatusRow = `| <a href="#!" onclick="updateDescuentoCodigoEstatus(${row.id},1)" >Reactivar</a>`;
                                // }
                            //}
                            if(row.estatus == 1){
                                estatusRow = `<a href="comisiones/${row.id}/edit">Editar</a>`;
                            }
                            let view    =   `<small> 
                                                ${estatusRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
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
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisiones" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Comisionista</th>
                                        <th>Reservación</th>
                                        <th>Comision bruta</th>
                                        <th>Iva</th>
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
