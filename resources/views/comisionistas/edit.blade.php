@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            /*
            let comisionistasTable = new DataTable('#comisionistas', {
                ajax: function (d,cb,settings) {
                    axios.get('/configuracion/comisionistas/show')
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
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" class="susp-alumno" comisionistaId="${row.id}">Eliminar</a>`;
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
            document.getElementById('comisionsistas-form').addEventListener('submit', (event) =>{
                const comisionistas = document.getElementById('comisionsistas-form');
                axios.post('/configuracion/comisionistas/store', {
                    '_token'  : '{{ csrf_token() }}',
                    "codigo"  : comisionistas.elements['codigo'].value,
                    "nombre"  : comisionistas.elements['nombre'].value,
                    "comision": comisionistas.elements['comision'].value,
                    "iva"     : comisionistas.elements['iva'].value
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
                event.preventDefault();
                comisionistas.reset();
                comisionistasTable.ajax.reload();
            });
            */
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">
                {{$comisionista['nombre']}}
            </h2>
            <p class="az-dashboard-text">Edición de comisionista</p>
        </div>
        <div class="az-content-header-right">
            <a href="/configuracion/comisionistas" class="btn btn-light btn-block mt-33">Comisionistas</a>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="comisionsistas-form" action="{{route("comisionistasUpdate",$comisionista['id'])}}">
                            @csrf
                            <input type="hidden" name="id" class="form-control" value="{{$comisionista['id']}}">  
                            <div class="form-group col-2 mt-3">
                                <label for="new-codigo" class="col-form-label">Código</label>    
                                <input type="text" id="new-codigo" name="codigo" class="form-control" value="{{$comisionista['codigo']}}">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisionista</label>    
                                <input type="text" id="new-nombre" name="nombre" class="form-control" value="{{$comisionista['nombre']}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisión %</label>
                                <input type="number" id="new-nombre" name="comision" class="form-control" min="1" max="90" value="{{$comisionista['comision']}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="new-iva" class="col-form-label">Iva %</label>
                                <input type="number" id="new-iva" name="iva" class="form-control" min="1" max="90" value="{{$comisionista['iva']}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Editar comisionista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
