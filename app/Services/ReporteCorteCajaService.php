<?php

namespace App\Services;

use App\Services\VentaService;
use App\Services\ProductoService;
use App\Models\Actividad;
use App\Models\FotoVideoVenta;
use App\Models\Reservacion;
use App\Models\TiendaVenta;
use App\Models\TiendaVentaPago;
use App\Models\TipoCambio;
use App\Models\TipoPago;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;

class ReporteCorteCajaService
{
    protected $ventaService;
    protected $productoService;

    protected $tiposPago = [1,2,3,5,8];
    protected $tipoCambio = 0;
    protected $ventasVideoArray = [];
    protected $ventasFotoArray = [];
    protected $ventasTiendaArray = [];

    public function __construct(
        VentaService $ventaService,
        ProductoService $productoService,
    ) {
        $this->ventaService = $ventaService;
        $this->productoService = $productoService;
        $this->tipoCambio = TipoCambio::where("seccion_uso","reportes")->first()["precio_compra"];
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
                //establecemos index para saber cual es la ultima actividad y agregar el restante de ingreso por PagoEfectivoUsd
                $index = 0;
                foreach($reservacion->getProductos() as $actividad){
                    $index++;
                    $isUltimaActividad = (count($reservacion->getProductos()) == $index);
                    
                    $actividadesIdArray[] = $actividad->getId();
                    $informacionActividadesArray[] = [
                        "id" => $actividad->getId(),
                        "folio" => $reservacion->getFolio(),
                        "efectivo" => $this->getPagoPorTipo($reservacion, $actividad, 'PagoEfectivo'),
                        "efectivoUsd" => $this->getPagoPorTipo($reservacion, $actividad, 'PagoEfectivoUsd', $isUltimaActividad),
                        "tarjeta" => $this->getPagoPorTipo($reservacion, $actividad, 'PagoTarjeta'),
                        "deposito" => $this->getPagoPorTipo($reservacion, $actividad, 'PagoDeposito'),
                        "cupon" => $this->getPagoPorTipo($reservacion, $actividad, 'PagoCupon'),
                        "cambio" => $this->getCambio($reservacion, 'Cambio', $isUltimaActividad),
                        "nombreCliente" => $reservacion->getNombreCliente(),
                    ];
                }
            }

            $actividades = $this->getReservacionActividades();
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

                $cambio = 0;

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

                        $cambio += $informacionActividad['cambio'];

                        $rowNumber += 1;
                    }
                }
                
                $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                        
                $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);

                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Cambio');
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $cambio);
                $rowNumber += 1;

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

            $ventasArray = [];
            $this->ventasTiendaArray = [];

            $tiendaVentas = $this->getTiendaVentasFecha($fechaInicio, $fechaFinal, $usuarios);
            foreach($tiendaVentas as $venta){
                $ventasArray[] = $this->setVenta($venta, $usuarios);
            }

            foreach($ventasArray as $venta){
                $efectivo = 0;
                $efectivoUsd = 0;
                $tarjeta = 0;
                $deposito = 0;
                $cupon = 0;
                $cambio = 0;
                $index = 0;

                foreach($venta->getProductos() as $producto){

                    $index++;

                    $efectivo += $this->getPagoPorTipo($venta, $producto, 'PagoEfectivo');
                    $efectivoUsd += $this->getPagoPorTipo($venta, $producto, 'PagoEfectivoUsd', true);
                    $tarjeta += $this->getPagoPorTipo($venta, $producto, 'PagoTarjeta');
                    $deposito += $this->getPagoPorTipo($venta, $producto, 'PagoDeposito');
                    $cupon += $this->getPagoPorTipo($venta,  $producto, 'PagoCupon');
                    $cambio += $this->getCambio($venta, 'Cambio', true);
                }

                $this->ventasTiendaArray[] = [
                    "folio" => $venta->getFolio(),
                    "efectivo" => $efectivo,
                    "efectivoUsd" => $efectivoUsd,
                    "tarjeta" => $tarjeta,
                    "deposito" => $deposito,
                    "cupon" => $cupon,
                    "cambio" => $cambio,
                    "nombreCliente" => $venta->getNombreCliente(),
                ];
            }

            $cambio = 0;
            foreach($this->ventasTiendaArray as $venta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $venta['folio']);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $venta['efectivo']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $venta['efectivoUsd']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $venta['tarjeta']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $venta['deposito']);
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$venta['nombreCliente']);

                $cambio += $venta['cambio'];

                $rowNumber += 1;
            }

            //Data
            // $initialRowNumber = $rowNumber;
            // $tiendaVentas = $this->getTiendaVentas($fechaInicio, $fechaFinal, $usuarios);
            // $cambio = 0;
            // foreach($tiendaVentas as $tiendaVenta){
            //     $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $tiendaVenta->folio);

            //     $pagosEfectivoResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'efectivo', $fechaInicio, $fechaFinal);
            //     $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $pagosEfectivoResult['pago']);

            //     $pagosEfectivoUsdResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'efectivoUsd', $fechaInicio, $fechaFinal);
            //     $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $pagosEfectivoUsdResult['pago']);

            //     $pagosTarjetaResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'tarjeta', $fechaInicio, $fechaFinal);
            //     $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $pagosTarjetaResult['pago']);
                    
            //     $pagosDepositoResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'deposito', $fechaInicio, $fechaFinal);
            //     $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $pagosDepositoResult['pago']);

            //     $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$tiendaVenta->nombre_cliente);

            //     $cambioResult = $this->getTiendaVentaPagosTotalesByType($usuarios, $tiendaVenta, 'cambio', $fechaInicio, $fechaFinal);
            //     $cambio += $cambioResult['pago'];

            //     $rowNumber += 1;
            // }

            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                        
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                    ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $cambio);
            $rowNumber += 1;


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

            $ventasArray = [];
            $this->ventasFotoArray = [];

            $tipo = "foto";
            $fotoVentas = $this->getFotoVideoVentasFecha($fechaInicio, $fechaFinal, $usuarios, $tipo);
            foreach($fotoVentas as $venta){
                $ventasArray[] = $this->setVenta($venta, $usuarios);
            }

            foreach($ventasArray as $venta){
                $efectivo = 0;
                $efectivoUsd = 0;
                $tarjeta = 0;
                $deposito = 0;
                $cupon = 0;
                $cambio = 0;

                $index = 0;
                foreach($venta->getProductos() as $producto){

                    $index++;
                    $isUltimoProducto = (count($venta->getProductos()) == $index);

                    $tempEfectivo = $this->getPagoPorTipo($venta, $producto, 'PagoEfectivo');
                    $tempEfectivoUsd = $this->getPagoPorTipo($venta, $producto, 'PagoEfectivoUsd', $isUltimoProducto);
                    $tempTarjeta = $this->getPagoPorTipo($venta, $producto, 'PagoTarjeta');
                    $tempDeposito = $this->getPagoPorTipo($venta, $producto, 'PagoDeposito');
                    $tempCupon = $this->getPagoPorTipo($venta,  $producto, 'PagoCupon');
                    $tempCambio = $this->getCambio($venta, 'Cambio', $isUltimoProducto);

                    if($producto->getTipo() == $tipo){
                        $efectivo += $tempEfectivo;
                        $efectivoUsd += $tempEfectivoUsd;
                        $tarjeta += $tempTarjeta;
                        $deposito += $tempDeposito;
                        $cupon += $tempCupon;
                        $cambio += $tempCambio;
                    }
                }

                $this->ventasFotoArray[] = [
                    "folio" => $venta->getFolio(),
                    "efectivo" => $efectivo,
                    "efectivoUsd" => $efectivoUsd,
                    "tarjeta" => $tarjeta,
                    "deposito" => $deposito,
                    "cupon" => $cupon,
                    "cambio" => $cambio,
                    "nombreCliente" => $venta->getNombreCliente(),
                ];
            }

            $cambio = 0;

            foreach($this->ventasFotoArray as $venta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $venta['folio']);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $venta['efectivo']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $venta['efectivoUsd']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $venta['tarjeta']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $venta['deposito']);
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$venta['nombreCliente']);

                $cambio += $venta['cambio'];

                $rowNumber += 1;
            }
                
            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                    
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $cambio);
            $rowNumber += 1;

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

            $ventasArray = [];
            $this->ventasVideoArray = [];

            $tipo = "video";
            $videoVentas = $this->getFotoVideoVentasFecha($fechaInicio, $fechaFinal, $usuarios, $tipo);
            foreach($videoVentas as $venta){
                $ventasArray[] = $this->setVenta($venta, $usuarios);
            }

            foreach($ventasArray as $venta){
                $efectivo = 0;
                $efectivoUsd = 0;
                $tarjeta = 0;
                $deposito = 0;
                $cupon = 0;
                $cambio = 0;
                
                $index = 0;
                foreach($venta->getProductos() as $producto){

                    $index++;
                    $isUltimoProducto = (count($venta->getProductos()) == $index);

                    $tempEfectivo = $this->getPagoPorTipo($venta, $producto, 'PagoEfectivo');
                    $tempEfectivoUsd = $this->getPagoPorTipo($venta, $producto, 'PagoEfectivoUsd', $isUltimoProducto);
                    $tempTarjeta = $this->getPagoPorTipo($venta, $producto, 'PagoTarjeta');
                    $tempDeposito = $this->getPagoPorTipo($venta, $producto, 'PagoDeposito');
                    $tempCupon = $this->getPagoPorTipo($venta,  $producto, 'PagoCupon');
                    $tempCambio = $this->getCambio($venta, 'Cambio', $isUltimoProducto);

                    if($producto->getTipo() == $tipo){
                        $efectivo += $tempEfectivo;
                        $efectivoUsd += $tempEfectivoUsd;
                        $tarjeta += $tempTarjeta;
                        $deposito += $tempDeposito;
                        $cupon += $tempCupon;
                        $cambio += $tempCambio;
                    }
                }

                $this->ventasVideoArray[] = [
                    "folio" => $venta->getFolio(),
                    "efectivo" => $efectivo,
                    "efectivoUsd" => $efectivoUsd,
                    "tarjeta" => $tarjeta,
                    "deposito" => $deposito,
                    "cupon" => $cupon,
                    "cambio" => $cambio,
                    "nombreCliente" => $venta->getNombreCliente(),
                ];
            }

            $cambio = 0;
            foreach($this->ventasVideoArray as $venta){
                $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", $venta['folio']);
                $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $venta['efectivo']);
                $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $venta['efectivoUsd']);
                $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $venta['tarjeta']);
                $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $venta['deposito']);
                $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", @$venta['nombreCliente']);

                $cambio += $venta['cambio'];

                $rowNumber += 1;
            }
                
            $spreadsheet->getActiveSheet()->getStyle("B{$initialRowNumber}:F{$rowNumber}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                    
            $spreadsheet->getActiveSheet()->getStyle("A{$rowNumber}:F{$rowNumber}")
                ->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", 'Cambio');
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $cambio);
            $rowNumber += 1;

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

                            $totalEfectivo += $informacionActividad['cambio'];
    
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

            $efectivoAcumulado = ($this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivo') + $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'cambio'));
            
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "TIENDA");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $efectivoAcumulado);
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosAcumuladosTotalesByType($acumuladoTiendaVentas, 'deposito'));
            //Calculo totales
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');
            $rowNumber += 1;
        }

        //Acumulado Fotos
        if(in_array("Fotos", $moduloRequest)){
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "FOTOS");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $this->getPagosTotalesByType($this->ventasFotoArray, 'efectivo'));
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosTotalesByType($this->ventasFotoArray, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosTotalesByType($this->ventasFotoArray, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosTotalesByType($this->ventasFotoArray, 'deposito'));
            //Calculo totales
            $spreadsheet->getActiveSheet()->setCellValue("G{$rowNumber}", '=SUM( B' . $rowNumber . ', (C' . $rowNumber . ' * H'. $tipoCambioRowNumber .' ) ,D' . $rowNumber . ' ,E' . $rowNumber .  ' ,F' . $rowNumber . ')');
            $rowNumber += 1;
        }

        //Acumulado Videos
        if(in_array("Videos", $moduloRequest)){
            $spreadsheet->getActiveSheet()->setCellValue("A{$rowNumber}", "VIDEOS");
            $spreadsheet->getActiveSheet()->setCellValue("B{$rowNumber}", $this->getPagosTotalesByType($this->ventasVideoArray, 'efectivo'));
            $spreadsheet->getActiveSheet()->setCellValue("C{$rowNumber}", $this->getPagosTotalesByType($this->ventasVideoArray, 'efectivoUsd'));
            $spreadsheet->getActiveSheet()->setCellValue("D{$rowNumber}", $this->getPagosTotalesByType($this->ventasVideoArray, 'tarjeta'));
            $spreadsheet->getActiveSheet()->setCellValue("E{$rowNumber}", $this->getPagosTotalesByType($this->ventasVideoArray, 'deposito'));
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
        
        //se utiliza el with para filtrar solo los pagos que coincidan con el criterio de busqueda añadiendolos al objeto reservacion.

        $reservaciones = Reservacion::where('estatus', 1)->whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('pagos.tipo_pago_id',$this->tiposPago)
                ->whereIn('pagos.usuario_id',$usuarios);
        })->with(['pagos' => function ($query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("pagos.created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('pagos.tipo_pago_id',$this->tiposPago)
                ->whereIn('pagos.usuario_id',$usuarios);
        }])->get();
        
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
            $reservacionTipoPagos[$pago->tipoPago->nombre] += $pago->cantidad;
        }
        return $reservacionTipoPagos;
    }

    private function setReservacion($reservacion, $usuarios){

        $actividades = $this->setReservacionActividad($reservacion);

        $ventaService = app(VentaService::class);

        $reservacionTipoPagos = $this->getReservacionPagos($reservacion, $usuarios);
        $ventaService->setFolio($reservacion->folio);
        $ventaService->setProductos($actividades);
        $ventaService->setNombreCliente($reservacion->nombre_cliente);
        $ventaService->setPagoEfectivo($reservacionTipoPagos['efectivo']);
        $ventaService->setPagoEfectivoUsd($reservacionTipoPagos['efectivoUsd']);
        $ventaService->setPagoTarjeta($reservacionTipoPagos['tarjeta']);
        $ventaService->setPagoDeposito($reservacionTipoPagos['deposito']);
        $ventaService->setPagoCupon($reservacionTipoPagos['cupon']);
        $ventaService->setCambio($reservacionTipoPagos['cambio']);

        return $ventaService;
    }

    private function setReservacionActividad($reservacion){
        $actividadesArray = [];
        $reservacionDetalles = $reservacion->reservacionDetalle;
        foreach($reservacionDetalles as $reservacionDetalle){
            $actividad = $reservacionDetalle->actividad;
            $numeroPersonas = $reservacionDetalle->numero_personas;

            $productoService = app(ProductoService::class);
            $productoService->setId($actividad->id);
            $productoService->setClave($actividad->clave);
            $productoService->setPrecio($actividad->precio);
            $productoService->setNumeroProductos($numeroPersonas);
            $productoService->setNombre($actividad->nombre);
            $productoService->setCantidadPagada(0);
            $actividadesArray[] = $productoService;
        }

        return $actividadesArray;
    }   

	private function getReservacionActividades()
	{
        $actividades = Actividad::orderBy('actividades.reporte_orden','asc')->get();
        return $actividades;
    }

    private function getFotoVideoVentasFecha($fechaInicio, $fechaFinal, $usuarios, $tipo)
	{
        //se utiliza el with para filtrar solo los pagos que coincidan con el criterio de busqueda añadiendolos al objeto reservacion.

        $ventas = FotoVideoVenta::where('estatus', 1)->whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id', $this->tiposPago)
                ->whereIn('usuario_id', $usuarios);
        })->with(['pagos' => function ($query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)
                ->whereIn('usuario_id',$usuarios);
        }])->whereHas('productos', function (Builder $query) use ($tipo) {
            $query->where('tipo', $tipo);
        })->get();

        return $ventas;
    }

    private function getTiendaVentasFecha($fechaInicio, $fechaFinal, $usuarios)
	{
        //se utiliza el with para filtrar solo los pagos que coincidan con el criterio de busqueda añadiendolos al objeto reservacion.
        
        $ventas = TiendaVenta::where('estatus', 1)->whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id', $this->tiposPago)
                ->whereIn('usuario_id', $usuarios);
        })->with(['pagos' => function ($query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)
                ->whereIn('usuario_id',$usuarios);
        }])->get();

        return $ventas;
    }

    private function getVentaPagos($venta, $usuarios)
    {
        $ventaTipoPagos = [];

        $tipoPagos = TipoPago::get();
        foreach($tipoPagos as $tipoPago){
            $ventaTipoPagos[$tipoPago->nombre] = 0;
        }

        foreach($venta->pagos as $pago){
            if(!in_array($pago->usuario_id, $usuarios->toArray())){
                continue;
            }
            $ventaTipoPagos[$pago->tipoPago->nombre] = $pago->cantidad;
        }
        return $ventaTipoPagos;
    }

    private function setVenta($venta, $usuarios)
    {

        $productos = $this->setProducto($venta);

        $ventaService = app(VentaService::class);

        $ventaTipoPagos = $this->getVentaPagos($venta, $usuarios);
        $ventaService->setFolio($venta->folio);
        $ventaService->setProductos($productos);
        $ventaService->setNombreCliente($venta->nombre_cliente);
        $ventaService->setPagoEfectivo($ventaTipoPagos['efectivo']);
        $ventaService->setPagoEfectivoUsd($ventaTipoPagos['efectivoUsd']);
        $ventaService->setPagoTarjeta($ventaTipoPagos['tarjeta']);
        $ventaService->setPagoDeposito($ventaTipoPagos['deposito']);
        $ventaService->setPagoCupon($ventaTipoPagos['cupon']);
        $ventaService->setCambio($ventaTipoPagos['cambio']);

        return $ventaService;
    }

    private function setProducto($venta)
    {
        $productosArray = [];
        $ventaDetalles = $venta->ventaDetalle;
        foreach($ventaDetalles as $ventaDetalle){
            $producto = $ventaDetalle->producto;

            $numeroProductos = $ventaDetalle->numero_productos;

            $productoService = app(ProductoService::class);
            $productoService->setId($producto->id);
            $productoService->setClave($producto->clave);
            $productoService->setPrecio($producto->precio_venta);
            $productoService->setTipo($producto->tipo);
            $productoService->setNumeroProductos($numeroProductos);
            $productoService->setNombre($producto->nombre);
            $productoService->setCantidadPagada(0);
            $productosArray[] = $productoService;
        }

        return $productosArray;
    }

    private function getCambio($venta, $tipoPago, $isUltimoProducto = false)
    {
        if(!$isUltimoProducto){
            return 0;
        }
        
        $nombreMetodoGet = \sprintf('get%s',$tipoPago);
        $nombreMetodoSet = \sprintf('set%s',$tipoPago);

        $cambio = $venta->{$nombreMetodoGet}();

        $venta->{$nombreMetodoSet}(0);

        return $cambio;
    }

    private function getPagoPorTipo($venta, $producto, $tipoPago, $isUltimoProducto = false)
    {
        $nombreMetodoGet = \sprintf('get%s',$tipoPago);
        $nombreMetodoSet = \sprintf('set%s',$tipoPago);
        $precioProducto = ($producto->getPrecio() * $producto->getNumeroProductos());
        $cantidadPagada = $producto->getCantidadPagada();
        $diferenciaPago = ($precioProducto - $cantidadPagada);
        $pago = $venta->{$nombreMetodoGet}();

        if($pago == 0){// || $diferenciaPago == 0){
            return 0;
        }
        
        //Se realiza una conversion de moneda para los calculos
        if($tipoPago == 'PagoEfectivoUsd'){
            $pago = $this->convertUsdToMxn($pago);
        }

        //Si el pago es mayor al costo del producto actual,
        //se descuenta el costo del producto actual y se modifica el monto con el residuo 
        //para el calculo en el siguiente producto
        if($pago > $diferenciaPago){
            
            $resta = $pago - $diferenciaPago;

            //Una vez finalizado el calculo regresamos el sobrante a la moneda original para que se siga descontando en el siguiente producto
            if($tipoPago == 'PagoEfectivoUsd'){
                $resta = $this->convertMxnToUsd($resta);

                //verificamos si es el ultimo producto/actividad que se generará si es asi le aplicaremos el total de dolares a esta
                if($isUltimoProducto){    
                    $venta->{$nombreMetodoSet}(0);

                    // //Una vez finalizado el calculo regresamos a la moneda original para el reporte
                    $pago = $this->convertMxnToUsd($pago);

                    $producto->setCantidadPagada($pago);
                    return $pago;
                }
            }

            $venta->{$nombreMetodoSet}($resta);
            $producto->setCantidadPagada($precioProducto);

            //regresamos a MXN para continuar con el proceso
            // if($tipoPago == 'PagoEfectivoUsd'){
            //     $diferenciaPago = $this->convertUsdToMxn($resta);

            // }
            
            // //Una vez finalizado el calculo regresamos a la moneda original para el reporte
            if($tipoPago == 'PagoEfectivoUsd'){
                $diferenciaPago = $this->convertMxnToUsd($precioProducto);
            }

            return $diferenciaPago;
        }

        //Si el pago es menor o igual al costo del producto actual,
        //se toma el monto total y se modifica el monto a 0
        //para el calculo en el siguiente producto
        $venta->{$nombreMetodoSet}(0);
        $producto->setCantidadPagada($cantidadPagada + $pago);
        
        //Una vez finalizado el calculo regresamos a la moneda original para el reporte
        if($tipoPago == 'PagoEfectivoUsd'){
            $pago = $this->convertMxnToUsd($pago);
        }
        return $pago;
    }

    private function convertUsdToMxn($pago)
    {
        return ($pago * $this->tipoCambio);
    }

    private function convertMxnToUsd($pago)
    {
        return ($pago / $this->tipoCambio);
    }

    private function getTiendaVentas($fechaInicio, $fechaFinal, $usuarios)
	{
        $ventas = TiendaVenta::where('estatus', 1)->whereHas('pagos', function (Builder $query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)
                ->whereIn('usuario_id',$usuarios);
        })->with(['pagos' => function ($query) use ($usuarios, $fechaInicio, $fechaFinal) {
            $query
                ->whereBetween("created_at", [$fechaInicio,$fechaFinal])
                ->whereIn('tipo_pago_id',$this->tiposPago)
                ->whereIn('usuario_id',$usuarios);
        }])->get();

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
        $pagoTipoId    = $this->getTipoPagoIdByName($pagoTipoNombre);

        return isset($venta[$pagoTipoId]) ? $venta[$pagoTipoId] : 0;
    }

    private function getPagosTotalesByType($ventas, $pagoTipoNombre)
    {
        $total = 0;
        foreach($ventas as $venta){
            $total += (float)$venta[$pagoTipoNombre];
        }

        return $total;        
    }

    private function getTiendaVentaPagosTotalesByType($usuarios, $venta, $pagoTipoNombre, $fechaInicio, $fechaFinal, $pendiente = 0)
	{
        $pagosId = $venta->pagos->pluck('id');
        $pagos   = TiendaVentaPago::whereIn("id",$pagosId)->whereBetween("created_at", [$fechaInicio,$fechaFinal])->whereIn('usuario_id', $usuarios)->get();

        $pagoTipoId    = $this->getTipoPagoIdByName($pagoTipoNombre);
        //total pagado en tipo de pago actual
        $totalPagado   = 0;
        foreach($pagos as $pago){
            if($pago->tipo_pago_id == $pagoTipoId){
                $totalPagado += $pago->cantidad;
            }
        }

        return ['pago' => $totalPagado];
    }

    private function getTipoPagoIdByName($tipoPago){
        $tipoPagoId = TipoPago::where('nombre',$tipoPago)->first()->id;
        return $tipoPagoId;
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
