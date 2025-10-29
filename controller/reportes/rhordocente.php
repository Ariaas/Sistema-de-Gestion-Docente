<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/rhordocente.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

function toRoman($number) {
    $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
    return $map[$number] ?? $number;
}

function abreviarNombreUC($nombre) {
  
    $mapaAbreviaturas = [
       
        'Introducción a la universidad y a los programas nacionales de formacion' => 'INPNFI',
        'Proyecto nacional y nueva ciudadanía' => 'PNNC',
        'Tecnologías de la información y comunicación' => 'TIC',
        'Matemática' => 'MAT INICIAL',
        
       
        'Algorítmica y Programación' => 'AP',
        'Arquitectura del Computador' => 'AC',
        'Formación Crítica I' => 'FC-I',
        'Idiomas I' => 'AA-I',
        'Matemática I' => 'MAT-I',
        'Proyecto Socio Tecnológico I' => 'PST-I',
        
        
        'Base de Datos' => 'BD',
        'Formación Crítica II' => 'FC-II',
        'Ingeniería del Software I' => 'IS-I',
        'Matemática II' => 'MAT-II',
        'Programación II' => 'PP-II',
        'Proyecto Socio Tecnológico II' => 'PST-II',
        'Redes de Computadoras' => 'REDES',
        
        
        'Formación Crítica III' => 'FC-III',
        'Ingeniería de Software II' => 'IS-II',
        'Investigación de operaciones' => 'IO',
        'Matemática Aplicada' => 'Mat Apli.',
        'Modelado de bases de datos' => 'MBD',
        'Proyecto Socio Tecnológico III' => 'PST-III',
        'Sistemas Operativos' => 'SO',

        
        'Administración de bases de datos' => 'ABD',
        'Auditoria de sistemas' => 'AUD SIS',
        'Formación Crítica IV' => 'FC-IV',
        'Gestión de proyecto Informático' => 'GPI',
        'Idiomas II' => 'IDIOMAS IV',
        'Proyecto Socio Tecnológico IV' => 'PST-IV',
        'Redes Avanzadas' => 'RED AVANZ',
        'Seguridad Informática' => 'SEG INF',
        

        'Actividades Acreditable I' => 'AA-I',
        'Actividades Acreditables II' => 'AA-II',
        'Actividades Acreditables III' => 'AA-III',
        'Actividades Acreditables IV' => 'AA-IV',
        'Electiva I' => 'Electiva I',
        'Electiva II' => 'Electiva II',
        'Electiva III' => 'Electiva III',
        'Electiva IV' => 'Electiva IV',
        
        
        'CREACIÓN INTELECTUAL'  => 'CREACIÓN INTELECTUAL',
        'INTEGRACIÓN COMUNIDAD' => 'INTEGRACIÓN COMUNIDAD',
        'GESTIÓN ACADÉMICA'     => 'GESTIÓN ACADÉMICA',
        
    ];

   
    if (isset($mapaAbreviaturas[$nombre])) {
        return $mapaAbreviaturas[$nombre];
    }

    
    $palabrasExcluidas = ['de', 'y', 'a', 'del', 'la', 'los', 'las', 'en'];
    $partes = explode(' ', $nombre);
    $numeral = '';
    $ultimoTermino = end($partes);
    if (in_array(strtoupper($ultimoTermino), ['I', 'II', 'III', 'IV', 'V', 'VI'])) {
        $numeral = ' ' . array_pop($partes);
    }
    $iniciales = '';
    foreach ($partes as $palabra) {
        if (!in_array(strtolower($palabra), $palabrasExcluidas) && mb_strlen($palabra) > 0) {
            $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
        }
    }
    return $iniciales . $numeral;
}

$oReporteHorario = new ReporteHorarioDocente();

if (isset($_POST['generar_rhd_report'])) {
    // Separar año y tipo del valor combinado
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';
    $cedulaDocenteSeleccionada = $_POST['cedula_docente'] ?? '';
    
    // Verificar si es intensivo
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';
    
    // Validar campos requeridos
    if (empty($cedulaDocenteSeleccionada) || empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar Año, Tipo y Docente.");
    }
    
    // Solo requerir fase si NO es intensivo
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oReporteHorario->setAnio($anio);
    $oReporteHorario->setAniTipo($ani_tipo);
    $oReporteHorario->setFase($fase);
    $oReporteHorario->set_cedula_docente($cedulaDocenteSeleccionada);

    $infoDocente = $oReporteHorario->obtenerInfoDocente();
    $asignacionesAcademicas = $oReporteHorario->obtenerAsignacionesAcademicas();
    $otrasActividades = $oReporteHorario->obtenerOtrasActividades();
    $datosParrillaHorario = $oReporteHorario->obtenerDatosParrillaHorario();
    $turnos_db = $oReporteHorario->getTurnos();

    if (!$infoDocente) {
        die("Error: No se encontró información para el docente seleccionado.");
    }
    
    $gridData = [];
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];
    $activeShifts = [];

    $clases_por_bloque = [];
    foreach ($datosParrillaHorario as $item) {
        $bloque_key = $item['hor_dia'] . '_' . $item['hor_horainicio'];
        $clases_por_bloque[$bloque_key][] = $item;
    }

    foreach ($clases_por_bloque as $clases_en_este_bloque) {
        $ucs_en_bloque = [];
        foreach ($clases_en_este_bloque as $clase) {
            if (!isset($ucs_en_bloque[$clase['uc_codigo']])) {
                $ucs_en_bloque[$clase['uc_codigo']] = ['data' => $clase, 'subgrupos' => [], 'secciones' => [], 'ambientes' => []];
            }
            if (!empty($clase['subgrupo'])) { $ucs_en_bloque[$clase['uc_codigo']]['subgrupos'][] = $clase['subgrupo']; }
            $ucs_en_bloque[$clase['uc_codigo']]['secciones'][] = $clase['sec_codigo'];
            $ucs_en_bloque[$clase['uc_codigo']]['ambientes'][] = $clase['esp_codigo_formatted'];
        }

        foreach ($ucs_en_bloque as $uc_info) {
            $item_base = $uc_info['data'];
            $dia_key_from_db = strtolower(trim(str_replace(['é', 'É'], 'e', $item_base['hor_dia'])));
            $dia_key = $day_map[$dia_key_from_db] ?? ucfirst($dia_key_from_db);
            $horaInicio = new DateTime($item_base['hor_horainicio']);
            $horaFin = new DateTime($item_base['hor_horafin']);

            foreach ($turnos_db as $turno) {
                if ($horaInicio >= new DateTime($turno['tur_horaInicio']) && $horaInicio < new DateTime($turno['tur_horaFin'])) {
                    $activeShifts[ucfirst(strtolower($turno['tur_nombre']))] = true;
                }
            }
            
            $diffMinutes = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 60;
            $bloques_span = round($diffMinutes / 40);
            if ($bloques_span < 1) $bloques_span = 1;

            $subgrupos_unicos = array_unique($uc_info['subgrupos']);
            sort($subgrupos_unicos);
            $subgrupoDisplay = !empty($subgrupos_unicos) ? " G(" . implode(', ', $subgrupos_unicos) . ")" : "";

            $secciones_unicas = array_unique($uc_info['secciones']);
            $seccionesFormateadas = '';
            foreach($secciones_unicas as $sec){
                if (!empty(trim($sec))) {
                    $primerCaracter = substr(trim($sec), 0, 1);

                    $seccionesFormateadas .=  trim($sec) . ', ';
                }
            }
            $seccionesFormateadas = rtrim($seccionesFormateadas, ', ');

            $ambientes_unicos = array_unique($uc_info['ambientes']);
            $ambientesFormateados = implode(', ', $ambientes_unicos);
            
            $nombreUC_Abreviado = abreviarNombreUC($item_base['uc_nombre']);
            $contenidoCeldaSimple = $nombreUC_Abreviado . $subgrupoDisplay . "\n" . $seccionesFormateadas . "\n" . $ambientesFormateados;

            if (!isset($gridData[$dia_key][$horaInicio->format('H:i')])) {
                $gridData[$dia_key][$horaInicio->format('H:i')] = [];
            }
            $gridData[$dia_key][$horaInicio->format('H:i')][] = ['content' => $contenidoCeldaSimple, 'span' => $bloques_span, 'type' => 'class'];
        }
    }

    $bloques_por_turno = [];
    $shiftOrder = ['Mañana' => 1, 'Tarde' => 2, 'Noche' => 3];
    usort($turnos_db, function($a, $b) use ($shiftOrder) {
        $pos_a = $shiftOrder[ucfirst(strtolower($a['tur_nombre']))] ?? 99;
        $pos_b = $shiftOrder[ucfirst(strtolower($b['tur_nombre']))] ?? 99;
        return $pos_a <=> $pos_b;
    });
    
    $todos_los_bloques_ordenados = [];
    foreach ($turnos_db as $turno) {
        $nombre_turno = ucfirst(strtolower($turno['tur_nombre']));
        $bloques = [];
        $tiempoActual = new DateTime($turno['tur_horaInicio']);
        $tiempoFin = new DateTime($turno['tur_horaFin']);
        while ($tiempoActual < $tiempoFin) {
            $inicioBloque = clone $tiempoActual;
            $hora_db_key = $inicioBloque->format('H:i');
            $todos_los_bloques_ordenados[] = $hora_db_key;
            
            $tiempoActual->add(new DateInterval('PT40M'));
            $finBloque = ($tiempoActual > $tiempoFin) ? $tiempoFin : clone $tiempoActual;
            $bloques[$hora_db_key] = $inicioBloque->format('h:i a') . ' a ' . $finBloque->format('h:i a');
        }
        $bloques_por_turno[$nombre_turno] = $bloques;
    }
    $todos_los_bloques_ordenados = array_values(array_unique($todos_los_bloques_ordenados));
    sort($todos_los_bloques_ordenados);

    $occupancyMap = [];
    $dayOccupancyCount = array_fill_keys(array_values($day_map), 0);
    
    foreach ($gridData as $dia => $horas) {
        foreach ($horas as $hora => $clases) {
            if (!empty($clases)) {
                $dayOccupancyCount[$dia] += $clases[0]['span'];
                $span = $clases[0]['span'];
                $currentTime = new DateTime($hora);
                for ($i = 0; $i < $span; $i++) {
                    $occupancyMap[$dia][$currentTime->format('H:i')] = true;
                    $currentTime->add(new DateInterval('PT40M'));
                }
            }
        }
    }
    
    asort($dayOccupancyCount);
    $diasDisponibles = array_keys($dayOccupancyCount);

    $actividadesParaColocar = [
        ['nombre' => 'CREACIÓN INTELECTUAL', 'horas' => $otrasActividades['act_creacion_intelectual'] ?? 0],
        ['nombre' => 'INTEGRACIÓN COMUNIDAD', 'horas' => $otrasActividades['act_integracion_comunidad'] ?? 0],
        ['nombre' => 'GESTIÓN ACADÉMICA', 'horas' => $otrasActividades['act_gestion_academica'] ?? 0],
    ];

    // Algoritmo mejorado para colocar actividades
    foreach ($actividadesParaColocar as $actividad) {
        $horasRestantes = intval($actividad['horas']);
        if ($horasRestantes <= 0) continue;
        
        // Intentar colocar las horas en diferentes días/turnos si es necesario
        foreach ($diasDisponibles as $dia) {
            if ($horasRestantes <= 0) break;
            
            // Buscar bloques libres consecutivos en cada turno activo
            foreach ($bloques_por_turno as $turno => $bloques) {
                if ($horasRestantes <= 0) break;
                if (!isset($activeShifts[$turno])) continue;
                
                // Encontrar el rango de índices para este turno
                $indicesTurno = [];
                foreach ($todos_los_bloques_ordenados as $index => $hora_db) {
                    if (array_key_exists($hora_db, $bloques)) {
                        $indicesTurno[] = $index;
                    }
                }
                
                // Buscar bloques libres consecutivos en este turno
                $i = 0;
                while ($i < count($indicesTurno) && $horasRestantes > 0) {
                    $index = $indicesTurno[$i];
                    $hora_db = $todos_los_bloques_ordenados[$index];
                    
                    // Verificar si este bloque está libre
                    if (!empty($occupancyMap[$dia][$hora_db])) {
                        $i++;
                        continue;
                    }
                    
                    // Contar cuántos bloques consecutivos están libres en este turno
                    $bloquesLibresConsecutivos = 0;
                    $j = $i;
                    while ($j < count($indicesTurno) && $bloquesLibresConsecutivos < $horasRestantes) {
                        $idx = $indicesTurno[$j];
                        $hora = $todos_los_bloques_ordenados[$idx];
                        
                        // Verificar que sea consecutivo (diferencia de 1 en índice global)
                        if ($j > $i && $idx != $indicesTurno[$j-1] + 1) {
                            break; // No es consecutivo, hay un salto
                        }
                        
                        if (!empty($occupancyMap[$dia][$hora])) {
                            break; // Bloque ocupado
                        }
                        
                        $bloquesLibresConsecutivos++;
                        $j++;
                    }
                    
                    // Si encontramos bloques libres, colocar la actividad
                    if ($bloquesLibresConsecutivos > 0) {
                        $bloquesAColocar = min($bloquesLibresConsecutivos, $horasRestantes);
                        
                        $gridData[$dia][$hora_db][] = [
                            'content' => $actividad['nombre'], 
                            'span' => $bloquesAColocar,
                            'type' => 'activity'
                        ];
                        
                        // Marcar bloques como ocupados
                        for ($k = 0; $k < $bloquesAColocar; $k++) {
                            $idx = $indicesTurno[$i + $k];
                            $hora = $todos_los_bloques_ordenados[$idx];
                            $occupancyMap[$dia][$hora] = true;
                        }
                        
                        $horasRestantes -= $bloquesAColocar;
                        $i += $bloquesAColocar;
                    } else {
                        $i++;
                    }
                }
            }
        }
    }
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Horario Docente");

    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setTop(0.5)->setRight(0.5)->setLeft(0.5)->setBottom(0.5);
    
    $styleBold = ['font' => ['bold' => true]];
    $styleCenter = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleLeft = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleSectionHeader = ['font' => ['bold' => true, 'color' => ['argb' => 'FF000000']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E0E0']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $allBorders = ['borders' => [ 'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']], ],];

    $sheet->getColumnDimension('A')->setWidth(18);
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(20); 


    $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-Gestion-Docente\public\assets\img\logo_uptaeb.png';


    if (file_exists($logoPath)) {
    $drawing = new Drawing();
    $drawing->setName('Logo')->setDescription('Logo UPTAEB')->setPath($logoPath);
   
    $drawing->setResizeProportional(false); 
    
   
    $drawing->setHeight(40); 
    $drawing->setWidth(50);
    $drawing->setCoordinates('H1');

    $drawing->setOffsetX(40); 
    $drawing->setOffsetY(5); 
    $drawing->setWorksheet($sheet);
    
    $sheet->getRowDimension(1)->setRowHeight(25);
}
    $sheet->mergeCells('A2:H2')->setCellValue('A2', 'HORARIO DEL PERSONAL DOCENTE');
    $sheet->getStyle('A2')->applyFromArray($styleBold)->getFont()->setSize(16);
    $sheet->getStyle('A2')->applyFromArray($styleCenter);

    $row = 3;
    $sheet->setCellValue('A'.$row, '1. PNF/CARRERA');
    $sheet->mergeCells('B'.$row.':F'.$row)->setCellValue('B'.$row, 'INFORMÁTICA');
    $sheet->setCellValue('G'.$row, '2. LAPSO');
    
    // Mostrar "Intensivo" si es intensivo, o "Fase X - Año" si es regular
    if ($esIntensivo) {
        $sheet->setCellValue('H'.$row, 'Intensivo-' . $anio);
    } else {
        $sheet->setCellValue('H'.$row, toRoman($fase) . '-' . $anio);
    }

    $row = 4;
    $sheet->setCellValue('A'.$row, '3. PROFESOR(A)');
    $sheet->mergeCells('B'.$row.':F'.$row)->setCellValue('B'.$row, $infoDocente['nombreCompleto']);
    $sheet->setCellValue('G'.$row, '4. CÉDULA');
    $sheet->setCellValue('H'.$row, $infoDocente['doc_cedula']);
    $sheet->getStyle('H'.$row)->applyFromArray($styleLeft);
    
$sheet->getStyle('H3')->applyFromArray($styleCenter); 
$sheet->getStyle('H4')->applyFromArray($styleCenter); 
$sheet->getStyle('H5')->applyFromArray($styleCenter);
    $row = 5;
    $sheet->setCellValue('A'.$row, '5. DEDICACIÓN');
    $sheet->setCellValue('B'.$row, $infoDocente['doc_dedicacion']);
    $sheet->setCellValue('C'.$row, '6. CONDICIÓN');
    $sheet->mergeCells('D'.$row.':F'.$row)->setCellValue('D'.$row, $infoDocente['doc_condicion']);
    $sheet->setCellValue('G'.$row, '7. CATEGORÍA');
    $sheet->setCellValue('H'.$row, $infoDocente['categoria']);

    
    $row = 6;
    $sheet->mergeCells('A'.$row.':A'.($row + 1))->setCellValue('A'.$row, "8. TÍTULO DE\nPREGRADO");
    $sheet->getStyle('A'.$row)->getAlignment()->setWrapText(true);
    
     $sheet->mergeCells('B'.$row.':C'.($row + 1));
    
    $titulosPregrado = str_replace(', ', "\n", $infoDocente['pregrado_titulo'] ?? '');
    $sheet->setCellValue('B'.$row, $titulosPregrado);
    
    $sheet->getStyle('B'.$row)->applyFromArray($styleCenter)->getAlignment()->setWrapText(true);

  
    $sheet->mergeCells('D'.$row.':D'.($row + 1))->setCellValue('D'.$row, '9. POSTGRADO');
    $sheet->mergeCells('E'.$row.':H'.($row + 1));
    
    $titulosPostgrado = str_replace(', ', "\n", $infoDocente['postgrado_titulo'] ?? '');
    $sheet->setCellValue('E'.$row, $titulosPostgrado);
    
    $sheet->getStyle('E'.$row)->applyFromArray($styleCenter)->getAlignment()->setWrapText(true);


    $sheet->getStyle('B3')->applyFromArray($styleBold); 
$sheet->getStyle('H3')->applyFromArray($styleBold); 
$sheet->getStyle('B4')->applyFromArray($styleBold); 
$sheet->getStyle('H4')->applyFromArray($styleBold); 
$sheet->getStyle('B5')->applyFromArray($styleBold); 
$sheet->getStyle('D5')->applyFromArray($styleBold); 
$sheet->getStyle('H5')->applyFromArray($styleBold); 
$sheet->getStyle('B6')->applyFromArray($styleBold); 
$sheet->getStyle('E6')->applyFromArray($styleBold); 

    $sheet->getStyle('A3:H7')->applyFromArray($allBorders);
    $sheet->getStyle('A3:A7')->applyFromArray($styleBold);
    $sheet->getStyle('C5')->applyFromArray($styleBold);
    $sheet->getStyle('G3:G5')->applyFromArray($styleBold);
    $sheet->getStyle('D6:D7')->applyFromArray($styleBold);

    $row = 9;
    
    $startRow = $row;
    $sheet->mergeCells('A'.$row.':H'.$row)->setCellValue('A'.$row, 'ACTIVIDADES ACADÉMICAS')->getStyle('A'.$row)->applyFromArray($styleSectionHeader);
    $row++;
    $sheet->mergeCells('A'.$row.':B'.$row)->setCellValue('A'.$row, '10. Unidad Curricular');
    $sheet->setCellValue('C'.$row, '11. Código')->setCellValue('D'.$row, '12. Sección')->setCellValue('E'.$row, '13. Ambiente')->setCellValue('F'.$row, '14. Eje');
    $sheet->mergeCells('G'.$row.':H'.$row)->setCellValue('G'.$row, '15. FASE');
    $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($styleBold)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;

    if (!empty($asignacionesAcademicas)) {
        foreach ($asignacionesAcademicas as $item) {
            $sheet->mergeCells('A'.$row.':B'.$row)->setCellValue('A'.$row, $item['uc_nombre']);
            $sheet->setCellValue('C'.$row, $item['uc_codigo']);
            
            $seccionesCrudas = explode(', ', $item['secciones']);
            $seccionesConPrefijo = [];
            foreach ($seccionesCrudas as $sec) {
                if (!empty(trim($sec))) {
                    $primerCaracter = substr(trim($sec), 0, 1);
                    $seccionesConPrefijo[] = trim($sec);
                }
            }
            $seccionesFormateadas = implode(', ', $seccionesConPrefijo);
            $sheet->setCellValue('D'.$row, $seccionesFormateadas);
            
            $sheet->setCellValue('E'.$row, $item['ambientes']);
            $sheet->setCellValue('F'.$row, $item['eje_nombre']);
            $sheet->mergeCells('G'.$row.':H'.$row)->setCellValue('G'.$row, $item['uc_periodo']);
            $sheet->getStyle('D'.$row.':F'.$row)->getAlignment()->setWrapText(true);
            $row++;
        }
    } else {
        $sheet->mergeCells('A'.$row.':H'.$row)->setCellValue('A'.$row, 'No hay asignaciones académicas para este período.');
        $row++;
    }
    $firstDataRow = $startRow + 2;
    $lastDataRow = $row - 1;
    
    if ($lastDataRow >= $firstDataRow) {
        
        $sheet->getStyle('A'.$firstDataRow.':H'.$lastDataRow)->applyFromArray($styleCenter);
    }
    $sheet->getStyle('A'.$startRow.':H'.($row - 1))->applyFromArray($allBorders);
    $row++;

    $startRow = $row;
    $sheet->mergeCells('A'.$row.':H'.$row)->setCellValue('A'.$row, 'CREACIÓN INTELECTUAL, INTEGRACIÓN COMUNIDAD, GESTIÓN ACADÉMICA Y OTRAS ACTIVIDADES')->getStyle('A'.$row)->applyFromArray($styleSectionHeader);
    $row++;
    $sheet->mergeCells('A'.$row.':C'.$row)->setCellValue('A'.$row, '16. Tipo de Actividad');
    $sheet->mergeCells('D'.$row.':F'.$row)->setCellValue('D'.$row, '17. Descripción (Horas)');
    $sheet->mergeCells('G'.$row.':H'.$row)->setCellValue('G'.$row, '18. Dependencia');
    $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($styleBold)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
    $otras = ['CREACIÓN INTELECTUAL' => $otrasActividades['act_creacion_intelectual'] ?? 0, 'INTEGRACIÓN COMUNIDAD' => $otrasActividades['act_integracion_comunidad'] ?? 0, 'GESTIÓN ACADÉMICA' => $otrasActividades['act_gestion_academica'] ?? 0, 'OTRAS ACT. ACADÉMICAS' => $otrasActividades['act_otras'] ?? 0];
    foreach($otras as $label => $valor) {
        $sheet->mergeCells('A'.$row.':C'.$row)->setCellValue('A'.$row, $label);
        $sheet->mergeCells('D'.$row.':F'.$row)->setCellValue('D'.$row, $valor);
        $sheet->mergeCells('G'.$row.':H'.$row);
        $row++;
    }
    $firstDataRow = $startRow + 2;
    $lastDataRow = $row - 1;
    if ($lastDataRow >= $firstDataRow) {
        
        $sheet->getStyle('A'.$firstDataRow.':H'.$lastDataRow)->applyFromArray($styleCenter);
    }

    $sheet->getStyle('A'.$startRow.':H'.($row - 1))->applyFromArray($allBorders);
    $row += 2;

    $startRowHorario = $row;
    uksort($activeShifts, function($a, $b) use ($shiftOrder) { return ($shiftOrder[$a] ?? 99) <=> ($shiftOrder[$b] ?? 99); });
    $turnosString = implode(' / ', array_map('mb_strtoupper', array_keys($activeShifts)));
    
    $sheet->mergeCells('A'.$row.':H'.$row)->setCellValue('A'.$row, '19. HORARIO: ' . $turnosString)->getStyle('A'.$row)->applyFromArray($styleSectionHeader);
    $row++;
    
    $diasDeLaSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $sheet->setCellValue('A'.$row, 'Hora');
    $sheet->setCellValue('B'.$row, 'Lunes');
    $sheet->setCellValue('C'.$row, 'Martes');
    $sheet->setCellValue('D'.$row, 'Miércoles');
    $sheet->setCellValue('E'.$row, 'Jueves');
    $sheet->setCellValue('F'.$row, 'Viernes');
    $sheet->setCellValue('G'.$row, 'Sábado');
    $sheet->setCellValue('H'.$row, 'Observación');
    $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($styleBold)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
    
    $celdasOcupadas = [];
    foreach ($bloques_por_turno as $nombreTurno => $bloques) {
        if (!isset($activeShifts[$nombreTurno])) continue;

        if ($nombreTurno != array_key_first(array_filter($bloques_por_turno, fn($key) => isset($activeShifts[$key]), ARRAY_FILTER_USE_KEY))) {
             $sheet->mergeCells('A'.$row.':H'.$row)->setCellValue('A'.$row, mb_strtoupper($nombreTurno, 'UTF-8'))->getStyle('A'.$row)->applyFromArray($styleSectionHeader);
             $row++;
        }
        
        foreach($bloques as $hora_db => $rango_hora) {
            $sheet->setCellValue('A'.$row, $rango_hora);
            $colIndex = 1;
            foreach($diasDeLaSemana as $dia) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                if (isset($celdasOcupadas[$dia][$hora_db])) {
                    $colIndex++;
                    continue;
                }

                $clases_en_celda = $gridData[$dia][$hora_db] ?? null;
                if ($clases_en_celda) {
                    $primera_clase = $clases_en_celda[0];
                    $span = $primera_clase['span'];
                    
                    if ($span > 1) {
                        $sheet->mergeCells($colLetter.$row.':'.$colLetter.($row + $span - 1));
                        $horaActual = new DateTime($hora_db);
                        for ($i = 1; $i < $span; $i++) {
                            $horaActual->add(new DateInterval('PT40M'));
                            $celdasOcupadas[$dia][$horaActual->format('H:i')] = true;
                        }
                    }
                    
                    $contenidos = [];
                    foreach ($clases_en_celda as $clase) {
                        $contenidos[] = $clase['content'];
                    }
                    $contenido_final = implode("\n----\n", $contenidos);
                    $sheet->getCell($colLetter.$row)->setValue($contenido_final);

                    if (isset($primera_clase['type']) && $primera_clase['type'] === 'class') {
    $sheet->getStyle($colLetter.$row)->applyFromArray($styleBold);
}
                }
                $sheet->getStyle($colLetter.$row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            $sheet->getRowDimension($row)->setRowHeight(35);
            $row++;
        }
    }
    $sheet->getStyle('A'.$startRowHorario.':H'.($row-1))->applyFromArray($allBorders);
    
    $startRowObs = $row;
    $sheet->setCellValue('A'.$row, '20. Observaciones:');
    $sheet->getStyle('A'.$row)->applyFromArray($styleBold)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    $sheet->mergeCells('B'.$row.':H'.$row);
    $sheet->setCellValue('B'.$row, ($infoDocente['doc_observacion'] ?? 'Ninguna.'));
    $sheet->getStyle('B'.$row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    $sheet->getRowDimension($row)->setRowHeight(25);
    $sheet->getStyle('A'.$startRowObs.':H'.$row)->applyFromArray($allBorders);
    $row++;

    
    $row++; 

   
    // Calcular horas de clase únicamente desde la malla curricular de las UCs asignadas
    $totalHorasClase = array_sum(array_column($asignacionesAcademicas, 'totalHorasClase'));
    $creacionIntelectual = $otrasActividades['act_creacion_intelectual'] ?? 0;
    $integracionComunidad = $otrasActividades['act_integracion_comunidad'] ?? 0;
    $asesoriaAcademica = $otrasActividades['act_otras'] ?? 0;
    $gestionAcademica = $otrasActividades['act_gestion_academica'] ?? 0;
    $otrasActAcademicas = 0;
    $granTotalHoras = $totalHorasClase + $creacionIntelectual + $integracionComunidad + $asesoriaAcademica + $gestionAcademica + $otrasActAcademicas;
    
    $startRowFinal = $row;
    $endRowFinal = $startRowFinal + 6;

    
    $sheet->mergeCells('A'.$startRowFinal.':B'.$startRowFinal)->setCellValue('A'.$startRowFinal, '21. TOTAL (Horas Clases + Horas Adm.)');
    $sheet->setCellValue('C'.$startRowFinal, $granTotalHoras);
    $sheet->mergeCells('A'.($startRowFinal+1).':B'.($startRowFinal+1))->setCellValue('A'.($startRowFinal+1), '21.1 Horas Clases');
    $sheet->setCellValue('C'.($startRowFinal+1), $totalHorasClase);
    $sheet->mergeCells('A'.($startRowFinal+2).':B'.($startRowFinal+2))->setCellValue('A'.($startRowFinal+2), '21.2 Creación Intelectual (CI)');
    $sheet->setCellValue('C'.($startRowFinal+2), $creacionIntelectual);
    $sheet->mergeCells('A'.($startRowFinal+3).':B'.($startRowFinal+3))->setCellValue('A'.($startRowFinal+3), '21.3 Integración Comunidad (IC)');
    $sheet->setCellValue('C'.($startRowFinal+3), $integracionComunidad);
    $sheet->mergeCells('A'.($startRowFinal+4).':B'.($startRowFinal+4))->setCellValue('A'.($startRowFinal+4), '21.4 Asesoría Académica (AA)');
    $sheet->setCellValue('C'.($startRowFinal+4), $asesoriaAcademica);
    $sheet->mergeCells('A'.($startRowFinal+5).':B'.($startRowFinal+5))->setCellValue('A'.($startRowFinal+5), '21.5 Gestión Académica (GA)');
    $sheet->setCellValue('C'.($startRowFinal+5), $gestionAcademica);
    $sheet->mergeCells('A'.($startRowFinal+6).':B'.($startRowFinal+6))->setCellValue('A'.($startRowFinal+6), '21.6 Otras Act. Académicas (OAA)');
    $sheet->setCellValue('C'.($startRowFinal+6), $otrasActAcademicas);

    
    $sheet->mergeCells('D'.$startRowFinal.':E'.$startRowFinal)->setCellValue('D'.$startRowFinal, '22. Firma del Profesor');
    
    
    $sheet->mergeCells('D'.($startRowFinal + 1).':E'.($endRowFinal - 1));

    
    $sheet->setCellValue('D'.$endRowFinal, '23. Fecha:'); 
                                                        

    $sheet->mergeCells('F'.$startRowFinal.':H'.$startRowFinal)->setCellValue('F'.$startRowFinal, '24. Vo Bo (Coordinador de PNF o Jefe Dpto)');
    $sheet->mergeCells('F'.($startRowFinal + 1).':H'.($startRowFinal + 1))->setCellValue('F'.($startRowFinal + 1), 'Firma y Sello');
    $sheet->mergeCells('F'.($startRowFinal + 2).':H'.$endRowFinal);
    
    
    $finalRange = 'A'.$startRowFinal.':H'.$endRowFinal;
    $sheet->getStyle($finalRange)->applyFromArray($allBorders);
    
    
    $sheet->getStyle('C33')->applyFromArray($styleBold);
   
    $sheet->getStyle('A'.$startRowFinal.':B'.$endRowFinal)->applyFromArray($styleBold); 
    $sheet->getStyle('D'.$startRowFinal)->applyFromArray($styleBold); 
    $sheet->getStyle('F'.$startRowFinal)->applyFromArray($styleBold);
  
    $sheet->getStyle('H'.$startRowFinal)->applyFromArray($styleBold);  
    $sheet->getStyle('D'.$endRowFinal)->applyFromArray($styleBold);   
     
     $sheet->getStyle('C'.$startRowFinal.':C'.$endRowFinal)->applyFromArray($styleCenter);
    
    $sheet->getStyle('A'.$startRowFinal.':H'.$endRowFinal)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
    $sheet->getStyle('F'.($startRowFinal + 1))->applyFromArray($styleCenter);
    $sheet->getStyle('F'.($startRowFinal + 1))->applyFromArray($styleBold);
    

    $fileName = "HorarioDocente_" . $cedulaDocenteSeleccionada;
    if ($esIntensivo) {
        $fileName .= "_Intensivo";
    }
    $fileName .= ".xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} elseif (isset($_POST['action']) && $_POST['action'] === 'obtener_docentes_por_anio') {
    // Endpoint AJAX para obtener docentes filtrados por año
    header('Content-Type: application/json');
    
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    if (empty($anio) || empty($ani_tipo)) {
        echo json_encode(['success' => false, 'message' => 'Año o tipo no válido']);
        exit;
    }
    
    $docentes = $oReporteHorario->obtenerDocentesPorAnio($anio, $ani_tipo);
    echo json_encode(['success' => true, 'docentes' => $docentes]);
    exit;
} else {
    $listaAnios = $oReporteHorario->getAniosActivos();
    $listaFases = $oReporteHorario->getFases();
    $listaDocentes = $oReporteHorario->obtenerDocentes();
    require_once('views/reportes/rhordocente.php');
}