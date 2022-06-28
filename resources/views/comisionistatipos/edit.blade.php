@extends('layouts.app')
@section('scripts')
    <script>
        function updateTipoComisionista(comisionistaTipo){
            axios.post(`/comisionistatipos/${comisionistaTipo.elements['id'].value}`, {
                '_token'       : '{{ csrf_token() }}',
                '_method'      : 'put',  
                "id"  : comisionistaTipo.elements['id'].value,
                "nombre"  : comisionistaTipo.elements['nombre'].value
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
                comisionistaTipos.reset();
            });
        }
        $(function(){
            document.getElementById('tipos-comisionista-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const comisionistaTipos = document.getElementById('tipos-comisionista-form');
                updateTipoComisionista(comisionistaTipos);
            });
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Tipo de comisionista</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="tipos-comisionista-form">
                            @csrf
                            <input type="hidden" name="id" value="{{$comisionistaTipo->id}}">
                            <div class="form-group col-2 mt-3">
                                <label for="codigo" class="col-form-label">Id</label>    
                                <input type="text" name="codigo" class="form-control" value="{{$comisionistaTipo->id}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre de tipo de comisionista</label>    
                                <input type="text" name="nombre" class="form-control" value="{{$comisionistaTipo->nombre}}">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-tipo-comisionista">Actualizar tipo de comisionista</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
