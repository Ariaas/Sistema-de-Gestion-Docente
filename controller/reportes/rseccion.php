<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once ('vendor/autoload.php');
require_once ('model/reportes/rseccion.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

function abreviarNombreLargo($nombre, $longitudMaxima = 25) {
    if (mb_strlen($nombre) <= $longitudMaxima) { return $nombre; }
    $palabrasExcluidas = ['de', 'y', 'a', 'del', 'la', 'los', 'las', 'en'];
    $partes = explode(' ', $nombre);
    $numeral = '';
    $ultimoTermino = end($partes);
    $romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
    if (in_array(strtoupper($ultimoTermino), $romanos)) { $numeral = ' ' . array_pop($partes); }
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
    
    $anio = $_POST['anio_id'] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    $trayecto_filtrado = $_POST['trayecto_id'] ?? '';

    if (empty($anio) || empty($fase)) { die("Error: Debe seleccionar un Año y una Fase."); }
    
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
            $display_string = $franja_inicio->format('h:i A') . ' a ' . $franja_fin->format('h:i A');
            $todas_las_franjas_por_turno[$nombre_turno][$display_string] = $db_start_time_key;
        }
    }

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
        $dataGroupedByTrayecto = [];
        foreach ($horarioDataRaw as $item) { $dataGroupedByTrayecto[$item['uc_trayecto']][] = $item; }
        ksort($dataGroupedByTrayecto);

        foreach($dataGroupedByTrayecto as $trayecto_id => $data_for_trayecto) {
            $sheet = new Worksheet($spreadsheet, "Trayecto " . $trayecto_id);
            $spreadsheet->addSheet($sheet);
            $currentRow = 1;

            $gruposDeSecciones = [];
            foreach ($data_for_trayecto as $item) {
                $groupId = !empty($item['grupo_union_id']) ? $item['grupo_union_id'] : $item['sec_codigo'];
                $gruposDeSecciones[$groupId][] = $item;
            }
            ksort($gruposDeSecciones);
            
            foreach($gruposDeSecciones as $groupId => $clasesDelGrupo) {
                
                $seccionesEnGrupo = [];
                foreach ($clasesDelGrupo as $clase) {
                    $seccionesEnGrupo[$clase['sec_codigo']] = true;
                }
                $codigosDeSeccion = array_keys($seccionesEnGrupo);
                sort($codigosDeSeccion);

                $tituloSeccion = "Sección";
                foreach ($codigosDeSeccion as $codigo) {
                    $prefijo = (substr($codigo, 0, 1) === '3' || substr($codigo, 0, 1) === '4') ? 'IIN' : 'IN';
                    $tituloSeccion .= " " . $prefijo . $codigo;
                }

                $clasesPorTurno = [];
                foreach ($turnos as $turno) {
                    $clasesPorTurno[ucfirst(strtolower($turno['tur_nombre']))] = [];
                }

                foreach ($clasesDelGrupo as $clase) {
                    if (empty($clase['hor_horainicio'])) continue;
                    $horaInicioClase = new DateTime($clase['hor_horainicio']);
                    foreach ($turnos as $turno) {
                        if ($horaInicioClase >= new DateTime($turno['tur_horaInicio']) && $horaInicioClase < new DateTime($turno['tur_horaFin'])) {
                            $clasesPorTurno[ucfirst(strtolower($turno['tur_nombre']))][] = $clase;
                            break;
                        }
                    }
                }

                foreach ($clasesPorTurno as $shiftName => $clasesDelTurno) {
                    if (empty($clasesDelTurno)) continue;

                    $columnasHeader = [];
                    $horarioGrid = [];
                    $diasConSubgrupos = [];
                    $clasesGeneralesPorDia = [];

                    foreach ($clasesDelTurno as $clase) {
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
                    usort($diasOrdenados, function($a, $b) use ($day_order) { return ($day_order[$a] ?? 99) <=> ($day_order[$b] ?? 99); });

                    foreach ($diasOrdenados as $dia) {
                        if (!empty($diasConSubgrupos[$dia])) {
                            $subgruposDelDia = array_keys($diasConSubgrupos[$dia]);
                            sort($subgruposDelDia);
                            foreach ($subgruposDelDia as $subgrupo) {
                                $columnasHeader[] = ['dia' => $dia, 'subgrupo' => $subgrupo];
                                if (!empty($clasesGeneralesPorDia[$dia])) {
                                    foreach($clasesGeneralesPorDia[$dia] as $hora => $clase) {
                                        if(!isset($horarioGrid[$dia][$subgrupo][$hora])) {
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

                    $sheet->mergeCells("A{$currentRow}:" . chr(65 + $numDataColumns) . $currentRow);
                    $sheet->setCellValue("A{$currentRow}", $tituloSeccion);
                    $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
                    $currentRow++;

                    $headerRow = $currentRow;
                    $sheet->setCellValue('A' . $headerRow, 'Hora');
                    $sheet->getColumnDimension('A')->setWidth(22);
                    $colIndex = 1;

                    $columnLocationInfo = [];
                    foreach($columnasHeader as $idx => $colInfo) {
                        $locationsInColumn = [];
                        foreach($horarioGrid[$colInfo['dia']][$colInfo['subgrupo']] ?? [] as $clase) {
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
                    $shiftTimeSlots = $todas_las_franjas_por_turno[$shiftName] ?? [];
                    foreach ($shiftTimeSlots as $displaySlot => $dbStartTimeKey) {
                        $sheet->setCellValue('A'.$currentRow, $displaySlot);
                        $sheet->getStyle('A'.$currentRow)->applyFromArray($styleTimeSlot);
                        
                        $colIndex = 1;
                        foreach ($columnasHeader as $idx => $colInfo) {
                            $cellAddress = chr(65 + $colIndex) . $currentRow;
                            if(isset($celdasOcupadas[$cellAddress])) { $colIndex++; continue; }

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
                    $currentRow++;
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