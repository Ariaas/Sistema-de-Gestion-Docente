<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('vendor/autoload.php');
require_once('model/reportes/raulario.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

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

$oAulario = new AularioReport();

if (isset($_POST['generar_aulario_report'])) {

    // Separar año y tipo del valor combinado
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';
    $espacio_filtrado = $_POST['espacio_id'] ?? '';

    // Verificar si es intensivo
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';
    
    // Validar campos requeridos
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año y Tipo.");
    }
    
    // Solo requerir fase si NO es intensivo
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oAulario->setAnio($anio);
    $oAulario->setAniTipo($ani_tipo);
    $oAulario->setFase($fase);
    $oAulario->setEspacio($espacio_filtrado);

    $turnos = $oAulario->getTurnosCompletos();
    $slot_duration_minutes = 40;
    $todas_las_franjas_por_turno = [];
    $franjas_ordenadas_con_turno = [];

    $orden_turnos = ['mañana' => 1, 'tarde' => 2, 'noche' => 3];

    usort($turnos, function ($a, $b) use ($orden_turnos) {
        $nombre_a = strtolower($a['tur_nombre']);
        $nombre_b = strtolower($b['tur_nombre']);
        return ($orden_turnos[$nombre_a] ?? 99) <=> ($orden_turnos[$nombre_b] ?? 99);
    });

    foreach ($turnos as $turno) {
        $nombre_turno = ucfirst(strtolower($turno['tur_nombre']));
        $todas_las_franjas_por_turno[$nombre_turno] = [];
        $hora_actual = new DateTime($turno['tur_horaInicio']);
        $hora_fin_turno = new DateTime($turno['tur_horaFin']);

        while ($hora_actual < $hora_fin_turno) {
            $franja_inicio = clone $hora_actual;
            $hora_actual->modify('+' . $slot_duration_minutes . ' minutes');
            $franja_fin = ($hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;

            if ($franja_inicio < $franja_fin) {
                $db_start_time_key = $franja_inicio->format('H:i:s');
                $display_string = $franja_inicio->format('h:i A') . ' a ' . $franja_fin->format('h:i A');

                $todas_las_franjas_por_turno[$nombre_turno][$display_string] = $db_start_time_key;
                $franjas_ordenadas_con_turno[] = [
                    'display' => $display_string,
                    'db_key' => $db_start_time_key,
                    'turno' => $nombre_turno
                ];
            }
        }
    }

    $horarioDataRaw = $oAulario->getAulariosFiltrados();
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0);

    $styleMainTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleDayHeader = ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF5B9BD5']], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleTimeColumn = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $days_of_week = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miércoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sábado' => 'Sábado'];

    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $dataGroupedByAula = [];
        foreach ($horarioDataRaw as $item) {
            $dataGroupedByAula[$item['esp_codigo']][] = $item;
        }
        ksort($dataGroupedByAula);

        foreach ($dataGroupedByAula as $espacioCodigo => $horarioData) {
            // Sanitizar el nombre de la hoja
            $nombreHoja = preg_replace('/[^A-Za-z0-9\-\. ]/', '', $espacioCodigo);
            // Si el nombre queda vacío después de sanitizar, usar un nombre por defecto
            if (empty($nombreHoja)) {
                $nombreHoja = 'Aula_' . substr(md5($espacioCodigo), 0, 8);
            }
            // Limitar a 31 caracteres (límite de Excel)
            $nombreHoja = substr($nombreHoja, 0, 31);
            
            $sheet = new Worksheet($spreadsheet, $nombreHoja);
            $spreadsheet->addSheet($sheet);
            $sheet->getColumnDimension('A')->setWidth(20);
            foreach (range('B', 'G') as $col) {
                $sheet->getColumnDimension($col)->setWidth(25);
            }

            $currentRow = 1;
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}")->setCellValue("A{$currentRow}", mb_strtoupper($espacioCodigo, 'UTF-8'));
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleMainTitle);
            $currentRow += 2;

            $horarioProcesado = [];
            foreach ($horarioData as $item) {
                $clave = $item['hor_dia'] . '|' . $item['hor_horainicio'] . '|' . $item['uc_codigo'] . '|' . ($item['doc_cedula'] ?? 'N/A') . '|' . ($item['subgrupo'] ?? '');
                if (!isset($horarioProcesado[$clave])) {
                    $horarioProcesado[$clave] = $item;
                    $horarioProcesado[$clave]['sec_codigo_list'] = [];
                }
                $horarioProcesado[$clave]['sec_codigo_list'][] = $item['sec_codigo_formatted'];
            }

            $gridData = [];
            foreach ($horarioProcesado as $item) {
                $dia_key_from_db = strtolower(trim(str_replace('é', 'e', $item['hor_dia'])));
                $dia_key = $day_map[$dia_key_from_db] ?? ucfirst($dia_key_from_db);
                $horaInicio = new DateTime($item['hor_horainicio']);
                // Almacenar múltiples clases en el mismo horario
                if (!isset($gridData[$dia_key][$horaInicio->format('H:i:s')])) {
                    $gridData[$dia_key][$horaInicio->format('H:i:s')] = [];
                }
                $gridData[$dia_key][$horaInicio->format('H:i:s')][] = $item;
            }

            $headerRow = $currentRow;
            $sheet->setCellValue('A' . $headerRow, 'Hora');
            $col = 'B';
            foreach ($days_of_week as $day) {
                $sheet->setCellValue($col++ . $headerRow, mb_strtoupper($day));
            }
            $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray($styleDayHeader);
            $currentRow++;

            $celdasOcupadas = [];
            foreach ($franjas_ordenadas_con_turno as $franja) {
                $displaySlot = $franja['display'];
                $dbStartTimeKey = $franja['db_key'];
                $nombreTurno = $franja['turno'];
                $nombreTurnoNormalizado = strtolower($nombreTurno);

               
                if ($nombreTurnoNormalizado === 'tarde' || $nombreTurnoNormalizado === 'noche') {
                    $filaTieneContenido = false;

                  
                    foreach ($days_of_week as $day) {
                        if (isset($gridData[$day][$dbStartTimeKey])) {
                            $filaTieneContenido = true;
                            break;
                        }
                    }

                   
                    if (!$filaTieneContenido) {
                        $estaOcupadaPorMerge = false;
                        for ($colNumCheck = 1; $colNumCheck <= count($days_of_week); $colNumCheck++) {
                            $cellAddressCheck = chr(65 + $colNumCheck) . $currentRow;
                            if (isset($celdasOcupadas[$cellAddressCheck])) {
                                $estaOcupadaPorMerge = true;
                                break;
                            }
                        }
                        if ($estaOcupadaPorMerge) {
                            $filaTieneContenido = true;
                        }
                    }

                    if (!$filaTieneContenido) {
                        continue;
                    }
                }
                

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
                        
                        // Procesar múltiples clases en el mismo horario
                        foreach ($clases as $idx => $clase) {
                            if ($idx > 0) {
                                $richText->createText("\n- - - - - - - - -\n");
                            }
                            
                            $secciones = implode(", ", array_unique($clase['sec_codigo_list']));
                            $ucAbreviada = abreviarNombreLargo($clase['uc_nombre']);
                            $subgrupoTexto = $clase['subgrupo'] ? ' (Grupo: ' . $clase['subgrupo'] . ')' : '';

                            $ucPart = $richText->createTextRun($ucAbreviada . $subgrupoTexto);
                            $ucPart->getFont()->setBold(true);

                            $docente = $clase['NombreCompletoDocente'] ?? '(Sin Docente)';
                            $richText->createText("\n" . $secciones . "\n" . $docente);
                        }

                        $sheet->setCellValue($cellAddress, $richText);

                        // Usar la primera clase para calcular el span
                        $primeraClase = $clases[0];
                        $horaInicioClase = new DateTime($primeraClase['hor_horainicio']);
                        $horaFinClase = new DateTime($primeraClase['hor_horafin']);

                        $minutosInicio = ($horaInicioClase->format('H') * 60) + $horaInicioClase->format('i');
                        $minutosFin = ($horaFinClase->format('H') * 60) + $horaFinClase->format('i');

                        $diffMinutes = $minutosFin - $minutosInicio;

                        $span = ceil($diffMinutes / $slot_duration_minutes);

                        if ($span < 1) {
                            $span = 1;
                        }

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
                $sheet->getRowDimension($currentRow)->setRowHeight(40);
                $currentRow++;
            }
        }
    }

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
    // Endpoint AJAX para obtener espacios filtrados por año
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
    $listaAnios = $oAulario->getAniosActivos();
    $listaFases = $oAulario->getFases();
    $listaEspacios = $oAulario->getEspacios();
    require_once("views/reportes/raulario.php");
}
