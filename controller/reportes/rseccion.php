<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once ('vendor/autoload.php');
require_once ('model/reportes/rseccion.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

    $turnos = $oReporte->getTurnosCompletos();
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
            $display_string = $franja_inicio->format('H:i') . ' a ' . $franja_fin->format('H:i');
            $todas_las_franjas_por_turno[$nombre_turno][$display_string] = $db_start_time_key;
        }
    }

    $horarioDataRaw = $oReporte->getHorariosFiltrados();
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); 

    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleScheduleCell = ['font' => ['size' => 10], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];

    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $dataGroupedByTrayecto = [];
        foreach ($horarioDataRaw as $item) { $dataGroupedByTrayecto[$item['uc_trayecto']][] = $item; }
        ksort($dataGroupedByTrayecto);

        foreach($dataGroupedByTrayecto as $trayecto_id => $data_for_trayecto) {
            $sheet = new Worksheet($spreadsheet, "Trayecto " . $trayecto_id);
            $spreadsheet->addSheet($sheet);
            $currentRow = 1;

            $horariosPorSeccion = [];
            foreach ($data_for_trayecto as $item) { $horariosPorSeccion[$item['sec_codigo']][] = $item; }
            ksort($horariosPorSeccion);
            
            foreach($horariosPorSeccion as $seccionCodigo => $horarioData) {
                $prefijo = (substr($seccionCodigo, 0, 1) === '3' || substr($seccionCodigo, 0, 1) === '4') ? 'IIN' : 'IN';
                
                $gridData = [];
                $activeShifts = [];
                $locationsPerDay = [];
                $activeDaysOrder = [];

                foreach($horarioData as $item) {
                    $dia_key_from_db = strtolower(trim(str_replace('é', 'e', $item['hor_dia'])));
                    $dia_key = $day_map[$dia_key_from_db] ?? ucfirst($dia_key_from_db);
                    
                    $locationsPerDay[$dia_key][$item['esp_codigo']] = $item['esp_tipo'];
                    $activeDaysOrder[$dia_key] = $dayOrder[$dia_key_from_db] ?? 7;

                    $horaInicio = new DateTime($item['hor_horainicio']);
                    $horaFin = new DateTime($item['hor_horafin']);
                    $diffMinutes = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 60;
                    $bloques_span = round($diffMinutes / $slot_duration_minutes);
                    if ($bloques_span < 1) $bloques_span = 1;
                    
                    $gridData[$dia_key][$horaInicio->format('H:i:s')] = [
                        'uc' => $item['uc_nombre'],
                        'docente' => $item['NombreCompletoDocente'],
                        'espacio' => $item['esp_codigo'],
                        'tipo' => $item['esp_tipo'],
                        'span' => $bloques_span
                    ];

                    foreach ($turnos as $turno) {
                        if ($horaInicio >= new DateTime($turno['tur_horaInicio']) && $horaInicio < new DateTime($turno['tur_horaFin'])) {
                            $activeShifts[ucfirst(strtolower($turno['tur_nombre']))] = true;
                        }
                    }
                }

                uksort($activeDaysOrder, function($a, $b) use ($day_map) {
                    $order = array_flip(array_values($day_map));
                    return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
                });
                $activeDays = array_keys($activeDaysOrder);
                
                if(empty($activeDays)) { $currentRow++; continue; }

                foreach (array_keys($activeShifts) as $shiftName) {
                    $shiftTimeSlots = $todas_las_franjas_por_turno[$shiftName] ?? [];
                    if(empty($shiftTimeSlots)) continue;

                    $numDataColumns = count($activeDays);
                    $sheet->mergeCells("A{$currentRow}:" . chr(65 + $numDataColumns) . $currentRow);
                    $sheet->setCellValue("A{$currentRow}", "Seccion " . $prefijo . $seccionCodigo);
                    $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
                    $sheet->getRowDimension($currentRow)->setRowHeight(20);
                    $currentRow++;

                    $headerRow = $currentRow;
                    $sheet->setCellValue('A' . $headerRow, 'Hora');
                    $sheet->getColumnDimension('A')->setWidth(20);
                    $currentCol = 1;
                    
                    foreach($activeDays as $day) {
                        $colLetter = chr(65 + $currentCol);
                        $headerText = mb_strtoupper($day);
                        $isSingleLocation = count($locationsPerDay[$day]) === 1;
                        $singleLocationType = $isSingleLocation ? current($locationsPerDay[$day]) : '';
                        
                        if($isSingleLocation && $singleLocationType !== 'Laboratorio') {
                            $headerText .= "\n" . key($locationsPerDay[$day]);
                        }
                        
                        $sheet->setCellValue($colLetter . $headerRow, $headerText);
                        $sheet->getColumnDimension($colLetter)->setWidth(25);
                        $currentCol++;
                    }
                    $sheet->getStyle("A{$headerRow}:" . chr(64 + $currentCol) . $headerRow)->applyFromArray($styleTableHeader);
                    $currentRow++;
                    
                    $celdasOcupadas = [];
                    foreach ($shiftTimeSlots as $displaySlot => $dbStartTimeKey) {
                        $sheet->setCellValue('A'.$currentRow, $displaySlot);
                        $sheet->getStyle('A'.$currentRow)->applyFromArray($styleTimeSlot);
                        
                        $colNum = 1;
                        foreach ($activeDays as $day) {
                            $cellAddress = chr(65 + $colNum) . $currentRow;
                            if(isset($celdasOcupadas[$cellAddress])) {
                                $colNum++;
                                continue;
                            }
                            
                            $clase = $gridData[$day][$dbStartTimeKey] ?? null;
                            if ($clase) {
                                $cellContent = $clase['uc'] . "\n" . $clase['docente'];
                                $isSingleLocation = count($locationsPerDay[$day]) === 1;
                                $singleLocationType = $isSingleLocation ? current($locationsPerDay[$day]) : '';
                                
                                if ($clase['tipo'] === 'Laboratorio' || !$isSingleLocation || $singleLocationType === 'Laboratorio') {
                                    $cellContent .= "\n" . $clase['espacio'];
                                }

                                $sheet->setCellValue($cellAddress, $cellContent);

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