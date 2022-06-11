@extends('layouts.app')
@section('scripts')
    <script>
        let reservacionesTable; 
        let allActividades = [];
        let reservacionesArray  = [];
        window.onload = function() {
            getDisponibilidad();
            reservacionesTable = new DataTable('#reservaciones', {
                searching: false,
                paging: false,
                info: false
            } );
           
        };
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Nueva Reserva</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <form class="row g-3 align-items-center f-auto" id="reservacion-form">
                            @csrf
                            <div class="col-12 mt-3">
                                <strong>Datos del ciente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" required="required" autocomplete="off">  
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="email" class="col-form-label">Email</label>    
                                <input type="email" name="email" class="form-control" autocomplete="off">  
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="localizacion" class="col-form-label">Localización</label>
                                <select name="localizacion" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                    <option value='0' selected="true">Seleccionar localización</option>
                                    
                                </select>
                            </div>  
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="origen" class="col-form-label">Lugar de origen</label>
                                <select name="origen" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                    <option value='0' selected="true">Seleccionar origen</option>
                                    
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="clave" class="col-form-label">Clave</label>
                                <select id="clave-actividad" name="clave" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                </select>
                            </div>
                            <div class="form-group col-3 mt-0 mb-0">
                                <label for="actividad" class="col-form-label">Actividad</label>
                                <select name="actividad" id="actividades"  class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                </select>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="horario" class="col-form-label">Horario</label>
                                <select name="horario" id="horarios" class="form-control">
                                </select>
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="cantidad" class="col-form-label">Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" max="200" autocomplete="off">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="disponibilidad" class="col-form-label">Disponibilidad</label>
                                <input type="number" name="disponibilidad" class="form-control" value="0" disabled="disabled">
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="fecha" class="col-form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" value="" autocomplete="off">
                            </div>
                            <input type="hidden" name="precio" id="precio" value="0">
                            <div class="form-group col-1 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="agregar-reservacion">+</button>
                            </div>
                            <div class="form-group col-12 mt-8 mb-8 bd-t">
                                <div class="row">
                                    <div class="col-12 mt-2 mb-2">
                                        <table id="reservaciones" class="display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Clave</th>
                                                    <th>Actividad</th>
                                                    <th>Horario</th>
                                                    <th>Cantidad</th>
                                                    <th>Costo P/P</th>
                                                    <th>Subtotal</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-9 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="agente" class="col-form-label">Reservado por</label>
                                                <select name="agente" class="form-control">
                                                    <option value="" selected="selected" disabled="disabled">
                                                        
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-4 mt-0 mb-0">
                                                <label for="comisionista" class="col-form-label">Comisionista</label>
                                                <select name="comisionista" class="search-drop-down form-control" data-show-subtext="true" data-live-search="true">
                                                    <option value='0' selected="true">Seleccionar comisionista</option>
                                                    
                                                </select>
                                            </div>
                                            <div class="col-4 mt-0 mb-0">
                                                <label for="codigo-descuento" class="col-form-label">Código descuento</label>
                                                <div class="input-button">
                                                    <input type="text" name="codigo-descuento" id="codigo-descuento"  class="form-control" autocomplete="off">
                                                    <button class="btn btn-info btn-block form-control" id="agregar-codigo">Agregar</button>
                                                </div>
                                            </div>
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="comentarios" class="col-form-label">Comentarios</label>
                                                <textarea name="comentarios" rows="5" style="width:100%;"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-3 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <div class="col-12 mt-3">
                                                    <strong>Detalle de la reservación</strong>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="total" class="col-form-label">Total:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="total" id="total" class="form-control amount" disabled="disabled" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="efectivo" class="col-form-label">Efectivo M.N.:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="efectivo" id="efectivo" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="efectivo-usd" class="col-form-label">Efectivo USD.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="efectio-usd" id="efectivo-usd" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="tarjeta" class="col-form-label">Tarjeta crédito.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="tarjeta" id="tarjeta" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="cupon" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="cupon" id="cupon" class="form-control amount" value="0.00">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="descuento" class="col-form-label">Descuento</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="descuento" id="descuento" class="form-control" value="0%" disabled="disabled">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="resta" class="col-form-label">Resta</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" name="resta" id="resta" class="form-control amount" disabled="disabled" value="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
