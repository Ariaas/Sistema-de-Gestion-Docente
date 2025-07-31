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

function formatAndSortSections($sectionsArray, $wrapAfter = 3) {
    // ... (esta función no cambia)
    if (empty($sectionsArray)) return '';
    $formattedSections = [];
    foreach($sectionsArray as $sec_codigo){
        $trayectoNum = substr($sec_codigo, 0, 1);
        if (in_array($trayectoNum, ['0', '1', '2'])) {
            $formattedSections[] = 'IN' . $sec_codigo;
        } elseif (in_array($trayectoNum, ['3', '4'])) {
            $formattedSections[] = 'IIN' . $sec_codigo;
        } else {
            $formattedSections[] = $sec_codigo;
        }
    }
    sort($formattedSections);
    $total = count($formattedSections);
    $output = '';
    foreach ($formattedSections as $index => $section) {
        $output .= $section;
        if ($index < $total - 1) {
            if (($index + 1) % $wrapAfter === 0) { $output .= "\n"; } 
            else { $output .= ' - '; }
        }
    }
    return $output;
}

$oReporte = new SeccionReport();

// Lógica para generar bloques de horario dinámicamente (no cambia)
$turnos_db = $oReporte->getTurnos();
$TODOS_LOS_BLOQUES_MANANA = [];
$TODOS_LOS_BLOQUES_TARDE = [];
function generarBloques($horaInicio, $horaFin) {
    $bloques = [];
    try {
        $tiempoActual = new DateTime($horaInicio);
        $tiempoFin = new DateTime($horaFin);
        while ($tiempoActual < $tiempoFin) {
            $inicioBloque = clone $tiempoActual;
            $tiempoActual->add(new DateInterval('PT40M'));
            if ($tiempoActual > $tiempoFin) { $finBloque = $tiempoFin; }
            else { $finBloque = clone $tiempoActual; }
            $formatoDisplay = $inicioBloque->format('h:i a') . " a " . $finBloque->format('h:i a');
            $formatoDBKey = $inicioBloque->format('H:i:s');
            $bloques[$formatoDisplay] = $formatoDBKey;
        }
    } catch (Exception $e) {}
    return $bloques;
}
foreach ($turnos_db as $turno) {
    if (stripos($turno['tur_nombre'], 'mañana') !== false) {
        $TODOS_LOS_BLOQUES_MANANA = generarBloques($turno['tur_horaInicio'], $turno['tur_horaFin']);
    } elseif (stripos($turno['tur_nombre'], 'tarde') !== false) {
        $TODOS_LOS_BLOQUES_TARDE = generarBloques($turno['tur_horaInicio'], $turno['tur_horaFin']);
    }
}

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

    // --- ESTILOS MÁS COMPACTOS ---
    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSubheaderTitle = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 8], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD0E4F5']]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 7], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleScheduleCell = ['font' => ['size' => 7], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];
    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    $renderScheduleTable = function(Worksheet $sheet, $title_suffix, $slots_to_render, &$currentRow, $seccionTitulo, $gridData) use ($days_of_week, $styleHeaderTitle, $styleSubheaderTitle, $styleTableHeader, $styleTimeSlot, $styleScheduleCell) {
        if (empty($slots_to_render)) return;
        $startRow = $currentRow;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "Horario: " . $seccionTitulo);
        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($currentRow)->setRowHeight(-1);
        $currentRow++;
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", $title_suffix);
        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleSubheaderTitle);
        $currentRow++;
        $sheet->setCellValue('A'.$currentRow, 'Hora');
        $col = 'B';
        foreach ($days_of_week as $day) { $sheet->setCellValue($col++.$currentRow, $day); }
        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleTableHeader);
        $currentRow++;

        foreach ($slots_to_render as $displaySlot => $dbStartTimeKey) {
            $sheet->getRowDimension($currentRow)->setRowHeight(48); // Altura de fila reducida
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
        // ...
    } else {
        $dataGroupedByTrayecto = [];
        foreach ($horarioDataRaw as $item) {
            $dataGroupedByTrayecto[$item['uc_trayecto']][] = $item;
        }

        foreach($dataGroupedByTrayecto as $trayecto_id => $data_for_trayecto) {
            $sheet = new Worksheet($spreadsheet, "Trayecto " . $trayecto_id);
            $spreadsheet->addSheet($sheet);
            // --- ANCHO DE CELDAS REDUCIDO ---
            $sheet->getColumnDimension('A')->setWidth(14);
            foreach (range('B', 'G') as $col) { $sheet->getColumnDimension($col)->setWidth(20); }
            $currentRow = 1;
            
            $seccionesConFirma = []; // ... (lógica de agrupación sin cambios)
            foreach ($data_for_trayecto as $item) {
                $sec_codigo = $item['sec_codigo'];
                if (!isset($seccionesConFirma[$sec_codigo])) {
                    $seccionesConFirma[$sec_codigo] = ['horario_items' => [], 'firma' => ''];
                }
                $seccionesConFirma[$sec_codigo]['horario_items'][] = $item;
                $firma_part = $item['uc_nombre'].'-'.$item['hor_dia'].'-'.$item['hor_horainicio'];
                $seccionesConFirma[$sec_codigo]['firma_parts'][] = $firma_part;
            }
            foreach ($seccionesConFirma as &$data) {
                sort($data['firma_parts']);
                $data['firma'] = implode('|', $data['firma_parts']);
            }
            unset($data);
            $gruposPorFirma = [];
            foreach ($seccionesConFirma as $sec_codigo => $data) {
                $key = $data['firma'];
                if ($key === '') { $key = 'no-unida-'.$sec_codigo; }
                $gruposPorFirma[$key]['secciones'][] = $sec_codigo;
                if (!isset($gruposPorFirma[$key]['horario_data'])) {
                    $gruposPorFirma[$key]['horario_data'] = $data['horario_items'];
                }
            }
            
            foreach($gruposPorFirma as $grupo) {
                $horarioData = $grupo['horario_data'];
                $seccionTitulo = formatAndSortSections($grupo['secciones']);

                $gridData = [];
                $tieneClasesManana = false;
                $tieneClasesTarde = false;

                foreach ($horarioData as $item) {
                    $dia = ucfirst(strtolower(trim($item['hor_dia'])));
                    $horaInicioBD = trim($item['hor_horainicio']);
                    
                    // --- NUEVA LÓGICA PARA FORMATEAR EL ESPACIO ---
                    $tipo_espacio = ($item['esp_tipo'] === 'Laboratorio') ? 'Lab:' : 'Aula:';
                    $esp_codigo = ($item['esp_tipo'] === 'Laboratorio') 
                                ? $item['esp_numero'] . ' - ' . $item['esp_edificio'] 
                                : $item['esp_edificio'] . ' - ' . $item['esp_numero'];
                    
                    $cell_content = [$item['uc_nombre'], $tipo_espacio . " " . $esp_codigo];
                    if (!empty($item['NombreCompletoDocente'])) { $cell_content[] = $item['NombreCompletoDocente']; }
                    $gridData[$dia][$horaInicioBD] = implode("\n\n", $cell_content);
                    
                    if (strcmp($horaInicioBD, "13:00:00") < 0) {
                        $tieneClasesManana = true;
                    } else {
                        $tieneClasesTarde = true;
                    }
                }
                
                if ($tieneClasesManana) {
                    $renderScheduleTable($sheet, "MAÑANA", $TODOS_LOS_BLOQUES_MANANA, $currentRow, $seccionTitulo, $gridData);
                }
                if ($tieneClasesTarde) {
                    $renderScheduleTable($sheet, "TARDE", $TODOS_LOS_BLOQUES_TARDE, $currentRow, $seccionTitulo, $gridData);
                }
                if (!$tieneClasesManana && !$tieneClasesTarde && !empty($grupo['secciones'])) {
                    $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", "La sección " . $seccionTitulo . " no tiene horario asignado.");
                    $currentRow += 2;
                }
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