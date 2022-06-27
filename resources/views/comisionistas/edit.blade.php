@extends('layouts.app')
@section('scripts')
    <script>
        function updateComisionista(comisionistas){
            axios.post(`/comisionistas/${comisionistas.elements['id'].value}`, {
                '_token'       : '{{ csrf_token() }}',
                '_method'      : 'put',  
                "codigo"  : comisionistas.elements['codigo'].value,
                "nombre"  : comisionistas.elements['nombre'].value,
                "tipo"  : comisionistas.elements['tipo'].value,
                "comision": comisionistas.elements['comision'].value,
                "iva"     : comisionistas.elements['iva'].value,
                "representante"  : comisionistas.elements['representante'].value,
                "direccion": comisionistas.elements['direccion'].value,
                "telefono"     : comisionistas.elements['telefono'].value
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
            document.getElementById('comisionistas-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const comisionistas = document.getElementById('comisionistas-form');
                updateComisionista(comisionistas);
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
                        <form class="row g-3 align-items-center f-auto" id="comisionistas-form">
                            @csrf
                            <input type="hidden" name="id" value="{{$comisionista->id}}">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Código</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionista->codigo}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre comisionista</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$comisionista->nombre}}">  
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="tipo" class="col-form-label">Tipo</label>
                                <select name="tipo" class="form-control">
                                    @foreach($tipos as $tipo)
                                        <option value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="comision" class="col-form-label">Comisión %</label>
                                <input type="number" name="comision" class="form-control" min="0" max="90" value="{{$comisionista->comision}}">
                            </div>
                            <div class="form-group col-2 mt-3">
                                <label for="iva" class="col-form-label">Iva %</label>
                                <input type="number" name="iva" class="form-control" min="0" max="90" value="{{$comisionista->iva}}">
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3">
                                <label for="representante" class="col-form-label">Representante</label>
                                <input type="text" id="representante" class="form-control" value="{{$comisionista->representante}}">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" id="direccion" class="form-control" value="{{$comisionista->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" id="telefono" class="form-control" value="{{$comisionista->telefono}}">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-comisionista">Actualizar comisionista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
