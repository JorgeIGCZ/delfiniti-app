@extends('layouts.app')
@section('scripts')
    <script>
        let comisionistasTable;
        $(function(){
            comisionistasTable = new DataTable('#comisionistas', {
                ajax: function (d,cb,settings) {
                    $('.loader').show();
                    axios.get('/tiendacomisionistas/show')
                    .then(function (response) {
                        $('.loader').hide();
                        cb(response.data)
                    })
                    .catch(function (error) {
                        $('.loader').hide();
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'nombre' },
                    { defaultContent: 'comision', 'render': function ( data, type, row ) 
                        {
                            return  `${row.comision}%`;
                        }
                    },
                    { defaultContent: 'iva', 'render': function ( data, type, row ) 
                        {
                            return  `${row.iva}%`;
                        }
                    },
                    { defaultContent: 'descuentoImpuesto', 'render': function ( data, type, row ) 
                        {
                            return  `${row.descuentoImpuesto}%`;
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let estatusRow = '';
                            let view       = '';  
                            @can('TiendaComisionista.update')
                                view    =   `<small> 
                                                <a href="tiendacomisionistas/${row.id}/edit">Editar</a>
                                                ${estatusRow}
                                            </small>`;
                            @endcan
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
            <h2 class="az-dashboard-title">Comisionistas</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="comisionistas" class="display dt-responsive" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                        <th>Descuento impuesto</th>
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
