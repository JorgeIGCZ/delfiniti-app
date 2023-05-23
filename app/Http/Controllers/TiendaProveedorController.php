<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\TiendaProveedor;

class TiendaProveedorController extends Controller
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
            if(count(TiendaProveedor::
                where('clave',$request->clave)->get()
            ) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            $proveedor = TiendaProveedor::create([
                'clave'             => $request->clave,
                'razon_social'      => mb_strtoupper($request->razonSocial),
                'RFC'               => mb_strtoupper($request->rfc),
                'nombre_contacto'   => mb_strtoupper($request->nombreContacto),
                'cargo_contacto'    => mb_strtoupper($request->cargoContacto),
                'direccion'         => mb_strtoupper($request->direccion),
                'ciudad'            => mb_strtoupper($request->ciudad),
                'estado'            => mb_strtoupper($request->estado),
                'cp'                => $request->cp,
                'pais'              => mb_strtoupper($request->pais),
                'telefono'          => $request->telefono,
                'email'             => mb_strtoupper($request->email),
                'comentarios'       => mb_strtoupper($request->comentarios),
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
    public function show(TiendaProveedor $proveedor = null)
    {
        if(is_null($proveedor)){
            $proveedores      = TiendaProveedor::all();
            $proveedoresArray = [];
            foreach ($proveedores as $proveedor) {
                $proveedoresArray[] = [
                    'id'               => $proveedor->id,
                    'clave'            => $proveedor->clave,
                    'razon_social'     => mb_strtoupper($proveedor->razon_social),
                    'RFC'              => mb_strtoupper($proveedor->RFC),
                    'nombre_contacto'  => mb_strtoupper($proveedor->nombre_contacto),
                    'cargo_contacto'   => mb_strtoupper($proveedor->cargo_contacto),
                    'direccion'        => mb_strtoupper($proveedor->direccion),
                    'ciudad'           => mb_strtoupper($proveedor->ciudad),
                    'estado'           => mb_strtoupper($proveedor->estado),
                    'cp'               => $proveedor->cp,
                    'pais'             => mb_strtoupper($proveedor->pais),
                    'telefono'         => $proveedor->telefono,
                    'email'            => mb_strtoupper($proveedor->email),
                    'comentarios'      => mb_strtoupper($proveedor->comentarios),
                    'estatus'          => $proveedor->estatus,
                ];
            }
            return json_encode(['data' => $proveedoresArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TiendaProveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaProveedor $proveedor)
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
            $proveedor          = TiendaProveedor::find($id);
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
    public function update(TiendaProveedor $proveedor, Request $request)
    {
        try {
            $proveedor->razon_social     = mb_strtoupper($request->razonSocial);
            $proveedor->RFC              = mb_strtoupper($request->rfc);
            $proveedor->nombre_contacto  = mb_strtoupper($request->nombreContacto);
            $proveedor->cargo_contacto   = mb_strtoupper($request->cargoContacto);
            $proveedor->direccion        = mb_strtoupper($request->direccion);
            $proveedor->ciudad           = mb_strtoupper($request->ciudad);
            $proveedor->estado           = mb_strtoupper($request->estado);
            $proveedor->cp               = $request->cp;
            $proveedor->pais             = mb_strtoupper($request->pais);
            $proveedor->telefono         = $request->telefono;
            $proveedor->email            = mb_strtoupper($request->email);
            $proveedor->comentarios      = mb_strtoupper($request->comentarios);
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
     * @param  TiendaProveedor  $proveedor
     * @return \Illuminate\Http\Response
     */
    public function destroy(TiendaProveedor $proveedor)
    {
        $result = $proveedor->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}