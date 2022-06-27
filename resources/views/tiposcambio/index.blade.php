@extends('layouts.app')
@section('scripts')
<script>
    document.getElementById('tipo-cambio').addEventListener('submit', (event) =>{
        event.preventDefault();
        const tipoCambio = document.getElementById('tipo-cambio');
        updateTpoCambio(tipoCambio);
    });
    function updateTpoCambio(tipoCambio){
        axios.post(`/tiposCambio/${tipoCambio.elements['id'].value}`, {
            '_token'       : '{{ csrf_token() }}',
            '_method'      : 'put',
            "precio_compra" : tipoCambio.elements['precio_compra'].value,
            "precio_venta"  : tipoCambio.elements['precio_venta'].value
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
            tipoCambio.reset();
        });
    }
</script>
@endsection
@section('content')
<div class="az-dashboard-one-title">
    <div>
        <h2 class="az-dashboard-title">Tipos de Cambio</h2>
    </div>
</div><!-- az-dashboard-one-title -->
<div class="row row-sm mg-b-20">
    <div class="col-lg-12 ht-lg-100p">
        <div class="card">
            <div class="card-body">
                <div>
                    <div class="row">
                        <div class="col-md-12 col-xl-12">
                            <div class="az-content-label tx-13 mg-b-15">General</div>
                            <div>
                                <form method="POST" class="row g-3 align-items-center f-auto" id="tipo-cambio">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$tiposCambio['general'][0]->id}}">
                                    <div class="form-group col-1">
                                        <label for="divisa" class="col-form-label">Divisa</label>
                                        <input type="text" name="divisa" class="form-control" value="{{$tiposCambio['general'][0]->divisa}}" disabled="disabled">
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="precio_compra" class="col-form-label">Precio Compra</label>
                                        <input type="text" name="precio_compra" class="form-control" value="{{$tiposCambio['general'][0]->precio_compra}}" required="required">
                                    </div><div class="form-group col-2">
                                        <label for="precio_compra" class="col-form-label">Precio Venta</label>
                                        <input type="text" name="precio_venta" class="form-control" value="{{$tiposCambio['general'][0]->precio_venta}}" required="required">
                                    </div>
                                    <div class="form-group col-2">
                                        <button class="btn btn-info btn-block mt-33">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection