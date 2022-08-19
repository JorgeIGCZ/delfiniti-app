<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use App\Models\Reservacion;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Pago;
use App\Models\TipoCambio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;



class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reservaciones =  Reservacion::all();
        //dd($reservaciones[0]->pagos);
        //dd($reservaciones[0]->pagos[0]->tipoPago->nombre);
        /*
        foreach($reservaciones as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                print_r($reservacionDetalle->actividad->nombre);
            }
        }
        */
        return view('reportes.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reporteReservaciones(Request $request){
        //$spreadsheet = new Spreadsheet();
        $usuario = new UsuarioController();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = $request->fechaInicio." 00:00:00";
        $fechaFinal = $request->fechaFinal." 23:59:00";
        // $fechaInicio = '2022-08-15 00:00:00';
        // $fechaFinal = '2022-08-15 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 

        $actividadesHorarios = $this->getActividadesHorarios($fechaInicio,$fechaFinal);
        $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio,$fechaFinal);
        

        $spreadsheet->getActiveSheet()->setCellValue("A2", "REPORTE DE RESERVACIONES");
        $spreadsheet->getActiveSheet()->setCellValue("A3", "Del {$formatoFechaInicio} al {$formatoFechaFinal}");		

        $rowNumber = 5;

        foreach ($actividadesHorarios as $actividadesHorario){
            foreach ($actividadesHorario as $actividadHorario){
                if(count($actividadHorario->reservacion) == 0){
                    continue;
                }

                $spreadsheet->getActiveSheet()->mergeCells("A{$rowNumber}:C{$rowNumber}");

                // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:C{$rowNumber}")
                //     ->getBorders()
                //     ->getOutline()
                //     ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:C{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FABF8F');
                    
                //Actividad
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividadHorario->actividad->nombre." ".$actividadesHorario[0]->horario_inicial);
                $rowNumber += 1;

                $spreadsheet->getActiveSheet()->mergeCells("A{$rowNumber}:B{$rowNumber}");

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFCC');

                //Titulos
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'CLIENTE');
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'ORIGEN');
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'PAX');
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'AGENTE/AGENCIA');
                // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'DESCUENTO');
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'T. PAGO');
                $rowNumber += 1;

                $initialRowNumber = $rowNumber;

                foreach ($actividadHorario->reservacion as $reservacion) {
                    $spreadsheet->getActiveSheet()->mergeCells("A{$rowNumber}:B{$rowNumber}");

                    // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    // ->getBorders()
                    // ->getOutline()
                    // ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                    $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('CCFFFF');

                    $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion->nombre_cliente);
                    $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", @$reservacion->origen);
                    $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getNumeroPersonas($actividadHorario,$reservacion->reservacionDetalle));
                    $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", @$reservacion->comisionista->nombre);
                    $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", ($reservacion->tipoPago !== null ? @$reservacion->tipoPago->pluck('nombre')[0] : ''));
                    $rowNumber += 1;
                }
                $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
                $rowNumber += 2;
            }
        }

        $rowNumber += 3;

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F0F000');

        //Titulos
        $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'PROGRAMA');
        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'PAGADOS');
        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'PENDIENTES');
        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'CORTESIAS');
        $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'TOTAL');
        
        $rowNumber += 1;
        $initialRowNumber = $rowNumber;

        foreach($actividadesFechaReservaciones as $actividad){
                if(!count($actividad->reservaciones) ){
                    continue;
                }
                
                $reservaciones = $actividad->reservaciones;
                $reservacionesTotales = $this->getReservacionesTotalesGeneral($actividad,$reservaciones);
                    
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacionesTotales['pagados']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacionesTotales['pendientes']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $reservacionesTotales['cortesias']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", '=SUM(' . 'B' . $rowNumber . ':D' . $rowNumber . ')');
                    
                $rowNumber += 1;
        }
                
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FABF8F');

        //Calculo totales
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');

        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/reservaciones/reservaciones.xlsx");
        return ['data'=>true];
    }

    private function getNumeroPersonas($actividadHorario,$reservacionDetalle){
        $numeroPersonas = 0;
        foreach($reservacionDetalle as $reservacionDetalle){
            if($reservacionDetalle->actividad_id == $actividadHorario->actividad->id){
                $numeroPersonas = $reservacionDetalle->numero_personas;
            }
        }
        
        return $numeroPersonas;
    }

    private function getActividadesFechaReservaciones($fechaInicio,$fechaFinal){
        
        $actividades = Actividad::with(['reservaciones' => function ($query) use ($fechaInicio,$fechaFinal) {
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->where('estatus',1);
        }])->get();
        
        return $actividades;
    }
    
    private function getReservacionesTotalesGeneral($actividad,$reservaciones){
        $reservacionesArray = $reservaciones->pluck('id');
        //CORTESIA ID = 6
        $cortesiasPersonas = 0;
        $pagados = Reservacion::whereIn('id',$reservacionesArray)->where('estatus',1)->where('estatus_pago',2)->get();
        $pendientes = Reservacion::whereIn('id',$reservacionesArray)->where('estatus',1)->whereIn('estatus_pago',[0,1])->get();
        
        $cortesias           = Reservacion::whereIn('id',$reservacionesArray)->where('estatus',1)->whereHas('descuentoCodigo', function (Builder $query) {
            $query
                ->whereRaw("nombre LIKE '%CORTESIA%' ");
        })->get();

        $cortesiasPersonas = 0;
        foreach($cortesias as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                if($reservacionDetalle->actividad_id == $actividad->id){
                    $cortesiasPersonas += $reservacionDetalle->numero_personas;
                }
            }
        }

        $numeroPersonasPagado = 0;
        foreach($pagados as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                if($reservacionDetalle->actividad_id == $actividad->id){
                    $numeroPersonasPagado += $reservacionDetalle->numero_personas;
                }
                
            }
        }

        $numeroPersonasPendiente = 0;
        foreach($pendientes as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                if($reservacionDetalle->actividad_id == $actividad->id){
                    $numeroPersonasPendiente += $reservacionDetalle->numero_personas;
                }
            }
        }
        return ['cortesias' => $cortesiasPersonas,'pagados' => ($numeroPersonasPagado-$cortesiasPersonas),'pendientes' => $numeroPersonasPendiente];
    }

    private function getActividadesHorarios($fechaInicio,$fechaFinal){
        $actividadesHorarios = ActividadHorario::with(['reservacion' => function ($query) use ($fechaInicio,$fechaFinal) {
                $query
                    ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                    ->where('estatus',1);
        }])->orderBy('horario_inicial', 'asc')->orderBy('id', 'asc')->get()->groupBy('horario_inicial');

        return $actividadesHorarios;
    }

    public function reporteCorteCaja(Request $request)
    {
        //$spreadsheet = new Spreadsheet();
        $usuario = new UsuarioController();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = $request->fechaInicio." 00:00:00";
        $fechaFinal = $request->fechaFinal." 23:59:00";
        // $fechaInicio = '2022-08-15 00:00:00';
        // $fechaFinal = '2022-08-15 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 
        $tipoCambio = TipoCambio::where("seccion_uso","reportes")->get()[0]["precio_compra"];

        $actividadesPagos = $this->getActividadesFechaPagos($fechaInicio,$fechaFinal);
        $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio,$fechaFinal);

        $spreadsheet->getActiveSheet()->setCellValue("A2", "CORTE DE CAJA");
        $spreadsheet->getActiveSheet()->setCellValue("A3", "Del {$formatoFechaInicio} al {$formatoFechaFinal}");

        $rowNumber = 5;
        
        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('00B050');

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFont()->setSize(16);

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", "Tipo de cambio: ");
        $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $tipoCambio);
        
        $rowNumber += 1;
        foreach($actividadesPagos as $actividad){
            if(!count($actividad->pagos) ){
                continue;
            }
            $reservacionesPago = $actividad->pagos->pluck('reservacion.id');
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->where('estatus',1)->get();
            
            //Estilo de encabezado
            $spreadsheet->getActiveSheet()->mergeCells("A{$rowNumber}:G{$rowNumber}");
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('538DD5');
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);
            //PROGRAMA/ACTIVIDAD
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
            
            $rowNumber += 1;

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('8DB4E2');
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);

            //Titulos
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Folio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares'); 
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Cupón');
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado por');

            $rowNumber += 1;

            //Data
            $initialRowNumber = $rowNumber;
            foreach($reservaciones as $reservacion){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion->folio);

                $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivo',false,0);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivoUsd',$pagosEfectivoResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividad,'tarjeta',$pagosEfectivoUsdResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);

                $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividad,'cupon',$pagosTarjetaResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosCuponResult['pago']);

                $pagosCambioResult = $this->getPagosTotalesByType($reservacion,$actividad,'cambio',0);
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $pagosCambioResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$reservacion->comisionista->nombre);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FABF8F');
                
            //Calculo totales
            $sumrangeB = 'B' . $initialRowNumber . ':B' . $rowNumber-1;
            $sumrangeC = 'C' . $initialRowNumber . ':C' . $rowNumber-1;
            $sumrangeD = 'D' . $initialRowNumber . ':D' . $rowNumber-1;
            $sumrangeE = 'E' . $initialRowNumber . ':E' . $rowNumber-1;
            $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
            $spreadsheet->getActiveSheet()->setCellValue('A' . $rowNumber, 'Subtotal');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . $sumrangeB . ')');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . $sumrangeC . ')');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . $sumrangeD . ')');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . $sumrangeE . ')');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . $sumrangeF . ')'); 

            $rowNumber += 1; 

            $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:C{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('00B050');

            $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:C{$rowNumber}")
                    ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, 'USD A MXN: '); 
            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=(C' . ($rowNumber-1) . '*G5)'); 

            $rowNumber += 3;
        }
        
        $rowNumber += 2;

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F0F000');
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true);
        
        //Titulos
        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares');
        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
        $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Cupón');
        $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Totales');
        $rowNumber += 1;

        $initialRowNumber = $rowNumber;
        foreach($actividadesPagos as $actividadPagos){

            $reservacionesPago = $actividadPagos->pagos->pluck('reservacion.id');
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->where('estatus',1)->get();

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F0F000');
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividadPagos->nombre);
            $totalEfectivo = 0;
            $totalEfectivoUSD = 0;
            $totalTarjeta = 0;
            $totalCupon = 0;
            foreach($reservaciones as $reservacion){
                
                $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'efectivo',0);
                $totalEfectivo    += $pagosEfectivoResult['pago'];

                $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'efectivoUsd',$pagosEfectivoResult['pendiente']);
                $totalEfectivoUSD += $pagosEfectivoUsdResult['pago'];

                $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'tarjeta',$pagosEfectivoUsdResult['pendiente']);
                $totalTarjeta     += $pagosTarjetaResult['pago'];

                $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'cupon',$pagosTarjetaResult['pendiente']);
                $totalCupon       += $pagosCuponResult['pago'];
            }
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $totalEfectivo);
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $totalEfectivoUSD);
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $totalTarjeta);
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $totalCupon);
            
            //Calculo totales
            // $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'B' . $rowNumber . ':E' . $rowNumber . ')');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * G5 ) ,D' . $rowNumber . ' ,E' . $rowNumber . ')');

            $rowNumber += 1;
        }

        $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FABF8F');

        $sumrangeB = 'B' . $initialRowNumber . ':B' . $rowNumber-1;
        $sumrangeC = 'C' . $initialRowNumber . ':C' . $rowNumber-1;
        $sumrangeD = 'D' . $initialRowNumber . ':D' . $rowNumber-1;
        $sumrangeE = 'E' . $initialRowNumber . ':E' . $rowNumber-1;
        $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . $sumrangeB . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . $sumrangeC . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . $sumrangeD . ')');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . $sumrangeE . ')');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . $sumrangeF . ')');
        
        $rowNumber += 1;
        
        $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:C{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('00B050');

        $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:C{$rowNumber}")
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, 'USD A MXN: '); 
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=(C' . ($rowNumber-1) . '*G5)'); 

        $rowNumber += 2;
        
        //# PROGRAMAS PAGADOS
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F0F000');

        //Titulos
        $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'PROGRAMA');
        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'PAGADOS');
        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'PENDIENTES');
        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'CORTESIAS');
        $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'TOTAL');
        
        $rowNumber += 1;
        $initialRowNumber = $rowNumber;

        foreach($actividadesFechaReservaciones as $actividad){
                if(!count($actividad->reservaciones) ){
                    continue;
                }
                
                $reservaciones = $actividad->reservaciones;
                $reservacionesTotales = $this->getReservacionesTotalesGeneral($actividad,$reservaciones);
                    
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacionesTotales['pagados']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacionesTotales['pendientes']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $reservacionesTotales['cortesias']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", '=SUM(' . 'B' . $rowNumber . ':D' . $rowNumber . ')');
                    
                $rowNumber += 1;
        }
                
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FABF8F');

        //Calculo totales
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');

        // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
        //     ->getFill()
        //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FABF8F');
        // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
        //     ->getAlignment()
        //     ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
        //     ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
        //     ->setWrapText(true);
        
        // //Titulos
        // $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'PROGRAMA');
        // $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'PAGADOS');
        // $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'CORTESIAS');
        // $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'TOTAL');
        
        // $rowNumber += 1;
        // $initialRowNumber = $rowNumber;


        // foreach($actividadesPagos as $actividad){
        //     if(!count($actividad->pagos) ){
        //         continue;
        //     }
        //     $reservacionesPago = $actividad->pagos->pluck('reservacion.id');
        //     $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->where('estatus',1)->where('estatus_pago',2)->get();

        //     $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
        //         ->getFill()
        //         ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //         ->getStartColor()->setARGB('F0F000');

        //     $reservacionesTotales = $this->getReservacionesTotales($reservaciones);
            
        //     $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
        //     $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacionesTotales['pagados']);
        //     $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacionesTotales['cortesias']);
        //     $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", count($reservaciones));

            
        //     $totalEfectivo = 0;
        //     $totalEfectivoUSD = 0;
        //     $totalTarjeta = 0;
        //     $totalCupon = 0;
        //     foreach($reservaciones as $reservacion){
        //         $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivo',0);
        //         $totalEfectivo    += $pagosEfectivoResult['pago'];

        //         $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivoUsd',$pagosEfectivoResult['pendiente']);
        //         $totalEfectivoUSD += $pagosEfectivoUsdResult['pago'];

        //         $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividad,'tarjeta',$pagosEfectivoUsdResult['pendiente']);
        //         $totalTarjeta     += $pagosTarjetaResult['pago'];

        //         $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividad,'cupon',$pagosTarjetaResult['pendiente']);
        //         $totalCupon       += $pagosCuponResult['pago'];
        //     }
            
        //     $rowNumber += 1;
        // }
                
        // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
        //         ->getFill()
        //         ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //         ->getStartColor()->setARGB('FABF8F');

        // //Calculo totales
        // $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
        // $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
        // $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
        

        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/corte_de_caja/corte-de-caja.xlsx");
        return ['data'=>true];
    }

    private function getReservacionesTotales($reservaciones){
        $reservacionesArray = $reservaciones->pluck('id');
        //CORTESIA ID = 6
        $cortesiasPersonas = 0;
        $pagados = Reservacion::whereIn('id',$reservacionesArray)->count();
        
        $cortesias           = Reservacion::whereIn('id',$reservacionesArray)->where('estatus',1)->whereHas('descuentoCodigo', function (Builder $query) {
            $query
                ->whereRaw("nombre LIKE '%CORTESIA%' ");
        })->get();
        foreach($cortesias as $reservacion){
            foreach($reservacion->reservacionDetalle as $reservacionDetalle){
                $cortesiasPersonas += $reservacionDetalle->numero_personas;
            }
        }
        return ['cortesias' => $cortesiasPersonas,'pagados' => $pagados];
    }

    private function getPagosTotalesByType($reservacion,$actividad,$pagoTipoNombre,$pendiente = 0){

        $pagos         = $reservacion->pagos;
        $reservaciones = new ReservacionController();
        $pagoTipoId    = $reservaciones->getTipoPagoId($pagoTipoNombre);
        //total pagado en tipo de pago actual
        $totalPagado   = 0;
        foreach($pagos as $pago){
            if($pago->tipo_pago_id == $pagoTipoId){
                $totalPagado += $pago->cantidad;
            }
        }
        
        $totalActividad = $this->getPagoActividadIndividual($reservacion,$actividad,$totalPagado,$pendiente);
        return ['pago' => $totalActividad[0],'pendiente' => $totalActividad[1]];
    }

    private function getPagoActividadIndividual($reservacion,$actividad,$totalPagado,$pendiente){
        $actividadIndividualPago = 0;
        $actividadIndividualPendiente = 0;
        $actividadPago = 0;
		if($totalPagado < 1){
			return [$actividadIndividualPago,$pendiente];
		}
		
        foreach($reservacion->reservacionDetalle as $reservacionDetalle){
            
            $actividadPrecio = ($reservacionDetalle->actividad->precio * $reservacionDetalle->numero_personas);

            if($pendiente > 0){
                if($totalPagado > $pendiente){
                    $actividadPago = $pendiente;
                    $pendiente = ($totalPagado - $actividadPago);
                }else{
                    $actividadPago = $pendiente;
                    $pendiente = ($totalPagado - $actividadPago);
                }
                $actividadPago = $pendiente;
                $pendiente     = ($pendiente - $totalPagado);
                //break;
            }else{
				if($totalPagado < 1){
				    $actividadPago = 0; 
				    $pendiente     = ($pendiente - $totalPagado);
				    //break;
				}else if($totalPagado >= $actividadPrecio){
				    $actividadPago = $actividadPrecio;
				    $pendiente     = 0; 
				    //break;
				}else{
				    $actividadPago = $totalPagado;
				    $pendiente     = ($pendiente - $totalPagado);
				    //break;
				}
			}

			if($reservacionDetalle->actividad->id == $actividad->id){
                $actividadIndividualPago = $actividadPago;
                $actividadIndividualPendiente = $pendiente;
			}
            
            $totalPagado = (round($totalPagado,2) - round($actividadPago,2));
        }

        return [$actividadIndividualPago,$actividadIndividualPendiente];
    }

    private function getActividadesFechaPagos($fechaInicio,$fechaFinal){
        
        $actividades = Actividad::with(['pagos' => function ($query) use ($fechaInicio,$fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',[1,2,3,4,5])->count();
        }])->get();
        
        return $actividades;
    }
}