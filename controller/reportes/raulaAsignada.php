<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/raulaAsignada.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // NUEVO: Se importa la clase Coordinate

if (isset($_POST['generar_asignacion_aulas_report'])) {
    $oAsignacionAulas = new AsignacionAulasReport();
    $aulasData = $oAsignacionAulas->getAulasConAsignaciones();

    $dataPorDia = [];
    $ordenDias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    foreach ($ordenDias as $dia) {
        $dataPorDia[$dia] = [];
    }

    if ($aulasData) {
        foreach ($aulasData as $row) {
            $dia = ucfirst(strtolower($row['hor_dia'])); 
            if (isset($dataPorDia[$dia])) {
                if (!in_array($row['esp_codigo'], $dataPorDia[$dia])) {
                    $dataPorDia[$dia][] = $row['esp_codigo'];
                }
            }
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Asignación de Aulas");

    $styleHeader = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]];
    $styleCell = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];

    $col = 'A';
    foreach ($ordenDias as $dia) {
        $sheet->setCellValue($col . '1', mb_strtoupper($dia, 'UTF-8'));
        $sheet->getStyle($col . '1')->applyFromArray($styleHeader);
        $sheet->getColumnDimension($col)->setWidth(15);
        $col++;
    }

    $maxRows = 0;
    foreach ($dataPorDia as $aulas) {
        if (count($aulas) > $maxRows) {
            $maxRows = count($aulas);
        }
    }

    for ($i = 0; $i < $maxRows; $i++) {
        $colIndex = 1; // El índice de columna empieza en 1 para Coordinate
        foreach ($dataPorDia as $dia => $aulas) {
            $cellValue = isset($aulas[$i]) ? $aulas[$i] : '';
            
            // MODIFICADO: Se usa un método alternativo para establecer el valor de la celda
            $coordinate = Coordinate::stringFromColumnIndex($colIndex) . ($i + 2);
            $sheet->setCellValue($coordinate, $cellValue);
            
            $colIndex++;
        }
    }

    if ($maxRows > 0) {
        $range = 'A1:' . $sheet->getHighestColumn() . ($maxRows + 1);
        $sheet->getStyle($range)->applyFromArray($styleCell);
    } else {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($styleCell);
    }

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Reporte_Asignacion_Aulas_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    require_once("views/reportes/raulaAsignada.php");
}
?>