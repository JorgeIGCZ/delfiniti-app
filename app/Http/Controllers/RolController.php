<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\hasAllRoles;

use Spatie\Permission\Models\RoleHasPermission;

class RolController extends Controller
{
    public function index(){
        $roles = Role::all();
        return view('roles.index',['roles' => $roles]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $usuario
     * @return \Illuminate\Http\Response
     */
    public function show(Role $rol)
    {
        $permisos = $rol->permissions;
        return $permisos;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        try{
            $role     = Role::where('id',$request->tipoUsuario)->first();
            $permisos = [];
            foreach ($request->permisos as $key => $permiso){
                ($permiso ? $permisos[] = $key : "");
            }
            $role->syncPermissions($permisos);

            return json_encode(['result' => "Success"]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
    }
}
