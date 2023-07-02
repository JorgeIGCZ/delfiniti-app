<?php

namespace App\Http\Controllers;

use App\Models\Directivo;
use Illuminate\Http\Request;
use App\Classes\CustomErrorHandler;
use App\Models\CanalVenta;
use App\Models\ComisionCanalDetalle;
use App\Models\ComisionFotoVideoDetalle;
use App\Models\ComisionTiendaDetalle;
use App\Models\DirectivoComisionFotoVideo;
use App\Models\DirectivoComisionFotoVideoDetalle;
use App\Models\DirectivoComisionReservacionCanalDetalle;
use App\Models\DirectivoComisionTiendaDetalle;
use Illuminate\Support\Facades\DB;

class DirectivoController extends Controller
{
    public function __construct() 
    {
        // $this->middleware('permission:Directivo.index')->only('index');
        // $this->middleware('permission:Directivo.update')->only('edit'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $canalesVenta = CanalVenta::all();
        return view('directivos.index',['canalesVenta' => $canalesVenta]);
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
            if(count(Directivo::where('clave',$request->clave)->get()) > 0){
                return json_encode(['result' => 'Error','message' => 'La clave ya se encuentra registrada.']);
            }

            DB::beginTransaction();

            $directivo = Directivo::create([
                'clave'             => $request->clave,
                'nombre'             => mb_strtoupper($request->nombre),
                'direccion'          => mb_strtoupper($request->direccion),
                'telefono'           => $request->telefono
            ]);
            
            $this->createComisionCanalDetalle($directivo['id'],$request);
            $this->createComisionTiendaDetalle($directivo['id'],$request);
            $this->createComisionFotoVideoDetalle($directivo['id'],$request);

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }
        return json_encode(['result' => is_numeric($directivo['id']) ? 'Success' : 'Error']);
    }

    private function createComisionCanalDetalle($directivoId,$request){
        foreach($request->comisionesSobreReservaciones as $key => $comisionesSobreReservaciones){
            DirectivoComisionReservacionCanalDetalle::create([
                'directivo_id'       => $directivoId,
                'canal_venta_id'        => $key,
                'comision'              => $comisionesSobreReservaciones['comision'],
                'iva'                   => $comisionesSobreReservaciones['iva'],
                'descuento_impuesto'    => $comisionesSobreReservaciones['descuentoImpuesto']
            ]);
        }
    }

    private function createComisionTiendaDetalle($directivoId,$request){
        DirectivoComisionTiendaDetalle::create([
            'directivo_id'          => $directivoId,
            'comision'              => $request->comisionesSobreTienda['comision'],
            'iva'                   => $request->comisionesSobreTienda['iva'],
            'descuento_impuesto'    => $request->comisionesSobreTienda['descuentoImpuesto']
        ]);
    }

    private function createComisionFotoVideoDetalle($directivoId,$request){
        DirectivoComisionFotoVideoDetalle::create([
            'directivo_id'          => $directivoId,
            'comision'              => $request->comisionesSobreFotoVideo['comision'],
            'iva'                   => $request->comisionesSobreFotoVideo['iva'],
            'descuento_impuesto'    => $request->comisionesSobreFotoVideo['descuentoImpuesto']
        ]);
    }

    /**
     * Display apll resource.
     *
     * @param  \App\Models\Directivo  $directivo
     * @return \Illuminate\Http\Response
     */
    public function show(Directivo $directivo = null)
    {
        if(is_null($directivo)){
            $directivos      = Directivo::all();
            $directivosArray = [];
            foreach ($directivos as $directivo) {
                $directivosArray[] = [
                    'id'                => $directivo->id,
                    'clave'            => $directivo->clave,
                    'nombre'            => mb_strtoupper($directivo->nombre),
                    'direccion'         => mb_strtoupper($directivo->direccion),
                    'telefono'          => $directivo->telefono,
                    'estatus'           => $directivo->estatus
                ];
            }
            return json_encode(['data' => $directivosArray]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Directivo  $directivo
     * @return \Illuminate\Http\Response
     */
    public function edit(Directivo $directivo)
    {
        $canalesVenta = CanalVenta::all();
        return view('directivos.edit',['directivo' => $directivo, 'canalesVenta' => $canalesVenta]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEstatus(Request $request, $id)
    {
        try{
            $directivo          = Directivo::find($id);
            $directivo->estatus = $request->estatus;
            $directivo->save();
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
            $directivo                     = Directivo::find($id);
            $directivo->nombre             = mb_strtoupper($request->nombre);
            $directivo->direccion          = mb_strtoupper($request->direccion);
            $directivo->telefono           = $request->telefono;
            $directivo->save();

            DirectivoComisionReservacionCanalDetalle::where('directivo_id',$id)->delete();

            foreach($request->comisiones_canal_detalles as $comisionCanalDetalle){
                foreach($comisionCanalDetalle as $key => $detalle){
                    DirectivoComisionReservacionCanalDetalle::create([
                        'directivo_id'       => $id,
                        'canal_venta_id'        => $key,
                        'comision'              => is_null($detalle['comision']) ? 0 : $detalle['comision'],
                        'iva'                   => is_null($detalle['iva']) ? 0 : $detalle['iva'],
                        'descuento_impuesto'    => is_null($detalle['descuento_impuesto']) ? 0 : $detalle['descuento_impuesto']
                    ]);
                }
            }

            DirectivoComisionTiendaDetalle::where('directivo_id',$id)->delete();

            DirectivoComisionTiendaDetalle::create([
                'directivo_id'          => $id,
                'comision'              => is_null($request->tienda_comision) ? 0 : $request->tienda_comision,
                'iva'                   => is_null($request->tienda_iva) ? 0 : $request->tienda_iva,
                'descuento_impuesto'    => is_null($request->tienda_descuento_impuesto) ? 0 : $request->tienda_descuento_impuesto
            ]);

            DirectivoComisionFotoVideoDetalle::where('directivo_id',$id)->delete();
            
            DirectivoComisionFotoVideoDetalle::create([
                'directivo_id'          => $id,
                'comision'              => is_null($request->foto_video_comision) ? 0 : $request->foto_video_comision,
                'iva'                   => is_null($request->foto_video_iva) ? 0 : $request->foto_video_iva,
                'descuento_impuesto'    => is_null($request->foto_video_descuento_impuesto) ? 0 : $request->foto_video_descuento_impuesto
            ]);
        } catch (\Exception $e){
            $CustomErrorHandler = new CustomErrorHandler();
            $CustomErrorHandler->saveError($e->getMessage(),$request);
            return json_encode(['result' => 'Error','message' => $e->getMessage()]);
        }

        return redirect()->route("directivos.index")->with(["result" => "Directivo actualizado"]);
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
     * @param  Directivo  $directivo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Directivo $directivo)
    {
        $result = $directivo->delete();
        return json_encode(['result' => $result ? 'Success' : 'Error']);
    }
}