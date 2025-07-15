<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rseccion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$oSeccion = new SeccionReport();

if (isset($_POST['generar_seccion_report'])) {
    $selectedSeccionId = isset($_POST['seccion_id']) ? $_POST['seccion_id'] : '';

    if (empty($selectedSeccionId)) {
        die("Error: Debe seleccionar una sección. Regrese y seleccione una.");
    }

    $oSeccion->set_seccion_id($selectedSeccionId);
    $horarioDataRaw = $oSeccion->getHorarioDataBySeccion();
    $distinctDbTimeSlots = $oSeccion->getDistinctTimeSlotsForSeccion();
    $seccionCodigo = $oSeccion->getSeccionCodigoById($selectedSeccionId);
    if (!$seccionCodigo) $seccionCodigo = "Sección Desconocida";
    
    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];
    
    $gridData = [];
    if ($horarioDataRaw) {
        foreach ($horarioDataRaw as $item) {
            $dia = ucfirst(strtolower(trim($item['hor_dia'])));
            $horaInicioBD = trim($item['hor_horainicio']);
            $cell_content = [];
            $cell_content[] = $item['uc_nombre'];
            $cell_content[] = "Aula: " . $item['esp_codigo'];
            if (!empty($item['NombreCompletoDocente'])) {
                $cell_content[] = $item['NombreCompletoDocente'];
            }
            $gridData[$dia][$horaInicioBD] = implode("\n\n", $cell_content);
        }
    }
    
    $morning_slots_render = [];
    $afternoon_slots_render = [];
    if ($distinctDbTimeSlots) {
        foreach ($distinctDbTimeSlots as $slot) {
            $db_inicio_key = trim($slot['hor_horainicio']);
            $db_fin_key = trim($slot['hor_horafin']);
            $display_string = substr($db_inicio_key, 0, 5) . " a " . substr($db_fin_key, 0, 5);
            if (strcmp($db_inicio_key, "13:00:00") < 0) {
                $morning_slots_render[$display_string] = $db_inicio_key;
            } else {
                $afternoon_slots_render[$display_string] = $db_inicio_key;
            }
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Horario ' . $seccionCodigo);

    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];

    $renderScheduleTable = function(Worksheet $sheet, $title_suffix, $slots_to_render, &$currentRow) use ($days_of_week, $gridData, $seccionCodigo, $styleHeaderTitle, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
        if (empty($slots_to_render)) return;
        $startRow = $currentRow;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "Horario de la Sección: " . $seccionCodigo);
        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
        $currentRow++;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", $title_suffix);
        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleSubheaderTitle);
        $currentRow++;
        $sheet->setCellValue('A'.$currentRow, 'Hora');
        $col = 'B';
        foreach ($days_of_week as $day) { $sheet->setCellValue($col++.$currentRow, $day); }
        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleTableHeader);
        $currentRow++;

        uksort($slots_to_render, function($a, $b) { return strcmp($a, $b); });
        foreach ($slots_to_render as $displaySlot => $dbStartTimeKey) {
            $sheet->getRowDimension($currentRow)->setRowHeight(65);
            $sheet->setCellValue('A'.$currentRow, $displaySlot)->getStyle('A'.$currentRow)->applyFromArray($styleTimeSlot);
            $colNum = 1;
            foreach ($days_of_week as $day) {
                $cellContent = isset($gridData[$day][$dbStartTimeKey]) ? $gridData[$day][$dbStartTimeKey] : '';
                $sheet->setCellValue(chr(65 + $colNum).$currentRow, $cellContent);
                $colNum++;
            }
            $sheet->getStyle("B{$currentRow}:G{$currentRow}")->applyFromArray($styleScheduleCell);
            $currentRow++;
        }
        $sheet->getStyle("A".($startRow+2).":G".($currentRow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;
    };
    
    $currentRow = 1;
    $sheet->getColumnDimension('A')->setWidth(18);
    foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(30); }
    
    $renderScheduleTable($sheet, "MAÑANA", $morning_slots_render, $currentRow);
    $renderScheduleTable($sheet, "TARDE", $afternoon_slots_render, $currentRow);
    
    if (empty($distinctDbTimeSlots)) {
        $sheet->setCellValue('A1', 'No hay horario definido para esta sección.');
    }

    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Horario_Seccion_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $seccionCodigo) . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} else {
    $listaSecciones = $oSeccion->getSecciones();
    require_once("views/reportes/rseccion.php");
}
?>