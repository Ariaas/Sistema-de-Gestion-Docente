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

$oAulario = new AularioReport();

if (isset($_POST['generar_aulario_report'])) {
    
    $anio = $_POST['anio_id'] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    $espacio_filtrado = $_POST['espacio_id'] ?? '';

    if (empty($anio) || empty($fase)) {
        die("Error: Debe seleccionar un Año y una Fase.");
    }
    
    $oAulario->setAnio($anio);
    $oAulario->setFase($fase);
    $oAulario->setEspacio($espacio_filtrado);

    $horarioDataRaw = $oAulario->getAulariosFiltrados();

    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); 

    
    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];
    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    $renderScheduleTable = function(Worksheet $sheet, $title_suffix, $slots_to_render, &$currentRow, $gridData) use ($days_of_week, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
        if (empty($slots_to_render)) return;
        $startRow = $currentRow;
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
                $cellContent = $gridData[$day][$dbStartTimeKey] ?? '';
                $sheet->setCellValue(chr(65 + $colNum).$currentRow, $cellContent);
                $colNum++;
            }
            $sheet->getStyle("B{$currentRow}:G{$currentRow}")->applyFromArray($styleScheduleCell);
            $currentRow++;
        }
        $sheet->getStyle("A".($startRow+1).":G".($currentRow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;
    };



    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $dataGroupedByAula = [];
        foreach ($horarioDataRaw as $item) {
            $dataGroupedByAula[$item['esp_codigo']][] = $item;
        }

        foreach($dataGroupedByAula as $espacioCodigo => $horarioData) {
            $sheet = new Worksheet($spreadsheet, preg_replace('/[^A-Za-z0-9\-\. ]/', '', $espacioCodigo));
            $spreadsheet->addSheet($sheet);
            $sheet->getColumnDimension('A')->setWidth(18);
            foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(30); }
            
            $currentRow = 1;
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "Horario del Aula: " . $espacioCodigo);
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
            $currentRow+=2;

            $gridData = [];
            $distinctDbTimeSlots = [];
            foreach ($horarioData as $item) {
                $dia = ucfirst(strtolower(trim($item['hor_dia'])));
                $horaInicioBD = trim($item['hor_horainicio']);
                
                $cell_content = [
                    $item['uc_nombre'],
                    "Sección: " . $item['sec_codigo']
                ];
                if (!empty($item['NombreCompletoDocente'])) {
                    $cell_content[] = $item['NombreCompletoDocente'];
                }

                
                if(isset($gridData[$dia][$horaInicioBD])) {
                    $gridData[$dia][$horaInicioBD] .= "\n---\n" . implode("\n\n", $cell_content);
                } else {
                    $gridData[$dia][$horaInicioBD] = implode("\n\n", $cell_content);
                }
                
                $distinctDbTimeSlots[$horaInicioBD] = $item['hor_horafin'];
            }
            
            $morning_slots_render = [];
            $afternoon_slots_render = [];
            foreach ($distinctDbTimeSlots as $inicio => $fin) {
                $display_string = substr($inicio, 0, 5) . " a " . substr($fin, 0, 5);
                if (strcmp($inicio, "13:00:00") < 0) {
                    $morning_slots_render[$display_string] = $inicio;
                } else {
                    $afternoon_slots_render[$display_string] = $inicio;
                }
            }

            $renderScheduleTable($sheet, "MAÑANA", $morning_slots_render, $currentRow, $gridData);
            $renderScheduleTable($sheet, "TARDE", $afternoon_slots_render, $currentRow, $gridData);
        }
    }

    
    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Reporte_Aulario_" . $anio . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} else {
   
    $listaAnios = $oAulario->getAniosActivos();
    $listaFases = $oAulario->getFases();
    $listaEspacios = $oAulario->getEspacios();
    require_once("views/reportes/raulario.php");
}