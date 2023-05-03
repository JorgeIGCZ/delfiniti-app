@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
            document.getElementById('costo').addEventListener('keyup', (event) =>{
                setTimeout(setMargenGanancia(),500);
            });

            document.getElementById('precioVenta').addEventListener('keyup', (event) =>{
                setTimeout(setMargenGanancia(),500);
            });
            
            function setMargenGanancia(){
                const costo = document.getElementById('costo').getAttribute('value');
                const precioVenta = document.getElementById('precioVenta').getAttribute('value');
                
                const gananciaBruta = (precioVenta-costo);
                const margenGanancia = parseFloat(((gananciaBruta)*100)/costo).toFixed(2);

                document.getElementById('margenGanancia').setAttribute('value', margenGanancia);
                document.getElementById('margenGanancia').value = `${margenGanancia}%`;
            }
        });
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Producto</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p"> 
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="productos-form" action="{{route("fotovideoproductos.update",$producto['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$producto->clave}}" disabled="disabled">  
                            </div>

                            <div class="form-group col-5 mt-3">
                                <label for="nombre" class="col-form-label">Nombre del producto</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" autocomplete="off" tabindex="1" value="{{$producto->nombre}}" required="required">  
                            </div>

                            <div class="form-group col-2 mt-2">
                                <label for="precioVenta" class="col-form-label">Precio venta</label>
                                <input type="text" name="precioVenta" id="precioVenta" class="form-control amount" autocomplete="off" tabindex="3" value="{{$producto->precio_venta}}" required="required">  
                            </div>

                            <div class="form-group col-6 mt-2">
                                <label for="stockMaximo" class="col-form-label">Comentarios</label>
                                <textarea name="comentarios" class="to-uppercase" rows="5" style="width:100%;" spellcheck="false">{{@$producto->comentarios}}</textarea>
                            </div>
                            <div class="form-group col-2 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-producto" tabindex="6">Actualizar producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection