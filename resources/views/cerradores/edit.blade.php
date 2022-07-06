@extends('layouts.app')
@section('scripts')
    <script>
        function updateComisionista(cerradores){
            axios.post(`/cerradores/${cerradores.elements['id'].value}`, {
                '_token'       : '{{ csrf_token() }}',
                '_method'      : 'put',  
                "nombre"       : cerradores.elements['nombre'].value,
                "comision"     : cerradores.elements['comision'].value,
                "iva"          : cerradores.elements['iva'].value,
                "direccion"    : cerradores.elements['direccion'].value,
                "telefono"     : cerradores.elements['telefono'].value
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
                cerradores.reset();
            });
        }
        $(function(){
            document.getElementById('cerradores-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const cerradores = document.getElementById('cerradores-form');
                updateComisionista(cerradores);
            });
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
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="cerradores-form">
                            @csrf
                            <input type="hidden" name="id" value="{{$cerrador->id}}">
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre cerrador</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$cerrador->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="{{$cerrador->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{$cerrador->iva}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" id="direccion" class="form-control" value="{{$cerrador->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" id="telefono" class="form-control" value="{{$cerrador->telefono}}">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-cerrador">Actualizar cerrador</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
