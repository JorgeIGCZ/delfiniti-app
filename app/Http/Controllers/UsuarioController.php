<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Classes\CustomErrorHandler;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $roles = Role::all();

        return view('usuarios.index',['roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if(count(User::
                where('email',$request->email)
                ->orWhere('username',$request->username)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'El correo o el nombre de usuario ya se encuentra registrado.']);
            }
            $user = User::create([
                    'username' => $request->username,
                    'name' => $request->nombre,
                    'email' => $request->email,
                    'limite_descuento' => $request->limiteDescuento,
                    'password' => Hash::make($request->password),
            ])->assignRole(Role::find($request->role)->name);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($user['id']) ? 'Success' : 'Error']);
    }

   /**
     * Display apll resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user = null)
    {
        if(is_null($user)){
            $usuarios      = User::all();
            $usuariosArray = [];
            foreach ($usuarios as $usuario) {
                $usuariosArray[] = [
                    'id'       => $usuario->id,
                    'username' => $usuario->username,
                    'name'     => $usuario->name,
                    'email'    => $usuario->email,
                    'limiteDescuento'  => $usuario->limite_descuento,
                    'rol'      => @$usuario->roles->pluck('name')[0]
                ];
            }
            return json_encode(['data' => $usuariosArray]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        return view('usuarios.edit',['usuario' => $usuario,'roles' => $roles]);
    } 

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $user                   = User::find($id);
            $user->username         = $request->username;
            $user->name             = $request->nombre;
            $user->email            = $request->email;
            $user->limite_descuento = $request->limite_descuento;

            $request->password !== '************' ? $user->password = $request->password : '';

            $user->save();

            $user->syncRoles(Role::find($request->rol)->name);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return redirect()->route("usuarios.index")->with(["result" => "Usuario actualizado"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $usuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $usuario)
    {
        $result = $usuario->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}
