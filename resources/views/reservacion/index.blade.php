@extends('layouts.app')
@section('scripts')
    <script>
    jQuery(document).ready( function () {
        $('#table_id').DataTable();
        /*
        let table = new DataTable('#table_id', {
            // options
        });
        */
        //alert("test");
        
        
        
    } );
    </script>
@endsection
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Reservaciones</h2>
        </div>
    </div><!-- az-dashboard-one-title -->
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <table id="table_id" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Reserva</th>
                                        <th>Cliente</th>
                                        <th>Personas</th>
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
