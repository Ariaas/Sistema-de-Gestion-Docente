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

    $turnos = $oAulario->getTurnosCompletos();
    $slot_duration_minutes = 40; 
    $todas_las_franjas_por_turno = [];

    foreach ($turnos as $turno) {
        $nombre_turno = ucfirst(strtolower($turno['tur_nombre']));
        $todas_las_franjas_por_turno[$nombre_turno] = [];
        $hora_actual = new DateTime($turno['tur_horaInicio']);
        $hora_fin_turno = new DateTime($turno['tur_horaFin']);
        while ($hora_actual < $hora_fin_turno) {
            $franja_inicio = clone $hora_actual;
            $hora_actual->modify('+' . $slot_duration_minutes . ' minutes');
            $franja_fin = ($hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;
            $db_start_time_key = $franja_inicio->format('H:i:s');
            $display_string = $franja_inicio->format('g:i a') . ' a ' . $franja_fin->format('g:i a');
            $todas_las_franjas_por_turno[$nombre_turno][$display_string] = $db_start_time_key;
        }
    }

    $horarioDataRaw = $oAulario->getAulariosFiltrados();
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); 
    
    $styleMainTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleShiftTitle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleDayHeader = ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF5B9BD5']], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleTimeColumn = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleScheduleCell = ['font' => ['size' => 10], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $days_of_week = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];

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
            $sheet->getColumnDimension('A')->setWidth(20);
            foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(25); }
            
            $currentRow = 1;
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", mb_strtoupper($espacioCodigo, 'UTF-8'));
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleMainTitle);
            $currentRow+=2;

            $gridData = [];
            $activeShifts = [];
            
            $horarioProcesado = [];
            foreach ($horarioData as $item) {
                $clave = $item['hor_dia'] . '|' . $item['hor_horainicio'] . '|' . $item['uc_nombre'] . '|' . $item['NombreCompletoDocente'];
                if (!isset($horarioProcesado[$clave])) {
                    $horarioProcesado[$clave] = $item;
                    $horarioProcesado[$clave]['sec_codigo_list'] = [];
                }
                $horarioProcesado[$clave]['sec_codigo_list'][] = $item['sec_codigo_formatted'];
            }

            foreach ($horarioProcesado as $item) {
                $dia_key_from_db = strtolower(trim(str_replace('é', 'e', $item['hor_dia'])));
                $dia_key = $day_map[$dia_key_from_db] ?? ucfirst($dia_key_from_db);
                
                $horaInicio = new DateTime($item['hor_horainicio']);
                foreach ($turnos as $turno) {
                    if ($horaInicio >= new DateTime($turno['tur_horaInicio']) && $horaInicio < new DateTime($turno['tur_horaFin'])) {
                        $activeShifts[ucfirst(strtolower($turno['tur_nombre']))] = true;
                    }
                }
                
                $horaFin = new DateTime($item['hor_horafin']);
                $diffMinutes = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 60;
                $bloques_span = round($diffMinutes / $slot_duration_minutes);
                if ($bloques_span < 1) $bloques_span = 1;
                
                $secciones = implode(",\n", array_unique($item['sec_codigo_list']));
                $cellContent = $secciones . "\n" . $item['uc_nombre'] . "\n" . $item['NombreCompletoDocente'];

                $gridData[$dia_key][$horaInicio->format('H:i:s')] = ['content' => $cellContent, 'span' => $bloques_span];
            }

            foreach(array_keys($activeShifts) as $nombreTurno) {
                $shiftTimeSlots = $todas_las_franjas_por_turno[$nombreTurno] ?? [];
                if(empty($shiftTimeSlots)) continue;

                $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", mb_strtoupper($nombreTurno, 'UTF-8'));
                $sheet->getStyle("A{$currentRow}")->applyFromArray($styleShiftTitle);
                $currentRow++;
                
                $headerRow = $currentRow;
                $sheet->setCellValue('A'.$headerRow, 'Hora');
                $col = 'B';
                foreach ($days_of_week as $day) { $sheet->setCellValue($col++.$headerRow, mb_strtoupper($day)); }
                $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray($styleDayHeader);
                $currentRow++;

                $celdasOcupadas = [];
                foreach ($shiftTimeSlots as $displaySlot => $dbStartTimeKey) {
                    $sheet->setCellValue('A'.$currentRow, $displaySlot);
                    $sheet->getStyle('A'.$currentRow)->applyFromArray($styleTimeColumn);
                    
                    $colNum = 1;
                    foreach ($days_of_week as $day) {
                        $cellAddress = chr(65 + $colNum) . $currentRow;
                        if(isset($celdasOcupadas[$cellAddress])) {
                            $colNum++; continue;
                        }
                        
                        $clase = $gridData[$day][$dbStartTimeKey] ?? null;
                        if ($clase) {
                            $sheet->setCellValue($cellAddress, $clase['content']);
                            if ($clase['span'] > 1) {
                                $endRow = $currentRow + $clase['span'] - 1;
                                $sheet->mergeCells($cellAddress . ':' . chr(65 + $colNum) . $endRow);
                                for ($i = 1; $i < $clase['span']; $i++) {
                                    $celdasOcupadas[chr(65 + $colNum) . ($currentRow + $i)] = true;
                                }
                            }
                        }
                        $sheet->getStyle($cellAddress)->applyFromArray($styleScheduleCell);
                        $colNum++;
                    }
                    $sheet->getRowDimension($currentRow)->setRowHeight(45);
                    $currentRow++;
                }
                $currentRow += 2;
            }
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