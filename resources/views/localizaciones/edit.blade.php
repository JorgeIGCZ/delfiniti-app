@extends('layouts.app')
@section('scripts')
    <script>
        function updateLocalizacion(localizaciones){
            axios.post(`/localizaciones/${localizaciones.elements['id'].value}`, {
                '_token'   : '{{ csrf_token() }}',
                '_method'  : 'put',  
                "codigo"   : localizaciones.elements['codigo'].value,
                "nombre"   : localizaciones.elements['nombre'].value,
                "direccion": localizaciones.elements['direccion'].value,
                "telefono" : localizaciones.elements['telefono'].value
            })
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro actualizado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Registro fallido',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Registro fallido',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
                comisionistas.reset();
            });
        }
        $(function(){
            document.getElementById('localizaciones-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const localizaciones = document.getElementById('localizaciones-form');
                updateLocalizacion(localizaciones);
            });
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Alojamiento</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="localizaciones-form">
                            @csrf
                            <input type="hidden" name="id" value="{{$localizacion->id}}">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" disabled="disabled" value="{{$localizacion->codigo}}">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Lugar de alojamiento</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$localizacion->nombre}}">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{$localizacion->direccion}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="telefono class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$localizacion->telefono}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-localizacion">Actualizar localización</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection