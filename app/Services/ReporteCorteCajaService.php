<?php

namespace App\Services;

use App\Http\Controllers\ReservacionController;
use App\Models\Actividad;
use App\Models\Pago;
use App\Models\Reservacion;
use App\Models\TipoCambio;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;

class ReporteCorteCajaService
{
	public function getReporte($request)
	{
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
        $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();
        $creadaPor   = $request->data['cajero'];
        $showCupones = ($request->data['cupones'] === "1");
        
        $usuarios = User::role(['Administrador','Recepcion'])->get()->pluck('id');

        if($creadaPor !== "0"){
            $usuarios = [$creadaPor];
        }
        
        // $fechaInicio = '2022-08-15 00:00:00';
        // $fechaFinal = '2022-08-15 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 
        $tipoCambio = TipoCambio::where("seccion_uso","reportes")->get()[0]["precio_compra"];

        $actividadesPagos = $this->getActividadesFechaPagos($fechaInicio,$fechaFinal,$usuarios,$showCupones);
        //Exclude visitas
        $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio,$fechaFinal,$usuarios)->whereRaw('exclusion_especial = 0')->get();

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
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->where('estatus',1)->whereIn("usuario_id", $usuarios)->get();
            
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
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Depósito / transferencia');
            if($showCupones){
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Cupón');
            }
            // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado por');

            $rowNumber += 1;

            //Data
            $initialRowNumber = $rowNumber;
            foreach($reservaciones as $reservacion){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion->folio);

                $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivo',$fechaInicio,$fechaFinal,false,0);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividad,'efectivoUsd',$fechaInicio,$fechaFinal,$pagosEfectivoResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividad,'tarjeta',$fechaInicio,$fechaFinal,$pagosEfectivoUsdResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                
                $pagosDepositoResult = $this->getPagosTotalesByType($reservacion,$actividad,'deposito',$fechaInicio,$fechaFinal,$pagosTarjetaResult['pendiente']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

                if($showCupones){
                    $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividad,'cupon',$fechaInicio,$fechaFinal,$pagosDepositoResult['pendiente']);
                    $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $pagosCuponResult['pago']);
                }

                // $pagosCambioResult = $this->getPagosTotalesByType($reservacion,$actividad,'cambio',0);
                // $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $pagosCambioResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$reservacion->nombre_cliente);

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
            $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
            // $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
            $spreadsheet->getActiveSheet()->setCellValue('A' . $rowNumber, 'Subtotal');
            $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . $sumrangeB . ')');
            $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . $sumrangeC . ')');
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . $sumrangeD . ')');
            $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . $sumrangeE . ')');
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . $sumrangeF . ')');

            $rowNumber += 4;
        }
        
        $rowNumber += 1;

        $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}:H{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('00B050');

        $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}:H{$rowNumber}")
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $spreadsheet->getActiveSheet()->getStyle("G{$rowNumber}:H{$rowNumber}")
                ->getFont()->setSize(16);

        $spreadsheet->getActiveSheet()->getStyle("H{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $tipoCambioRowNumber = $rowNumber;

        $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", "Tipo de cambio: ");
        $spreadsheet->getActiveSheet()->setCellValue("H{$rowNumber}", $tipoCambio);
        
        $rowNumber += 1;
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
            ->getFont()->setSize(12);
        
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:H{$rowNumber}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F2F2F2');
        //Titulos
        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares');
        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
        $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Depósito / transferencia');
        if($showCupones){
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'Cupón.');
        }
        $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Totales');
        $rowNumber += 1;

        $initialRowNumber = $rowNumber;
        foreach($actividadesPagos as $actividadPagos){

            $reservacionesPago = $actividadPagos->pagos->pluck('reservacion.id');
            $reservaciones = Reservacion::whereIn('id',$reservacionesPago)->where('estatus',1)->whereIn("usuario_id", $usuarios)->get();
            
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividadPagos->nombre);
            $totalEfectivo = 0;
            $totalEfectivoUSD = 0;
            $totalTarjeta = 0;
            $totalDeposito = 0;
            $totalCupon = 0;

            foreach($reservaciones as $reservacion){
                
                $pagosEfectivoResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'efectivo',$fechaInicio,$fechaFinal,0);
                $totalEfectivo    += $pagosEfectivoResult['pago'];

                $pagosEfectivoUsdResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'efectivoUsd',$fechaInicio,$fechaFinal,$pagosEfectivoResult['pendiente']);
                $totalEfectivoUSD += $pagosEfectivoUsdResult['pago'];

                $pagosTarjetaResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'tarjeta',$fechaInicio,$fechaFinal,$pagosEfectivoUsdResult['pendiente']);
                $totalTarjeta     += $pagosTarjetaResult['pago'];

                $pagosDepositoResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'deposito',$fechaInicio,$fechaFinal,$pagosTarjetaResult['pendiente']);
                $totalDeposito     += $pagosDepositoResult['pago'];

                if($showCupones){
                    $pagosCuponResult = $this->getPagosTotalesByType($reservacion,$actividadPagos,'cupon',$fechaInicio,$fechaFinal,$pagosDepositoResult['pendiente']);//remove
                    $totalCupon       += $pagosCuponResult['pago'];
                }
            }
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $totalEfectivo);
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $totalEfectivoUSD);
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $totalTarjeta);
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $totalDeposito);
            if($showCupones){
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $totalCupon);
            }
            
            //Calculo totales
            // $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'B' . $rowNumber . ':E' . $rowNumber . ')');
            $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');

            $rowNumber += 1;
        }

        $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:G{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getFont()->setBold(true);
            
        $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:G{$rowNumber}")
                ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        $sumrangeB = 'B' . $initialRowNumber . ':B' . $rowNumber-1;
        $sumrangeC = 'C' . $initialRowNumber . ':C' . $rowNumber-1;
        $sumrangeD = 'D' . $initialRowNumber . ':D' . $rowNumber-1;
        $sumrangeE = 'E' . $initialRowNumber . ':E' . $rowNumber-1;
        $sumrangeF = 'F' . $initialRowNumber . ':F' . $rowNumber-1;
        $sumrangeG = 'G' . $initialRowNumber . ':G' . $rowNumber-1;
        $spreadsheet->getActiveSheet()->setCellValue('B' . $rowNumber, '=SUM(' . $sumrangeB . ')');
        $spreadsheet->getActiveSheet()->setCellValue('C' . $rowNumber, '=SUM(' . $sumrangeC . ')');
        $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . $sumrangeD . ')');
        $spreadsheet->getActiveSheet()->setCellValue('E' . $rowNumber, '=SUM(' . $sumrangeE . ')');
        $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . $sumrangeF . ')');
        $spreadsheet->getActiveSheet()->setCellValue('G' . $rowNumber, '=SUM(' . $sumrangeG . ')');
        
        $rowNumber += 3;
        
        //# PROGRAMAS PAGADOS
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
        $writer->save("Reportes/corte_de_caja/corte-de-caja.xlsx");
        return ['data'=>true];
	}

	private function getActividadesFechaPagos($fechaInicio,$fechaFinal,$usuarios,$showCupones)
	{
        $tiposPago = [1,2,3,5,8];
        ($showCupones ?  array_push($tiposPago,4) : '');

        $actividades = Actividad::with(['pagos' => function ($query) use ($fechaInicio,$fechaFinal,$tiposPago) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$tiposPago)->count();
        }])
        ->with(['reservaciones' => function ($query) use ($usuarios) {
            $query
                ->whereIn('usuario_id',$usuarios);
        }])
        ->orderBy('actividades.reporte_orden','asc')->get();
         
        return $actividades;
    }

	private function getActividadesFechaReservaciones($fechaInicio,$fechaFinal,$usuarios)
	{

        $actividades = Actividad::with(['reservaciones' => function ($query) use ($fechaInicio,$fechaFinal,$usuarios) {
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->whereIn("usuario_id", $usuarios)
                ->where('estatus',1);
        }]);

        return $actividades;
    }

	private function getPagosTotalesByType($reservacion,$actividad,$pagoTipoNombre,$fechaInicio,$fechaFinal,$pendiente = 0)
	{
        $pagosId = $reservacion->pagos->pluck('id');
        $pagos   = Pago::whereIn("id",$pagosId)->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])->get();

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

	private function getPagoActividadIndividual($reservacion,$actividad,$totalPagado,$pendiente)
	{
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

	private function getReservacionesTotalesGeneral($actividad,$reservaciones)
	{
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
}