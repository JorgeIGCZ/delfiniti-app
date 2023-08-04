@extends('layouts.app')
@section('scripts')
    <script>
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Supervisores</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="supervisores-form" action="{{route("supervisores.update",$supervisor['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-1 mt-3">
                                <label for="clave" class="col-form-label">Clave</label>    
                                <input type="text" name="clave" class="form-control" value="{{$supervisor->clave}}" disabled="disabled">  
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control to-uppercase" value="{{$supervisor->nombre}}">  
                            </div>
                            <div class="form-group col-4 mt-3 general-settings">
                                <label for="direccion" class="col-form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control to-uppercase" value="{{$supervisor->direccion}}">
                            </div>
                            <div class="form-group col-3 mt-3 general-settings">
                                <label for="telefono" class="col-form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{$supervisor->telefono}}">
                            </div>


                            <div class="col-md-12">
                                <div class="card wrapper">
                                    <div class="card-header buttonWrapper">
                                        <ul class="nav nav-pills">
                                            <li class="nav-item">
                                                <a class="nav-link tab-button active" href="#" data-id="tienda">Tienda</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link tab-button" href="#" data-id="foto_video">Foto y video</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="card-body">
                                        <div class="tab-content contentWrapper">
                                            <div class="tab-panel content active" id="tienda">
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_comision" class="col-form-label">Comisión %</label>
                                                    <input type="number" step="0.01" name="tienda_comision" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionTiendaDetalle->comision}}">
                                                </div>
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_iva" class="col-form-label">Iva %</label>
                                                    <input type="number" name="tienda_iva" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionTiendaDetalle->iva}}">
                                                </div>
                
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="tienda_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                    <input type="number" step="0.01" name="tienda_descuento_impuesto" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionTiendaDetalle->descuento_impuesto}}">
                                                </div>
                                            </div>
                                            <div class="tab-panel content" id="foto_video">
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_comision" class="col-form-label">Comisión %</label>
                                                    <input type="number" step="0.01" name="foto_video_comision" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionFotoVideoDetalle->comision}}">
                                                </div>
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_iva" class="col-form-label">Iva %</label>
                                                    <input type="number" name="foto_video_iva" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionFotoVideoDetalle->iva}}">
                                                </div>
                
                                                <div class="form-group col-2 mt-3 general-settings">
                                                    <label for="foto_video_descuento_impuesto" class="col-form-label">Descuentro por imp. %</label>
                                                    <input type="number" step="0.01" name="foto_video_descuento_impuesto" class="form-control" min="0" max="90" value="{{@$supervisor->supervisorComisionFotoVideoDetalle->descuento_impuesto}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-supervisor">Actualizar supervisor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
