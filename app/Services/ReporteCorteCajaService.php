<?php

namespace App\Services;

use App\Services\ReservacionService;
use App\Services\ActividadService;
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
use App\Models\TipoPago;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReporteCorteCajaService
{
    protected $reservacionService;
    protected $actividadService;

    protected $tiposPago = [1,2,3,5,8];

    public function __construct(
        ReservacionService $reservacionService,
        ActividadService $actividadService,
    ) {
        $this->reservacionService = $reservacionService;
        $this->actividadService = $actividadService;
    }

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
        }else{
            $usuarios = User::whereIn('id',$usuarios)->pluck('id');
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

            $reservacionesArray = [];
            $informacionActividadesArray = [];
            $actividadesIdArray = [];

            $reservaciones = $this->getReservacionesFecha($fechaInicio,$fechaFinal,$usuarios,$showCupones);
            foreach($reservaciones as $reservacion){
                $reservacionesArray[] = $this->setReservacion($reservacion, $usuarios);
            }

            foreach($reservacionesArray as $reservacion){
                foreach($reservacion->getActividades() as $actividad){
                    $actividadesIdArray[] = $actividad->getId();
                    $informacionActividadesArray[] = [
                        "id" => $actividad->getId(),
                        "folio" => $reservacion->getFolio(),
                        "efectivo" => $this->getPagoActividadPorTipo($reservacion, $actividad, 'PagoEfectivo'),
                        "efectivoUsd" => $this->getPagoActividadPorTipo($reservacion, $actividad, 'PagoEfectivoUsd'),
                        "tarjeta" => $this->getPagoActividadPorTipo($reservacion, $actividad, 'PagoTarjeta'),
                        "deposito" => $this->getPagoActividadPorTipo($reservacion, $actividad, 'PagoDeposito'),
                        "cupon" => $this->getPagoActividadPorTipo($reservacion, $actividad, 'PagoCupon'),
                        "nombreCliente" => $reservacion->getNombreCliente(),
                    ];
                }
            }

            $actividades = $this->getActividades();
            foreach($actividades as $actividad){
                if(!in_array($actividad->id, $actividadesIdArray)){
                    continue;
                }

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

                $initialRowNumber = $rowNumber;

                foreach($informacionActividadesArray as $informacionActividad){
                    if($informacionActividad['id'] == $actividad->id){
                        //Data
                        $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $informacionActividad['folio']);
                        $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $informacionActividad['efectivo']);
                        $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $informacionActividad['efectivoUsd']);
                        $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $informacionActividad['tarjeta']);
                        $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $informacionActividad['deposito']);

                        if($showCupones){
                            $spreadsheet->getActiveSheet()->setCellValue("F{$rowNumber}", $informacionActividad['cupon']);
                        }

                        $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$informacionActividad['nombreCliente']);

                        $rowNumber += 1;
                    }
                }
                $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                            
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

                $pagosEfectivoResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'efectivo', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

                $pagosEfectivoUsdResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'efectivoUsd', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

                $pagosTarjetaResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'tarjeta', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                    
                $pagosDepositoResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'deposito', $fechaInicio, $fechaFinal);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$tiendaVenta->nombre_cliente);

                $rowNumber += 1;
            }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                    
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
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                    
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
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                    
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
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

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
        // $actividadesPagos = $this->getActividadesFechaPagos($fechaInicio,$fechaFinal,$usuarios,$showCupones);
        if(in_array("Reservaciones", $moduloRequest)){
            $actividades = Actividad::where('estatus',1)->get();
            foreach($actividades as $actividad){

                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $actividad->nombre);
                $totalEfectivo = 0;
                $totalEfectivoUSD = 0;
                $totalTarjeta = 0;
                $totalDeposito = 0;
                $totalCupon = 0;

                if(in_array($actividad->id, $actividadesIdArray)){
                    foreach($informacionActividadesArray as $informacionActividad){
                        if($informacionActividad['id'] == $actividad->id){
                            //Data
                            $totalEfectivo += $informacionActividad['efectivo'];
                            $totalEfectivoUSD += $informacionActividad['efectivoUsd'];
                            $totalTarjeta += $informacionActividad['tarjeta'];
                            $totalDeposito += $informacionActividad['deposito'];
    
                            if($showCupones){
                                $totalCupon += $informacionActividad['cupon'];
                            }
                        }
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
            $acumuladoTiendaVentas = $this->getAcumuladoTiendaVentas($usuarios, $tiendaVentas, $fechaInicio, $fechaFinal);
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
            $acumuladoTiendaVentas = $this->getAcumuladoFotoVideoVentas($usuarios, $fotoVentas, $fechaInicio, $fechaFinal);
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
            $acumuladoTiendaVentas = $this->getAcumuladoFotoVideoVentas($usuarios, $videoVentas, $fechaInicio, $fechaFinal);
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
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

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
            $actividadesFechaReservaciones = $this->getActividadesFechaReservaciones($fechaInicio, $fechaFinal)->whereRaw('exclusion_especial = 0')->get();

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

    private function getReservacionesFecha($fechaInicio,$fechaFinal,$usuarios,$showCupones) {
        ($showCupones ?  array_push($this->tiposPago,4) : '');
        // DB::enableQueryLog();

        $reservaciones = Reservacion::whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('pagos.tipo_pago_id',$this->tiposPago)
                ->whereIn('pagos.usuario_id',$usuarios);
        })->get();
        // dd(DB::getQueryLog());

        return $reservaciones;
    }

    private function getReservacionPagos($reservacion, $usuarios){
        $reservacionTipoPagos = [];

        $tipoPagos = TipoPago::get();
        foreach($tipoPagos as $tipoPago){
            $reservacionTipoPagos[$tipoPago->nombre] = 0;
        }

        foreach($reservacion->pagos as $pago){
            if(!in_array($pago->usuario_id, $usuarios->toArray())){
                continue;
            }
            $reservacionTipoPagos[$pago->tipoPago->nombre] = $pago->cantidad;
        }
        return $reservacionTipoPagos;
    }

    private function setReservacion($reservacion, $usuarios){

        $actividades = $this->setActividad($reservacion);

        $reservacionService = app(ReservacionService::class);

        $reservacionTipoPagos = $this->getReservacionPagos($reservacion, $usuarios);
        $reservacionService->setFolio($reservacion->folio);
        $reservacionService->setActividades($actividades);
        $reservacionService->setNombreCliente($reservacion->nombre_cliente);
        $reservacionService->setPagoEfectivo($reservacionTipoPagos['efectivo']);
        $reservacionService->setPagoEfectivoUsd($reservacionTipoPagos['efectivoUsd']);
        $reservacionService->setPagoTarjeta($reservacionTipoPagos['tarjeta']);
        $reservacionService->setPagoDeposito($reservacionTipoPagos['deposito']);
        $reservacionService->setPagoCupon($reservacionTipoPagos['cupon']);

        return $reservacionService;
    }

    private function setActividad($reservacion){
        $actividadesArray = [];
        $reservacionDetalles = $reservacion->reservacionDetalle;
        foreach($reservacionDetalles as $reservacionDetalle){
            $actividad = $reservacionDetalle->actividad;
            $numeroPersonas = $reservacionDetalle->numero_personas;

            $actividadService = app(ActividadService::class);
            $actividadService->setId($actividad->id);
            $actividadService->setClave($actividad->clave);
            $actividadService->setPrecio($actividad->precio);
            $actividadService->setNumeroPersonas($numeroPersonas);
            $actividadService->setNombre($actividad->nombre);
            $actividadService->setCantidadPagada(0);
            $actividadesArray[] = $actividadService;
        }

        return $actividadesArray;
    }

    private function getPagoActividadPorTipo($reservacion, $actividad, $tipoPago){
        $nombreMetodoGet = \sprintf('get%s',$tipoPago);
        $nombreMetodoSet = \sprintf('set%s',$tipoPago);
        $precioActividad = ($actividad->getPrecio() * $actividad->getNumeroPersonas());
        $cantidadPagada = $actividad->getCantidadPagada();
        $pendientePagoActividad = ($precioActividad - $cantidadPagada);
        $pago = $reservacion->{$nombreMetodoGet}();

        if($pago <= 0 || $pendientePagoActividad == 0){
            return 0;
        }

        if($pago > $pendientePagoActividad){
            $resta = $pago - $pendientePagoActividad;
            $reservacion->{$nombreMetodoSet}($resta);
            $actividad->setCantidadPagada($precioActividad);

            return $pendientePagoActividad;
        }
        $reservacion->{$nombreMetodoSet}(0);
        $actividad->setCantidadPagada($cantidadPagada + $pago);

        return $pago;
    }
        
	private function getActividades()
	{
        $actividades = Actividad::orderBy('actividades.reporte_orden','asc')->get();
        return $actividades;
    }

    private function getTiendaVentas($fechaInicio, $fechaFinal, $usuarios)
	{
        $ventas = TiendaVenta::whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)
                ->whereIn('usuario_id',$usuarios);
        })->get();

        return $ventas;
    }

    private function getFotoVideoVentas($fechaInicio, $fechaFinal, $usuarios, $tipo)
	{
        $ventas = FotoVideoVenta::whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id', $this->tiposPago)
                ->whereIn('usuario_id', $usuarios);
        })->whereHas('productos', function (Builder $query) use ($tipo) {
            $query->where('tipo', $tipo);
        })->get();

        return $ventas;
    }

    private function getAcumuladoTiendaVentas($usuarios, $tiendaVentas, $fechaInicio, $fechaFinal)
    {
        $pagosArray = [];
        $acumuladoVentas = [];
        foreach($tiendaVentas as $tiendaVenta){
            $pagosId = $tiendaVenta->pagos->pluck('id');
            $pagos   = TiendaVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio, $fechaFinal])->whereIn('usuario_id', $usuarios)->get();
    
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

    private function getAcumuladoFotoVideoVentas($usuarios, $ventas, $fechaInicio, $fechaFinal)
    {
        $pagosArray = [];
        $acumuladoVentas = [];
        foreach($ventas as $venta){
            $pagosId = $venta->pagos->pluck('id');
            $pagos   = FotoVideoVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio, $fechaFinal])->whereIn('usuario_id', $usuarios)->get();
    
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

	private function getActividadesFechaReservaciones($fechaInicio, $fechaFinal)
	{

        $actividades = Actividad::with(['reservaciones' => function ($query) use ($fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("fecha", [$fechaInicio,$fechaFinal])
                ->where('estatus',1);
        }]);

        return $actividades;
    }

    private function getPagosAcumuladosTotalesByType($venta, $pagoTipoNombre)
    {
        $reservaciones = new ReservacionController();
        $pagoTipoId    = $reservaciones->getTipoPagoId($pagoTipoNombre);

        return isset($venta[$pagoTipoId]) ? $venta[$pagoTipoId] : 0;
    }

    private function getTiendaVentaPagosTotalesByType($usuarios, $venta, $pagoTipoNombre, $fechaInicio, $fechaFinal, $pendiente = 0)
	{
        $pagosId = $venta->pagos->pluck('id');
        $pagos   = TiendaVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereIn('usuario_id', $usuarios)->get();

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
