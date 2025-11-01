<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'vendor/autoload.php';

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}
require_once("model/" . $pagina . ".php");

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment; 
use PhpOffice\PhpSpreadsheet\Style\Border;   
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;


function _abreviarNombreLargo($nombre) {
    if (!is_string($nombre) || empty($nombre)) return 'N/A';
    if (stripos($nombre, 'matemática') !== false) return 'Matemática' . _extraerNumeral($nombre);
    if (stripos($nombre, 'formación crítica') !== false) return 'Formación Crítica' . _extraerNumeral($nombre);
    if (stripos($nombre, 'proyecto socio') !== false) return 'PST' . _extraerNumeral($nombre);
    if (stripos($nombre, 'actividades acreditable') !== false) return 'AA' . _extraerNumeral($nombre);
    if (stripos($nombre, 'algorítmica y programación') !== false) return 'AP';
    if (stripos($nombre, 'arquitectura del computador') !== false) return 'AC';
    if (stripos($nombre, 'ingeniería del software') !== false) return 'IS' . _extraerNumeral($nombre);
    if (stripos($nombre, 'sistemas operativos') !== false) return 'SO';
    if (stripos($nombre, 'introducción a la universidad') !== false) return 'IUPNF';
    if (stripos($nombre, 'proyecto nacional y nueva') !== false) return 'PNNC';
    if (stripos($nombre, 'tecnologías de la información') !== false) return 'TIC';
    return $nombre;
}


function _extraerNumeral($cadena) {
    $partes = explode(' ', $cadena);
    $ultimoTermino = end($partes);
    $numeralesRomanos = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    if (in_array(strtoupper($ultimoTermino), $numeralesRomanos)) {
        return ' ' . strtoupper($ultimoTermino);
    }
    return '';
}


function _analizarEspaciosPorColumna($horario, $columnasHeader) {
    $espaciosPorColumna = [];
    foreach($columnasHeader as $idx => $colInfo) {
        $espacios = [];
        foreach($horario as $clase) {
            if(strtoupper($clase['hor_dia']) === $colInfo['dia'] && $clase['subgrupo'] === $colInfo['subgrupo']) {
                if (!empty($clase['espacio_nombre'])) {
                    $espacios[] = $clase['espacio_nombre'];
                }
            }
        }
        if (count($espacios) > 0 && !empty($espacios[0]) && count(array_unique($espacios)) === 1) {
            $espaciosPorColumna[$idx] = $espacios[0];
        }
    }
    return $espaciosPorColumna;
}


function _prepararColumnasYGrid($horario) {
    $day_map = ['LUNES' => 'Lunes', 'MARTES' => 'Martes', 'MIÉRCOLES' => 'Miércoles', 'JUEVES' => 'Jueves', 'VIERNES' => 'Viernes', 'SÁBADO' => 'Sábado'];
    $day_order = array_flip(array_keys($day_map));
    $horarioGrid = [];
    $diasConSubgrupos = [];
    $clasesGeneralesPorDia = [];
    $columnasHeader = [];

    foreach ($horario as $clase) {
        $dia = strtoupper($clase['hor_dia']);
        $subgrupo = $clase['subgrupo'];
        if ($subgrupo) {
            $diasConSubgrupos[$dia][$subgrupo] = true;
        } else {
            $clasesGeneralesPorDia[$dia] = true;
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
            }
        } else {
            $columnasHeader[] = ['dia' => $dia, 'subgrupo' => null];
        }
    }

    foreach ($horario as $clase) {
        $dia = strtoupper($clase['hor_dia']);
        $hora = $clase['hor_horainicio'];
        $subgrupo = $clase['subgrupo'];
        
        if ($subgrupo) {
            $horarioGrid[$hora][$dia][$subgrupo] = $clase;
        } else {
            foreach($columnasHeader as $col) {
                if ($col['dia'] === $dia && $col['subgrupo'] === null) {
                     $horarioGrid[$hora][$dia]['general'] = $clase;
                }
            }
        }
    }
    return ['columnas' => $columnasHeader, 'grid' => $horarioGrid];
}


function _formatearEspacio($espacio_nombre) {
    if (empty($espacio_nombre)) return '';
    if (stripos($espacio_nombre, 'LAB ') === 0) return $espacio_nombre;
    if (strpos($espacio_nombre, ' - ') === false) return $espacio_nombre;
    list($edificio, $resto) = explode(' - ', $espacio_nombre, 2);
    list($tipo, $numero) = sscanf($resto, "%s %s");
    return strtoupper(substr($edificio, 0, 1)) . '-' . $numero;
}



function generarReportePDF($secciones_codigos, $horario, $anio, $turnos) {
    if (empty($turnos)) {
        die("Error: No se ha definido una estructura de turnos para generar el reporte.");
    }
    
    $preparacion = _prepararColumnasYGrid($horario);
    $columnasHeader = $preparacion['columnas'];
    $grid = $preparacion['grid'];
    $espaciosPorColumna = _analizarEspaciosPorColumna($horario, $columnasHeader);
    
   
    $time_slots = [];
    if (empty($horario)) {
        
        foreach (array_slice($turnos, 0, 7) as $turno) {
             $time_slots[$turno['tur_horainicio']] = $turno['tur_horafin'];
        }
    } else {
      
        $min_time = '23:59:59';
        $max_time = '00:00:00';
        foreach ($horario as $clase) {
            if ($clase['hor_horainicio'] < $min_time) $min_time = $clase['hor_horainicio'];
            if ($clase['hor_horafin'] > $max_time) $max_time = $clase['hor_horafin'];
        }

       
        foreach ($turnos as $turno) {
           
            if ($turno['tur_horainicio'] < $max_time && $turno['tur_horafin'] > $min_time) {
                $time_slots[$turno['tur_horainicio']] = $turno['tur_horafin'];
            }
        }
    }
    ksort($time_slots);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $tituloSeccion = "Sección";
    sort($secciones_codigos);
    $nombresSecciones = [];
    foreach ($secciones_codigos as $codigo) {
        $nombresSecciones[] = $codigo;
    }
    $tituloSeccion .= (count($nombresSecciones) > 1 ? "es: " : ": ") . implode(' - ', $nombresSecciones);

    $html = '<html><head><style>
        @page { margin: 25px; }
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h2 { text-align: center; margin: 0 0 15px 0; font-size: 16px; font-weight: bold; }
        .schedule-table { width: 100%; border-collapse: collapse; }
        .schedule-table th, .schedule-table td { border: 1px solid #ccc; padding: 6px; text-align: center; vertical-align: middle; }
        .schedule-table th { background-color: #f2f2f2; font-weight: bold; font-size: 11px; }
        .schedule-table th small { display: block; font-weight: normal; color: #6c757d; font-size: 10px; }
        .time-col { width: 120px; font-weight: normal; white-space: nowrap; font-size: 9px; }
        .class-cell { line-height: 1.4; padding-top: 10px; padding-bottom: 10px;}
        .class-cell strong { font-size: 11px; font-weight: bold; display: block; }
        .class-cell small { font-size: 9px; color: #6c757d; }
    </style></head><body>';
    $html .= '<h2>' . htmlspecialchars($tituloSeccion) . ' (' . htmlspecialchars($anio) . ')</h2>';
    $html .= '<table class="schedule-table"><thead><tr><th class="time-col">Hora</th>';

    foreach ($columnasHeader as $idx => $colInfo) {
        $headerText = ucfirst(mb_strtolower($colInfo['dia']));
        if ($colInfo['subgrupo']) {
            $headerText .= '<br><small>(Grupo ' . htmlspecialchars($colInfo['subgrupo']) . ')</small>';
        }
        if (isset($espaciosPorColumna[$idx])) {
            $headerText .= '<br><small>' . _formatearEspacio($espaciosPorColumna[$idx]) . '</small>';
        }
        $html .= '<th>' . $headerText . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    if (empty($time_slots)) {
        $html .= '<tr><td colspan="' . (count($columnasHeader) + 1) . '">No hay horario para mostrar.</td></tr>';
    } else {
        foreach ($time_slots as $start_time => $end_time) {
            $html .= '<tr><td class="time-col">' . date('h:i A', strtotime($start_time)) . ' a ' . date('h:i A', strtotime($end_time)) . '</td>';
            foreach ($columnasHeader as $idx => $colInfo) {
                $keySubgrupo = $colInfo['subgrupo'] ?? 'general';
                $clase = $grid[$start_time][$colInfo['dia']][$keySubgrupo] ?? null;
                
                if (is_array($clase)) {
                    $rowspan = 0;
                    foreach($time_slots as $s_start => $s_end) {
                        if ($s_start >= $clase['hor_horainicio'] && $s_start < $clase['hor_horafin']) $rowspan++;
                    }
                    $rowspanAttr = $rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '';

                    $uc_abreviada = _abreviarNombreLargo($clase['uc_nombre']);
                    $docente_texto = htmlspecialchars($clase['docente_nombre'] ?: '(Sin Docente)');
                    $subgrupo_texto = $clase['subgrupo'] ? '(G: ' . htmlspecialchars($clase['subgrupo']) . ') ' : '';
                    
                    $html .= '<td class="class-cell"' . $rowspanAttr . '><strong>' . $subgrupo_texto . htmlspecialchars($uc_abreviada) . '</strong>';
                    
                    $info_adicional = $docente_texto;
                    if (!isset($espaciosPorColumna[$idx])) {
                         $espacio_formateado = _formatearEspacio($clase['espacio_nombre']);
                         if ($espacio_formateado) $info_adicional .= '<br>' . htmlspecialchars($espacio_formateado);
                    }
                    $html .= '<small>' . $info_adicional . '</small></td>';

                    if ($rowspan > 1) {
                         $temp_time = $start_time;
                         for ($i = 0; $i < $rowspan - 1; $i++) {
                             
                             $keys = array_keys($time_slots);
                             $current_key_index = array_search($temp_time, $keys);
                             if ($current_key_index !== false && isset($keys[$current_key_index + 1])) {
                                 $temp_time = $keys[$current_key_index + 1];
                                 $grid[$temp_time][$colInfo['dia']][$keySubgrupo] = '__SPAN__';
                             }
                         }
                    }
                } elseif ($clase !== '__SPAN__') {
                    $html .= '<td>&nbsp;</td>';
                }
            }
            $html .= '</tr>';
        }
    }
    $html .= '</tbody></table></body></html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    ob_end_clean();
    $dompdf->stream('horario_' . implode('_', $secciones_codigos) . '.pdf', ['Attachment' => true]);
}
function generarReporteExcel($secciones_codigos, $horario, $anio, $turnos) {
    if (empty($turnos)) {
        die("Error: No se ha definido una estructura de turnos.");
    }

    $preparacion = _prepararColumnasYGrid($horario);
    $columnasHeader = $preparacion['columnas'];
    $grid = $preparacion['grid'];
    $espaciosPorColumna = _analizarEspaciosPorColumna($horario, $columnasHeader);
    
    
    $time_slots = [];
    if (!empty($horario)) {
        $min_time = min(array_column($horario, 'hor_horainicio'));
        $max_time = max(array_column($horario, 'hor_horafin'));
        foreach ($turnos as $turno) {
            if ($turno['tur_horainicio'] < $max_time && $turno['tur_horafin'] > $min_time) {
                $time_slots[$turno['tur_horainicio']] = $turno['tur_horafin'];
            }
        }
        ksort($time_slots);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();


    $styleTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]];
    $styleTimeCol = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleCell = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];


    $tituloSeccion = "Sección";
    sort($secciones_codigos);
    $nombresSecciones = $secciones_codigos; 
    $tituloSeccion .= (count($nombresSecciones) > 1 ? "es: " : ": ") . implode(' - ', $nombresSecciones) . " ({$anio})";

    $lastColLetter = chr(65 + count($columnasHeader));
    $sheet->mergeCells("A1:{$lastColLetter}1");
    $sheet->setCellValue('A1', $tituloSeccion);
    $sheet->getStyle('A1')->applyFromArray($styleTitle);

    
    $sheet->setCellValue('A2', 'Hora');
    $sheet->getColumnDimension('A')->setWidth(20);

    foreach ($columnasHeader as $idx => $colInfo) {
        $colLetter = chr(66 + $idx);
        $sheet->getColumnDimension($colLetter)->setWidth(25);
        
        
        $headerText = ucfirst(mb_strtolower($colInfo['dia']));
        
        if ($colInfo['subgrupo']) {
            $headerText .= "\n(Grupo " . htmlspecialchars($colInfo['subgrupo']) . ")";
        }
        if (isset($espaciosPorColumna[$idx])) {
            $headerText .= "\n" . _formatearEspacio($espaciosPorColumna[$idx]);
        }
        
        $sheet->getCell($colLetter . '2')->setValue($headerText);
        $sheet->getStyle($colLetter . '2')->getAlignment()->setWrapText(true);
    }
    
    $currentRow = 3;
    $celdasOcupadas = [];

    foreach ($time_slots as $start_time => $end_time) {
        $sheet->getRowDimension($currentRow)->setRowHeight(45);
        $sheet->setCellValue('A' . $currentRow, date('h:i A', strtotime($start_time)) . ' a ' . date('h:i A', strtotime($end_time)));
        
        foreach ($columnasHeader as $idx => $colInfo) {
            $colLetter = chr(66 + $idx);
            $cellAddress = $colLetter . $currentRow;

            if (isset($celdasOcupadas[$cellAddress])) continue;

            $keySubgrupo = $colInfo['subgrupo'] ?? 'general';
            $clase = $grid[$start_time][$colInfo['dia']][$keySubgrupo] ?? null;

            if (is_array($clase)) {
                $rowspan = 0;
                foreach($time_slots as $s_start => $s_end) {
                    if ($s_start >= $clase['hor_horainicio'] && $s_start < $clase['hor_horafin']) $rowspan++;
                }

            
                $subgrupo_texto = $clase['subgrupo'] ? '(G: ' . htmlspecialchars($clase['subgrupo']) . ') ' : '';
                $texto_completo = $subgrupo_texto . _abreviarNombreLargo($clase['uc_nombre']);
                $texto_completo .= "\n" . ($clase['docente_nombre'] ?: '(Sin Docente)');
                
               
                if (!isset($espaciosPorColumna[$idx])) {
                    $espacio_formateado = _formatearEspacio($clase['espacio_nombre']);
                    if ($espacio_formateado) {
                        $texto_completo .= "\n" . $espacio_formateado;
                    }
                }

                $sheet->getCell($cellAddress)->setValue($texto_completo);
                $sheet->getStyle($cellAddress)->getAlignment()->setWrapText(true);
                
                if ($rowspan > 1) {
                    $endRow = $currentRow + $rowspan - 1;
                    $sheet->mergeCells($cellAddress . ':' . $colLetter . $endRow);
                    for ($k = 1; $k < $rowspan; $k++) {
                        $celdasOcupadas[$colLetter . ($currentRow + $k)] = true;
                    }
                }
            }
        }
        $currentRow++;
    }


    $lastRow = $currentRow - 1;
    $range = "A2:{$lastColLetter}{$lastRow}";
    $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray($styleHeader);
    $sheet->getStyle("A3:A{$lastRow}")->applyFromArray($styleTimeCol);
    $sheet->getStyle("B3:{$lastColLetter}{$lastRow}")->applyFromArray($styleCell);
    $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCCCCCC'));


    
    $fileName = 'horario_' . implode('_', $nombresSecciones) . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
    header('Cache-Control: max-age=0');
    ob_end_clean();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}
function generarReporteWord($secciones_codigos, $horario, $anio, $turnos) {
    if (empty($turnos)) {
        die("Error: No se ha definido una estructura de turnos.");
    }
    
    $preparacion = _prepararColumnasYGrid($horario);
    $columnasHeader = $preparacion['columnas'];
    $grid = $preparacion['grid'];
    $espaciosPorColumna = _analizarEspaciosPorColumna($horario, $columnasHeader);
    
    $time_slots = [];
    if (!empty($horario)) {
        $min_time = min(array_column($horario, 'hor_horainicio'));
        $max_time = max(array_column($horario, 'hor_horafin'));
        foreach ($turnos as $turno) {
            if ($turno['tur_horainicio'] < $max_time && $turno['tur_horafin'] > $min_time) {
                $time_slots[$turno['tur_horainicio']] = $turno['tur_horafin'];
            }
        }
        ksort($time_slots);
    }

    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['orientation' => 'landscape', 'marginLeft' => 600, 'marginRight' => 600, 'marginTop' => 600, 'marginBottom' => 600]);

    $tituloSeccion = "Sección";
    sort($secciones_codigos);
    $nombresSecciones = $secciones_codigos; 
    $tituloSeccion .= (count($nombresSecciones) > 1 ? "es: " : ": ") . implode(' - ', $nombresSecciones) . " ({$anio})";
    $section->addText($tituloSeccion, ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
    $section->addTextBreak(1);

    $tableStyle = ['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 80, 'alignment' => Jc::CENTER];
    $headerCellStyle = ['valign' => 'center', 'bgColor' => 'F2F2F2'];
    $cellStyle = ['valign' => 'center'];
    $centerParagraphStyle = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

    $table = $section->addTable($tableStyle);
    $table->addRow();
    $table->addCell(1800, $headerCellStyle)->addText('Hora', ['bold' => true, 'size' => 11], $centerParagraphStyle);

    foreach ($columnasHeader as $idx => $colInfo) {
        $cell = $table->addCell(2000, $headerCellStyle);
        $cell->addText(ucfirst(mb_strtolower($colInfo['dia'])), ['bold' => true, 'size' => 11], $centerParagraphStyle);
        if ($colInfo['subgrupo']) {
            $cell->addText('(Grupo ' . htmlspecialchars($colInfo['subgrupo']) . ')', ['size' => 9, 'color' => '6c757d'], $centerParagraphStyle);
        }
        if (isset($espaciosPorColumna[$idx])) {
            $cell->addText(_formatearEspacio($espaciosPorColumna[$idx]), ['size' => 10, 'color' => '6c757d'], $centerParagraphStyle);
        }
    }

    foreach ($time_slots as $start_time => $end_time) {
        $table->addRow();
        $table->addCell(1800, $cellStyle)->addText(date('h:i A', strtotime($start_time)) . ' a ' . date('h:i A', strtotime($end_time)), ['size' => 9], $centerParagraphStyle);

        foreach ($columnasHeader as $idx => $colInfo) {
            $keySubgrupo = $colInfo['subgrupo'] ?? 'general';
            $clase = $grid[$start_time][$colInfo['dia']][$keySubgrupo] ?? null;

            if (is_array($clase)) { 
                $rowspan = 0;
                foreach($time_slots as $s_start => $s_end) {
                    if ($s_start >= $clase['hor_horainicio'] && $s_start < $clase['hor_horafin']) $rowspan++;
                }
                
                $cell = $table->addCell(2000, ['vMerge' => 'restart'] + $cellStyle);
                $subgrupo_texto = $clase['subgrupo'] ? '(G: ' . htmlspecialchars($clase['subgrupo']) . ') ' : '';
                $cell->addText($subgrupo_texto . _abreviarNombreLargo($clase['uc_nombre']), ['bold' => true, 'size' => 10], $centerParagraphStyle);
                
                $info_adicional = htmlspecialchars($clase['docente_nombre'] ?: '(Sin Docente)');
                if (!isset($espaciosPorColumna[$idx])) {
                     $espacio_formateado = _formatearEspacio($clase['espacio_nombre']);
                     if ($espacio_formateado) $info_adicional .= "\n" . htmlspecialchars($espacio_formateado);
                }
                $cell->addText($info_adicional, ['size' => 8, 'color' => '6c757d'], $centerParagraphStyle);

                
                if ($rowspan > 1) {
                    $temp_time = $start_time;
                    for ($i = 0; $i < $rowspan - 1; $i++) {
                        $keys = array_keys($time_slots);
                        $current_key_index = array_search($temp_time, $keys);
                        if ($current_key_index !== false && isset($keys[$current_key_index + 1])) {
                            $temp_time = $keys[$current_key_index + 1];
                            $grid[$temp_time][$colInfo['dia']][$keySubgrupo] = '__SPAN__';
                        }
                    }
                }

            } else if ($clase === '__SPAN__') {
                
                $table->addCell(null, ['vMerge' => 'continue'] + $cellStyle);
            } else {
                
                $table->addCell(2000, $cellStyle);
            }
        }
    }

  
    $fileName = 'horario_' . implode('_', $nombresSecciones) . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
    header('Cache-Control: max-age=0');
    ob_end_clean();
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
}

if (isset($_POST['accion']) && $_POST['accion'] === 'generar_reporte') {
    $sec_codigo = $_POST['sec_codigo'] ?? null;
    $ani_anio = $_POST['ani_anio'] ?? null;
    $formato = $_POST['formato'] ?? 'pdf';

    if (!$sec_codigo || !$ani_anio) {
        die("Error: Faltan datos clave para generar el reporte.");
    }

    $o = new Seccion();
    
    $datosReporte = $o->obtenerDatosCompletosHorarioParaReporte($sec_codigo, $ani_anio);

    if ($datosReporte['resultado'] !== 'ok') {
        die("Error al obtener los datos del horario: " . $datosReporte['mensaje']);
    }

    
    switch ($formato) {
       case 'excel':
      
        generarReporteExcel(
            $datosReporte['secciones'], 
            $datosReporte['horario'],
            $datosReporte['anio'],
            $datosReporte['turnos']
        );
        break;
        
    case 'word':
       
        generarReporteWord(
            $datosReporte['secciones'], 
            $datosReporte['horario'],
            $datosReporte['anio'],
            $datosReporte['turnos']
        );
        break;
        case 'pdf':
       default:
        
        generarReportePDF(
            $datosReporte['secciones'],
            $datosReporte['horario'],
            $datosReporte['anio'],
            $datosReporte['turnos'] 
        );
        break;
    }
    exit; 
}
$acciones_json_validas = [
    'obtener_datos_selects',
    'consultar_agrupado',
    'consultar_detalles',
    'modificar',
    'obtener_uc_por_docente',
    'registrar_seccion',
    'eliminar_seccion_y_horario',
    'validar_clase_en_vivo',
    'unir_horarios',
    'verificar_codigo_seccion',
    'verificar_malla',
    'duplicar_anio_anterior'
];

if (empty($_POST) || (isset($_POST['accion']) && !in_array($_POST['accion'], $acciones_json_validas))) {
    $o = new Seccion();

    $countDocentes = $o->contarDocentes();
    $countEspacios = $o->contarEspacios();
    $countTurnos = $o->contarTurnos();
    $countAnios = $o->contarAniosActivos();
    $countMallas = $o->contarMallasActivas();
    $anios = $o->obtenerAnios();
    $reporte_promocion = $o->ActualizarSeccionesParaFase2();
    if ($reporte_promocion !== null) {
        $_SESSION['reporte_promocion'] = $reporte_promocion;
    }

   $mostrar_prompt_duplicar = false;
$anio_activo = $o->obtenerAnioActivo();

if ($anio_activo) {
    $existen_secciones_actual = $o->existenSeccionesParaAnio($anio_activo);


    if (!$existen_secciones_actual) {
        $anio_anterior = $anio_activo - 1;
        $existen_secciones_anterior = $o->existenSeccionesParaAnio($anio_anterior);

      
        if ($existen_secciones_anterior) {
            $mostrar_prompt_duplicar = true;
        }
    }
}
    if (is_file("views/" . $pagina . ".php")) {
        require_once("views/" . $pagina . ".php");
    } else {
        echo "Página en construcción: " . "views/" . $pagina . ".php";
    }
} else {
    $accion = $_POST['accion'] ?? '';
    $respuesta = ['resultado' => 'error', 'mensaje' => 'Acción no reconocida o faltante.'];

    try {
        $o = new Seccion();

        switch ($accion) {

            case 'obtener_datos_selects':
                $respuesta = [
                    'resultado' => 'ok',
                    'anio_activo' => $o->obtenerAnioActivo(), 
                    'ucs' => $o->obtenerUnidadesCurriculares(),
                    'espacios' => $o->obtenerEspacios(),
                    'docentes' => $o->obtenerDocentes(),
                    'turnos' => $o->obtenerTurnos(),
                    'cohortes' => $o->obtenerCohortesMalla(),
                    'horarios_existentes' => $o->obtenerTodosLosHorarios()
                ];
                break;
            case 'registrar_seccion':
                $anioCompuesto = $_POST['anioId'] ?? null;
                list($anio_anio, $anio_tipo) = explode('|', $anioCompuesto . '|');
                $codigoSeccion = $_POST['codigoSeccion'] ?? null;
                $cantidadSeccion = $_POST['cantidadSeccion'] ?? null;
                $forzar_cohorte = isset($_POST['forzar_cohorte']) && $_POST['forzar_cohorte'] === 'true';
                $respuesta = $o->RegistrarSeccion(
                    $codigoSeccion,
                    $cantidadSeccion,
                    $anio_anio,
                    $anio_tipo,
                    $forzar_cohorte
                );
                break;

            case 'consultar_agrupado':
                $respuesta = $o->ListarAgrupado();
                break;

           case 'consultar_detalles':
    $respuesta = $o->ConsultarDetalles($_POST['sec_codigo'] ?? null, $_POST['ani_anio'] ?? null);
    break;

            case 'obtener_uc_por_docente':
                    $doc_cedula = $_POST['doc_cedula'] ?? null;
                    $sec_codigo_actual = $_POST['sec_codigo_actual'] ?? null;
                    $trayecto_seccion = null;

                    if ($sec_codigo_actual) {
                       
                        $numericPart = preg_replace('/^\D+/', '', $sec_codigo_actual);
                        if (strlen($numericPart) > 0) {
                           
                            $trayecto_seccion = substr($numericPart, 0, 1);
                        }
                    }

                    $resultado_uc = $o->obtenerUcPorDocente($doc_cedula, $trayecto_seccion);
                    $respuesta = [
                        'resultado' => 'ok',
                        'ucs_docente' => $resultado_uc['data'],
                        'mensaje_uc' => $resultado_uc['mensaje']
                    ];
                    break;

         case 'modificar':
                $forzar = isset($_POST['forzar_guardado']) && $_POST['forzar_guardado'] === 'true';
                $modo_operacion = $_POST['modo_operacion'] ?? 'modificar';
                $respuesta = $o->Modificar(
                    $_POST['sec_codigo'] ?? null,
                    $_POST['ani_anio'] ?? null, 
                    $_POST['items_horario'] ?? '[]',
                    $_POST['cantidadSeccion'] ?? null,
                    $forzar,
                    $modo_operacion,
                    $_POST['bloques_personalizados'] ?? '[]',
                    $_POST['bloques_eliminados'] ?? '[]'
                );
                break;

           case 'eliminar_seccion_y_horario':
                    $respuesta = $o->EliminarSeccionYHorario(
                        $_POST['sec_codigo'] ?? null, 
                        $_POST['ani_anio'] ?? null 
                    );
                    break;

            case 'validar_clase_en_vivo':
                 $espacio = isset($_POST['espacio']) ? json_decode($_POST['espacio'], true) : null;
                 $respuesta = $o->ValidarClaseEnVivo(
                    $_POST['doc_cedula'] ?? null,
                    $_POST['uc_codigo'] ?? null, 
                    $espacio,
                    $_POST['dia'] ?? null,
                    $_POST['hora_inicio'] ?? null,
                    $_POST['hora_fin'] ?? null,
                    $_POST['sec_codigo'] ?? null,
                    $_POST['ani_anio'] ?? null
                );
                break;
            
            case 'verificar_codigo_seccion':
                $anioCompuesto = $_POST['anioId'] ?? null;
                list($anio_anio, $anio_tipo) = explode('|', $anioCompuesto . '|');
                $codigoSeccion = $_POST['codigoSeccion'] ?? null;

                $existe = false;
                if($codigoSeccion && $anio_anio && $anio_tipo) {
                   $existe = $o->VerificarCodigoSeccion($codigoSeccion, $anio_anio, $anio_tipo);
                }
                $respuesta = ['resultado' => 'ok', 'existe' => $existe];
                break;
            
            case 'verificar_malla':
                $numeroMalla = $_POST['numeroMalla'] ?? null;
                $existe = false;
                
                if ($numeroMalla !== null) {
                    $existe = $o->VerificarMallaExiste($numeroMalla);
                }
                
                $respuesta = ['resultado' => 'ok', 'existe' => $existe];
                break;

            case 'unir_horarios':
                $respuesta = $o->UnirHorarios(
                    $_POST['id_seccion_origen'] ?? null,
                    $_POST['secciones_a_unir'] ?? []
                );
                break;
                
            case 'duplicar_anio_anterior':
                $respuesta = $o->duplicarSeccionesAnioAnterior();
                break;
        }
    } catch (Exception $e) {
        error_log("Error en seccionC.php: " . $e->getMessage());
        $respuesta = ['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()];
    }

    header('Content-Type: application/json; charset=utf-8');

    array_walk_recursive($respuesta, function (&$item, $key) {
        if (is_string($item)) {
            if (!mb_check_encoding($item, 'UTF-8')) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item, 'UTF-8, ISO-8859-1', true));
            }
        }
    });

    $json_respuesta = json_encode($respuesta, JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al codificar JSON en seccionC.php: " . json_last_error_msg() . " - Data: " . print_r($respuesta, true));
        $json_respuesta = json_encode(['resultado' => 'error', 'mensaje' => 'Error al codificar JSON: ' . json_last_error_msg()]);
    }

    echo $json_respuesta;
    exit;
}