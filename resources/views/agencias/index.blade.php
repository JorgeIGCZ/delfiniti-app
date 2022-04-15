@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Agencias</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <div class="row g-3 align-items-center">

                            <div class="form-group col-2 mt-3">
                                <label for="new-codigo" class="col-form-label">Código</label>    
                                <input type="text" id="new-codigo" class="form-control">  
                            </div>
                            <div class="form-group col-6 mt-3">
                                <label for="new-nombre" class="col-form-label">Nombre agencia</label>    
                                <input type="text" id="new-nombre" class="form-control">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="new-nombre" class="col-form-label">Comisión %</label>
                                <input type="number" id="new-nombre" class="form-control" min="1" max="90">
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="new-iva" class="col-form-label">Iva %</label>
                                <input type="number" id="new-iva" class="form-control" min="1" max="90">
                            </div>


                            <div class="col-12 mt-3">
                                <strong>Datos Representante</strong>
                            </div>
                            <div class="form-group col-5 mt-3">
                                <label for="new-numero" class="col-form-label">Representante</label>
                                <input type="text" id="new-nombre-rep" class="form-control">
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="new-direccion" class="col-form-label">direccion</label>
                                <input type="text" id="new-direccion" class="form-control">
                            </div>
                            <div class="form-group col-3 mt-3">
                                <label for="new-numero" class="col-form-label">Número</label>
                                <input type="text" id="new-numero" class="form-control">
                            </div>

                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-agencia">Crear agencia</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <div class="row">
                            <table id="example" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Comisión</th>
                                        <th>Iva</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
