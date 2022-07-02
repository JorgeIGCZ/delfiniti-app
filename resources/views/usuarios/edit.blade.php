@extends('layouts.app')
@section('content')
    <div class="az-dashboard-one-title">
        <div>
            <h2 class="az-dashboard-title">Usuarios</h2>
        </div>
    </div><!-- az-dashboard-one-title --> 
    <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body"> 
                    <div class="container">
                        <form method="POST" class="row g-3 align-items-center f-auto" id="actividades-form" action="{{route("usuarios.update",$usuario['id'])}}">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" autocomplete="off" value ="{{$usuario->name}}">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="email" class="col-form-label">Email</label>    
                                <input
                                 type="email" name="email" class="form-control" autocomplete="off" value ="{{$usuario->email}}">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="limite_descuento" class="col-form-label">Limite descuento</label>    
                                <input
                                 type="number" name="limite_descuento" class="form-control" autocomplete="off" value ="{{$usuario->limite_descuento}}">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="password" class="col-form-label">Password</label>    
                                <input type="password" name="password" class="form-control" autocomplete="off" value ="************">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="rol" class="col-form-label">Rol</label>
                                <select name="rol" class="form-control">
                                    @foreach($roles as $rol)
                                        <option value="{{$rol->id}}" {{$usuario->roles->pluck('id')[0] === $rol->id ? 'selected="selected"' : ''}}>{{$rol->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="actualizar-usuario">Actualizar usuario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection