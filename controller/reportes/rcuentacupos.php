<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rcuentacupos.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$oCuentaCupos = new CuentaCupos();

if (isset($_POST['generar_reporte'])) {

    $oCuentaCupos->set_anio($_POST['anio'] ?? '');
    $datosReporte = $oCuentaCupos->obtenerCuentaCupos();

    // 1. Agrupar los datos por Trayecto
    $datosAgrupados = [];
    $totalGeneral = 0;
    if ($datosReporte) {
        foreach ($datosReporte as $fila) {
            $datosAgrupados[$fila['Trayecto']][] = $fila;
            $totalGeneral += $fila['Cantidad'];
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Cuenta de Cupos");

    // Estilos
    $styleHeader = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeaderColumnas = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD3D3D3']]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];

    $filaActual = 1;

    // Título de la tabla
    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'MATRICULA TRAYECTO - UPTAEB'); // Título ajustado
    $sheet->getStyle('A1')->applyFromArray($styleHeader);
    $filaActual += 2;

    // Cabeceras de las columnas
    $filaInicioTabla = $filaActual;
    $sheet->setCellValue('A'.$filaActual, 'TRAYECTO');
    $sheet->setCellValue('B'.$filaActual, 'SECCION');
    $sheet->setCellValue('C'.$filaActual, 'CANTIDAD');
    $sheet->getStyle('A'.$filaActual.':C'.$filaActual)->applyFromArray($styleHeaderColumnas);
    $filaActual++;

    // 2. Iterar sobre cada Trayecto para construir las filas
    if(!empty($datosAgrupados)){
        foreach ($datosAgrupados as $numTrayecto => $secciones) {
            $filaInicioTrayecto = $filaActual;
            $numFilasTrayecto = count($secciones);

            // Fusionar celda de Trayecto
            if ($numFilasTrayecto > 0) {
                $sheet->mergeCells('A'.$filaInicioTrayecto.':A'.($filaInicioTrayecto + $numFilasTrayecto - 1));
                $sheet->setCellValue('A'.$filaInicioTrayecto, $numTrayecto);
                $sheet->getStyle('A'.$filaInicioTrayecto)->applyFromArray($styleCentrado);
            }

            // Escribir las secciones y sus cantidades individuales
            foreach ($secciones as $seccion) {
                $sheet->setCellValue('B'.$filaActual, $seccion['Seccion']);
                $sheet->setCellValue('C'.$filaActual, $seccion['Cantidad']);
                $sheet->getStyle('C'.$filaActual)->applyFromArray($styleCentrado);
                $filaActual++;
            }
        }
    }
    
    // Fila de TOTAL
    $sheet->mergeCells('A'.$filaActual.':B'.$filaActual);
    $sheet->setCellValue('A'.$filaActual, 'TOTAL');
    $sheet->setCellValue('C'.$filaActual, $totalGeneral);
    $sheet->getStyle('A'.$filaActual.':C'.$filaActual)->getFont()->setBold(true);

    // Aplicar bordes a toda la tabla
    $sheet->getStyle('A'.$filaInicioTabla.':C'.$filaActual)->applyFromArray($styleBordes);

    // Auto-ajustar el ancho de las columnas
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // 3. Enviar el archivo al navegador
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Reporte_Cuenta_Cupos_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    // Si no se envía el formulario, solo muestra la vista
    $anios = $oCuentaCupos->obtenerAnios();
    require_once("views/reportes/rcuentacupos.php");
}