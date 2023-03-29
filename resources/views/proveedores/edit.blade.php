@extends('layouts.app')
@section('scripts')
    <script>
        // function changeComisionesSettings(){
        //     const tipo = document.getElementById('tipo');
            
        //     if(tipo.options[tipo.selectedIndex].getAttribute('proveedorCanal') == '1'){
        //         $('.general-settings').hide();
        //         $('.comisiones-sobre-actividades').hide();
        //         $('.comisiones-sobre-canales').show();
        //         return false;
        //     }else if(tipo.options[tipo.selectedIndex].getAttribute('proveedorActividad') == '1'){
        //         $('.general-settings').hide();
        //         $('.comisiones-sobre-actividades').show();
        //         $('.comisiones-sobre-canales').hide();
        //         return false;
        //     }
        //     $('.general-settings').show();
        //     $('.comisiones-sobre-actividades').hide();
        //     $('.comisiones-sobre-canales').hide();
        //     return true;
        // }
        // $(function(){
        //     changeComisionesSettings();

        //     $('#tipo').on('change', function (e) {
        //         document.querySelectorAll("#tipo option").forEach(function(el) {
        //             el.removeAttribute("selected");
        //         })

        //         changeComisionesSettings();
        //     });
            
        // });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Proveedores</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="proveedores-form" action="{{route("proveedores.update",$proveedor['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$proveedor->clave}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="razon_social" class="col-form-label">Razon social</label>    
                                <input type="text" name="razonSocial" class="form-control" value="{{$proveedor->razon_social}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="rfc" class="col-form-label">RFC</label>    
                                <input type="text" name="rfc" class="form-control" value="{{$proveedor->RFC}}" disabled="disabled">  
                            </div>


                            <div class="col-12 mt-3 general-settings">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3 general-settings">
                                <label for="nombre_contacto" class="col-form-label">Nombre</label>
                                <input type="text" name="nombreContacto" class="form-control to-uppercase" value="{{$proveedor->nombre_contacto}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="cargo_contacto" class="col-form-label">Cargo</label>
                                <input type="text" name="cargoContacto" class="form-control to-uppercase" value="{{$proveedor->cargo_contacto}}">
                            </div>
                            <div class="form-group col-5 mt-3 general-settings">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control to-uppercase" value="{{$proveedor->direccion}}">
                            </div>
                            <div class="form-group col-5 mt-3 general-settings">
                                <label for="ciudad" class="col-form-label">Ciudad</label>
                                <input type="text" name="ciudad" class="form-control to-uppercase" value="{{$proveedor->ciudad}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="estado" class="col-form-label">Estado</label>
                                <input type="text" name="estado" class="form-control to-uppercase" value="{{$proveedor->estado}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="cp" class="col-form-label">CP</label>
                                <input type="text" name="cp" class="form-control to-uppercase" value="{{$proveedor->cp}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="pais" class="col-form-label">Pais</label>
                                <input type="text" name="pais" class="form-control to-uppercase" value="{{$proveedor->pais}}">
                            </div>
                            <div class="form-group col-3 mt-3 general-settings">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control to-uppercase" value="{{$proveedor->telefono}}">
                            </div>
                            <div class="form-group col-2 mt-3 general-settings">
                                <label for="email" class="col-form-label">Email</label>
                                <input type="text" name="email" class="form-control to-uppercase" value="{{$proveedor->email}}">
                            </div>
                            <div class="form-group col-5 mt-3 general-settings">
                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                <input type="text" name="comentarios" class="form-control to-uppercase" value="{{$proveedor->comentarios}}">
                            </div>




                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-proveedor">Actualizar proveedor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
