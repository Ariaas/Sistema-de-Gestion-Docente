<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('vendor/autoload.php');
require_once('model/reportes/rseccion.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

$oReporte = new SeccionReport();

if (isset($_POST['generar_seccion_report'])) {

    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';
    $trayecto_filtrado = $_POST['trayecto_id'] ?? '';

    
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';

    
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año y Tipo.");
    }

    
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oReporte->setAnio($anio);
    $oReporte->setAniTipo($ani_tipo);
    $oReporte->setFase($fase);
    $oReporte->setTrayecto($trayecto_filtrado);

    $turnos = $oReporte->getTurnosCompletos();
    $slot_duration_minutes = 40;

    $morningSlots = [];
    $afternoonSlots = [];
    $nightSlots = [];

    $afternoon_start_time = '13:00:00';
    $night_start_time = '18:00:00';

    foreach ($turnos as $turno) {
        $franjas_del_turno = [];
        $hora_actual = new DateTime($turno['tur_horaInicio']);
        $hora_fin_turno = new DateTime($turno['tur_horaFin']);

        while ($hora_actual < $hora_fin_turno) {
            $franja_inicio = clone $hora_actual;
            $hora_actual->modify('+' . $slot_duration_minutes . ' minutes');
            $franja_fin = ($hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;
            $db_start_time_key = $franja_inicio->format('H:i:s');
            $display_string = $franja_inicio->format('h:i A') . ' a ' . $franja_fin->format('h:i A');
            $franjas_del_turno[$db_start_time_key] = $display_string;
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

    ksort($morningSlots);
    ksort($afternoonSlots);
    ksort($nightSlots);

    $horarioDataRaw = $oReporte->getHorariosFiltrados();
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0);

    $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleTableHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleTimeSlot = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleScheduleCell = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miércoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sábado' => 'Sábado'];
    $day_order = array_flip(array_values($day_map));

    if (empty($horarioDataRaw)) {
        $sheet = new Worksheet($spreadsheet, "Sin Resultados");
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setCellValue('A1', 'No se encontraron horarios con los criterios seleccionados.');
    } else {
        $gruposDeSecciones = [];
        foreach ($horarioDataRaw as $item) {
            $groupId = !empty($item['grupo_union_id']) ? 'g_' . $item['grupo_union_id'] : 's_' . $item['sec_codigo'];
            $gruposDeSecciones[$groupId][] = $item;
        }
        ksort($gruposDeSecciones);

        foreach ($gruposDeSecciones as $groupId => $clasesDelGrupo) {

            $seccionesEnGrupo = [];
            foreach ($clasesDelGrupo as $clase) {
                $seccionesEnGrupo[$clase['sec_codigo']] = true;
            }
            $codigosDeSeccion = array_keys($seccionesEnGrupo);
            sort($codigosDeSeccion);

            $clasesDelGrupo = array_values(array_unique($clasesDelGrupo, SORT_REGULAR));

            $tituloSeccion = "Sección";
            $sheetTitleText = "";
            $nombresSecciones = [];
            foreach ($codigosDeSeccion as $codigo) {
                $nombresSecciones[] = $codigo;
            }
            if (count($nombresSecciones) > 1) {
                $tituloSeccion .= "es " . implode(' - ', $nombresSecciones);
                $sheetTitleText = implode('-', $nombresSecciones);
            } else {
                $tituloSeccion .= " " . $nombresSecciones[0];
                $sheetTitleText = $nombresSecciones[0];
            }
            
            
            if ($ani_tipo && strtolower($ani_tipo) === 'intensivo') {
                $tituloSeccion .= " (Intensivo)";
            }

            $safeSheetTitle = substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $sheetTitleText), 0, 31);
            $sheet = new Worksheet($spreadsheet, $safeSheetTitle);
            $spreadsheet->addSheet($sheet);
            $currentRow = 1;

            if (empty($clasesDelGrupo)) continue;

            $columnasHeader = [];
            $horarioGrid = [];
            $diasConSubgrupos = [];
            $clasesGeneralesPorDia = [];

            foreach ($clasesDelGrupo as $clase) {
                $dia = $day_map[strtolower(str_replace('é', 'e', $clase['hor_dia']))] ?? $clase['hor_dia'];
                $subgrupo = $clase['subgrupo'];
                $horaInicio = (new DateTime($clase['hor_horainicio']))->format('H:i:s');

                if ($subgrupo) {
                    $diasConSubgrupos[$dia][$subgrupo] = true;
                    $horarioGrid[$dia][$subgrupo][$horaInicio] = $clase;
                } else {
                    $clasesGeneralesPorDia[$dia][$horaInicio] = $clase;
                }
            }

            $diasOrdenados = array_keys(array_merge($diasConSubgrupos, $clasesGeneralesPorDia));
            usort($diasOrdenados, function ($a, $b) use ($day_order) {
                return ($day_order[$a] ?? 99) <=> ($day_order[$b] ?? 99);
            });

            foreach ($diasOrdenados as $dia) {
                if (!empty($diasConSubgrupos[$dia])) {
                    $subgruposDelDia = array_keys($diasConSubgrupos[$dia]);
                    sort($subgruposDelDia);
                    foreach ($subgruposDelDia as $subgrupo) {
                        $columnasHeader[] = ['dia' => $dia, 'subgrupo' => $subgrupo];
                        if (!empty($clasesGeneralesPorDia[$dia])) {
                            foreach ($clasesGeneralesPorDia[$dia] as $hora => $clase) {
                                if (!isset($horarioGrid[$dia][$subgrupo][$hora])) {
                                    $horarioGrid[$dia][$subgrupo][$hora] = $clase;
                                }
                            }
                        }
                    }
                } else {
                    $columnasHeader[] = ['dia' => $dia, 'subgrupo' => 'general'];
                    if (!empty($clasesGeneralesPorDia[$dia])) {
                        $horarioGrid[$dia]['general'] = $clasesGeneralesPorDia[$dia];
                    }
                }
            }

            $numDataColumns = count($columnasHeader);
            if ($numDataColumns == 0) continue;

            $occupiedDbKeys = [];
            $allPossibleSlotsForGroup = $morningSlots + $afternoonSlots + $nightSlots;
            $sortedSlotKeysForGroup = array_keys($allPossibleSlotsForGroup);
            sort($sortedSlotKeysForGroup);

            foreach ($clasesDelGrupo as $clase) {
                if (empty($clase['hor_horainicio']) || empty($clase['hor_horafin'])) continue;
                $horaInicioClase = new DateTime($clase['hor_horainicio']);
                $horaFinClase = new DateTime($clase['hor_horafin']);
                foreach ($sortedSlotKeysForGroup as $dbStartTimeKey) {
                    $slotStart = new DateTime($dbStartTimeKey);
                    $slotEnd = (clone $slotStart)->modify('+' . $slot_duration_minutes . ' minutes');
                    if ($horaInicioClase < $slotEnd && $horaFinClase > $slotStart) {
                        $occupiedDbKeys[$dbStartTimeKey] = true;
                    }
                }
            }

            $sheet->mergeCells("A{$currentRow}:" . chr(65 + $numDataColumns) . $currentRow);
            $sheet->setCellValue("A{$currentRow}", $tituloSeccion);
            $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
            $currentRow++;

            $headerRow = $currentRow;
            $sheet->setCellValue('A' . $headerRow, 'Hora');
            $sheet->getColumnDimension('A')->setWidth(22);
            $colIndex = 1;

            $columnLocationInfo = [];
            foreach ($columnasHeader as $idx => $colInfo) {
                $locationsInColumn = [];
                foreach ($horarioGrid[$colInfo['dia']][$colInfo['subgrupo']] ?? [] as $clase) {
                    $locationsInColumn[$clase['esp_codigo']] = $clase['esp_tipo'];
                }
                $columnLocationInfo[$idx] = [
                    'is_single' => count($locationsInColumn) === 1,
                    'name' => count($locationsInColumn) === 1 ? key($locationsInColumn) : null,
                    'type' => count($locationsInColumn) === 1 ? current($locationsInColumn) : null,
                ];

                $colLetter = chr(65 + $colIndex++);
                $headerText = mb_strtoupper($colInfo['dia']);

                $subgrupo = $colInfo['subgrupo'];
                if ($subgrupo && $subgrupo !== 'general') {
                    $headerText .= "\n(Grupo " . strtoupper($subgrupo) . ")";
                }

                if ($columnLocationInfo[$idx]['is_single'] && strtolower($columnLocationInfo[$idx]['type']) !== 'laboratorio') {
                    $headerText .= "\n" . $columnLocationInfo[$idx]['name'];
                }
                $sheet->setCellValue($colLetter . $headerRow, $headerText);
                $sheet->getColumnDimension($colLetter)->setWidth(25);
            }
            $sheet->getStyle("A{$headerRow}:" . chr(64 + $colIndex) . $headerRow)->applyFromArray($styleTableHeader);
            $currentRow++;

            $celdasOcupadas = [];

            $allSlots = [
                'morning' => $morningSlots,
                'afternoon' => $afternoonSlots,
                'night' => $nightSlots
            ];

            foreach ($allSlots as $shift => $timeSlots) {
                foreach ($timeSlots as $dbStartTimeKey => $displaySlot) {

                    if ($shift !== 'morning' && !isset($occupiedDbKeys[$dbStartTimeKey])) {
                        continue;
                    }

                    $sheet->setCellValue('A' . $currentRow, $displaySlot);
                    $sheet->getStyle('A' . $currentRow)->applyFromArray($styleTimeSlot);

                    $colIndex = 1;
                    foreach ($columnasHeader as $idx => $colInfo) {
                        $cellAddress = chr(65 + $colIndex) . $currentRow;
                        if (isset($celdasOcupadas[$cellAddress])) {
                            $colIndex++;
                            continue;
                        }

                        $clasesEnBloque = $horarioGrid[$colInfo['dia']][$colInfo['subgrupo']][$dbStartTimeKey] ?? null;

                        if ($clasesEnBloque) {
                            if (!is_array($clasesEnBloque) || !isset($clasesEnBloque[0])) {
                                $clasesEnBloque = [$clasesEnBloque];
                            }

                            $richText = new RichText();
                            foreach ($clasesEnBloque as $cIdx => $clase) {
                                if ($cIdx > 0) {
                                    $richText->createText("\n- - - - - - - - -\n");
                                }

                                $subgrupoTexto = ($clase['subgrupo'] && $clase['subgrupo'] !== 'general') ? '(G: ' . $clase['subgrupo'] . ') ' : '';
                                $ucAbreviada = abreviarNombreLargo($clase['uc_nombre']);
                                $ucPart = $richText->createTextRun($subgrupoTexto . $ucAbreviada);
                                $ucPart->getFont()->setBold(true);

                                $docente = $clase['NombreCompletoDocente'] ?? '(Sin docente)';
                                $richText->createText("\n" . $docente);

                                $currentColumnInfo = $columnLocationInfo[$idx];
                                if (!$currentColumnInfo['is_single'] || strtolower($currentColumnInfo['type']) === 'laboratorio') {
                                    $espacio = $clase['esp_codigo'] ?? '(Sin espacio)';
                                    $richText->createText("\n" . $espacio);
                                }
                            }

                            $sheet->getCell($cellAddress)->setValue($richText);

                            $primeraClase = $clasesEnBloque[0];
                            $horaInicioClase = new DateTime($primeraClase['hor_horainicio']);
                            $horaFinClase = new DateTime($primeraClase['hor_horafin']);
                            $diffMinutes = ($horaFinClase->getTimestamp() - $horaInicioClase->getTimestamp()) / 60;
                            $span = ceil($diffMinutes / $slot_duration_minutes);
                            if ($span < 1) $span = 1;

                            if ($span > 1) {
                                $endRow = $currentRow + $span - 1;
                                $sheet->mergeCells($cellAddress . ':' . chr(65 + $colIndex) . $endRow);
                                for ($k = 1; $k < $span; $k++) {
                                    $celdasOcupadas[chr(65 + $colIndex) . ($currentRow + $k)] = true;
                                }
                            }
                        }
                        $sheet->getStyle($cellAddress)->applyFromArray($styleScheduleCell);
                        $colIndex++;
                    }
                    $sheet->getRowDimension($currentRow)->setRowHeight(45);
                    $currentRow++;
                }
            }

            if ($currentRow > $headerRow + 1) { 
                $lastVisibleRow = $currentRow - 1;
                $range = 'A' . $lastVisibleRow . ':' . chr(65 + $numDataColumns) . $lastVisibleRow;
                $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            }

        }
    }

    if (ob_get_length()) ob_end_clean();
    $outputFileName = "Reporte_Horarios_Seccion";
    if ($ani_tipo && strtolower($ani_tipo) === 'intensivo') {
        $outputFileName .= "_Intensivo";
    }
    $outputFileName .= ".xlsx";
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
