<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\ActividadHorario;
use App\Models\Pago;
use App\Models\Reservacion;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReporteReservacionesService
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

        // $fechaInicio = '2022-08-15 00:00:00';
        // $fechaFinal = '2022-08-15 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 

        $actividadesHorarios = $this->getActividadesHorarios($fechaInicio,$fechaFinal);
        $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio,$fechaFinal,$usuarios)->get();
        

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
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'DESCUENTO');
                $spreadsheet->getActiveSheet()->setCellValue("H{$rowNumber}", 'T. PAGO');
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

                    $porcentajeDescuento = '0%';
                    $pagosArray          = $reservacion->pagos->pluck('id');
                    $tipoPago            = 'descuentoPersonalizado';

                    $descuento = Pago::whereIn('id',$pagosArray)->whereHas('TipoPago', function (Builder $query) use ($tipoPago) {
                        $query
                            ->where('nombre',$tipoPago);
                    })->get();

                    if(isset($descuento) && count($descuento)>0){
                        $porcentajeDescuento = $descuento[0]->valor;
                    }

                    $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $reservacion->nombre_cliente);
                    $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", @$reservacion->origen);
                    $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getNumeroPersonas($actividadHorario,$reservacion->reservacionDetalle));
                    $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", @$reservacion->comisionista->nombre);
                    $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", $porcentajeDescuento);
                    $spreadsheet->getActiveSheet()->setCellValue("H{$rowNumber}", ($reservacion->tipoPago !== null ? @$reservacion->tipoPago->pluck('nombre')[0] : ''));
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

	private function getActividadesHorarios($fechaInicio,$fechaFinal)
	{
        $actividadesHorarios = ActividadHorario::with(['reservacion' => function ($query) use ($fechaInicio,$fechaFinal) {
                $query
                    ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                    ->where('estatus',1);
        }])->orderBy('horario_inicial', 'asc')->orderBy('id', 'asc')->get()->groupBy('horario_inicial');

        return $actividadesHorarios;
    }

	private function getActividadesFechaReservaciones($fechaInicio,$fechaFinal,$usuarios)
	{
        $actividades = Actividad::with(['reservaciones' => function ($query) use ($fechaInicio,$fechaFinal,$usuarios) {
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->where('estatus',1);
        }]);
        
        return $actividades;
    }

    private function getNumeroPersonas($actividadHorario,$reservacionDetalle)
	{
        $numeroPersonas = 0;
        foreach($reservacionDetalle as $reservacionDetalle){
            if($reservacionDetalle->actividad_id == $actividadHorario->actividad->id){
                $numeroPersonas = $reservacionDetalle->numero_personas;
            }
        }
        
        return $numeroPersonas;
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
                ->whereRaw("tipo = 'porcentaje' AND descuento = '100' ");
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