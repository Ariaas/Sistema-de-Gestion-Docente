<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('vendor/autoload.php');
// Asegúrate de que la ruta al modelo sea correcta
require_once('model/reportes/raulario.php'); 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

// --- INICIO FUNCIÓN HELPER ---
// (Recomendación: Mover esto a un archivo 'helpers.php' e incluirlo)
function abreviarNombreLargo($nombre, $longitudMaxima = 25)
{
    if (mb_strlen($nombre) <= $longitudMaxima) {
        return $nombre;
    }
    $palabrasExcluidas = ['de', 'y', 'a', 'del', 'la', 'los', 'las', 'en'];
    $partes = explode(' ', $nombre);
    $numeral = '';
    $ultimoTermino = end($partes);
    $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
    if (in_array(strtoupper($ultimoTermino), $romanos)) {
        $numeral = ' ' . array_pop($partes);
    }
    $iniciales = [];
    foreach ($partes as $palabra) {
        if (!in_array(strtolower($palabra), $palabrasExcluidas) && !empty($palabra)) {
            $iniciales[] = strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'));
        }
    }
    return implode('', $iniciales) . $numeral;
}
// --- FIN FUNCIÓN HELPER ---


$oAulario = new AularioReport();

if (isset($_POST['generar_aulario_report'])) {

    // --- 1. VALIDACIÓN DE ENTRADA ---
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';
    $espacio_filtrado = $_POST['espacio_id'] ?? '';

    $esIntensivo = strtolower($ani_tipo) === 'intensivo';
    
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año y Tipo.");
    }
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oAulario->setAnio($anio);
    $oAulario->setAniTipo($ani_tipo);
    $oAulario->setFase($fase);
    $oAulario->setEspacio($espacio_filtrado);

    // --- 2. GENERACIÓN DE FRANJAS HORARIAS (Lógica de rseccion) ---

    $turnos = $oAulario->getTurnosCompletos();
    $bloques_personalizados = $oAulario->getBloquesPersonalizados();
    $bloques_eliminados = $oAulario->getBloquesEliminados();
    $slot_duration_minutes = 40; // Asumes 40 min, ajústalo si es necesario

    $morningSlots = [];
    $afternoonSlots = [];
    $nightSlots = [];

    // Define las horas de inicio de los turnos
    $afternoon_start_time = '13:00:00';
    $night_start_time = '18:00:00';

    // Genera franjas estándar
    foreach ($turnos as $turno) {
        $franjas_del_turno = [];
        $hora_actual = new DateTime($turno['tur_horaInicio']);
        $hora_fin_turno = new DateTime($turno['tur_horaFin']);

        while ($hora_actual < $hora_fin_turno) {
            $franja_inicio = clone $hora_actual;
            $hora_actual->modify('+' . $slot_duration_minutes . ' minutes');
            $franja_fin = ($hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;
            
            if ($franja_inicio < $franja_fin) {
                $db_start_time_key = $franja_inicio->format('H:i:s');
                $display_string = $franja_inicio->format('h:i A') . ' a ' . $franja_fin->format('h:i A');
                $franjas_del_turno[$db_start_time_key] = $display_string;
            }
        }

        $hora_inicio_turno_str = (new DateTime($turno['tur_horaInicio']))->format('H:i:s');

        if ($hora_inicio_turno_str < $afternoon_start_time) {
            $morningSlots += $franjas_del_turno;
        } elseif ($hora_inicio_turno_str < $night_start_time) {
            $afternoonSlots += $franjas_del_turno;
        } else {
            $nightSlots += $franjas_del_turno;
        }
    }

    // Agrega bloques personalizados
    foreach ($bloques_personalizados as $bloque) {
        $hora_inicio = new DateTime($bloque['tur_horainicio']);
        $hora_fin = new DateTime($bloque['tur_horafin']);
        $db_start_time_key = $hora_inicio->format('H:i:s');
        $display_string = $hora_inicio->format('h:i A') . ' a ' . $hora_fin->format('h:i A');
        
        $hora_inicio_str = $hora_inicio->format('H:i:s');
        
        if ($hora_inicio_str < $afternoon_start_time) {
            $morningSlots[$db_start_time_key] = $display_string;
        } elseif ($hora_inicio_str < $night_start_time) {
            $afternoonSlots[$db_start_time_key] = $display_string;
        } else {
            $nightSlots[$db_start_time_key] = $display_string;
        }
    }

    // Elimina bloques eliminados
    foreach ($bloques_eliminados as $hora_eliminada) {
        $hora_key = (new DateTime($hora_eliminada))->format('H:i:s');
        unset($morningSlots[$hora_key]);
        unset($afternoonSlots[$hora_key]);
        unset($nightSlots[$hora_key]);
    }

    // Ordena las franjas de cada turno
    ksort($morningSlots);
    ksort($afternoonSlots);
    ksort($nightSlots);

    // Crea un mapa global de todas las franjas disponibles
    $allAvailableSlots = [
        'morning' => $morningSlots,
        'afternoon' => $afternoonSlots,
        'night' => $nightSlots
    ];

    $shiftNameMapping = [
        'mañana' => 'morning',
        'tarde' => 'afternoon',
        'noche' => 'night'
    ];

    // --- 3. PROCESAMIENTO DE DATOS Y EXCEL ---
    $horarioDataRaw = $oAulario->getAulariosFiltrados();
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0);

    // Define estilos
    $styleMainTitle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleDayHeader = ['font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF5B9BD5']], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleTimeColumn = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleScheduleCell = ['font' => ['size' => 8], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $days_of_week = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miércoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sábado' => 'Sábado'];

    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        // Agrupa los datos por aula (cada aula será una hoja)
        $dataGroupedByAula = [];
        foreach ($horarioDataRaw as $item) {
            $dataGroupedByAula[$item['esp_codigo']][] = $item;
        }
        ksort($dataGroupedByAula);

        // --- 4. BUCLE PRINCIPAL POR AULA (HOJA) ---
        foreach ($dataGroupedByAula as $espacioCodigo => $horarioData) {
            
            $nombreHoja = preg_replace('/[^A-Za-z0-9\-\. ]/', '', $espacioCodigo);
            if (empty($nombreHoja)) $nombreHoja = 'Aula_' . substr(md5($espacioCodigo), 0, 8);
            $nombreHoja = substr($nombreHoja, 0, 31);
            
            $sheet = new Worksheet($spreadsheet, $nombreHoja);
            $spreadsheet->addSheet($sheet);
            $sheet->getColumnDimension('A')->setAutoSize(true);
            foreach (range('B', 'G') as $col) $sheet->getColumnDimension($col)->setWidth(25); 

            $currentRow = 1;
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", mb_strtoupper($espacioCodigo, 'UTF-8'));
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleMainTitle);
            $currentRow += 2;

            // --- INICIO LÓGICA DE FILTRADO DE TURNO ---

            // 4.1. Procesa los datos del aula para agrupar secciones y crear la cuadrícula
            $horarioProcesado = [];
            $earliestClassTime = '23:59:59'; // Para encontrar el turno principal
            foreach ($horarioData as $item) {
                $clave = $item['hor_dia'] . '|' . $item['hor_horainicio'] . '|' . $item['uc_codigo'] . '|' . ($item['doc_cedula'] ?? 'N/A') . '|' . ($item['subgrupo'] ?? '');
                if (!isset($horarioProcesado[$clave])) {
                    $horarioProcesado[$clave] = $item;
                    $horarioProcesado[$clave]['sec_codigo_list'] = [];
                    
                    // Encuentra la hora más temprana para definir el turno principal
                    $classStartTime = (new DateTime($item['hor_horainicio']))->format('H:i:s');
                    if ($classStartTime < $earliestClassTime) {
                        $earliestClassTime = $classStartTime;
                    }
                }
                $horarioProcesado[$clave]['sec_codigo_list'][] = $item['sec_codigo_formatted'];
            }

            // 4.2. Define el turno principal del AULA
            $mainShiftName = 'mañana';
            if ($earliestClassTime >= $night_start_time) {
                $mainShiftName = 'noche';
            } elseif ($earliestClassTime >= $afternoon_start_time) {
                $mainShiftName = 'tarde';
            }
            $mainShiftMapped = $shiftNameMapping[$mainShiftName] ?? 'morning';

            // 4.3. Crea la cuadrícula y encuentra las franjas OCUPADAS
            $gridData = [];
            $occupiedDbKeys = []; // Franjas que tienen al menos una clase
            foreach ($horarioProcesado as $item) {
                $dia_key_from_db = strtolower(trim(str_replace('é', 'e', $item['hor_dia'])));
                $dia_key = $day_map[$dia_key_from_db] ?? ucfirst($dia_key_from_db);
                $horaInicio = new DateTime($item['hor_horainicio']);
                $horaFinClase = new DateTime($item['hor_horafin']);
                $horaInicioKey = $horaInicio->format('H:i:s');
                
                if (!isset($gridData[$dia_key][$horaInicioKey])) {
                    $gridData[$dia_key][$horaInicioKey] = [];
                }
                $gridData[$dia_key][$horaInicioKey][] = $item;

                // Marca todas las franjas que esta clase ocupa
                foreach ($allAvailableSlots as $shiftSlots) {
                    foreach ($shiftSlots as $dbStartTimeKey => $displayString) {
                        $slotStart = new DateTime($dbStartTimeKey);
                        // Comprueba si la franja (slot) se solapa con la clase
                        if ($slotStart >= $horaInicio && $slotStart < $horaFinClase) {
                            $occupiedDbKeys[$dbStartTimeKey] = true;
                        }
                    }
                }
            }

            // 4.4. Construye el MAPA de franjas a MOSTRAR (Turno principal + Ocupadas)
            $slotsToDisplayMap = [];
            foreach ($allAvailableSlots as $shift => $timeSlots) {
                $isMainShift = ($shift === $mainShiftMapped);
                foreach ($timeSlots as $dbStartTimeKey => $displaySlot) {
                    $isOccupied = isset($occupiedDbKeys[$dbStartTimeKey]);
                    // Muestra la franja si (es del turno principal) O (está ocupada)
                    if ($isOccupied || $isMainShift) {
                        $slotsToDisplayMap[$dbStartTimeKey] = $displaySlot;
                    }
                }
            }
            ksort($slotsToDisplayMap); // Ordena el mapa de franjas por hora
            
            // --- FIN LÓGICA DE FILTRADO DE TURNO ---


            // 4.5. Dibuja la cabecera de la tabla
            $headerRow = $currentRow;
            $sheet->setCellValue('A' . $headerRow, 'Hora');
            $col = 'B';
            foreach ($days_of_week as $day) {
                $sheet->setCellValue($col++ . $headerRow, mb_strtoupper($day));
            }
            $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray($styleDayHeader);
            $currentRow++;

            // 4.6. Bucle principal de filas (HORAS) - Modificado
            $celdasOcupadas = [];
            $slotsKeys = array_keys($slotsToDisplayMap); // Array de claves de franjas visibles

            foreach ($slotsToDisplayMap as $dbStartTimeKey => $displaySlot) {
            
                // Dibuja la columna de la hora
                $sheet->setCellValue('A' . $currentRow, $displaySlot);
                $sheet->getStyle('A' . $currentRow)->applyFromArray($styleTimeColumn);

                $colNum = 1;
                foreach ($days_of_week as $day) {
                    $cellAddress = chr(65 + $colNum) . $currentRow;
                    $sheet->getStyle($cellAddress)->applyFromArray($styleScheduleCell);

                    if (isset($celdasOcupadas[$cellAddress])) {
                        $colNum++;
                        continue;
                    }

                    $clases = $gridData[$day][$dbStartTimeKey] ?? null;
                    if ($clases) {
                        $richText = new RichText();
                        
                        foreach ($clases as $idx => $clase) {
                            if ($idx > 0) $richText->createText("\n- - - - - - - - -\n");
                            
                            $secciones = implode(", ", array_unique($clase['sec_codigo_list']));
                            $uc_nombre = $clase['uc_nombre'] ?? null;
                            $ucAbreviada = $uc_nombre ? abreviarNombreLargo($uc_nombre) : '(Sin UC)';
                            $subgrupoTexto = $clase['subgrupo'] ? ' (Grupo: ' . $clase['subgrupo'] . ')' : '';

                            $ucPart = $richText->createTextRun($ucAbreviada . $subgrupoTexto);
                            $ucPart->getFont()->setBold(true);

                            $docente = $clase['NombreCompletoDocente'] ?? '(Sin Docente)';
                            $richText->createText("\n" . $secciones . "\n" . $docente);
                        }

                        $sheet->setCellValue($cellAddress, $richText);

                        // --- CÁLCULO DE SPAN (MERGE) MODIFICADO ---
                        // (Usa la lógica de rseccion, que cuenta las franjas visibles)
                        $primeraClase = $clases[0];
                        $horaInicioClase = new DateTime($primeraClase['hor_horainicio']);
                        $horaFinClase = new DateTime($primeraClase['hor_horafin']);
                        
                        $span = 0;
                        $currentKeyIndex = array_search($dbStartTimeKey, $slotsKeys);

                        if ($currentKeyIndex !== false) {
                            for ($i = $currentKeyIndex; $i < count($slotsKeys); $i++) {
                                $slotKey = $slotsKeys[$i];
                                $displayString = $slotsToDisplayMap[$slotKey];
                                $parts = explode(' a ', $displayString);
                                $slotStart = new DateTime(date('H:i:s', strtotime($parts[0])));
                                
                                // Si la franja comienza después de que termine la clase, paramos
                                if ($slotStart >= $horaFinClase) {
                                    break;
                                }
                                
                                // Si la clase comenzó antes o en esta franja, contamos
                                if ($horaInicioClase <= $slotStart) {
                                    $span++;
                                }
                            }
                        }
                        if ($span < 1) $span = 1;

                        if ($span > 1) {
                            $endRow = $currentRow + $span - 1;
                            $sheet->mergeCells($cellAddress . ':' . chr(65 + $colNum) . $endRow);
                            for ($i = 1; $i < $span; $i++) {
                                $celdasOcupadas[chr(65 + $colNum) . ($currentRow + $i)] = true;
 }
                            }
                    }
                    $sheet->getStyle($cellAddress)->applyFromArray($styleScheduleCell);
                    $colNum++;
                }
                
                // Ajusta la altura de la fila
                $maxLines = 1;
                foreach ($days_of_week as $day) {
                    $clases = $gridData[$day][$dbStartTimeKey] ?? null;
                    if ($clases) {
                        foreach($clases as $clase) {
                            $lineCount = 3; // Asume 3 líneas (Materia, Secciones, Docente)
                            $maxLines = max($maxLines, $lineCount * count($clases));
                        }
                    }
                }
                $rowHeight = max(30, $maxLines * 16);
                $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
                
                $currentRow++;
            }
        }
    }

    // --- 5. SALIDA DEL ARCHIVO ---
    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Reporte_Aulario";
    if ($esIntensivo) {
        $outputFileName .= "_Intensivo";
    }
    $outputFileName .= ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} elseif (isset($_POST['action']) && $_POST['action'] === 'obtener_espacios_por_anio') {
    // --- LÓGICA AJAX (Sin cambios) ---
    header('Content-Type: application/json');
    
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    if (empty($anio) || empty($ani_tipo)) {
        echo json_encode(['success' => false, 'message' => 'Año o tipo no válido']);
        exit;
    }
    
    $espacios = $oAulario->getEspaciosPorAnio($anio, $ani_tipo);
    echo json_encode(['success' => true, 'espacios' => $espacios]);
    exit;
} else {
    // --- CARGA DE PÁGINA INICIAL (Sin cambios) ---
    $listaAnios = $oAulario->getAniosActivos();
    $listaFases = $oAulario->getFases();
    $listaEspacios = $oAulario->getEspacios();
    require_once("views/reportes/raulario.php");
}
?>