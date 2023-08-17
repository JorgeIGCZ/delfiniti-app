<?php

namespace App\Services;

use App\Http\Controllers\FotoVideoVentaController;
use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\TiendaVentaController;
use App\Models\Actividad;
use App\Models\FotoVideoVenta;
use App\Models\FotoVideoVentaPago;
use App\Models\Pago;
use App\Models\Reservacion;
use App\Models\TiendaVenta;
use App\Models\TiendaVentaPago;
use App\Models\TipoCambio;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;

class ReporteCorteCajaService
{
    protected $tiposPago = [1,2,3,5,8];

	public function getReporte($request)
	{
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reportTemplates/template.xlsx');
        $fechaInicio = Carbon::parse($request->fechaInicio)->startOfDay();
        $fechaFinal  = Carbon::parse($request->fechaFinal)->endOfDay();

        $moduloRequest = $request->data['modulo'];
        $moduloRequest = json_decode($moduloRequest);
        $moduloRequest = $moduloRequest->filtro_modulo_corte_caja;

        $usuarios    = [$request->data['cajero']];
        $showCupones = ($request->data['cupones'] === "1");

        if(in_array(0,$usuarios)){
            // $usuarios = User::role(['Administrador','Recepcion'])->get()->pluck('id');
            $usuarios = User::get()->pluck('id');
        }
        
        // $fechaInicio = '2022-08-15 00:00:00';
        // $fechaFinal = '2022-08-15 23:59:00';
        $formatoFechaInicio = date_format(date_create($fechaInicio),"d-m-Y"); 
        $formatoFechaFinal = date_format(date_create($fechaFinal),"d-m-Y"); 
        $tipoCambio = TipoCambio::where("seccion_uso","reportes")->get()[0]["precio_compra"];

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
        //Reservaciones
        if(in_array("Reservaciones", $moduloRequest)){
            $actividadesPagos = $this->getActividadesFechaPagos($fechaInicio,$fechaFinal,$usuarios,$showCupones);
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
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado para');

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
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);
                    
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
        }
        //Termina Reservaciones

        //Tienda
        if(in_array("Tienda", $moduloRequest)){
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
                
            //Encabezado
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "TIENDA");
            $rowNumber += 1;

            //Titulos
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

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Folio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares'); 
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Depósito / transferencia');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado para');
            $rowNumber += 1;

            //Data
            $initialRowNumber = $rowNumber;
            $tiendaVentas = $this->getTiendaVentas($fechaInicio, $fechaFinal, $usuarios);
            foreach($tiendaVentas as $tiendaVenta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $tiendaVenta->folio);

                $pagosEfectivoResult = $this->getTiendaVentaPagosTotalesByType($tiendaVenta, 'efectivo', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getTiendaVentaPagosTotalesByType($tiendaVenta, 'efectivoUsd', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getTiendaVentaPagosTotalesByType($tiendaVenta, 'tarjeta', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                    
                $pagosDepositoResult = $this->getTiendaVentaPagosTotalesByType($tiendaVenta, 'deposito', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$tiendaVenta->nombre_cliente);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);
                    
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
            // $rowNumber += 1;
            $rowNumber += 4;
        }
        //Termina Tienda

        //Fotos
        if(in_array("Fotos", $moduloRequest)){
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
                
            //Encabezado
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "FOTOS");
            $rowNumber += 1;

            //Titulos
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

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Folio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares'); 
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Depósito / transferencia');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado para');
            $rowNumber += 1;

            //Data
            $initialRowNumber = $rowNumber;
            $fotoVentas = $this->getFotoVideoVentas($fechaInicio, $fechaFinal, $usuarios, "foto");
            foreach($fotoVentas as $fotoVenta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $fotoVenta->folio);

                $pagosEfectivoResult = $this->getFotoVideoVentaPagosTotalesByType($fotoVenta, 'efectivo', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getFotoVideoVentaPagosTotalesByType($fotoVenta, 'efectivoUsd', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getFotoVideoVentaPagosTotalesByType($fotoVenta, 'tarjeta', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                    
                $pagosDepositoResult = $this->getFotoVideoVentaPagosTotalesByType($fotoVenta, 'deposito', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$fotoVenta->nombre_cliente);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);
                    
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
        //Termina Fotos

        //Videos
        if(in_array("Videos", $moduloRequest)){
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
                
            //Encabezado
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "VIDEOS");
            $rowNumber += 1;

            //Titulos
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

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Folio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", 'Pesos');
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", 'Dolares'); 
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", 'Tarjeta');
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", 'Depósito / transferencia');
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", 'Reservado para');
            $rowNumber += 1;

            //Data
            $initialRowNumber = $rowNumber;
            $videoVentas = $this->getFotoVideoVentas($fechaInicio, $fechaFinal, $usuarios, "video");
            foreach($videoVentas as $videoVenta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $videoVenta->folio);

                $pagosEfectivoResult = $this->getFotoVideoVentaPagosTotalesByType($videoVenta, 'efectivo', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getFotoVideoVentaPagosTotalesByType($videoVenta, 'efectivoUsd', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getFotoVideoVentaPagosTotalesByType($videoVenta, 'tarjeta', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                    
                $pagosDepositoResult = $this->getFotoVideoVentaPagosTotalesByType($videoVenta, 'deposito', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$videoVenta->nombre_cliente);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);
                    
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
        //Termina Videos

        //Resumen
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
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);

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
        
        //Acumulado Reservaciones
        if(in_array("Reservaciones", $moduloRequest)){
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
        }
        
        //Acumulado Tienda
        if(in_array("Tienda", $moduloRequest)){
            $acumuladoTiendaVentas = $this->getAcumuladoTiendaVentas($tiendaVentas, $fechaInicio, $fechaFinal);
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "TIENDA");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivo'));
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'deposito'));
            //Calculo totales
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');
            $rowNumber += 1;
        }

        //Acumulado Fotos
        if(in_array("Fotos", $moduloRequest)){
            $acumuladoTiendaVentas = $this->getAcumuladoFotoVideoVentas($fotoVentas, $fechaInicio, $fechaFinal);
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "FOTOS");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivo'));
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'deposito'));
            //Calculo totales
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');
            $rowNumber += 1;
        }

        //Acumulado Videos
        if(in_array("Videos", $moduloRequest)){
            $acumuladoTiendaVentas = $this->getAcumuladoFotoVideoVentas($videoVentas, $fechaInicio, $fechaFinal);
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "VIDEOS");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivo'));
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'deposito'));
            //Calculo totales
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');
            $rowNumber += 1;
        }   

        $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:G{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER);

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
        //Termina Resumen
        
        //# PROGRAMAS PAGADOS
        if(in_array("Reservaciones", $moduloRequest)){
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

            //Excluye visitas
            $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio,$fechaFinal,$usuarios)->whereRaw('exclusion_especial = 0')->get();

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
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save("Reportes/corte_de_caja/corte-de-caja.xlsx");
        return ['data'=>true];
	}

	private function getActividadesFechaPagos($fechaInicio,$fechaFinal,$usuarios,$showCupones)
	{
        ($showCupones ?  array_push($this->tiposPago,4) : '');

        $actividades = Actividad::with(['pagos' => function ($query) use ($fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)->count();
        }])
        ->with(['reservaciones' => function ($query) use ($usuarios) {
            $query
                ->whereIn('usuario_id',$usuarios);
        }])
        ->orderBy('actividades.reporte_orden','asc')->get();
         
        return $actividades;
    }

    private function getTiendaVentas($fechaInicio, $fechaFinal, $usuarios)
	{
        $ventas = TiendaVenta::whereIn('usuario_id',$usuarios)->whereHas('pagos', function (Builder $query) use ($fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago);
        })->get();

        return $ventas;
    }

    private function getFotoVideoVentas($fechaInicio, $fechaFinal, $usuarios, $tipo)
	{
        $ventas = FotoVideoVenta::whereIn('usuario_id',$usuarios)->whereHas('pagos', function (Builder $query) use ($fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago);
        })->whereHas('productos', function (Builder $query) use ($tipo) {
            $query->where('tipo',$tipo);
        })->get();

        return $ventas;
    }

    private function getAcumuladoTiendaVentas($tiendaVentas, $fechaInicio, $fechaFinal)
    {
        $pagosArray = [];
        $acumuladoVentas = [];
        foreach($tiendaVentas as $tiendaVenta){
            $pagosId = $tiendaVenta->pagos->pluck('id');
            $pagos   = TiendaVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio, $fechaFinal])->get();
    
            $pagosArray[] = $pagos;
        }

        foreach($pagosArray as $pagos){
            foreach($pagos as $pago){
                $acumuladoVentas[$pago->tipo_pago_id] = isset($acumuladoVentas[$pago->tipo_pago_id]) 
                    ? ($acumuladoVentas[$pago->tipo_pago_id] + $pago->cantidad) 
                    : $pago->cantidad;
            }
        }

        return $acumuladoVentas;
    }

    private function getAcumuladoFotoVideoVentas($ventas, $fechaInicio, $fechaFinal)
    {
        $pagosArray = [];
        $acumuladoVentas = [];
        foreach($ventas as $venta){
            $pagosId = $venta->pagos->pluck('id');
            $pagos   = FotoVideoVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio, $fechaFinal])->get();
    
            $pagosArray[] = $pagos;
        }

        foreach($pagosArray as $pagos){
            foreach($pagos as $pago){
                $acumuladoVentas[$pago->tipo_pago_id] = isset($acumuladoVentas[$pago->tipo_pago_id]) 
                    ? ($acumuladoVentas[$pago->tipo_pago_id] + $pago->cantidad) 
                    : $pago->cantidad;
            }
        }

        return $acumuladoVentas;
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

    private function getPagosAcumuladosTotalesByType($venta, $pagoTipoNombre){
        $reservaciones = new ReservacionController();
        $pagoTipoId    = $reservaciones->getTipoPagoId($pagoTipoNombre);

        return isset($venta[$pagoTipoId]) ? $venta[$pagoTipoId] : 0;
    }

    private function getTiendaVentaPagosTotalesByType($venta,$pagoTipoNombre,$fechaInicio,$fechaFinal,$pendiente = 0)
	{
        $pagosId = $venta->pagos->pluck('id');
        $pagos   = TiendaVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio,$fechaFinal])->get();

        $ventas = new TiendaVentaController();
        $pagoTipoId    = $ventas->getTipoPagoId($pagoTipoNombre);
        //total pagado en tipo de pago actual
        $totalPagado   = 0;
        foreach($pagos as $pago){
            if($pago->tipo_pago_id == $pagoTipoId){
                $totalPagado += $pago->cantidad;
            }
        }

        return ['pago' => $totalPagado];
    }

    private function getFotoVideoVentaPagosTotalesByType($venta, $pagoTipoNombre, $fechaInicio, $fechaFinal)
	{
        $pagosId = $venta->pagos->pluck('id');
        $pagos   = FotoVideoVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio,$fechaFinal])->get();

        $ventas = new FotoVideoVentaController();
        $pagoTipoId    = $ventas->getTipoPagoId($pagoTipoNombre);
        //total pagado en tipo de pago actual
        $totalPagado   = 0;
        foreach($pagos as $pago){
            if($pago->tipo_pago_id == $pagoTipoId){
                $totalPagado += $pago->cantidad;
            }
        }

        return ['pago' => $totalPagado];
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
