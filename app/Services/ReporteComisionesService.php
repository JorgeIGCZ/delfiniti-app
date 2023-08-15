<?php

namespace App\Services;

use App\Models\Comision;
use App\Models\Comisionista;
use App\Models\CanalVenta;
use App\Models\Directivo;
use App\Models\DirectivoComisionFotoVideo;
use App\Models\DirectivoComisionReservacion;
use App\Models\DirectivoComisionTienda;
use App\Models\FotoVideoComision;
use App\Models\Reservacion;
use App\Models\SupervisorComisionFotoVideo;
use App\Models\SupervisorComisionTienda;
use App\Models\TiendaComision;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;

class ReporteComisionesService
{
    public function getReporte($request)
    {   
        $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
        $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();

        $canalesVentaRequest = $request->data['canalesVenta'];
        $canalesVentaRequest = json_decode($canalesVentaRequest);
        $canalesVentaRequest = $canalesVentaRequest->comisiones_canales_venta;

        $moduloRequest = $request->data['modulo'];
        $moduloRequest = json_decode($moduloRequest);
        $moduloRequest = $moduloRequest->filtro_modulo_comisiones;

        $cantidadIva = config('app.iva');

        // $fechaInicio = '2022-08-31 00:00:00';
        // $fechaFinal = '2022-08-31 23:59:00';
        $formatoFechaInicio  = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal   = date_format(date_create($fechaFinal),"d-m-Y"); 
        $comisionesTipo      = $this->getCanalVenta(0,$fechaInicio,$fechaFinal);
        $comisionesDirectivo = $this->getComisionesDirectivos($fechaInicio,$fechaFinal);
        
        // $comisionesCerradores = $this->getComisionesCerradores($fechaInicio,$fechaFinal);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');

        $sheetNumber = 1;
        // HOJA RESERVACIONES
        if(in_array("Reservaciones", $moduloRequest)){
            $spreadsheet->getActiveSheet()->setTitle("REPORTE DE RESERVACIONES");

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
            $sumaG = [];

            $comisionesAgrupadasTipoPorcentaje = $this->getComisionesAgrupadasTipoPorcentaje($comisionesTipo,$canalesVentaRequest);

            foreach($comisionesAgrupadasTipoPorcentaje as $key => $comisionAgrupadaTipoPorcentajeGeneral){
                //Titulos
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                    ->getFont()->setBold(true)->setSize(12);
                
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F2F2F2');

                $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", '#');
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $key);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'TOTAL');
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'VISITAS');
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'COM. BRUTA S/IVA');
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'DESC. IMPUESTO');
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'A PAGAR');
                $spreadsheet->getActiveSheet()->setCellValue("H{$rowNumber}", 'FIRMA');

                $rowNumber += 1;
                $index = 1;
                $initialRowNumber = $rowNumber;

                $comisionesAgrupadasComisionistas = $this->getComisionesAgrupadasComisionistas($comisionAgrupadaTipoPorcentajeGeneral);

                foreach($comisionesAgrupadasComisionistas as $comision){
                    $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:H{$rowNumber}")
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                    $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)->setRowHeight(40);

                    $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $index);
                    $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                    $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $comision['pagoTotal']);
                    $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $comision['cantidadComisionesEspeciales']);
                    $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                    $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                    $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);
                    $rowNumber += 1;
                    $index++;
                }

                //Calculo total
                $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:G{$rowNumber}")
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:G{$rowNumber}")
                        ->getFont()->setBold(true);
                
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                        ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTAL');
                
                //Evita comisionistas especiales sumen al total ya que es la misma reservacion ya contada
                if(!$comisionAgrupadaTipoPorcentajeGeneral[0]['comisionistaEspecial']){
                    $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
                }
                $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
                $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
                $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');
                $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=SUM(' . 'G' . $initialRowNumber . ':G' . $rowNumber-1 . ')');

                //Is comisionista especial
                //Evita comisionistas especiales sumen al total general ya que es la misma reservacion ya contada
                if(!$comisionAgrupadaTipoPorcentajeGeneral[0]['comisionistaEspecial']){
                    $sumaC[] = 'C' . $rowNumber;
                }

                $sumaD[] = 'D' . $rowNumber;
                $sumaE[] = 'E' . $rowNumber;
                $sumaF[] = 'F' . $rowNumber;
                $sumaG[] = 'G' . $rowNumber;

                $rowNumber += 1;

                $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}")
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

                $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                        ->getFont()->setBold(true);

                $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, "PORCENTAJE: ");
                $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=IF(ISERROR(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '),0,(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '))');

                $rowNumber += 2;
            }
            
            //Comisiones Directivos
            //Titulos
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFont()->setBold(true)->setSize(12);
                
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:H{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", '#');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'DIRECTIVO');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'TOTAL');
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'VISITAS');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'COM. BRUTA S/IVA');
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'DESC. IMPUESTO');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'A PAGAR');
            $spreadsheet->getActiveSheet()->setCellValue("H{$rowNumber}", 'FIRMA');

            $rowNumber += 1;
            $index = 1;
        
            $initialRowNumber = $rowNumber;
            
            $comisionesAgrupadasDirectivos = $this->getComisionesAgrupadasDirectivos($comisionesDirectivo);

            foreach($comisionesAgrupadasDirectivos as $comision){
                $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $comision['pagoTotal']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $comision['cantidadComisionesEspeciales']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);
                $rowNumber += 1;
                $index++;
            }

            //Calculo total
            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);
                
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTAL');
                
            //Evita comisionistas especiales sumen al total ya que es la misma reservacion ya contada
            // if(!$comisionAgrupadaTipoPorcentajeGeneral[0]['comisionistaEspecial']){
            //     $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
            // }
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=SUM(' . 'G' . $initialRowNumber . ':G' . $rowNumber-1 . ')');

            //Is comisionista especial
            //Evita comisionistas especiales sumen al total general ya que es la misma reservacion ya contada
            // if(!$comisionAgrupadaTipoPorcentajeGeneral[0]['comisionistaEspecial']){
            //     $sumaC[] = 'C' . $rowNumber;
            // }

            $sumaD[] = 'D' . $rowNumber;
            $sumaE[] = 'E' . $rowNumber;
            $sumaF[] = 'F' . $rowNumber;
            $sumaG[] = 'G' . $rowNumber;

            $rowNumber += 1;
        

            $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, "PORCENTAJE: ");
            $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=IF(ISERROR(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '),0,(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '))');
            $rowNumber += 2;
        

            //Calculo totales
            $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:G{$rowNumber}")
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                        ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTALES');

            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=' . implode('+',$sumaC));

            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=' . implode('+',$sumaD));
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=' . implode('+',$sumaE));
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=' . implode('+',$sumaF));
            $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=' . implode('+',$sumaG));

            //VISITAS
            if(in_array("Reservaciones", $moduloRequest)){
                $rowNumber += 4;
                $spreadsheet->getActiveSheet()->getStyle("B{$rowNumber}")
                    ->getFont()->setBold(true)->setSize(12);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'VISITAS');

                $rowNumber += 1;

                $reservaciones = Reservacion::whereHas('comisiones', function (Builder $query) use ($fechaInicio,$fechaFinal) {
                    $query
                        ->whereBetween("comisiones.created_at", [$fechaInicio,$fechaFinal])
                        ->where('estatus',1); 
                })->whereHas('comisionesDirectivos', function (Builder $query) use ($fechaInicio,$fechaFinal) {
                    $query
                        ->whereBetween("directivo_comisiones_reservacion.created_at", [$fechaInicio,$fechaFinal])
                        ->where('estatus',1); 
                })
                ->where('estatus',1)
                ->where("comisionable", 1)
                ->where("comisiones_especiales", 1)
                ->get();

                $comisionesEspecialesDetalle = [];
                $comisionistasId = [];
                $cerradoresId = [];
                $directivosId = [];
                foreach($reservaciones as $reservacion){

                    $reservacionId = $reservacion->id;
                    $comisionistaId = $reservacion->comisionista->id;

                    $comision = Comision::where('reservacion_id',$reservacionId)->get();

                    $cerradoresCanal = CanalVenta::where('comisionista_cerrador',1)->get();
                    $directivos = Directivo::where('estatus',1)->get();

                    // $comisionPromotor = Comision::where('reservacion_id',$reservacionId)->where('comisionista_id',$reservacion->comisionista_id)->get();

                    // Inicializar comisionista
                    if(!in_array($comisionistaId,$comisionistasId)){
                        $comisionistasId[] = $comisionistaId;
                        
                        $comisionesEspecialesDetalle[$comisionistaId] = [
                            'NOMBRE'   => $reservacion->comisionista->nombre,
                            'VISITAS'  => 0,
                            'COMISION' => 0
                        ];
                    }

                    $comisionesEspecialesDetalle[$comisionistaId]['VISITAS']  += isset($reservacion->ReservacionDetalle[0]->numero_personas) ? $reservacion->ReservacionDetalle[0]->numero_personas : 0;
                    $comisiones = Comision::where('reservacion_id',$reservacionId)->where('comisionista_id',$comisionistaId)->first();
                    if(isset($comisiones->cantidad_comision_neta)){
                        $comisionesEspecialesDetalle[$comisionistaId]['COMISION'] += $comisiones->cantidad_comision_neta;
                    }
                    
                    $cerradoresId[$comisionistaId] = [];

                    foreach($cerradoresCanal as $cerradorCanal){
                        foreach($cerradorCanal->comisionistas as $comisionistas){

                            // Inicializar cerradores
                            if(!in_array($comisionistas->id,$cerradoresId[$comisionistaId])){
                                $cerradoresId[$comisionistaId][] = $comisionistas->id;
                                
                                if(!isset($comisionesEspecialesDetalle[$comisionistaId]['CERRADOR']['COMISION'])){
                                    $comisionesEspecialesDetalle[$comisionistaId]['CERRADOR']['COMISION'] = 0;
                                }
                            }
                            
                            $comisiones = Comision::where('reservacion_id',$reservacionId)->where('comisionista_id',$comisionistas->id)->first();
                            if(isset($comisiones->cantidad_comision_neta)){
                                $comisionesEspecialesDetalle[$comisionistaId]['CERRADOR']['Id'] = $comisionistas->id;
                                $comisionesEspecialesDetalle[$comisionistaId]['CERRADOR']['COMISION'] += $comisiones->cantidad_comision_neta;
                            }
                        }
                    }

                    $directivosId[$comisionistaId] = [];

                    foreach($directivos as $key => $directivo){
                        // Inicializar directivos
                        if(!in_array($directivo->id,$directivosId[$comisionistaId])){
                            $directivosId[$comisionistaId][] = $directivo->id;
                            
                            if(!isset($comisionesEspecialesDetalle[$comisionistaId]['DIRECTIVOS'][$key]['COMISION'])){
                                $comisionesEspecialesDetalle[$comisionistaId]['DIRECTIVOS'][$key]['COMISION'] = 0;
                            }
                        }
                        
                        $comisiones = DirectivoComisionReservacion::where('reservacion_id',$reservacionId)->where('directivo_id',$directivo->id)->first();
                        if(isset($comisiones->cantidad_comision_neta)){
                            $comisionesEspecialesDetalle[$comisionistaId]['DIRECTIVOS'][$key]['Id'] = $directivo->id;
                            $comisionesEspecialesDetalle[$comisionistaId]['DIRECTIVOS'][$key]['COMISION'] += $comisiones->cantidad_comision_neta;
                        }
                    }
                }

                // dd($comisionesEspecialesDetalle);

                //Titulos comisiones Especiales Detalle
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:K{$rowNumber}")
                    ->getFont()->setBold(true)->setSize(12);
            
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:K{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F2F2F2');

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:K{$rowNumber}")
                    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                //Titulos estaticos
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'NOMBRE');
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'VISITAS');
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'COMISION');
                
                //Titulos dinamicos
                $column = 2;
                $index = 0;
                $titulosCreados = [];
                foreach($comisionesEspecialesDetalle as $comisionEspecialDetalle){
                    foreach($comisionEspecialDetalle as $titulo => $value){
                        // if($index < 3){
                        //     $index++;
                        //     continue;
                        // }

                        if(!in_array($titulo,$titulosCreados)){
                            $titulosCreados[] = $titulo;

                            if($titulo == 'CERRADOR'){
                                $comisionista = Comisionista::find($value['Id']);
                                $titulo = $comisionista->nombre;
                            }

                            if($titulo == 'DIRECTIVOS'){
                                foreach($value as $key => $value){
                                    $directivo = Directivo::find($value['Id']);
                                    $titulo = $directivo->nombre;

                                    $spreadsheet->getSheet(0)->setCellValueByColumnAndRow($column, $rowNumber, $titulo);
                                    $column++;
                                }
                                continue;
                            }

                            $spreadsheet->getSheet(0)->setCellValueByColumnAndRow($column, $rowNumber, $titulo);
                            $column++;
                        }
                    }
                }
                $rowNumber += 1;

                //Valores dinamicos
                foreach($comisionesEspecialesDetalle as $comisionEspecialDetalle){
                    $spreadsheet->getActiveSheet()->getStyle("D{$rowNumber}:K{$rowNumber}")
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    $column = 2;
                    $index = 0;
                    foreach($comisionEspecialDetalle as $titulo => $value){
                        
                        if($titulo == 'CERRADOR'){
                            $spreadsheet->getSheet(0)->setCellValueByColumnAndRow($column, $rowNumber, $value['COMISION']);
                            $index++;
                            $column++;
                            continue;
                        }

                        if($titulo == 'DIRECTIVOS'){
                            foreach($value as $key => $value){
                                $spreadsheet->getSheet(0)->setCellValueByColumnAndRow($column, $rowNumber, $value['COMISION']);
                                $index++;
                                $column++;
                            }
                            continue;
                        }

                        $spreadsheet->getSheet(0)->setCellValueByColumnAndRow($column, $rowNumber, $value);

                        $index++;
                        $column++;
                    }
                    $rowNumber++;          
                }

                $rowNumber += 3;

                


                //Titulos comisiones Especiales 
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                    ->getFont()->setBold(true)->setSize(12);
                
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('F2F2F2');

                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", '#');
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'NOMBRE');
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'VISITAS');
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'COMISIÓN');

                $rowNumber += 1;

                $reservaciones = Reservacion::whereHas('comisiones', function (Builder $query) use ($fechaInicio,$fechaFinal) {
                    $query
                        ->whereBetween("comisiones.created_at", [$fechaInicio,$fechaFinal])
                        ->where('estatus',1); 
                })->whereHas('comisionesDirectivos', function (Builder $query) use ($fechaInicio,$fechaFinal) {
                    $query
                        ->whereBetween("directivo_comisiones_reservacion.created_at", [$fechaInicio,$fechaFinal])
                        ->where('estatus',1); 
                })
                ->where('estatus',1)
                ->where("comisionable", 1)
                ->where("comisiones_especiales", 1)
                ->get();
                
                $comisionesEspeciales = [];

                $comisionistasId =[];
                $directivosId =[];
                foreach($reservaciones as $reservacion){

                    $comisiones = $reservacion->comisiones;
                    foreach($comisiones as $comision){
                    
                        $comisionistaId = $comision->comisionista->id;

                        if(!in_array($comisionistaId,$comisionistasId)){
                            $comisionistasId[] = $comisionistaId;
                            
                            $comisionesEspeciales['COMISIONISTAS'][$comisionistaId] = [
                                'nombre'   => $comision->comisionista->nombre,
                                'visitas'  => isset($comision->reservacion->ReservacionDetalle[0]->numero_personas) ? $comision->reservacion->ReservacionDetalle[0]->numero_personas : 0,
                                'comision' => isset($comision->cantidad_comision_neta) ? $comision->cantidad_comision_neta : 0,
                                'directivo' => 0,
                                'cerrador' => $comision->comisionista->tipo->comisionista_cerrador
                            ];
                            continue;
                        }

                        $comisionesEspeciales['COMISIONISTAS'][$comisionistaId]['visitas']  += isset($comision->reservacion->ReservacionDetalle[0]->numero_personas) ? $comision->reservacion->ReservacionDetalle[0]->numero_personas : 0;
                        $comisionesEspeciales['COMISIONISTAS'][$comisionistaId]['comision'] += isset($comision->cantidad_comision_neta) ? $comision->cantidad_comision_neta : 0;

                    }

                    $comisionesDirectivos = $reservacion->comisionesDirectivos;
                    foreach($comisionesDirectivos as $comision){

                        $directivoId = $comision->directivo->id;

                        if(!in_array($directivoId,$directivosId)){
                            $directivosId[] = $directivoId;
                            
                            $comisionesEspeciales['DIRECTIVOS'][$directivoId] = [
                                'nombre'   => $comision->directivo->nombre,
                                'visitas'  => isset($comision->reservacion->ReservacionDetalle[0]->numero_personas) ? $comision->reservacion->ReservacionDetalle[0]->numero_personas : 0,
                                'comision' => isset($comision->cantidad_comision_neta) ? $comision->cantidad_comision_neta : 0,
                                'directivo' => 1,
                                'cerrador' => 0
                            ];
                            continue;
                        }

                        $comisionesEspeciales['DIRECTIVOS'][$directivoId]['visitas']  += isset($comision->reservacion->ReservacionDetalle[0]->numero_personas) ? $comision->reservacion->ReservacionDetalle[0]->numero_personas : 0;
                        $comisionesEspeciales['DIRECTIVOS'][$directivoId]['comision'] += isset($comision->cantidad_comision_neta) ? $comision->cantidad_comision_neta : 0;
                    }
                }

                $index = 1;
                $sumaC = [];
                $sumaD = [];

                $spreadsheet->getActiveSheet()->getStyle("D{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                foreach($comisionesEspeciales as $key => $comisionEspecialTipo){
                    foreach($comisionEspecialTipo as $comisionEspecial){
                        $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $index);
                        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $comisionEspecial['nombre']);
                        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $comisionEspecial['visitas']);
                        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $comisionEspecial['comision']);
            
                        if($comisionEspecial['cerrador'] == 0 && $key !== 'DIRECTIVOS'){
                            $sumaC[] = 'C' . $rowNumber;
                        }
                        $sumaD[] = 'D' . $rowNumber;
            
                        $rowNumber += 1;      
                        $index++;      
                    }
                }

                //Calculo totales
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
                            ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                $spreadsheet->getActiveSheet()->getStyle("D{$rowNumber}:K{$rowNumber}")
                            ->getNumberFormat()
                            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                
                $spreadsheet->getActiveSheet()->getStyle("C{$rowNumber}:K{$rowNumber}")
                            ->getFont()->setBold(true);

                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'TOTALES');
                $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=' . implode('+',$sumaC));
                $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=' . implode('+',$sumaD));
            }
        }

        //HOJA FOTO Y VIDEO
        if(in_array("FotoVideo", $moduloRequest)){
            $FotoVideoSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'REPORTE DE FOTO Y VIDEO');
            $spreadsheet->addSheet($FotoVideoSheet, $sheetNumber);

            $spreadsheet->getSheet($sheetNumber)->mergeCells("A1:H1");
            $spreadsheet->getSheet($sheetNumber)->mergeCells("A2:H2");
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(1)->setRowHeight(35);
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(2)->setRowHeight(25);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1")
                ->getFont()->setSize(20);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A2")
                ->getFont()->setSize(12);
            $spreadsheet->getSheet($sheetNumber)->getStyle('A1:B2')
                ->getFont()
                ->getColor()
                ->setRGB ('17365D');
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1:H2")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);

            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('D')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('E')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('F')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('G')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('H')->setAutoSize(true);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A1", "REPORTE DE FOTO Y VIDEO");
            $spreadsheet->getSheet($sheetNumber)->setCellValue("A2", "INGRESOS FOTO Y VIDEO DEL {$formatoFechaInicio} AL {$formatoFechaFinal}");

            $rowNumber = 5;
            
            $comisionesFotoVideoComisionista = $this->getComisionesFotoVideoComisionista($fechaInicio,$fechaFinal);
            $comisionesFotoVideoDirectivo = $this->getComisionesFotoVideoDirectivos($fechaInicio,$fechaFinal);
            $comisionesFotoVideoSupervisor = $this->getComisionesFotoVideoSupervisores($fechaInicio,$fechaFinal);

            //Titulos
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFont()->setBold(true)->setSize(12);
                
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", '#');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", 'FOTO Y VIDEO');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", 'TOTAL');

            $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", 'COM. BRUTA S/IVA');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", 'DESC. IMPUESTO');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", 'A PAGAR');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("H{$rowNumber}", 'FIRMA');

            $rowNumber += 1;
            $index = 1;
            $initialRowNumber = $rowNumber;
            
            //Comisiones mostrador
            $comisionesAgrupadasTiendaMostrador = $this->getComisionesAgrupadasFotoVideoComisionista($comisionesFotoVideoComisionista);
            foreach($comisionesAgrupadasTiendaMostrador as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);
                
                $sumaC[] = 'C' . $rowNumber;

                $rowNumber += 1;
                $index++;
            }

            //Comisiones directivos
            $comisionesAgrupadasTiendaDirectivos = $this->getComisionesAgrupadasFotoVideoDirectivos($comisionesFotoVideoDirectivo);
            foreach($comisionesAgrupadasTiendaDirectivos as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);

                $rowNumber += 1;
                $index++;
            }

            //Comisiones supervisores
            $comisionesAgrupadasTiendaSupervisores = $this->getComisionesAgrupadasFotoVideoSupervisores($comisionesFotoVideoSupervisor);
            foreach($comisionesAgrupadasTiendaSupervisores as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);

                $rowNumber += 1;
                $index++;
            }

            
            //Calculo total
            $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getSheet($sheetNumber)->getStyle("B{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);
                
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", 'TOTAL');
                
            // $spreadsheet->getSheet($sheetNumber)->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('G' . $rowNumber, '=SUM(' . 'G' . $initialRowNumber . ':G' . $rowNumber-1 . ')');


            $sumaD[] = 'D' . $rowNumber;
            $sumaE[] = 'E' . $rowNumber;
            $sumaF[] = 'F' . $rowNumber;
            $sumaG[] = 'G' . $rowNumber;

            $rowNumber += 1;

            $spreadsheet->getSheet($sheetNumber)->getStyle("G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $spreadsheet->getSheet($sheetNumber)->getStyle("F{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);

            $spreadsheet->getSheet($sheetNumber)->setCellValue('F' . $rowNumber, "PORCENTAJE: ");
            $spreadsheet->getSheet($sheetNumber)->setCellValue('G' . $rowNumber, '=IF(ISERROR(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '),0,(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '))');
            $rowNumber += 2;

            $sheetNumber++;
        }
        
        //HOJA TIENDA
        if(in_array("Tienda", $moduloRequest)){
            $TiendaSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'REPORTE DE TIENDA');
            $spreadsheet->addSheet($TiendaSheet, $sheetNumber);

            $spreadsheet->getSheet($sheetNumber)->mergeCells("A1:H1");
            $spreadsheet->getSheet($sheetNumber)->mergeCells("A2:H2");
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(1)->setRowHeight(35);
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(2)->setRowHeight(25);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1")
                ->getFont()->setSize(20);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A2")
                ->getFont()->setSize(12);
            $spreadsheet->getSheet($sheetNumber)->getStyle('A1:B2')
                ->getFont()
                ->getColor()
                ->setRGB ('17365D');
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1:H2")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);

            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('D')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('E')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('F')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('G')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('H')->setAutoSize(true);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A1", "REPORTE DE TIENDA");
            $spreadsheet->getSheet($sheetNumber)->setCellValue("A2", "INGRESOS TIENDA DEL {$formatoFechaInicio} AL {$formatoFechaFinal}");

            $rowNumber = 5;
            
            $comisionesTiendaMostrador = $this->getComisionesTiendaMostrador($fechaInicio,$fechaFinal);
            $comisionesTiendaDirectivo = $this->getComisionesTiendaDirectivos($fechaInicio,$fechaFinal);
            $comisionesTiendaSupervisor = $this->getComisionesTiendaSupervisores($fechaInicio,$fechaFinal); 

            //Titulos
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFont()->setBold(true)->setSize(12);
                
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", '#');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", 'TIENDA');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", 'TOTAL');

            $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", 'COM. BRUTA S/IVA');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", 'DESC. IMPUESTO');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", 'A PAGAR');
            $spreadsheet->getSheet($sheetNumber)->setCellValue("H{$rowNumber}", 'FIRMA');

            $rowNumber += 1;
            $index = 1;
            $initialRowNumber = $rowNumber;
            
            //Comisiones mostrador
            $comisionesAgrupadasTiendaMostrador = $this->getComisionesAgrupadasTiendaMostrador($comisionesTiendaMostrador);
            foreach($comisionesAgrupadasTiendaMostrador as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);
                
                $sumaC[] = 'C' . $rowNumber;

                $rowNumber += 1;
                $index++;
            }

            //Comisiones directivos
            $comisionesAgrupadasTiendaDirectivos = $this->getComisionesAgrupadasTiendaDirectivos($comisionesTiendaDirectivo);
            foreach($comisionesAgrupadasTiendaDirectivos as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);

                $rowNumber += 1;
                $index++;
            }

            //Comisiones supervisores
            $comisionesAgrupadasTiendaSupervisores = $this->getComisionesAgrupadasTiendaSupervisores($comisionesTiendaSupervisor);
            foreach($comisionesAgrupadasTiendaSupervisores as $comision){
                $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:H{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                $spreadsheet->getSheet($sheetNumber)->getRowDimension($rowNumber)->setRowHeight(40);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $index);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $comision['comisionistaNombre']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("C{$rowNumber}", $comision['pagoTotal']);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("E{$rowNumber}", $comision['comisionBruta']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("F{$rowNumber}", $comision['descuentoImpuesto']);
                $spreadsheet->getSheet($sheetNumber)->setCellValue("G{$rowNumber}", $comision['cantidadNeta']);

                $rowNumber += 1;
                $index++;
            }

            //Calculo total
            $spreadsheet->getSheet($sheetNumber)->getStyle("C{$rowNumber}:G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getSheet($sheetNumber)->getStyle("B{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);
                
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:H{$rowNumber}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", 'TOTAL');
                
            // $spreadsheet->getSheet($sheetNumber)->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('E' . $rowNumber, '=SUM(' . 'E' . $initialRowNumber . ':E' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('G' . $rowNumber, '=SUM(' . 'G' . $initialRowNumber . ':G' . $rowNumber-1 . ')');


            $sumaD[] = 'D' . $rowNumber;
            $sumaE[] = 'E' . $rowNumber;
            $sumaF[] = 'F' . $rowNumber;
            $sumaG[] = 'G' . $rowNumber;

            $rowNumber += 1;

            $spreadsheet->getSheet($sheetNumber)->getStyle("G{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $spreadsheet->getSheet($sheetNumber)->getStyle("F{$rowNumber}:G{$rowNumber}")
                    ->getFont()->setBold(true);

            $spreadsheet->getSheet($sheetNumber)->setCellValue('F' . $rowNumber, "PORCENTAJE: ");
            $spreadsheet->getSheet($sheetNumber)->setCellValue('G' . $rowNumber, '=IF(ISERROR(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '),0,(' . 'G' . $rowNumber-1 . '/C' . $rowNumber-1 . '))');
            $rowNumber += 2;

            $sheetNumber++;
        }
        
        //HOJA V&O
        if(in_array("Reservaciones", $moduloRequest)){
            $VOSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'V&O');
            $spreadsheet->addSheet($VOSheet, $sheetNumber);

            $spreadsheet->getSheet($sheetNumber)->mergeCells("A1:H1");
            $spreadsheet->getSheet($sheetNumber)->mergeCells("A2:H2");
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(1)->setRowHeight(35);
            $spreadsheet->getSheet($sheetNumber)->getRowDimension(2)->setRowHeight(25);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1")
                ->getFont()->setSize(20);
            $spreadsheet->getSheet($sheetNumber)->getStyle("A2")
                ->getFont()->setSize(12);
            $spreadsheet->getSheet($sheetNumber)->getStyle('A1:B2')
                ->getFont()
                ->getColor()
                ->setRGB ('17365D');
            $spreadsheet->getSheet($sheetNumber)->getStyle("A1:H2")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
                ->setWrapText(true);

            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('D')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('E')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('F')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('G')->setAutoSize(true);
            $spreadsheet->getSheet($sheetNumber)->getColumnDimension('H')->setAutoSize(true);

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A1", "REPORTE V&O");
            $spreadsheet->getSheet($sheetNumber)->setCellValue("A2", "INGRESOS POR CANALES DE VENTAS DEL {$formatoFechaInicio} AL {$formatoFechaFinal}");

            $rowNumber = 5;
            
            //Titulos
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:Z{$rowNumber}")
                        ->getFont()->setBold(true);
                    
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:Z{$rowNumber}")
                        ->getFont()->setSize(12);

            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:Z{$rowNumber}")
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('F2F2F2');

            $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", "CANAL DE VENTA");
            $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", "INGRESO S/IVA");
            
            $headerRowNumber = $rowNumber;
            $rowNumber += 1;

            //Se encuentra aqui comisiones_especiales ya que se calculan pero no debe aparecer en canales 
            $comisiones = Comision::whereBetween("comisiones.created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
                $query
                ->where('estatus',1)
                ->where('comisiones_especiales', 0)
                ->where('comisiones_canal', 1);
            })->get();

            $comisionesId = $comisiones->pluck('id');
            
            $canalesVenta = CanalVenta::with(['comisiones' => function ($query) use ($comisionesId) {
                    $query->whereIn('comisiones.id',$comisionesId);
                }])
                ->where('comisionista_canal',0)
                ->where('comisionista_actividad',0)
                ->where('comisionista_cerrador',0)
                ->get();

            $directivos = Directivo::where('estatus',1)->get();
            
            $initialRowNumber = $rowNumber;
            foreach($canalesVenta as $key => $canalVenta){

                if(!in_array($canalVenta->id, $canalesVentaRequest)){
                    continue;
                }
                
                //Canal de venta
                $spreadsheet->getSheet($sheetNumber)->setCellValue("A{$rowNumber}", $canalVenta->nombre);
                
                $reservacionesId = [];
                $pagoTotalSinIva = 0;
                $comisiones      = $canalVenta->comisiones;
                foreach($comisiones as $comision){
                    $pagoTotalSinIva  += round(($comision->pago_total / (1+($cantidadIva/100))),2);
                    $reservacionesId[] = $comision->reservacion_id;
                }

                //echo("<br>Pago Totoal SIN IVA: ".$pagoTotalSinIva);

                $spreadsheet->getSheet($sheetNumber)->setCellValue("B{$rowNumber}", $pagoTotalSinIva);
                
                
                    $column = 3;
                    foreach($directivos as $directivo){
                        $directivoComisionReservacionCanalDetalle = $directivo->directivoComisionReservacionCanalDetalle->groupBy('canal_venta_id');
                        $nombreDirectivo        = $directivo->nombre;
        
        
                        // echo("<br>Comisiones sobre canales: ");
                        // echo(@$comisionistasSobreTiposId[$canalVenta->id][0]->comision);
                        // echo("<br>");
        
                        // titulos dinamicos
                        $header = "% {$nombreDirectivo}";
                        $spreadsheet->getSheet($sheetNumber)->setCellValueByColumnAndRow($column, $headerRowNumber, $header);
        
                        $spreadsheet->getSheet($sheetNumber)->setCellValueByColumnAndRow($column, $rowNumber, @$directivoComisionReservacionCanalDetalle[$canalVenta->id][0]->comision);
                        $column += 1;
        
                        // $canalVentaId       = $canalVenta->id;
                        // $comisionistasCanal = Comisionista::where('Id',$directivo->id)->whereHas('directivoComisionReservacionCanalDetalle', function (Builder $query) use ($canalVentaId) {
                        //                                                         $query->where('canal_venta_id',$canalVentaId);
                        //                                                     })->get();
        
                        // $comisionistasCanalId              = $comisionistasCanal->pluck('id');
                        
                        $comisionesComisionistasSobreTipos = $directivo->directivoComisionReservacion->whereIn('reservacion_id',$reservacionesId)->whereIn('directivo_id',$directivo->id);
                        if($canalVenta->nombre == 'LOCACION'){
                            // dd($comisionistasCanal);$comision['descuentoImpuesto']
                        } 
                        $comisionComisionistasSobreTiposBrutaSinIva = 0;
                        foreach($comisionesComisionistasSobreTipos as $comisionComisionistasSobreTipos){
                            $comisionComisionistasSobreTiposBrutaSinIva += $comisionComisionistasSobreTipos->cantidad_comision_bruta;
                        }
                        
                        // titulos dinamicos
                        $header = "TOTAL {$nombreDirectivo}";
                        $spreadsheet->getSheet($sheetNumber)->setCellValueByColumnAndRow($column, $headerRowNumber, $header);
                        
                        $spreadsheet->getSheet($sheetNumber)->setCellValueByColumnAndRow($column, $rowNumber, @$comisionComisionistasSobreTiposBrutaSinIva);
                        $column += 1;
                    }
                
                $rowNumber += 1;
            }
            

            //Calculo totales
            $spreadsheet->getSheet($sheetNumber)->getStyle("B{$initialRowNumber}:B{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getSheet($sheetNumber)->getStyle("D{$initialRowNumber}:D{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getSheet($sheetNumber)->getStyle("F{$initialRowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);
                
            $spreadsheet->getSheet($sheetNumber)->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $spreadsheet->getSheet($sheetNumber)->setCellValue('A' . $rowNumber, 'TOTALES');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
            $spreadsheet->getSheet($sheetNumber)->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');
            
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/comisiones/comisiones.xlsx");
        return ['data'=>true];
    }

    private function getCanalVenta($isComisionistaCanal,$fechaInicio,$fechaFinal)
    {
        // $comisionistaCanal = ($isComisionistaCanal ? [0] : [0,1]);

        $comisiones = Comision::whereBetween("comisiones.created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
            $query
                ->where("estatus", 1);
                // ->where("comisionable", 1);
                // ->where("comisiones_especiales", 0);
        })->get();

        $comisionesId = $comisiones->pluck('id');

        $canalVenta = CanalVenta::whereHas('comisiones', function ($query) use ($comisionesId) {
            $query->whereIn('comisiones.id',$comisionesId);
        })->with(['comisiones' => function ($query) use ($comisionesId) {
            $query->whereIn('comisiones.id',$comisionesId);
        }])->get();
        
        return $canalVenta;
    }

    private function getComisionesDirectivos($fechaInicio,$fechaFinal)
    {
        $comisiones = DirectivoComisionReservacion::whereBetween("directivo_comisiones_reservacion.created_at", [$fechaInicio,$fechaFinal])->whereHas('reservacion',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesTiendaDirectivos($fechaInicio,$fechaFinal)
    {
        $comisiones = DirectivoComisionTienda::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesTiendaSupervisores($fechaInicio,$fechaFinal)
    {
        $comisiones = SupervisorComisionTienda::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesTiendaMostrador($fechaInicio,$fechaFinal)
    {
        $comisiones = TiendaComision::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesFotoVideoDirectivos($fechaInicio,$fechaFinal)
    {
        $comisiones = DirectivoComisionFotoVideo::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesFotoVideoSupervisores($fechaInicio,$fechaFinal)
    {
        $comisiones = SupervisorComisionFotoVideo::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesFotoVideoComisionista($fechaInicio,$fechaFinal)
    {
        $comisiones = FotoVideoComision::whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereHas('venta',function ($query){
            $query
                ->where("estatus", 1);
        })->get();
        
        return $comisiones;
    }

    private function getComisionesAgrupadasTipoPorcentaje($comisionesTipo,$canalesVenta)
    {
        $comisionesTipoPorcentaje = [];
        $comisionesAgrupadasTipoPorcentaje = [];
        $isComisionistaEspecial = false;
        foreach($comisionesTipo as $comisionTipo){
            if(!in_array($comisionTipo->id, $canalesVenta)){
                continue;
            }
            
            $comisiones = $comisionTipo->comisiones;

            //IS COMISIONISTA ESPECIAL
            if($comisionTipo->comisionista_actividad || $comisionTipo->comisionista_cerrador){
                $isComisionistaEspecial = true;
            }

            foreach($comisiones as $comision){
                $comsisionNombre = $comisionTipo->nombre;
                $comsision = $comision->comisionista->comision;
                $comisionKey = $comsisionNombre.' - '.$comsision.'%';

                //Evitar mostrar comisiones no comisionables a comisionistas que no sean directivos(comisionista_canal)
                if($comision->reservacion->comisionable == 0){
                    continue;
                }
                //Evitar mostrar comisiones que sean de comisionistas sin comisiones de canal, a comisionistas directivos(comisionista_canal)
                // if($comision->reservacion->comisiones_canal == 0 && $comisionTipo->comisionista_canal == 1){
                //     continue;
                // }

                $comisionesAgrupadasTipoPorcentaje[$comisionKey][] = [
                    'comisionistaId'        => $comision->comisionista->id,
                    'comisionistaNombre'    => $comision->comisionista->nombre,
                    'comisionistaEspecial'  => $isComisionistaEspecial,
                    'pagoTotal'             => $comision->pago_total,
                    'comisionBruta'         => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'     => $comision->descuento_impuesto,
                    'cantidadNeta'          => $comision->cantidad_comision_neta,
                    'comisionesEspeciales'  => $comision->reservacion->comisiones_especiales
                ];
            }
        }
        return $comisionesAgrupadasTipoPorcentaje;
    }

    private function getComisionesAgrupadasComisionistas($comisionAgrupadaTipoPorcentajeGeneral)
    {

        $comisionistasId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisionAgrupadaTipoPorcentajeGeneral as $comisionTipo){
                
            $comisionistaId = $comisionTipo["comisionistaId"];


            if(!in_array($comisionistaId,$comisionistasId)){
                $comisionistasId[]     = $comisionistaId;
                $comisionesAgrupadasComisionistas[$comisionistaId] = [
                    'comisionistaNombre' => $comisionTipo['comisionistaNombre'],
                    'pagoTotal'          => ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['pagoTotal'],
                    'comisionBruta'      => ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['comisionBruta'],
                    'descuentoImpuesto'  => ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['descuentoImpuesto'],
                    'cantidadNeta'       => $comisionTipo['cantidadNeta'],
                    'cantidadComisionesEspeciales' => ($comisionTipo['comisionesEspeciales']) ? $comisionTipo['cantidadNeta'] : 0
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$comisionistaId]['pagoTotal']         += ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['pagoTotal'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['comisionBruta']     += ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['comisionBruta'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['descuentoImpuesto'] += ($comisionTipo['comisionesEspeciales']) ? 0 : $comisionTipo['descuentoImpuesto'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['cantidadNeta']      += $comisionTipo['cantidadNeta'];
            $comisionesAgrupadasComisionistas[$comisionistaId]['cantidadComisionesEspeciales']      += ($comisionTipo['comisionesEspeciales']) ? $comisionTipo['cantidadNeta'] : 0;
        }
        
        return $comisionesAgrupadasComisionistas;
    }
    
    private function getComisionesAgrupadasDirectivos($comisionesDirectivo)
    {
        $directivosId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisionesDirectivo as $comision){
            $directivoId = $comision->directivo_id; 

            if(!in_array($directivoId,$directivosId)){
                $directivosId[]     = $directivoId;
                $comisionesAgrupadasComisionistas[$directivoId] = [
                    'comisionistaNombre' => $comision->directivo->nombre,
                    'pagoTotal'          => ($comision->reservacion->comisiones_especiales) ? 0 : $comision->pago_total,
                    'comisionBruta'      => ($comision->reservacion->comisiones_especiales) ? 0 : $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => ($comision->reservacion->comisiones_especiales) ? 0 : $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta,
                    'cantidadComisionesEspeciales' => ($comision->reservacion->comisiones_especiales) ? $comision->cantidad_comision_neta : 0
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$directivoId]['pagoTotal']         += ($comision->reservacion->comisiones_especiales) ? 0 : $comision->pago_total;
            $comisionesAgrupadasComisionistas[$directivoId]['comisionBruta']     += ($comision->reservacion->comisiones_especiales) ? 0 : $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$directivoId]['descuentoImpuesto'] += ($comision->reservacion->comisiones_especiales) ? 0 : $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$directivoId]['cantidadNeta']      += $comision->cantidad_comision_neta;
            $comisionesAgrupadasComisionistas[$directivoId]['cantidadComisionesEspeciales']      += ($comision->reservacion->comisiones_especiales) ? $comision->cantidad_comision_neta : 0;
        }
        // dd($comisionesAgrupadasComisionistas);
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesAgrupadasTiendaMostrador($comisiones)
    {
        $comisionistasId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisiones as $comision){
            $comisionistaId = $comision->comisionista_id; 

            if(!in_array($comisionistaId, $comisionistasId)){
                $comisionistasId[]     = $comisionistaId;
                $comisionesAgrupadasComisionistas[$comisionistaId] = [
                    'comisionistaNombre' => $comision->usuario->name,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$comisionistaId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasComisionistas[$comisionistaId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$comisionistaId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$comisionistaId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesAgrupadasTiendaDirectivos($comisiones)
    {
        $directivosId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisiones as $comision){
            $directivoId = $comision->directivo_id; 

            if(!in_array($directivoId, $directivosId)){
                $directivosId[] = $directivoId;
                $comisionesAgrupadasComisionistas[$directivoId] = [
                    'comisionistaNombre' => $comision->directivo->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$directivoId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasComisionistas[$directivoId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$directivoId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$directivoId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesAgrupadasTiendaSupervisores($comisiones)
    {
        $supervisoresId     = [];
        $comisionesAgrupadasSupervisores = [];

        foreach($comisiones as $comision){
            $supervisorId = $comision->supervisor_id; 

            if(!in_array($supervisorId, $supervisoresId)){
                $supervisoresId[] = $supervisorId;
                $comisionesAgrupadasSupervisores[$supervisorId] = [
                    'comisionistaNombre' => $comision->supervisor->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasSupervisores[$supervisorId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasSupervisores[$supervisorId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasSupervisores[$supervisorId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasSupervisores[$supervisorId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasSupervisores;
    }

    private function getComisionesAgrupadasFotoVideoComisionista($comisiones)
    {
        $comisionistasId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisiones as $comision){
            $comisionistaId = $comision->comisionista_id; 

            if(!in_array($comisionistaId, $comisionistasId)){
                $comisionistasId[]     = $comisionistaId;
                $comisionesAgrupadasComisionistas[$comisionistaId] = [
                    'comisionistaNombre' => @$comision->comisionista->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$comisionistaId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasComisionistas[$comisionistaId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$comisionistaId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$comisionistaId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesAgrupadasFotoVideoDirectivos($comisiones)
    {
        $directivosId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisiones as $comision){
            $directivoId = $comision->directivo_id; 

            if(!in_array($directivoId, $directivosId)){
                $directivosId[] = $directivoId;
                $comisionesAgrupadasComisionistas[$directivoId] = [
                    'comisionistaNombre' => $comision->directivo->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$directivoId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasComisionistas[$directivoId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$directivoId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$directivoId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasComisionistas;
    }

    private function getComisionesAgrupadasFotoVideoSupervisores($comisiones)
    {
        $supervisoresId     = [];
        $comisionesAgrupadasComisionistas = [];

        foreach($comisiones as $comision){
            $supervisorId = $comision->supervisor_id; 

            if(!in_array($supervisorId, $supervisoresId)){
                $supervisoresId[] = $supervisorId;
                $comisionesAgrupadasComisionistas[$supervisorId] = [
                    'comisionistaNombre' => $comision->supervisor->nombre,
                    'pagoTotal'          => $comision->pago_total,
                    'comisionBruta'      => $comision->cantidad_comision_bruta,
                    'descuentoImpuesto'  => $comision->descuento_impuesto,
                    'cantidadNeta'       => $comision->cantidad_comision_neta
                ];
                continue;
            }

            $comisionesAgrupadasComisionistas[$supervisorId]['pagoTotal']         += $comision->pago_total;
            $comisionesAgrupadasComisionistas[$supervisorId]['comisionBruta']     += $comision->cantidad_comision_bruta;
            $comisionesAgrupadasComisionistas[$supervisorId]['descuentoImpuesto'] += $comision->descuento_impuesto;
            $comisionesAgrupadasComisionistas[$supervisorId]['cantidadNeta']      += $comision->cantidad_comision_neta;
        }
        return $comisionesAgrupadasComisionistas;
    }
}
