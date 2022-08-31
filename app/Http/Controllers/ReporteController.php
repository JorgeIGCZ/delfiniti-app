<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use App\Models\Comision;
use App\Models\ComisionistaTipo;
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
        return view('reportes.index');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reporteComisiones(Request $request){
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = $request->fechaInicio." 00:00:00";
        $fechaFinal  = $request->fechaFinal." 23:59:00";
        // $fechaInicio = '2022-08-19 00:00:00';
        // $fechaFinal = '2022-08-19 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal  = date_format(date_create($fechaFinal),"d-m-Y"); 

        $comisionesTipo       = $this->getComisionesTipo(0,$fechaInicio,$fechaFinal);
        // $comisionesCerradores = $this->getComisionesCerradores($fechaInicio,$fechaFinal);


        $spreadsheet->getActiveSheet()->setCellValue("A2", "REPORTE DE COMISIONES");
        $spreadsheet->getActiveSheet()->setCellValue("A3", "Del {$formatoFechaInicio} al {$formatoFechaFinal}");	

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);

        $rowNumber = 5;

        $sumaC = [];
        $sumaD = [];
        $sumaE = [];
        $sumaF = [];

        $comisionesAgrupadasTipoPorcentaje = $this->getComisionesAgrupadasTipoPorcentaje($comisionesTipo);

        foreach($comisionesAgrupadasTipoPorcentaje as $key => $comisionAgrupadaTipoPorcentajeGeneral){
            //Titulos
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setBold(true);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setSize(12);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:G{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", '#');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $key);
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'COM. BRUTA S/IVA');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", '4%');
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'A PAGAR');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'FIRMA');

            $rowNumber += 1;
            $index = 1;
            $initialRowNumber = $rowNumber;

            $comisionesAgrupadasComisionistas = $this->getComisionesAgrupadasComisionistas($comisionAgrupadaTipoPorcentajeGeneral);

            foreach($comisionesAgrupadasComisionistas as $comision){
                $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)->setRowHeight(40);

                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $comision['pagoTotal']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $comision['cantidadNeta']);
                $rowNumber += 1;
                $index++;
            }

            //Calculo total
            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');

            $sumaC[] = 'C' . $rowNumber;
            $sumaD[] = 'D' . $rowNumber;
            $sumaE[] = 'E' . $rowNumber;
            $sumaF[] = 'F' . $rowNumber;

            $rowNumber += 1;

            $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $spreadsheet->getActiveSheet()->getStyle("E{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, "PORCENTAJE: ");
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=IF(ISERROR(' . 'F' . $rowNumber-1 . '/C' . $rowNumber-1 . '),0,(' . 'F' . $rowNumber-1 . '/C' . $rowNumber-1 . '))');

            $rowNumber += 2;
        }

        $rowNumber += 2;
        //Calculo totales
        $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTALES');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=' . implode('+',$sumaC));
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=' . implode('+',$sumaD));
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=' . implode('+',$sumaE));
        $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=' . implode('+',$sumaF));


        //SECOND SHEET
        // $VOSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'V&O');
        // $spreadsheet->addSheet($VOSheet, 1);

        // $spreadsheet->getSheet(1)->setCellValue("A2", "REPORTE V&O");
        // $spreadsheet->getActiveSheet()->setCellValue("A3", "INGRESOS POR CANALES DE VENTAS Del {$formatoFechaInicio} al {$formatoFechaFinal}");

        
        // $comisionesTipo       = $this->getComisionesTipo(1,$fechaInicio,$fechaFinal);

        // foreach($comisionesTipo as $key => $comisionTipo){

        //     echo("<br>comisionTipo ".$comisionTipo->nombre);
        //     $comisionBrutaSinIva = 0;
        //     print_r($comisionTipo->comisionistaCanalDetalle->);
        //     // foreach($comisionTipo->comisiones as $comision){
        //     //     $comisionBrutaSinIva +=  $comision->cantidad_comision_bruta;
        //     // }
        //     // echo("<br>comisionBrutaSinIva ".$comisionBrutaSinIva);
            
        //     // $comisionAgrupadasComisionistas = $this->getComisionesAgrupadasComisionistas($comisionTipo);
            
        //     // // comisionistaCanalDetalle
        //     // foreach($comisionAgrupadasComisionistas as $comision){

        //     //     echo("<br>canal ".$comision['comisionBruta']);
        //     //     echo("<br>comisionBruta ".$comision['comisionBruta']);
        //     //     // $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:F{$rowNumber}")
        //     //     //     ->getNumberFormat()
        //     //     //     ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        //     //     // $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)->setRowHeight(40);

        //     //     // $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $index);
        //     //     // $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
        //     //     // $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $comision['pagoTotal']);
        //     //     // $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $comision['comisionBruta']);
        //     //     // $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $comision['descuentoImpuesto']);
        //     //     // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $comision['cantidadNeta']);
        //     //     // $rowNumber += 1;
        //     //     // $index++;
        //     // }
        // }
        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/comisiones/comisiones.xlsx");
        return ['data'=>true];
    }

    private function getComisionesAgrupadasTipoPorcentaje($comisionesTipo){
        $comisionesTipoPorcentaje = [];
        $comisionesAgrupadasTipoPorcentaje = [];
        foreach($comisionesTipo as $comisionTipo){
            $comisiones = $comisionTipo->comisiones;

            foreach($comisiones as $comision){
                $comsisionNombre = $comisionTipo->nombre;
                $comsision = $comision->comisionista->comision;
                $comisionKey = $comsisionNombre.' - '.$comsision.'%';
                
                if(!in_array($comisionKey,$comisionesTipoPorcentaje)){
                    $comisionesTipoPorcentaje[] = [$comisionKey];

                    $comisionesAgrupadasTipoPorcentaje[$comisionKey][] = [
                        'comisionistaId'     => $comision->comisionista->id,
                        'comisionistaNombre' => $comision->comisionista->nombre,
                        'pagoTotal'          => $comision->pago_total,
                        'comisionBruta'      => $comision->cantidad_comision_bruta,
                        'descuentoImpuesto'  => $comision->descuento_impuesto,
                        'cantidadNeta'       => $comision->cantidad_comision_neta
                    ];
                    continue;
                }

                $comisionesAgrupadasTipoPorcentaje[$comisionKey][] = [
                    'comisionistaId'     => $comision->comisionista->id,
                    'comisionistaNombre' => $comision->comisionista->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
            }
        }
        return $comisionesAgrupadasTipoPorcentaje;
    }

    private function getComisionesAgrupadasComisionistas($comisionAgrupadaTipoPorcentajeGeneral){

        $comisionistasId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisionAgrupadaTipoPorcentajeGeneral as $comisionTipo){
                
            $comisionistaId = $comisionTipo["comisionistaId"];

            if(!in_array($comisionistaId,$comisionistasId)){
                $comisionistasId[]     = $comisionistaId;
                $comisionesAgrupadasComisionistas[$comisionistaId] = [
                    'comisionistaNombre' => $comisionTipo['comisionistaNombre'],
                    'pagoTotal'          => $comisionTipo['pagoTotal'],
                    'comisionBruta'      => $comisionTipo['comisionBruta'],
                    'descuentoImpuesto'  => $comisionTipo['descuentoImpuesto'],
                    'cantidadNeta'       => $comisionTipo['cantidadNeta']
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$comisionistaId]['pagoTotal']         += $comisionTipo['pagoTotal'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['comisionBruta']     += $comisionTipo['comisionBruta'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['descuentoImpuesto'] += $comisionTipo['descuentoImpuesto'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['cantidadNeta']      += $comisionTipo['cantidadNeta'];
        }
        
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesTipo($isComisionistaCanal,$fechaInicio,$fechaFinal){
        $comisionistaCanal = ($isComisionistaCanal ? [1] : [0,1]);

        $comisiones = Comision::whereBetween("comisiones.created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
            $query->where('estatus',1);
        })->get();

        $comisionesId = $comisiones->pluck('id');

        $comisionistaTipo = ComisionistaTipo::whereHas('comisiones', function ($query) use ($comisionesId) {
            $query->whereIn('comisiones.id',$comisionesId);
        })->with(['comisiones' => function ($query) use ($comisionesId) {
            $query->whereIn('comisiones.id',$comisionesId);
        }])->whereIn('comisionista_canal',$comisionistaCanal)->get();
        
        return $comisionistaTipo;
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
        
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

        $rowNumber = 5;

        foreach ($actividadesHorarios as $actividadesHorario){
            foreach ($actividadesHorario as $actividadHorario){
                if(count($actividadHorario->reservacion) == 0){
                    continue;
                }

                $spreadsheet->getActiveSheet()->mergeCells("A{$rowNumber}:B{$rowNumber}");

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:B{$rowNumber}")
                    ->getFont()->setBold(true);
                
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:B{$rowNumber}")
                    ->getFont()->setSize(12);

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:B{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('D9D9D9');
                    
                //Actividad
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividadHorario->actividad->nombre." ".$actividadesHorario[0]->horario_inicial);
                $rowNumber += 1;

                $spreadsheet->getActiveSheet()->mergeCells("B{$rowNumber}:C{$rowNumber}");

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);
                
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setSize(12);

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F2F2F2');

                //Titulos
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'FECHA');
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'CLIENTE');
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'ORIGEN');
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'PAX');
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'AGENTE/AGENCIA');
                // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'DESCUENTO');
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'T. PAGO');
                $rowNumber += 1;

                $initialRowNumber = $rowNumber;

                foreach ($actividadHorario->reservacion as $reservacion) {
                    $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion->fecha);

                    $spreadsheet->getActiveSheet()->mergeCells("B{$rowNumber}:C{$rowNumber}");

                    // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                    // ->getBorders()
                    // ->getOutline()
                    // ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                    // $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    // ->getFill()
                    // ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    // ->getStartColor()->setARGB('CCFFFF');

                    $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacion->nombre_cliente);
                    $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", @$reservacion->origen);
                    $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getNumeroPersonas($actividadHorario,$reservacion->reservacionDetalle));
                    $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", @$reservacion->comisionista->nombre);
                    $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", ($reservacion->tipoPago !== null ? @$reservacion->tipoPago->pluck('nombre')[0] : ''));
                    $rowNumber += 1;
                }
                $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
                $rowNumber += 2;
            }
        }

        $rowNumber += 3;

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
            ->getFont()->setSize(12);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F2F2F2');

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
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getFont()->setBold(true);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

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

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

        $rowNumber = 5;
        
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
                ->getStartColor()->setARGB('D9D9D9');

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
                ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
                ->getFont()->setSize(12);
            
            //PROGRAMA/ACTIVIDAD
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
            
            $rowNumber += 1;

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setSize(12);

            //Titulos
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Folio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares'); 
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Cupón');
            // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Reservado por');

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

                // $pagosCambioResult = $this->getPagosTotalesByType($reservacion,$actividad,'cambio',0);
                // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $pagosCambioResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", @$reservacion->comisionista->nombre);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFont()->setBold(true);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            //Calculo totales
            $sumrangeB = 'B' . $initialRowNumber . ':B' . $rowNumber-1;
            $sumrangeC = 'C' . $initialRowNumber . ':C' . $rowNumber-1;
            $sumrangeD = 'D' . $initialRowNumber . ':D' . $rowNumber-1;
            $sumrangeE = 'E' . $initialRowNumber . ':E' . $rowNumber-1;
            // $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
            $spreadsheet->getActiveSheet()->setCellValue('A' . $rowNumber, 'Subtotal');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . $sumrangeB . ')');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . $sumrangeC . ')');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . $sumrangeD . ')');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . $sumrangeE . ')');

            $rowNumber += 4;
        }
        
        $rowNumber += 1;

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('00B050');

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                ->getFont()->setSize(16);

        $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $tipoCambioRowNumber = $rowNumber;

        $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", "Tipo de cambio: ");
        $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $tipoCambio);
        
        $rowNumber += 1;
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
            ->getFont()->setSize(12);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F2F2F2');
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
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * G'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber . ')');

            $rowNumber += 1;
        }

        $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFont()->setBold(true);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

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
        
        $rowNumber += 3;
        
        //# PROGRAMAS PAGADOS
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setBold(true);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setSize(12);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

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

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getFont()->setBold(true);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:E{$rowNumber}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        //Calculo totales
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');

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
        }])->orderBy('actividades.reporte_orden','asc')->get();
        
        return $actividades;
    }
}