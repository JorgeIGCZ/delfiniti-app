@extends('layouts.app')
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
                        <div class="row g-3 align-items-center">
                            <div class="col-12 mt-3">
                                <strong>Datos del ciente</strong>
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="new-nombre" class="col-form-label">Nombre</label>    
                                <input type="text" id="new-nombre" class="form-control">  
                            </div>
                            <div class="form-group col-4 mt-0 mb-0">
                                <label for="new-email" class="col-form-label">Email</label>    
                                <input type="text" id="new-email" class="form-control">  
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="new-localizacion" class="col-form-label">Localización</label>
                                <input type="text" id="new-localizacion" class="form-control" >
                            </div>
                            <div class="form-group col-6 mt-0 mb-0">
                                <label for="new-origen" class="col-form-label">Lugar de origen</label>
                                <input type="text" id="new-origen" class="form-control" >
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos de la reservación</strong>
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="new-clave" class="col-form-label">Clave</label>
                                <input type="text" id="new-clave" class="form-control">
                            </div>
                            <div class="form-group col-5 mt-0 mb-0">
                                <label for="new-actividad" class="col-form-label">Actividad</label>
                                <input type="text" id="new-actividad" class="form-control">
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="new-horario" class="col-form-label">Horario</label>
                                <input type="time" id="new-horario" class="form-control">
                            </div>
                            <div class="form-group col-2 mt-0 mb-0">
                                <label for="new-fecha" class="col-form-label">Fecha</label>
                                <input type="date" id="new-fecha" class="form-control">
                            </div>
                            <div class="form-group col-1 mt-0 mb-0">
                                <label for="new-cantidad" class="col-form-label">Cantidad</label>
                                <input type="number" id="new-cantidad" class="form-control">
                            </div>

                            <div class="form-group col-12 mt-0 mb-0">
                                <div class="row">
                                    <div class="form-group col-9 mt-0 mb-0">
                                        <div class="row">
                                            <div class="form-group col-6 mt-0 mb-0">
                                                <label for="new-clave" class="col-form-label">Reservado por</label>
                                                <input type="text" id="new-clave" class="form-control">
                                            </div>
                                            <div class="form-group col-6 mt-0 mb-0">
                                                <label for="new-clave" class="col-form-label">Reservado por</label>
                                                <input type="text" id="new-clave" class="form-control">
                                            </div>
                                            <div class="form-group col-12 mt-0 mb-0">
                                                <label for="new-clave" class="col-form-label">Comentarios</label>
                                                <textarea name="textarea" rows="5" style="width:100%;">Write something here</textarea>
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
                                                        <label for="new-clave" class="col-form-label">Total:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <p id="total">$1,900.00</p>
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="new-clave" class="col-form-label">Efectivo M.N.:</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" id="new-clave" class="form-control amount">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="new-clave" class="col-form-label">Efectivo USD.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" id="new-clave" class="form-control amount">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="new-clave" class="col-form-label">Tarjeta crédito.</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" id="new-clave" class="form-control amount">
                                                    </div>

                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <label for="new-clave" class="col-form-label">Cupón</label>
                                                    </div>
                                                    <div class="form-group col-6 mt-0 mb-0">
                                                        <input type="text" id="new-clave" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-3 mt-0 mb-0">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Generar</button>
                            </div>
                            <div class="form-group col-3 mt-0 mb-0">
                                <button class="mt-33 btn btn-gray-700 btn-block" id="crear-agencia">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
