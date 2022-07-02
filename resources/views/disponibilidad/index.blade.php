@extends('layouts.app')
@section('scripts')
    <script>
        /*
    		let comisionistasTable;
    		function verificacionDestroy(id){
    			Swal.fire({
    				title: '¿Desea eliminar comisionista?',
    				text: "Este proceso no se puede revertir!",
    				icon: 'warning',
    				showCancelButton: true,
    				confirmButtonColor: '#3085d6',
    				cancelButtonColor: '#d33',
    				confirmButtonText: '¡Si, Eliminar!'
    			}).then((result) => {
    				if (result.isConfirmed) {
    					destroyComisionista(id);
    				}else{
    					return false;
    				}
    			}) 
    		}
    		function destroyComisionista(id){
    			axios.get(`/comisionistas/destroy/${id}`)
    			.then(function (response) {
    				Swal.fire({
    					icon: 'success',
    					title: 'Registro eliminado',
    					showConfirmButton: false,
    					timer: 1500
    				})
    			})
    			.catch(function (error) {
    				Swal.fire({
    					icon: 'error',
    					title: 'Eliminacion fallida',
    					showConfirmButton: false,
    					timer: 1500
    				})
    			});
    			comisionistasTable.ajax.reload();
    		}
    		*/
        /*
    		function createDisponibilidad(disponibilidad){
    			axios.post('/disponibilidad/store', {
    				'_token'  : '{{ csrf_token() }}',
    				"nombre"  : comisionistas.elements['nombre'].value,
    				"capacidad": comisionistas.elements['capacidad'].value,
    				"horarioInicial" : comisionistas.elements['horario-inicial'].value,
    				"horarioFinal"   : comisionistas.elements['horario-final'].value
    			})
    			.then(function (response) {
    				Swal.fire({
    					icon: 'success',
    					title: 'Registro creado',
    					showConfirmButton: false,
    					timer: 1500
    				})
    			})
    			.catch(function (error) {
    				Swal.fire({
    					icon: 'error',
    					title: 'Registro fallido',
    					showConfirmButton: false,
    					timer: 1500
    				})
    			});
    			comisionistas.reset();
    			comisionistasTable.ajax.reload();
    		}
    		*/
        $(function() {
            let reservaciones = new DataTable('.reservaciones-table', {
                searching: false,
                paging: false,
                info: false,
                columnDefs: [{
                        targets: 0,
                        className: 'dt-body-center'
                    },
                    {
                        targets: -1,
                        className: 'dt-body-center'
                    }
                ]
            });
            /*
            comisionistasTable = new DataTable('#comisionistas', {
            	ajax: function (d,cb,settings) {
            		axios.get('/comisionistas/show')
            		.then(function (response) {
            			cb(response.data)
            		})
            		.catch(function (error) {
            		});
            	},
            	columns: [
            		{ data: 'codigo' },
            		{ data: 'nombre' },
            		{ data: 'comision' },
            		{ data: 'iva' },
            		{ defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
            			{
            				let removeRow = '';
            				//if('{{ @session()->get('user_roles')['Alumnos']->Estatus }}' == 'Y'){
            					removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
            				//}
            				let view    =   `<small> 
        									<a href="comisionistas/edit/${row.id}">Editar</a>
        									${removeRow}
        								</small>`;
            				return  view;
            			}
            		}
            	]
            } );
            */
            /*
            document.getElementById('actividad-form').addEventListener('submit', (event) =>{
            	event.preventDefault();
            	const actividad = document.getElementById('actividad-form');
            	createActividad(actividad);
            });
            **/
            document.getElementById('toogle-info').addEventListener('click', (event) => {
                event.preventDefault();
                const reservacionesTable = document.querySelectorAll('.reservaciones-table');
                const toogleBtn = document.querySelectorAll('#toogle-info>i');
                reservacionesTable.forEach(reservacionTable => {
                    if (reservacionTable.style.display === "none") {
                        reservacionTable.style.display = "block";
                    } else {
                        reservacionTable.style.display = "none";
                    }
                });

                if (toogleBtn[0].className == 'fa fa-compress') {
                    toogleBtn[0].classList.remove('fa-compress');
                    toogleBtn[0].classList.add('fa-expand');
                } else {
                    toogleBtn[0].classList.remove('fa-expand');
                    toogleBtn[0].classList.add('fa-compress');
                }
            });

        });
    </script>
@endsection
@section('content')
    <style>
        .az-content .container {
            max-width: 100%;
            padding: 0 20px;
        }
    </style>
    <div class="az-dashboard-one-title disponibilidad-title">
        <div>
            <h2 class="az-dashboard-title">Disponibilidad</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20 disponibilidad-section">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" action="disponibilidad" method="GET">
                            @csrf
                            <div class="col-auto actividades mt-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="fecha-actividades" class="col-form-label">fecha</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" id="fecha-actividades" name="fecha_actividades"
                                            class="form-control" value="{{ $fechaActividades }}" onChange="this.form.submit()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-3">
                            </div>
                        </form>

                        <button id="toogle-info">
                            <i class="fa fa-expand" aria-hidden="true"></i>
                        </button>
                    </div>
                </div><!-- card-body -->
            </div><!-- card -->
            <div class="program-container">
                @foreach ($actividadesHorarios as $actividadesHorario)
                    <div class="card ">
                        <div class="card-body">
                            <div class="p-container">
                                <div class="col-horario">
                                    <h3>{{ $actividadesHorario[0]->horario_inicial }}</h3>
                                </div>
                                <div class="col-programas">
                                    @foreach ($actividadesHorario as $actividadHorario)
                                        <div class="programa">
                                            <strong class="p-title"><a
                                                    href="reservaciones/create/?id={{ $actividadHorario->actividad->id }}&h={{ $actividadHorario->id }}&f={{ $fechaActividades }}">{{ $actividadHorario->actividad->nombre }}</a></strong>
                                            <div class="p-detalles">
                                                <div>
                                                    <p class="mg-b-0">Reserv. total: <span>
                                                            @php
                                                                $numeroReservaciones = 0;
                                                                foreach ($actividadHorario->reservacionDetalle as $reservacionDetalle) {
                                                                    $numeroReservaciones += $reservacionDetalle->numero_personas;
                                                                }
                                                            @endphp
                                                            {{ $numeroReservaciones }}
                                                        </span></p>
                                                </div>
                                                <div>
                                                    <p class="mg-b-0">Disponibilidad:
                                                        <span>{{ $actividadHorario->actividad->capacidad - $numeroReservaciones }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <table class="display reservaciones-table" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Reserva</th>
                                                        <th>Cliente</th>
                                                        <th>Personas</th>
                                                        <th>Estatus</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($actividadHorario->reservacionDetalle as $reservacionDetalle)
                                                        <tr>
                                                            <td>{{ $reservacionDetalle->reservacion_id }}</td>
                                                            <td>{{ $reservacionDetalle->reservacion->nombre_cliente }}</td>
                                                            <td>{{ @$reservacionDetalle->numero_personas }}</td>
                                                            <td>{{ $actividadHorario->horario_inicial }}</td>
                                                            <td><a href="#">Ver</a> | <a href="#">Editar</a></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div><!-- col -->
    </div><!-- row -->
@endsection
