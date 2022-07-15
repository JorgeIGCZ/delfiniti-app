<?php

namespace App\Http\Controllers;

use App\Models\Reservacion;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



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
    public function corteCaja(Request $request){
        //$spreadsheet = new Spreadsheet();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template-corte-caja.xlsx');

        $arrayData = [
            [NULL, 2010, 2011, 2012,0,123,1],
            ['Q1',   12,   15,   21,0,123,1],
            ['Q2',   56,   73,   86,0,123,1],
            ['Q3',   52,   61,   69,0,123,1],
            ['Q4',   30,   32,    0,0,123,1],
        ];
        $first_i  = 7;
        $last_i   = 11;
        $sumrange = 'B' . $first_i . ':B' . $last_i;
        $spreadsheet->getActiveSheet()->insertNewRowBefore(7, 4);
        $spreadsheet->getActiveSheet()->setCellValue('B' . $last_i+1, '=SUM(' . $sumrange . ')');
        
        $spreadsheet->getActiveSheet()->getStyle('A7:G' . ($last_i))
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); //Set horizontal center

        $spreadsheet->getActiveSheet()->getStyle('A7:G' . ($last_i))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('fff0');
            
        $spreadsheet->getActiveSheet()
            ->fromArray(
                $arrayData,  // The data to set
                NULL,        // Array values with this value will not be set
                'A7'         // Top left coordinate of the worksheet range where
                             //    we want to set these values (default is A1)
            );

        /*
            $spreadsheet->getActiveSheet()->mergeCells('A1:O1');
            $spreadsheet->getActiveSheet()->getStyle('A1:O1')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('00FF7F');
            $spreadsheet->getActiveSheet()->getStyle('A1:O1')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true); 

            $spreadsheet->getActiveSheet()->mergeCells('A2:O2');
            $spreadsheet->getActiveSheet()->getStyle('A2:O2')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('00FF7F');
            $spreadsheet->getActiveSheet()->getStyle('A2:O2')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true); 

            $spreadsheet->getActiveSheet()->mergeCells('A2:O2');
            $spreadsheet->getActiveSheet()->getStyle('A2:O2')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('00FF7F');
            $spreadsheet->getActiveSheet()->getStyle('A2:O2')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //Set vertical center
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER) //Set horizontal center
            ->setWrapText(true); 
            
            $sheet->setCellValue('A1', 'Delfiniti de México S.A. de C.V.');
            $sheet->setCellValue('A2', 'Corte de caja');
            $sheet->setCellValue('A3', 'Del viernes 24/junio/2022 al viernes 24/junio/2022');
        */
        $writer = new Xlsx($spreadsheet);
        $writer->save('test.xlsx');
        return ['data'=>true];
    }

}
