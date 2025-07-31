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

    // --- 1. Generar la Plantilla de Horarios completa ---
    $turnos = $oReporte->getTurnosCompletos();
    $slot_duration_minutes = 40;
    $todas_las_franjas = [];
    foreach ($turnos as $turno) {
        $nombre_turno = ucfirst(strtolower($turno['tur_nombre']));
        $todas_las_franjas[$nombre_turno] = [];
        $hora_actual = new DateTime($turno['tur_horaInicio']);
        $hora_fin_turno = new DateTime($turno['tur_horaFin']);

        while ($hora_actual < $hora_fin_turno) {
            $franja_inicio = clone $hora_actual;
            $hora_actual->modify('+' . $slot_duration_minutes . ' minutes');
            $franja_fin = ($hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;
            
            $db_start_time_key = $franja_inicio->format('H:i:00');
            $display_string = $franja_inicio->format('H:i') . ' a ' . $franja_fin->format('H:i');
            $todas_las_franjas[$nombre_turno][$display_string] = $db_start_time_key;
        }
    }

    $horarioDataRaw = $oReporte->getHorariosFiltrados();

    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); 

    // --- Estilos y Definiciones ---
    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];
    $days_of_week = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

    // --- Función para renderizar UN SOLO TURNO de la tabla de horario ---
    $renderScheduleTable = function(Worksheet $sheet, $turnoNombre, $franjas, &$currentRow, $gridData) use ($days_of_week, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
        $startRowForTurno = $currentRow;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", mb_strtoupper($turnoNombre, 'UTF-8'));
        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleSubheaderTitle);
        $currentRow++;
        $sheet->setCellValue('A'.$currentRow, 'Hora');
        $col = 'B';
        foreach ($days_of_week as $day) { $sheet->setCellValue($col++.$currentRow, $day); }
        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleTableHeader);
        $currentRow++;

        uksort($franjas, 'strnatcmp');
        foreach ($franjas as $displaySlot => $dbStartTimeKey) {
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
        $sheet->getStyle("A".($startRowForTurno+1).":G".($currentRow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $currentRow++; 
    };
    
    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $dataGroupedByTrayecto = [];
        foreach ($horarioDataRaw as $item) {
            $dataGroupedByTrayecto[$item['uc_trayecto']][] = $item;
        }
        ksort($dataGroupedByTrayecto);

        foreach($dataGroupedByTrayecto as $trayecto_id => $data_for_trayecto) {
            $sheet = new Worksheet($spreadsheet, "Trayecto " . $trayecto_id);
            $spreadsheet->addSheet($sheet);
            $sheet->getColumnDimension('A')->setWidth(18);
            foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(30); }
            $currentRow = 1;

            $horariosPorSeccion = [];
            foreach ($data_for_trayecto as $item) {
                $horariosPorSeccion[$item['sec_codigo']][] = $item;
            }
            ksort($horariosPorSeccion);
            
            foreach($horariosPorSeccion as $seccionCodigo => $horarioData) {
                
                $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "Horario de la Sección: " . $seccionCodigo);
                $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
                $currentRow += 2;

                $turnosActivos = [];
                foreach ($horarioData as $item) {
                    $horaClase = new DateTime($item['hor_horainicio']);
                    foreach ($turnos as $turno) {
                        if ($horaClase >= new DateTime($turno['tur_horaInicio']) && $horaClase < new DateTime($turno['tur_horaFin'])) {
                            $turnosActivos[ucfirst(strtolower($turno['tur_nombre']))] = true;
                        }
                    }
                }

                $gridData = [];
                foreach ($horarioData as $item) {
                    $dia = ucfirst(strtolower(trim($item['hor_dia'])));
                    if ($dia == 'Miercoles') $dia = 'Miércoles';
                    if ($dia == 'Sabado') $dia = 'Sábado';
                    $horaInicioBD = trim($item['hor_horainicio']);
                    $cell_content = [$item['uc_nombre'], "Aula: " . $item['esp_codigo']];
                    if (!empty($item['NombreCompletoDocente'])) { $cell_content[] = $item['NombreCompletoDocente']; }
                    if(isset($gridData[$dia][$horaInicioBD])) {
                        $gridData[$dia][$horaInicioBD] .= "\n---\n" . implode("\n\n", $cell_content);
                    } else {
                        $gridData[$dia][$horaInicioBD] = implode("\n\n", $cell_content);
                    }
                }
                
                foreach (array_keys($turnosActivos) as $nombreTurno) {
                    $franjasDelTurno = $todas_las_franjas[$nombreTurno] ?? [];
                    if (!empty($franjasDelTurno)) {
                        $renderScheduleTable($sheet, $nombreTurno, $franjasDelTurno, $currentRow, $gridData);
                    }
                }
                $currentRow++; 
            }
        }
    }

    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Reporte_Horarios_Seccion.xlsx";
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