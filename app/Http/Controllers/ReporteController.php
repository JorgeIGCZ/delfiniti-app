<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Reservacion;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Pago;
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
    public function corteCaja(Request $request)
    {
        //$spreadsheet = new Spreadsheet();
        $usuario = new UsuarioController();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template-corte-caja.xlsx');
        // $fechaInicio = $request->fechaInicio." 00:00:00:000";
        // $fechaFinal = $request->fechaFinal." 23:59:00:000";
        $fechaInicio = '2022-08-15 00:00:00:000';
        $fechaFinal = '2022-08-15 23:59:00:000';


        $actividadesPagos = $this->getActividadesFechaPagos($fechaInicio,$fechaFinal);
        $actividadesPagadas = $this->getActividadesPagadas($fechaInicio,$fechaFinal);

        $spreadsheet->getActiveSheet()->setCellValue("A3", "Del {$fechaInicio} al {$fechaFinal}");				
        

        // foreach($actividades as $actividad){
        //     echo($actividad->nombre."<br>");
        //     foreach($actividad->pagos as $pagos){
        //         echo($pagos->created_at."<br>");
        //         echo("<br>");
        //     }
        //     echo("<br><br>");
        // }


        $rowNumber = 3;
        foreach($actividadesPagos as $actividad){
            if(!count($actividad->pagos) ){
                continue;
            }
            $reservacionesPago = $actividad->pagos->pluck('reservacion.id');
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->get();
            
            // echo($actividad->nombre."<br>");
            // echo($actividad->folio."<br>");
            // foreach($actividad->pagos as $pagos){
            //     echo($pagos->created_at."<br>");
            //     echo($pagos->reservacion->folio."<br>");
            //     echo("<br>");
            // }
            // $reservaciones = $actividad->pagos->groupBy('reservacion.folio');

            // foreach($pagosReservaciones as $pagos){
            //     dd($pagos[0]->reservacion);
            // }
            // echo("<br><br>");

            $rowNumber += 2;
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

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $usuario->getUsuarioNombre($reservacion->agente_id));
                $rowNumber += 1;
            }
            
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
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->get();

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
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'B' . $rowNumber . ':E' . $rowNumber . ')');

            $rowNumber += 1;
        }

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

        $rowNumber += 2;
        
        //# PROGRAMAS PAGADOS
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FABF8F');
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:D{$rowNumber}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true);
        
        //Titulos
        $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'PROGRAMA');
        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'PAGADOS');
        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'CORTESIAS');
        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'TOTAL');
        
        $rowNumber += 1;
        $initialRowNumber = $rowNumber;

        foreach($actividadesPagadas as $actividad){
            $reservacionesPago = $actividad->pagos->pluck('reservacion.id');
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->get();

            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F0F000');

            $reservacionesTotales = $this->getReservacionesTotales($reservaciones);
            
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacionesTotales['pagados']);
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacionesTotales['cortesias']);
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", count($reservaciones));

            
            // $totalEfectivo = 0;
            // $totalEfectivoUSD = 0;
            // $totalTarjeta = 0;
            // $totalCupon = 0;
            // foreach($reservaciones as $reservacion){
            //     $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivo',0);
            //     $totalEfectivo    += $pagosEfectivoResult['pago'];

            //     $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivoUsd',$pagosEfectivoResult['pendiente']);
            //     $totalEfectivoUSD += $pagosEfectivoUsdResult['pago'];

            //     $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividad,'tarjeta',$pagosEfectivoUsdResult['pendiente']);
            //     $totalTarjeta     += $pagosTarjetaResult['pago'];

            //     $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividad,'cupon',$pagosTarjetaResult['pendiente']);
            //     $totalCupon       += $pagosCuponResult['pago'];
            // }
            // $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $totalEfectivo);
            // $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $totalEfectivoUSD);
            // $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $totalTarjeta);
            // $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $totalCupon);
            
            // //Calculo totales
            // $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'B' . $rowNumber . ':E' . $rowNumber . ')');
            $rowNumber += 1;
        }
        //Calculo totales
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . 'B' . $initialRowNumber . ':B' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . 'C' . $initialRowNumber . ':C' . $rowNumber-1 . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/corte_de_caja/corte-de-caja.xlsx");
        return ['data'=>true];
    }

    private function getReservacionesTotales($reservaciones){
        //CORTESIA ID = 6
        $cortesias = 0;
        $pagados = 0;
        foreach($reservaciones as $reservacion){
            $isCortesia = 0; 
            //echo("<br><br>".$reservacion->folio."<br>");
            foreach($reservacion->pagos as $pago){
                if($pago->tipo_pago_id == 6){
                    $isCortesia = 1;
                    continue;
                }
            }
            //echo($isCortesia);
            if($isCortesia === 0){
                $pagados += 1;
            }else{
                $cortesias += 1;
            }
        }
        return ['cortesias' => $cortesias,'pagados' => $pagados];
    }

    private function getPagosTotalesByType($reservacion,$actividad,$pagoTipoNombre,$pendiente = 0){
        // if($pendiente == 0){
        //     return ['pago' => 0,'pendiente' => 0];
        // }

        $pagos         = $reservacion->pagos;
        $reservaciones = new ReservacionController();
        $pagoTipoId    = $reservaciones->getTipoPagoId($pagoTipoNombre);
        //total pagado en tipo de pago actual
        $totalPagado   = 0;
        foreach($pagos as $pago){
            if($pago->tipo_pago_id == $pagoTipoId){
                // if($actividad->nombre == 'FAMILY CLUB MED' && $reservacion->id == '10' && $pagoTipoNombre == 'tarjeta'){
                //     echo('<br>PAGO;'.$pagoTipoNombre." ACT;   ".$actividad->nombre.'<br>'.$pagoTipoNombre.': '.$pago->cantidad);
                // }
                // if($actividad->nombre == 'FAMILY CLUB MED' && $reservacion->id == '10' && $pagoTipoNombre == 'efectivo'){
                //     echo('<br>PAGO;'.$pagoTipoNombre." ACT;   ".$actividad->nombre.'<br>'.$pagoTipoNombre.': '.$pago->cantidad);
                // }

                // if($actividad->nombre == 'EXPERIENCIA' && $reservacion->id == '10' && $pagoTipoNombre == 'tarjeta'){
                //     echo('<br>PAGO;'.$pagoTipoNombre." ACT;   ".$actividad->nombre.'<br>'.$pagoTipoNombre.': '.$pago->cantidad);
                // }
                // if($actividad->nombre == 'EXPERIENCIA' && $reservacion->id == '10' && $pagoTipoNombre == 'efectivo'){
                //     echo('<br>PAGO;'.$pagoTipoNombre." ACT;   ".$actividad->nombre.'<br>'.$pagoTipoNombre.': '.$pago->cantidad);
                // }
                $totalPagado += $pago->cantidad;
            }
        }
		// if($reservacion->id == '10'){
		// 	echo('<br><br>Reserv '.$reservacion->id.' Actividad '.$actividad->nombre.' PAGO; '.$pagoTipoNombre.'<br>');
		// }
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
			// if($reservacion->id == '10'){
			// 	echo('<br><br>Reserv '.$reservacion->id.' Actividad '.$reservacionDetalle->actividad->nombre.'<br>');
			// }
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
            
			//if($totalPagado >= $actividadPago){
            $totalPagado = (round($totalPagado,2) - round($actividadPago,2));
            //}
        }

        return [$actividadIndividualPago,$actividadIndividualPendiente];
    }

    private function getActividadesFechaPagos($fechaInicio,$fechaFinal){
        // DB::enableQueryLog();
        // $pagos = Pago::whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])->get();

        // dd(DB::getQueryLog());
        $actividades = Actividad::with(['pagos' => function ($query) use ($fechaInicio,$fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal]);
        }])->get();

        // foreach($actividades as $actividad){
        //     echo($actividad->nombre."<br>");
        //     foreach($actividad->pagos as $pagos){
        //         echo($pagos->created_at."<br>");
        //         echo("<br>");
        //     }
        //     echo("<br><br>");
        // }
        return $actividades;
    }

    private function getActividadesPagadas($fechaInicio,$fechaFinal){
        DB::enableQueryLog();
        $actividadesPagadas = Actividad::with(['reservaciones' => function ($query) use ($fechaInicio,$fechaFinal) {
            $query
            ->where("estatus","1")
            ->where("estatus_pago","2")
            ->where("fecha_creacion","<",$fechaFinal)
            ->where("fecha_creacion",">=",$fechaInicio);
        }])->get();

        //dd($actividadesPagadas->reservaciones,2);
        // foreach($actividadesPagadas as $actividadPagada){
        //     foreach($actividadPagada->reservaciones as $reservacion){
        //         echo($reservacion->estatus."<br>");
        //         echo($reservacion->estatus_pago."<br>");
        //         echo($reservacion->fecha_creacion."<br>");
        //         echo("<br>");
        //     }
        //     echo("<br><br>");
        // }
        return $actividadesPagadas;
    }
}