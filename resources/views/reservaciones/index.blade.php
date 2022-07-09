@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            comisionistasTable = new DataTable('#reservaciones', {
                ajax: function (d,cb,settings) {
                    axios.get('/reservaciones/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'folio' },
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
                            editRow = `<a href="reservaciones/${row.reservacionId}/edit">Editar</a>`;
                            let view    =   `<small> 
                                                ${editRow}
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
                                        <th>Folio</th>
                                        <th>Actividad</th>
                                        <th>Horario</th>
                                        <th>Fecha creación</th>
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
