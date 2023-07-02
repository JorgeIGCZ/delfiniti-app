@extends('layouts.app')
@section('scripts')
    <script>
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Directivos</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="directivos-form" action="{{route("directivos.update",$directivo['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$directivo->clave}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$directivo->nombre}}">  
                            </div>
                            <div class="form-group col-4 mt-3 general-settings">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control to-uppercase" value="{{$directivo->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3 general-settings">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$directivo->telefono}}">
                            </div>



                            <div class="col-md-12">
                                <div class="card wrapper">
                                    <div class="card-header buttonWrapper">
                                        <ul class="nav nav-pills">
                                            <li class="nav-item">
                                                <a class="nav-link tab-button active" href="#" data-id="reservaciones">Reservaciones</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link tab-button" href="#" data-id="tienda">Tienda</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link tab-button" href="#" data-id="foto_video">Foto y video</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="card-body">
                                        <div class="tab-content contentWrapper">
                                            <div class="tab-panel content active" id="reservaciones">
                                                <div class="form-group col-12 mt-3 comisiones-sobre-canales">
                                                    <strong>
                                                        Comisiones sobre canales
                                                    </strong>
                                                    <table class="mt-3">
                                                        <tr>
                                                            <th>Canal de venta</th>
                                                            <th>Comisión %</th>
                                                            <th>Iva %</th>
                                                            <th>Descuentro por imp. %</th>
                                                        </tr>
                                                        @foreach($canalesVenta as $key => $canalesVenta)
                                                            <tr tipoid="{{$canalesVenta->id}}" class="tipo_comisiones">
                                                                <td>{{$canalesVenta->nombre}}</td>
                                                                <td>
                                                                    <input type="number" step="0.01" name="comisiones_canal_detalles[{{$key}}][{{$canalesVenta->id}}][comision]" class="tipo_comision form-control" value="{{@$directivo->directivoComisionReservacionCanalDetalle->groupBy('canal_venta_id')[$canalesVenta->id][0]->comision}}">  
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" name="comisiones_canal_detalles[{{$key}}][{{$canalesVenta->id}}][iva]" class="tipo_iva form-control" value="{{@$directivo->directivoComisionReservacionCanalDetalle->groupBy('canal_venta_id')[$canalesVenta->id][0]->iva}}">  
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" name="comisiones_canal_detalles[{{$key}}][{{$canalesVenta->id}}][descuento_impuesto]" class="tipo_descuento_impuesto form-control" value="{{@$directivo->directivoComisionReservacionCanalDetalle->groupBy('canal_venta_id')[$canalesVenta->id][0]->descuento_impuesto}}">  
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-panel content" id="tienda">
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_comision" class="col-form-label">Comisión %</label>
                                                    <input type="number" step="0.01" name="tienda_comision" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionTiendaDetalle->comision}}">
                                                </div>
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_iva" class="col-form-label">Iva %</label>
                                                    <input type="number" name="tienda_iva" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionTiendaDetalle->iva}}">
                                                </div>
                
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                    <input type="number" step="0.01" name="tienda_descuento_impuesto" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionTiendaDetalle->descuento_impuesto}}">
                                                </div>
                                            </div>
                                            <div class="tab-panel content" id="foto_video">
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_comision" class="col-form-label">Comisión %</label>
                                                    <input type="number" step="0.01" name="foto_video_comision" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionFotoVideoDetalle->comision}}">
                                                </div>
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_iva" class="col-form-label">Iva %</label>
                                                    <input type="number" name="foto_video_iva" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionFotoVideoDetalle->iva}}">
                                                </div>
                
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                    <input type="number" step="0.01" name="foto_video_descuento_impuesto" class="form-control" min="0" max="90" value="{{@$directivo->directivoComisionFotoVideoDetalle->descuento_impuesto}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-directivo">Actualizar directivo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
