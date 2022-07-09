<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\CapabilityProfile;

class ImprimirController extends Controller
{
    public function imprimirTicket($nombreImpresora){
        //"POS-58";
        $reservaciones = [];

        echo("TESTING2...".$nombreImpresora);

        try {
            // Enter the share name for your USB printer here
            //$connector = $nombreImpresora;
//            $connector = new WindowsPrintConnector("EPSON");

            $hostname = 'CAJA02';//gethostbyaddr($_SERVER['REMOTE_ADDR']);

            $connector = new WindowsPrintConnector("smb://$hostname/EPSON");


        
            /* Print a "Hello world" receipt" */
            $printer = new Printer($connector);
            $printer -> text("Hello World!\n");
            $printer -> cut();
            
            /* Close printer */
            $printer -> close();
        } catch (\Exception $e){
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }
        /*
        $connector = new FilePrintConnector("php://stdout");
        $printer = new Printer($connector);
        $printer -> text("Hello World!\n");
        $printer -> cut();
        $printer -> close();
        

        $profile = CapabilityProfile::load("simple");
        $connector = new WindowsPrintConnector("smb://computer/printer");
        $printer = new Printer($connector, $profile);
        $printer -> text("Hello World!\n");
        $printer -> cut();
        $printer -> close();

        
        $connector = new WindowsPrintConnector($nombreImpresora);
        $printer   = new Printer($connector);
        $printer->text("DELFINITI DE MEXICO S.A. DE C.V.");
        $printer->text("RFC: DME-990323-PR7");
        $printer->text("ZONA HOTELERA 1");
        $printer->text("LOTE ANEXO 6-B");
        $printer->text("TEL. (755) 553-2707");
        $printer->text("IXTAPA - ZIHUATANEJO, GUERRERO MEXICO.");
        $printer->text("C.P. 40884");
        $printer->text("FOLIO: 0000-B");
        $printer->text("LUGAR DE EXPEDICIÓN: IXTAPA - ZIHUATANEJO");
        $printer->text("FECHA DE EXPEDICION: 00/MES/0000 hh:mm:ss A.M.");
        $printer->text("CAJERO: XXXXXX");
        $printer->text("NOMBRE: XXXXXX");
        $printer->text("DIRECCIÓN: XXXXX");
        $printer->text("CIUDAD: XXXXXX");
        $printer->text("---------------------------------------------------");
        $printer->text("CLAVE  CANT   DESC.          PRECIO      IMPORTE   ");
        $printer->text("---------------------------------------------------");
        $printer->text("022    2      NADO           $1,200.00   $2,400.00 ");
        $printer->text("\n\n\n\n\n\n");
        $printer->text("---------------------------------------------------");
        $printer->text("                   EFECTIVO M.N.         $2,400.00");
        $printer->text("                   EFECTIVO USD              $0.00");
        $printer->text("                   TARJ. CREDITO             $0.00");
        $printer->text("                   TOTAL                 $2,400.00");
        $printer->text("                   CAMBIO                    $0.00");
        $printer->text("DOSMIL CUATROCIENTOS PESOS 00/100 M.N.            ");
        $printer->text("---------------------------------------------------");
        $printer->text("ESTE COMPROBANTE FORMA PARTE DE ");
        $printer->text("LA FACTURA GLOBAL A PUBLICO");
        $printer->text("LA FACTURA GLOBAL A PUBLICO EN GENERAL. ");
        $printer->text("---------------------------------------------------");
        $printer->text("SI SE REQUIERE FACTURA FAVOR DE");
        $printer->text("COLICITARLA EN RECEPCIÓN EN EL ");
        $printer->text("MOMENTO, YA QUE NO SE PODRÁ ");
        $printer->text("FACTURAR DIAS ANTERIORES.");
        $printer->text("DELFINITI");
        $printer->feed();
        $printer->cut();
        $printer->pulse();
        $printer->close();
        */
    }
}
