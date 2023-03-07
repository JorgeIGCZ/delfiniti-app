<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;

class ProveedorController extends Controller
{
    public function __construct() {
        // $this->middleware('permission:Proveedor.index')->only('index');
        // $this->middleware('permission:Proveedor.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('proveedores.index',[]);
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
            if(count(Proveedor::
                where('clave',$request->clave)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $proveedor = Proveedor::create([
                'clave'             => $request->clave,
                'razon_social'      => strtoupper($request->razonSocial),
                'nombre_contacto'   => strtoupper($request->nombreContacto),
                'cargo_contacto'    => strtoupper($request->cargoContacto),
                'direccion'         => strtoupper($request->direccion),
                'ciudad'            => strtoupper($request->ciudad),
                'estado'            => strtoupper($request->estado),
                'cp'                => $request->cp,
                'pais'              => strtoupper($request->pais),
                'telefono'          => $request->telefono,
                'email'             => strtoupper($request->email),
                'comentarios'       => strtoupper($request->comentarios),
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($proveedor['id']) ? 'Success' : 'Error']);
    }


    /**
     * Display apll resource.
     *
     * @param  \App\Models\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function show(Proveedor $proveedor = null)
    {
        if(is_null($proveedor)){
            $proveedores      = Proveedor::all();
            $proveedoresArray = [];
            foreach ($proveedores as $proveedor) {
                $proveedoresArray[] = [
                    'id'               => $proveedor->id,
                    'clave'            => $proveedor->clave,
                    'razon_social'     => strtoupper($proveedor->razon_social),
                    'nombre_contacto'  => strtoupper($proveedor->nombre_contacto),
                    'cargo_contacto'   => strtoupper($proveedor->cargo_contacto),
                    'direccion'        => strtoupper($proveedor->direccion),
                    'ciudad'           => strtoupper($proveedor->ciudad),
                    'estado'           => strtoupper($proveedor->estado),
                    'cp'               => $proveedor->cp,
                    'pais'             => strtoupper($proveedor->pais),
                    'telefono'         => $proveedor->telefono,
                    'email'            => strtoupper($proveedor->email),
                    'comentarios'      => strtoupper($proveedor->comentarios),
                    'estatus'          => $proveedor->estatus,
                ];
            }
            return json_encode(['data' => $proveedoresArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit',['proveedor' => $proveedor]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEstatus(Request $request, $id){
        try{
            $proveedor          = Proveedor::find($id);
            $proveedor->estatus = $request->estatus;
            $proveedor->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => 'Success']);
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
            $proveedor                     = Proveedor::find($id);
            $proveedor->clave            = strtoupper($request->clave);
            $proveedor->razon_social     = strtoupper($request->razon_social);
            $proveedor->nombre_contacto  = strtoupper($request->nombre_contacto);
            $proveedor->cargo_contacto   = strtoupper($request->cargo_contacto);
            $proveedor->direccion        = strtoupper($request->direccion);
            $proveedor->ciudad           = strtoupper($request->ciudad);
            $proveedor->estado           = strtoupper($request->estado);
            $proveedor->cp               = $request->cp;
            $proveedor->pais             = strtoupper($request->pais);
            $proveedor->telefono         = $request->telefono;
            $proveedor->email            = strtoupper($request->email);
            $proveedor->comentarios      = strtoupper($request->comentarios);
            $proveedor->estatus          = $request->estatus;
            $proveedor->save();
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("proveedores.index")->with(["result" => "Proveedor actualizado"]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Proveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proveedor $proveedor)
    {
        $result = $proveedor->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}