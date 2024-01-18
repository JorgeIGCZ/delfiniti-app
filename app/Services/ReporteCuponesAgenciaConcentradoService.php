<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Comisionista;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteCuponesAgenciaConcentradoService
{
	public function getReporte($request)
	{
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = Carbon::createFromFormat('d/m/Y', $request->fechaInicio)->startOfDay();
        $fechaFinal  = Carbon::createFromFormat('d/m/Y', $request->fechaFinal)->endOfDay();

        $usuarios = User::role('Recepcion')->get()->pluck('id');

        //if($creadaPor !== 0){
        //    $usuarios = [$creadaPor];
        //}

        // $fechaInicio = Carbon::parse('2023-12-01')->startOfDay();
        // $fechaFinal = Carbon::parse('2023-12-15')->endOfDay();
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 
        
        $actividades = Actividad::all();
        // $comisionistasId = [25, 26];
        $comisionistasId = $request->data['agencias'];
        $comisionistasId = json_decode($comisionistasId);
        $comisionistasId = $comisionistasId->filtro_agencia_cupon;

        $spreadsheet->getActiveSheet()->setCellValue("A2", "RESERVACIONES REALIZADAS POR AGENCIAS");
        $spreadsheet->getActiveSheet()->setCellValue("A3", "Del {$formatoFechaInicio} al {$formatoFechaFinal}");	
        
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        
        $rowNumber = 5;
        
        foreach($comisionistasId as $comisionistaId){
            $column = 5;
            
            $nombreComisionista = Comisionista::find($comisionistaId)->nombre;
            
            $reservacionesCuponByComisionista = $this->getFechaReservacionesCuponByComisionista($fechaInicio, $fechaFinal, $comisionistaId);

            if(empty($reservacionesCuponByComisionista)){
                continue;
            }
                    
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}")
            ->getFont()->setBold(true)->setSize(12);
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $nombreComisionista);
            
            $rowNumber += 2;

            //Titulos
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:Z{$rowNumber}")
            ->getFont()->setBold(true)->setSize(12);
        
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:Z{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');
    
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:Z{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'RESERVACIÓN');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'CUPÓN');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'FECHA');
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'IMPORTE');

            //Titulos dinamicos
            foreach($actividades as $actividad){
                $spreadsheet->getActiveSheet()->getColumnDimensionByColumn($column)->setAutoSize(true);
                $spreadsheet->getActiveSheet()->setCellValue([$column, $rowNumber], $actividad->nombre);
                $column += 1;       
            }
    
            $rowNumber += 1;
    
            $initialRowNumber = $rowNumber;

            $reservacionActividadesTotal = [];
            foreach($reservacionesCuponByComisionista as $reservacion){
                $spreadsheet->getActiveSheet()->getStyle("D{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
    
                // $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)->setRowHeight(40);
    
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion['reservacionFolio']);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacion['cupon']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacion['fecha']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $reservacion['importe']);

                //Valores dinamicos
                $column = 5;
                foreach($actividades as $keyActividad => $actividad){
                    foreach($reservacion['actividades'] as $key =>$reservacionActividad){
                        if($actividad->id == $key){
                            $spreadsheet->getActiveSheet()->setCellValue([$column, $rowNumber], $reservacionActividad);
                            $reservacionActividadesTotal[$keyActividad] = isset($reservacionActividadesTotal[$keyActividad]) ? $reservacionActividadesTotal[$keyActividad] + $reservacionActividad : $reservacionActividad;
                            $column += 1;
                            continue 2;
                        }
                    }       

                    $spreadsheet->getActiveSheet()->setCellValue([$column, $rowNumber], 0);
                    $reservacionActividadesTotal[$keyActividad] = $reservacionActividadesTotal[$keyActividad] ?? 0;
                    $column += 1;
                }

                $rowNumber += 1;
            }
    
            //Calculo totales

            $spreadsheet->getActiveSheet()->getStyle("D{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            $spreadsheet->getActiveSheet()->setCellValue('D' . $rowNumber, '=SUM(' . 'D' . $initialRowNumber . ':D' . $rowNumber-1 . ')');

            //Totales dinamicos
            $columnStart = 5;

            if(empty($reservacionActividadesTotal)){
                continue;
            }
            
            for ($i = $columnStart; $i < $column; $i++) {
                $spreadsheet->getActiveSheet()->setCellValue([$i, $rowNumber], $reservacionActividadesTotal[$i-$columnStart]);
            }

            $rowNumber += 3;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/cupones/cupones-agencia-concentrado.xlsx");
        return ['data'=>true];
	}

	private function getFechaReservacionesCuponByComisionista($fechaInicio, $fechaFinal, $comisionistaId)
	{
        $pagosReservacionCupon = Pago::whereHas('reservacion', function ($query) use ($fechaInicio, $fechaFinal, $comisionistaId){
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->where('comisionista_id', $comisionistaId)
                ->where('estatus',1); 
        })->whereHas('tipoPago', function ($query){
            $query
                ->where('nombre','cupon'); 
        })->get();

        $reservacionesCuponArray = [];

         foreach($pagosReservacionCupon as $pagoReservacionCupon){
            $numeroActividades = $this->getNumeroActividad($pagoReservacionCupon);
            $reservacionesCuponArray[] = [
                'reservacionFolio' => $pagoReservacionCupon->reservacion->folio,
                'cupon' => $pagoReservacionCupon->reservacion->num_cupon,
                'fecha' => @Carbon::parse($pagoReservacionCupon->reservacion->fecha)->format('d/m/Y'),
                'importe' => $pagoReservacionCupon->cantidad,
                'actividades' => $numeroActividades
            ];
         }

        return $reservacionesCuponArray;
    }

    private function getNumeroActividad($pagoReservacionCupon)
    {
        $actividades = Actividad::all();

        $numeroActividadesArray = [];

        foreach($actividades as $actividad){
            foreach($pagoReservacionCupon->reservacion->reservacionDetalle as $reservacionDetalle){
                if($actividad->id == $reservacionDetalle->actividad_id){
                    $numeroActividadesArray[$actividad->id] = $reservacionDetalle->numero_personas;
                }
            }
        }
        
        return $numeroActividadesArray;
    }
}