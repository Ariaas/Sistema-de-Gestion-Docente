<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once ('vendor/autoload.php');
require_once ('model/reportes/raulario.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$modeloPath = "model/reportes/raulario.php";
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/raulario.php";
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}
$oAulario = new AularioReport();
$listaEspacios = $oAulario->getEspacios();

function format_time_short($time_str) {
    if (empty($time_str) || strlen($time_str) < 5) return '';
    return substr($time_str, 0, 5);
}

if (isset($_POST['generar_aulario_report'])) {
    $selectedEspacioId = isset($_POST['espacio_aul']) ? $_POST['espacio_aul'] : '';

    if (empty($selectedEspacioId)) {
        die("Error: Debe seleccionar un aula. Regrese y seleccione una.");
    }

    $oAulario->set_espacio_id($selectedEspacioId);
    $horarioDataRaw = $oAulario->getHorarioDataByEspacio();
    $distinctDbTimeSlots = $oAulario->getDistinctTimeSlotsForEspacio();
    $espacioCodigo = $oAulario->getEspacioCodigoByCodigo($selectedEspacioId);
    if (!$espacioCodigo) $espacioCodigo = "Aula Desconocida";

    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    $gridData = [];
    if ($horarioDataRaw) {
        foreach ($horarioDataRaw as $item) {
            // ▼▼▼ CORRECCIÓN PRINCIPAL ▼▼▼
            // Se formatea el día para que coincida con el arreglo (Ej: 'martes' -> 'Martes')
            $dia = ucfirst(strtolower(trim($item['hor_dia'])));
            
            // Se limpian los espacios de las horas
            $horaInicioBD = trim($item['hor_inicio']);

            if (!isset($gridData[$dia][$horaInicioBD])) {
                $gridData[$dia][$horaInicioBD] = ['events_uc_sec' => [], 'all_teachers_in_slot' => []];
            }
            $ucDisplay = $item['UnidadDisplay'];
            if (isset($item['NombreCompletoUC']) && (strtoupper($item['NombreCompletoUC']) === 'TRAYECTO INICIAL' || stripos($item['NombreCompletoUC'], 'PST-') === 0)) {
                 $ucDisplay = $item['NombreCompletoUC'];
            }
            $uc_sec_pair = $ucDisplay;
            if (!empty($item['NombreSeccion'])) {
                $uc_sec_pair .= "\n" . $item['NombreSeccion'];
            }
            if (!in_array($uc_sec_pair, $gridData[$dia][$horaInicioBD]['events_uc_sec'])) {
                $gridData[$dia][$horaInicioBD]['events_uc_sec'][] = $uc_sec_pair;
            }
            if (!empty($item['NombreCompletoDocente']) && !in_array($item['NombreCompletoDocente'], $gridData[$dia][$horaInicioBD]['all_teachers_in_slot'])) {
                $gridData[$dia][$horaInicioBD]['all_teachers_in_slot'][] = $item['NombreCompletoDocente'];
            }
        }
    }

    $morning_slots_render = [];
    $afternoon_slots_render = [];
    if ($distinctDbTimeSlots) {
        foreach ($distinctDbTimeSlots as $slot) {
            $db_inicio_key = trim($slot['hor_inicio']);
            $db_fin_key = trim($slot['hor_fin']);
            $display_string = format_time_short($db_inicio_key) . " a " . format_time_short($db_fin_key);
            if (strcmp($db_inicio_key, "13:00:00") < 0) {
                $morning_slots_render[$display_string] = $db_inicio_key;
            } else {
                $afternoon_slots_render[$display_string] = $db_inicio_key;
            }
        }
    }

    // --- LÓGICA PARA GENERAR EXCEL (SIN CAMBIOS) ---

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Horario ' . $espacioCodigo);

    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 8], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];

    $renderScheduleTable = function(Worksheet $sheet, $title_suffix, $slots_to_render, &$currentRow) use ($days_of_week, $gridData, $espacioCodigo, $styleHeaderTitle, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
        if (empty($slots_to_render)) return;
        $startRow = $currentRow;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", $espacioCodigo);
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
            $sheet->getRowDimension($currentRow)->setRowHeight(50);
            $sheet->setCellValue('A'.$currentRow, $displaySlot)->getStyle('A'.$currentRow)->applyFromArray($styleTimeSlot);
            $colNum = 1;
            foreach ($days_of_week as $day) {
                $cellContent = '';
                if (isset($gridData[$day][$dbStartTimeKey])) {
                    $slot_data = $gridData[$day][$dbStartTimeKey];
                    $cellParts = [];
                    if (!empty($slot_data['events_uc_sec'])) { $cellParts[] = implode("\n", $slot_data['events_uc_sec']); }
                    if (!empty($slot_data['all_teachers_in_slot'])) { $cellParts[] = implode("\n", $slot_data['all_teachers_in_slot']); }
                    $cellContent = implode("\n\n", $cellParts);
                }
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
    foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(25); }
    
    $renderScheduleTable($sheet, "MAÑANA", $morning_slots_render, $currentRow);
    $renderScheduleTable($sheet, "TARDE", $afternoon_slots_render, $currentRow);
    
    if (empty($distinctDbTimeSlots)) {
        $sheet->setCellValue('A1', 'No hay franjas horarias definidas para esta aula.');
    }

    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Aulario_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $espacioCodigo) . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} else {
    require_once($vistaPath);
}
?>