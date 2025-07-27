<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }



require_once ('vendor/autoload.php');
require_once ('model/reportes/rseccion.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$oReporte = new SeccionReport();

if (isset($_POST['generar_seccion_report'])) {
   
    $anio = $_POST['anio_id'] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    $trayecto_filtrado = $_POST['trayecto_id'] ?? '';

    if (empty($anio) || empty($fase)) die("Error: Debe seleccionar un Año y una Fase.");
    
    $oReporte->setAnio($anio);
    $oReporte->setFase($fase);
    $oReporte->setTrayecto($trayecto_filtrado);

    $horarioDataRaw = $oReporte->getHorariosFiltrados();

    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); 

   
    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];
    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    $renderScheduleTable = function(Worksheet $sheet, $title_suffix, $slots_to_render, &$currentRow, $seccionCodigo, $gridData) use ($days_of_week, $styleHeaderTitle, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
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
                $cellContent = $gridData[$day][$dbStartTimeKey] ?? '';
                $sheet->setCellValue(chr(65 + $colNum).$currentRow, $cellContent);
                $colNum++;
            }
            $sheet->getStyle("B{$currentRow}:G{$currentRow}")->applyFromArray($styleScheduleCell);
            $currentRow++;
        }
        $sheet->getStyle("A".($startRow+2).":G".($currentRow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;
    };

   
    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $dataGrouped = [];
    
        if (!empty($trayecto_filtrado)) {
            $dataGrouped[$trayecto_filtrado] = $horarioDataRaw;
        } else {
            foreach ($horarioDataRaw as $item) {
                $dataGrouped[$item['uc_trayecto']][] = $item;
            }
        }

        foreach($dataGrouped as $group_id => $data_for_group) {
            $sheet = new Worksheet($spreadsheet, "Trayecto " . $group_id);
            $spreadsheet->addSheet($sheet);
            $sheet->getColumnDimension('A')->setWidth(18);
            foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(30); }
            $currentRow = 1;

            $horariosPorSeccion = [];
            foreach ($data_for_group as $item) {
                $horariosPorSeccion[$item['sec_codigo']][] = $item;
            }
            
            foreach($horariosPorSeccion as $seccionCodigo => $horarioData) {
                $gridData = [];
                $distinctDbTimeSlots = [];
                foreach ($horarioData as $item) {
                    $dia = ucfirst(strtolower(trim($item['hor_dia'])));
                    $horaInicioBD = trim($item['hor_horainicio']);
                    $cell_content = [$item['uc_nombre'], "Aula: " . $item['esp_codigo']];
                    if (!empty($item['NombreCompletoDocente'])) { $cell_content[] = $item['NombreCompletoDocente']; }
                    $gridData[$dia][$horaInicioBD] = implode("\n\n", $cell_content);
                    $distinctDbTimeSlots[$horaInicioBD] = $item['hor_horafin'];
                }
                
                $morning_slots_render = [];
                $afternoon_slots_render = [];
                foreach ($distinctDbTimeSlots as $inicio => $fin) {
                    $display_string = substr($inicio, 0, 5) . " a " . substr($fin, 0, 5);
                    if (strcmp($inicio, "13:00:00") < 0) { $morning_slots_render[$display_string] = $inicio; }
                    else { $afternoon_slots_render[$display_string] = $inicio; }
                }

                $renderScheduleTable($sheet, "MAÑANA", $morning_slots_render, $currentRow, $seccionCodigo, $gridData);
                $renderScheduleTable($sheet, "TARDE", $afternoon_slots_render, $currentRow, $seccionCodigo, $gridData);
            }
        }
    }

 
    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Reporte_Horarios.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} else {
   
    $listaAnios = $oReporte->getAniosActivos();
    $listaFases = $oReporte->getFases();
    $listaTrayectos = $oReporte->getTrayectos();
    require_once("views/reportes/rseccion.php");
}
?>