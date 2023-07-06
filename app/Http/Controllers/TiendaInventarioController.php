<?php

namespace App\Http\Controllers;

use App\Classes\CustomErrorHandler;
use App\Models\TiendaMovimientoInventario;
use App\Models\TiendaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaInventarioController extends Controller
{
    public function __construct() {
        $this->middleware('role:Administrador')->only('edit'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TiendaProducto $inventario)
    {
        return view('inventario.edit',['producto' => $inventario]);
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
        try{
            DB::beginTransaction();
            $Productos = new TiendaProductoController();
            $Productos->updateStock($id, $request->accion, $request->numeroProductos);

            $this->setMovimientoInventario([
                'producto_id' => $id,
                'movimiento' => $request->accion,
                'cantidad' => $request->numeroProductos,
                'usuario_id' => auth()->user()->id,
                'comentarios' => $request->comentarios
            ]);

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return back()->withErrors($e->getMessage());
        }
        return redirect()->route("productos.index")->with(["result" => "Inventario actualizado"]);
    }

    public function getMovimientosInventario(Request $request, $id)
    {
        if(!is_null($request->fecha)){
            switch (@$request->fecha) {
                case 'year':
                    $fechaInicio = Carbon::now()->startOfYear();
                    $fechaFinal  = Carbon::now()->endOfYear();
                    break;
                case 'custom':
                    $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
                    $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();
                    break;
            }   
        }
        DB::enableQueryLog();
        $movimientos = TiendaMovimientoInventario::whereBetween("created_at", [$fechaInicio,$fechaFinal])->where("producto_id", $id)->get();

        $movimientosArray = [];
        foreach($movimientos as $movimiento){ 
            
            $movimientosArray[] = [
                'id'              => $movimiento->id,
                'movimiento'      => $movimiento->movimiento,
                'cantidad'        => $movimiento->cantidad,
                'comentarios'     => $movimiento->comentarios,
                'usuario'         => $movimiento->usuario->name,
                'fechaMovimiento' => Carbon::parse($movimiento->created_at)->format('d/m/Y')
            ];
        }
        
        return json_encode(['data' => $movimientosArray]);
    }

    public function setMovimientoInventario($data){
        TiendaMovimientoInventario::create($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
