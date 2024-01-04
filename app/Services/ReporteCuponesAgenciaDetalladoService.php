<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Comisionista;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteCuponesAgenciaDetalladoService
{
	public function getReporte($request)
	{
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
        $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();

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
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
            ->getFont()->setBold(true)->setSize(12);
        
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F2F2F2');
    
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'RESERVACIÓN');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'CLIENTE');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'FECHA');
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'CUPÓN');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'DETALLE');
            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", 'IMPORTE');
    
            $rowNumber += 1;
    
            $initialRowNumber = $rowNumber;

            foreach($reservacionesCuponByComisionista as $reservacion){
                $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
    
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $reservacion['reservacionFolio']);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacion['cliente']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $reservacion['fecha']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $reservacion['cupon']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $reservacion['detalle']);
                $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $reservacion['importe']);

                $rowNumber += 1;
            }
    
            //Calculo totales

            $spreadsheet->getActiveSheet()->getStyle("F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            $spreadsheet->getActiveSheet()->setCellValue('F' . $rowNumber, '=SUM(' . 'F' . $initialRowNumber . ':F' . $rowNumber-1 . ')');

            $rowNumber += 3;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/cupones/cupones-agencia-detallado.xlsx");
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
        $actividades = [];
        $actividadDetalles = [];

        foreach($pagosReservacionCupon as $pagoReservacionCupon){
            $actividadDetalles = $this->getActividadDetalles($pagoReservacionCupon->reservacion->reservacionDetalle);

            $reservacionesCuponArray[] = [
                'reservacionFolio' => $pagoReservacionCupon->reservacion->folio,
                'cliente' => $pagoReservacionCupon->reservacion->nombre_cliente,
                'fecha' => @Carbon::parse($pagoReservacionCupon->reservacion->fecha)->format('d/m/Y'),
                'cupon' => $pagoReservacionCupon->reservacion->num_cupon,
                'detalle' => implode(',', $actividadDetalles),
                'importe' => $pagoReservacionCupon->cantidad
            ];
        }

        return $reservacionesCuponArray;
    }

    private function getActividadDetalles($reservacionesDetalle)
    {
        $actividades = Actividad::all();

        $actividadesArray = [];

        foreach($actividades as $actividad){
            foreach($reservacionesDetalle as $reservacionDetalle){
                if($actividad->id == $reservacionDetalle->actividad_id){
                    $actividadesArray[] = sprintf('%s %s: %u',$reservacionDetalle->horario->horario_inicial, $actividad->nombre, $reservacionDetalle->numero_personas);
                }
            }
        }
        

        return $actividadesArray;
    }
}