<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/raulaAsignada.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
            $dia = ucfirst(strtolower(trim($row['hor_dia'])));
            if (isset($dataPorDia[$dia])) {
             
                if (!in_array($row['aula_completa'], $dataPorDia[$dia])) {
                    $dataPorDia[$dia][] = $row['aula_completa'];
                }
            }
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Asignación de Aulas");

    $styleHeader = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]];
    $styleCell = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];

    $col = 'A';
    foreach ($ordenDias as $dia) {
        $sheet->setCellValue($col . '1', mb_strtoupper($dia, 'UTF-8'));
        $sheet->getStyle($col . '1')->applyFromArray($styleHeader);
        $sheet->getColumnDimension($col)->setWidth(25); 
        $col++;
    }

    $maxRows = 0;
    foreach ($dataPorDia as $aulas) {
        if (count($aulas) > $maxRows) {
            $maxRows = count($aulas);
        }
    }

    if ($maxRows > 0) {
        for ($i = 0; $i < $maxRows; $i++) {
            $colChar = 'A';
            foreach ($dataPorDia as $dia => $aulas) {
               
                $cellValue = isset($aulas[$i]) ? $aulas[$i] : '';
                $sheet->setCellValue($colChar . ($i + 2), $cellValue);
                $colChar++;
            }
        }
        $range = 'A1:' . $sheet->getHighestColumn() . ($maxRows + 1);
        $sheet->getStyle($range)->applyFromArray($styleCell);
    } else {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($styleCell);
        $sheet->mergeCells('A2:F2')->setCellValue('A2', 'No se encontraron aulas asignadas.');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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