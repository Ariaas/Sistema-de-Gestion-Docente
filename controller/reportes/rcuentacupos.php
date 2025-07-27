<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("vendor/autoload.php");
require_once("model/reportes/rcuentacupos.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


function convertirTrayectoARomano($numeroTrayecto) {
    switch ($numeroTrayecto) {
        case '1': return 'I';
        case '2': return 'II';
        case '3': return 'III';
        case '4': return 'IV';
        default: return 'INICIAL. '; 
    }
}

$oCuentaCupos = new CuentaCupos();

if (isset($_POST['generar_reporte'])) {

    $oCuentaCupos->set_anio($_POST['anio'] ?? '');
   
    $datosReporte = $oCuentaCupos->obtenerCuentaCupos();

  
    $datosAgrupados = [];
    $totalGeneral = 0;
    if ($datosReporte) {
        foreach ($datosReporte as $fila) {
            $trayectoRomano = convertirTrayectoARomano($fila['Trayecto']);
            $datosAgrupados[$trayectoRomano][] = $fila;
            $totalGeneral += $fila['Cantidad'];
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Matricula Trayecto");

   
    $styleHeader = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
   $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    
  
    $sheet->mergeCells('A1:C1')->setCellValue('A1', 'MATRICULA TRAYECTO ' . htmlspecialchars($_POST['anio'] ?? ''));
    $sheet->getStyle('A1')->applyFromArray($styleHeader);
    $sheet->mergeCells('A2:C2')->setCellValue('A2', 'UPTAEB');
    $sheet->getStyle('A2')->applyFromArray($styleHeader);

    
    $filaActual = 4;
    $filaInicioTabla = $filaActual;
    $sheet->setCellValue('A'.$filaActual, 'TRAYECTO');
    $sheet->setCellValue('B'.$filaActual, 'SECCION');
    $sheet->setCellValue('C'.$filaActual, 'CANTIDAD');
    $sheet->getStyle('A'.$filaActual.':C'.$filaActual)->applyFromArray($styleHeaderColumnas);
    $filaActual++;

    
    if(!empty($datosAgrupados)){
        foreach ($datosAgrupados as $trayectoRomano => $seccionesDelTrayecto) {
            $filaInicioTrayecto = $filaActual;
            $numFilas = count($seccionesDelTrayecto);

            if ($numFilas > 0) {
                
                $sheet->mergeCells('A'.$filaInicioTrayecto.':A'.($filaInicioTrayecto + $numFilas - 1));
                $sheet->setCellValue('A'.$filaInicioTrayecto, $trayectoRomano);
                $sheet->getStyle('A'.$filaInicioTrayecto)->applyFromArray($styleCentrado);
            }
            
          
            foreach ($seccionesDelTrayecto as $seccion) {
                $sheet->setCellValue('B'.$filaActual, $seccion['Seccion']);
                $sheet->getStyle('B'.$filaActual)->applyFromArray($styleCentrado);
                $sheet->setCellValue('C'.$filaActual, $seccion['Cantidad']);
                $sheet->getStyle('C'.$filaActual)->applyFromArray($styleCentrado);
                $filaActual++;
            }
        }
    }
    
   
    $sheet->mergeCells('A'.$filaActual.':B'.$filaActual);
    $sheet->setCellValue('A'.$filaActual, 'TOTAL');
    $sheet->getStyle('A'.$filaActual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A'.$filaActual)->getFont()->setBold(true);

    $sheet->setCellValue('C'.$filaActual, $totalGeneral);
    $sheet->getStyle('C'.$filaActual)->applyFromArray($styleCentrado)->getFont()->setBold(true);

   
    $sheet->getStyle('A'.$filaInicioTabla.':C'.$filaActual)->applyFromArray($styleBordes);

   
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(12);
    
   
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Reporte_Cuenta_Cupos" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $anios = $oCuentaCupos->obtenerAnios();
    require_once("views/reportes/rcuentacupos.php");
}
?>