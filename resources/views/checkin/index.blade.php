@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            checkinTable = new DataTable('#reservaciones', {
                order: [[0, 'desc']],
                ajax: function (d,cb,settings) {
                    axios.get('/checkin/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                }, 
                columns: [
                    { data: 'id' },
                    { data: 'folio' },
                    { data: 'cliente' },
                    { data: 'actividad' },
                    { data: 'personas' },
                    { data: 'horario' },
                    { data: 'fecha' },
                    { data: 'fechaCreacion' },
                    { data: 'notas' },
                    { defaultContent: 'checkin', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            return  (row.checkin ? 'Registrado' : 'Pendiente');
                        }
                    },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let checkinEstatus = '';
                            let checkinAccion = '';
                            if(!row.checkin){
                                checkinAccion = `<button class="btn btn-outline-success btn-block form-control" onclick="verificacionCheckIn(${row.id})" >Registrar</button>`;
                            }
                            let view    =   `<small> 
                                                ${checkinAccion}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
        } );

        function verificacionCheckIn(id){
            Swal.fire({
                title: '¿Desea registrar visita?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, registrar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    checkIn(id,);
                }else{
                    return false;
                }
            }) 
        }

        function checkIn(id){
            axios.post(`checkin/registro/${id}`, {
                '_token'  : '{{ csrf_token() }}',
                'estatus' : 1,
                '_method' : 'PATCH'
            })
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Visita registrada',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    checkinTable.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Actualización fallida',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
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
            <h2 class="az-dashboard-title">Check-in</h2>
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
                                        <th>Cliente</th>
                                        <th>Actividad</th>
                                        <th>Personas</th>
                                        <th>Horario</th>
                                        <th>Fecha Actividad</th>
                                        <th>Fecha creación</th>
                                        <th>Notas</th>
                                        <th>Registro</th>
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
