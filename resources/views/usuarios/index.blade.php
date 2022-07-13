@extends('layouts.app')
@section('scripts')
    <script>
        let usuariosTable;
        function verificacionDestroy(id){
            Swal.fire({
                title: '¿Desea eliminar usuario?',
                text: "Este proceso no se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, Eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    destroyComisionista(id);
                }else{
                    return false;
                }
            }) 
        }
        function destroyComisionista(id){
            axios.delete(`/usuarios/${id}`)
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro eliminado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    usuariosTable.ajax.reload();
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Eliminacion fallida',
                        html: `<small class="alert alert-danger mg-b-0">${response.data.message}</small>`,
                        showConfirmButton: true
                    })
                }
            })
            .catch(function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eliminacion fallida',
                    html: `<small class="alert alert-danger mg-b-0">Error de conexión.</small>`,
                    showConfirmButton: true
                })
            }); 
            
        }
        function createUsuario(usuario){
            let rol   = usuario.elements['rol'];
            rol       = rol.options[rol.selectedIndex].value;
            axios.post('/usuarios', {
                '_token'  : '{{ csrf_token() }}',
                "username": usuario.elements['username'].value,
                "name": usuario.elements['nombre'].value,
                "email"   : usuario.elements['email'].value,
                "limiteDescuento" : usuario.elements['limite-descuento'].value,
                "password": usuario.elements['password'].value,
                "role"    : rol,
            })
            .then(function (response) {
                if(response.data.result == "Success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro creado',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    location.reload();
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
            });
            usuariosTable.ajax.reload();
        }
        function formValidity(formId){
            const reservacion = document.getElementById(formId);
            let response = true;
            if(reservacion.checkValidity()){
                event.preventDefault();
            }else{
                reservacion.reportValidity();
                response = false;
            }
            return response;
        }
        $(function(){
            usuariosTable = new DataTable('#usuarios', {
                ajax: function (d,cb,settings) {
                    axios.get('/usuarios/show')
                    .then(function (response) {
                        cb(response.data)
                    })
                    .catch(function (error) {
                    });
                },
                columns: [
                    { data: 'id' },
                    { data: 'username' },
                    { data: 'name' },
                    { data: 'email' },
                    { defaultContent: 'limiteDescuento', className: 'dt-left', 'render': function ( data, type, row ) 
                        {
                            return `${row.limiteDescuento}%`;
                        }
                    },
                    { data: 'rol' },
                    { defaultContent: 'Acciones', className: 'dt-center', 'render': function ( data, type, row ) 
                        {
                            let removeRow = '';
                            //if('{{(@session()->get('user_roles')['Alumnos']->Estatus)}}' == 'Y'){
                                removeRow = `| <a href="#" onclick="verificacionDestroy(${row.id})" >Eliminar</a>`;
                            //}
                            let view    =   `<small> 
                                                <a href="usuarios/${row.id}/edit">Editar</a>
                                                ${removeRow}
                                            </small>`;
                            return  view;
                        }
                    }
                ]
            } );
            
            document.getElementById('usuarios-form').addEventListener('submit', (event) =>{
                event.preventDefault();
                const usaurio = document.getElementById('usuarios-form');
                if(formValidity('usuarios-form')){
                    createUsuario(usaurio);
                }
            });
            
        });
    </script>
@endsection
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
                        <form class="row g-3 align-items-center f-auto" id="usuarios-form">
                            @csrf
                            <div class="form-group col-2 mt-3">
                                <label for="username" class="col-form-label">Usuario</label>    
                                <input type="text" name="username" class="form-control" autocomplete="off" required="required">  
                            </div>
                            <div class="form-group col-4 mt-3">
                                <label for="nombre" class="col-form-label">Nombre</label>    
                                <input type="text" name="nombre" class="form-control" autocomplete="off" required="required">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="email" class="col-form-label">Email</label>    
                                <input
                                 type="email" name="email" class="form-control" autocomplete="off" required="required">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="limite-descuento" class="col-form-label">Limite descuento</label>    
                                <input
                                 type="number" name="limite-descuento" class="form-control" autocomplete="off" required="required" min="0" max="100">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="password" class="col-form-label">Password</label>    
                                <input type="password" name="password" class="form-control" autocomplete="off" required="required">  
                            </div>

                            <div class="form-group col-2 mt-3">
                                <label for="rol" class="col-form-label">Rol</label>
                                <select name="rol" class="form-control">
                                    @foreach($roles as $rol)
                                        <option value="{{$rol->id}}">{{$rol->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group col-3 mt-3">
                                <button class="btn btn-info btn-block mt-33" id="crear-usuario">Crear usuario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="row row-sm mg-b-20">
        <div class="col-lg-12 ht-lg-100p">
            <div class="card">
                <div class="card-body">
                    <div class="row overflow-auto">
                        <div class="col-12">
                            <table id="usuarios" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Limite descuento</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
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