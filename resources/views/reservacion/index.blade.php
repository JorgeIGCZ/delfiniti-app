@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            comisionistasTable = new DataTable('#reservaciones', {
                ajax: function (d,cb,settings) {
                    axios.get('/reservacion/get')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'reservacionId' },
                    { data: 'actividad' },
                    { data: 'horario' },
                    { data: 'fecha' },
                    { data: 'cliente' },
                    { data: 'personas' },
                    { data: 'notas' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            let editRow   = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            editRow = `<a href="reservacion/edit/${row.id}">Editar</a>`;
                            let view    =   `<small> 
                                                <a href="reservacion/show/${row.id}">Ver</a> |
                                                ${editRow}
                                                ${removeRow}
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
                            <table id="reservaciones" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Reservacion Id</th>
                                        <th>Actividad</th>
                                        <th>Horario</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Personas</th>
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
