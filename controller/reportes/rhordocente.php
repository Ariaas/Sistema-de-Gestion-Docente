<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/rhordocente.php");

use Dompdf\Dompdf;
use Dompdf\Options;

function toRoman($number) {
    $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
    return $map[$number] ?? $number;
}

function abreviarNombreUC($nombre, $longitudMaxima = 25) {
    if (mb_strlen($nombre) <= $longitudMaxima) {
        return $nombre;
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
    $anio = $_POST['anio_id'] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    $cedulaDocenteSeleccionada = $_POST['cedula_docente'] ?? ''; 
    if (empty($cedulaDocenteSeleccionada) || empty($anio) || empty($fase)) { die("Error: Debe seleccionar Año, Fase y Docente."); }

    $oReporteHorario->setAnio($anio);
    $oReporteHorario->setFase($fase);
    $oReporteHorario->set_cedula_docente($cedulaDocenteSeleccionada);

    $infoDocente = $oReporteHorario->obtenerInfoDocente();
    $asignacionesAcademicas = $oReporteHorario->obtenerAsignacionesAcademicas();
    $otrasActividades = $oReporteHorario->obtenerOtrasActividades();
    $datosParrillaHorario = $oReporteHorario->obtenerDatosParrillaHorario();

    if (!$infoDocente) { die("Error: No se encontró información para el docente seleccionado."); }
    
    
    $gridData = [];
    $turnos_db = $oReporteHorario->getTurnos();
    $day_map = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'];
    $activeShifts = [];

    $clases_por_bloque = [];
    foreach ($datosParrillaHorario as $item) {
        $bloque_key = $item['hor_dia'] . '_' . $item['hor_horainicio'];
        $clases_por_bloque[$bloque_key][] = $item;
    }

    foreach ($clases_por_bloque as $bloque_key => $clases_en_este_bloque) {
        
        $ucs_en_bloque = [];
        foreach ($clases_en_este_bloque as $clase) {
            $uc_codigo = $clase['uc_codigo'];
            if (!isset($ucs_en_bloque[$uc_codigo])) {
                $ucs_en_bloque[$uc_codigo] = [
                    'data' => $clase,
                    'subgrupos' => [],
                    'secciones' => [],
                    'ambientes' => []
                ];
            }
            if (!empty($clase['subgrupo'])) {
                $ucs_en_bloque[$uc_codigo]['subgrupos'][] = $clase['subgrupo'];
            }
            $ucs_en_bloque[$uc_codigo]['secciones'][] = $clase['sec_codigo'];
            $ucs_en_bloque[$uc_codigo]['ambientes'][] = $clase['esp_codigo_formatted'];
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

            $subgrupoDisplay = !empty($subgrupos_unicos) ? " <b>G(" . implode(', ', $subgrupos_unicos) . ")</b>" : "";
            
            $secciones_unicas = array_unique($uc_info['secciones']);
            $seccionesFormateadas = '';
            foreach($secciones_unicas as $sec){
                $prefijo = (in_array(substr($sec, 0, 1), ['3', '4'])) ? 'IIN' : 'IN';
                $seccionesFormateadas .= $prefijo . $sec . ', ';
            }
            $seccionesFormateadas = rtrim($seccionesFormateadas, ', ');

            $ambientes_unicos = array_unique($uc_info['ambientes']);
            $ambientesFormateados = implode(', ', $ambientes_unicos);

            $nombreUC_Abreviado = abreviarNombreUC(htmlspecialchars($item_base['uc_nombre']));
            $contenidoCelda = "<b>" . $nombreUC_Abreviado . "</b>" . $subgrupoDisplay . "<br>" . $seccionesFormateadas . "<br>" . htmlspecialchars($ambientesFormateados);
            
            if (!isset($gridData[$dia_key][$horaInicio->format('H:i')])) {
                $gridData[$dia_key][$horaInicio->format('H:i')] = [];
            }
            $gridData[$dia_key][$horaInicio->format('H:i')][] = ['content' => $contenidoCelda, 'span' => $bloques_span];
        }
    }
    
    $bloques_por_turno = [];
    $shiftOrder = ['Mañana' => 1, 'Tarde' => 2, 'Noche' => 3];
    usort($turnos_db, function($a, $b) use ($shiftOrder) {
        $pos_a = $shiftOrder[ucfirst(strtolower($a['tur_nombre']))] ?? 99;
        $pos_b = $shiftOrder[ucfirst(strtolower($b['tur_nombre']))] ?? 99;
        return $pos_a <=> $pos_b;
    });

    foreach ($turnos_db as $turno) {
        $nombre_turno = ucfirst(strtolower($turno['tur_nombre']));
        $bloques = [];
        $tiempoActual = new DateTime($turno['tur_horaInicio']);
        $tiempoFin = new DateTime($turno['tur_horaFin']);
        while ($tiempoActual < $tiempoFin) {
            $inicioBloque = clone $tiempoActual;
            $tiempoActual->add(new DateInterval('PT40M'));
            $finBloque = ($tiempoActual > $tiempoFin) ? $tiempoFin : clone $tiempoActual;
            $formatoDisplay = $inicioBloque->format('h:i a') . ' a ' . $finBloque->format('h:i a');
            $formatoDBKey = $inicioBloque->format('H:i');
            $bloques[$formatoDBKey] = $formatoDisplay;
        }
        $bloques_por_turno[$nombre_turno] = $bloques;
    }
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    @page { margin: 25px; } body { font-family: sans-serif; font-size: 7px; } table { width: 100%; border-collapse: collapse; margin-bottom: 8px;}
    th, td { border: 1px solid black; padding: 5px; text-align: center; vertical-align: middle; }
    .tabla-encabezado td { font-weight: bold; text-align: left; } .titulo-principal { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 10px; }
    .titulo-seccion { font-weight: bold; text-align: center; background-color: #E0E0E0; } .sin-borde { border: none; }
    .texto-izquierda { text-align: left; } .texto-centro { text-align: center; } 
    .tabla-horario th { background-color: #E0E0E0; font-size: 7px; font-weight:bold; }
    .tabla-horario td { height: 1px; font-size: 8px; line-height: 1.4; word-wrap: break-word; } 
    .tabla-resumen td { height: auto; font-size: 8px; }
    </style></head><body>';

    
    $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/nuevo gestion docente/Sistema-de-Gestion-Docente/public/assets/img/logo_uptaeb.png';
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageSrc = 'data:image/png;base64,' . $imageData;

    $html .= '<table class="sin-borde"><tr>
        <td width="80%" class="sin-borde" style="padding:0;">
            <div class="titulo-principal">HORARIO DEL PERSONAL DOCENTE</div>
        </td>
        <td width="20%" class="sin-borde texto-centro" style="vertical-align:top;">
            <img src="' . $imageSrc . '" width="90px">
        </td>
    </tr></table>';

    $html .= '<table class="tabla-encabezado">
        <tr>
            <td style="width:15%">1. PNF/CARRERA:</td><td style="width:35%" colspan="2">INFORMATICA</td>
            <td style="width:15%">2. LAPSO:</td><td style="width:35%" colspan="2">' . htmlspecialchars($anio) . '-' . toRoman($fase) . '</td>
        </tr>
        <tr>
            <td>3. PROFESOR(A):</td><td colspan="2">'.htmlspecialchars($infoDocente['nombreCompleto']).'</td>
            <td>4. CÉDULA:</td><td colspan="2">'.htmlspecialchars($infoDocente['doc_cedula']).'</td>
        </tr>
        <tr>
            <td>5. DEDICACIÓN:</td><td>'.htmlspecialchars($infoDocente['doc_dedicacion']).'</td>
            <td>6. CONDICIÓN:</td><td>'.htmlspecialchars($infoDocente['doc_condicion']).'</td>
            <td>7. CATEGORIA:</td><td>'.htmlspecialchars($infoDocente['categoria']).'</td>
        </tr>
        <tr>
            <td>8. TITULO DE PREGRADO:</td><td colspan="2">'.htmlspecialchars($infoDocente['pregrado_titulo'] ?? 'N/A').'</td>
            <td>9. TITULO DE POSTGRADO:</td><td colspan="2">'.htmlspecialchars($infoDocente['postgrado_titulo'] ?? 'N/A').'</td>
        </tr>
    </table>';

    $html .= '<table><tr><td colspan="6" class="titulo-seccion">ACTIVIDADES ACADÉMICAS</td></tr>
        <tr style="font-size:9px; font-weight:bold;"><td width="44%">10. Unidad Curricular</td><td width="12%">11. Código</td><td width="15%">13. Sección</td><td width="8%">14. Ambiente</td><td width="14%">15. Eje</td><td width="7%">16. FASE</td></tr>';
    
    if (!empty($asignacionesAcademicas)) {
        foreach($asignacionesAcademicas as $item) {
            $html .= '<tr><td class="texto-izquierda">'.htmlspecialchars($item['uc_nombre']).'</td><td>'.htmlspecialchars($item['uc_codigo']).'</td><td>'.nl2br(htmlspecialchars($item['secciones'])).'</td><td>'.nl2br(htmlspecialchars($item['ambientes'])).'</td><td>'.htmlspecialchars($item['eje_nombre']).'</td><td>'.htmlspecialchars($item['uc_periodo']).'</td></tr>';
        }
    } else { $html .= '<tr><td colspan="6">No hay asignaciones académicas para este período.</td></tr>'; }
    $html .= '</table>';
    
    $html .= '<table><tr><td colspan="3" class="titulo-seccion">CREACIÓN INTELECTUAL, INTEGRACIÓN COMUNIDAD, GESTIÓN ACADÉMICA Y OTRAS ACTIVIDADES</td></tr>
        <tr style="font-weight:bold; font-size:9px;"><td width="40%">17. Tipo de Actividad</td><td width="40%">18. Descripción (Horas)</td><td width="20%">19. Dependencia</td></tr>
        <tr><td class="texto-izquierda">CREACIÓN INTELECTUAL</td><td>'.($otrasActividades['act_creacion_intelectual'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">INTEGRACIÓN COMUNIDAD</td><td>'.($otrasActividades['act_integracion_comunidad'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">GESTIÓN ACADEMICA</td><td>'.($otrasActividades['act_gestion_academica'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">OTRAS ACT. ACADEMICAS</td><td>'.($otrasActividades['act_otras'] ?? 0).'</td><td></td></tr>
    </table>';
    
    
    $diasDeLaSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    
    uksort($activeShifts, function($a, $b) use ($shiftOrder) {
        return ($shiftOrder[$a] ?? 99) <=> ($shiftOrder[$b] ?? 99);
    });
    $turnosString = implode(' / ', array_map('mb_strtoupper', array_keys($activeShifts)));

    $html .= '<table class="tabla-horario">';
    
    $html .= '<tr><th colspan="8" class="titulo-seccion" style="padding: 4px 8px;">
                <div style="float: left; text-align: left;">20. HORARIO</div>
                <div style="float: right; text-align: center; width: 80%;">'.$turnosString.'</div>
                <div style="clear: both;"></div>
            </th></tr>';

    $html .= '<tr><th width="15%">Hora</th>';
    foreach ($diasDeLaSemana as $dia) { $html .= '<th width="12.14%">'.$dia.'</th>'; }
    $html .= '<th width="12.14%">Observación</th></tr>';
    
    $celdasOcupadas = [];

    foreach ($bloques_por_turno as $nombreTurno => $bloques) {
        if (!isset($activeShifts[$nombreTurno])) continue;

        if ($nombreTurno != array_key_first($activeShifts)) {
                $html .= '<tr><td colspan="8" class="titulo-seccion" style="font-weight: bold;">'.mb_strtoupper($nombreTurno, 'UTF-8').'</td></tr>';
        }
        
        ksort($bloques);
        
        foreach($bloques as $hora_db => $rango_hora) {
            $html .= '<tr><td>'.$rango_hora.'</td>';
            foreach($diasDeLaSemana as $dia) {
                if (isset($celdasOcupadas[$dia][$hora_db])) {
                    continue;
                }

                $clases_en_celda = $gridData[$dia][$hora_db] ?? null;

                if ($clases_en_celda) {
                    $primera_clase = $clases_en_celda[0];
                    $span = $primera_clase['span'];
                    $rowspan = $span > 1 ? ' rowspan="'.$span.'"' : '';
                    
                    if ($span > 1) {
                        $horaActual = new DateTime($hora_db);
                        for ($i = 1; $i < $span; $i++) {
                            $horaActual->add(new DateInterval('PT40M'));
                            $celdasOcupadas[$dia][$horaActual->format('H:i')] = true;
                        }
                    }

                    $contenidos = [];
                    foreach ($clases_en_celda as $clase) {
                        $contenidos[] = '<div style="padding: 3px 0;">' . $clase['content'] . '</div>';
                    }
                    $contenido_final = implode('<div style="border-top: 1px solid #ccc; margin: 1px auto; width: 80%;"></div>', $contenidos);
                    
                    $html .= '<td'.$rowspan.'>'.$contenido_final.'</td>';

                } else {
                    $html .= '<td></td>';
                }
            }
            $html .= '<td></td></tr>';
        }
    }
    $html .= '</table>';

    $totalHorasClase = array_sum(array_column($asignacionesAcademicas, 'totalHorasClase'));
    $granTotalHoras = $totalHorasClase + ($otrasActividades['act_creacion_intelectual'] ?? 0) + ($otrasActividades['act_integracion_comunidad'] ?? 0) + ($otrasActividades['act_gestion_academica'] ?? 0) + ($otrasActividades['act_otras'] ?? 0);

    $html .= '<table class="tabla-encabezado">
        <tr>
            <td class="titulo-seccion" style="width:25%; text-align:left;">21. OBSERVACIONES:</td>
            <td class="texto-izquierda">'.htmlspecialchars($infoDocente['doc_observacion'] ?? 'Ninguna.').'</td>
        </tr>
    </table>';
    
    $html .= '<table class="sin-borde" style="margin-top: 8px; font-size:9px;"><tr><td width="50%" class="sin-borde texto-izquierda" style="vertical-align:top;"><table class="tabla-resumen">
        <tr><td class="texto-izquierda" style="font-weight: bold;">22. TOTAL (Horas Clases + Horas Adm.)</td><td style="font-weight: bold;">'.$granTotalHoras.'</td></tr>
        <tr><td class="texto-izquierda">22.1 Horas Clases</td><td>'.$totalHorasClase.'</td></tr>
        <tr><td class="texto-izquierda">22.2 Creación Intelectual (CI)</td><td>'.($otrasActividades['act_creacion_intelectual'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">22.3 Integración Comunidad (IC)</td><td>'.($otrasActividades['act_integracion_comunidad'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">22.4 Gestión Académica (GA)</td><td>'.($otrasActividades['act_gestion_academica'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">22.5 Otras Act. Académicas (OAA)</td><td>'.($otrasActividades['act_otras'] ?? 0).'</td></tr>
        </table></td>
        <td width="50%" class="sin-borde" style="vertical-align: bottom;"><table class="sin-borde">
        <tr><td class="texto-centro sin-borde"><br><br>____________________<br>23. Firma del Profesor</td><td class="texto-centro sin-borde"><br><br>____________________<br>25. Vo Bo (Coordinador de PNF o Jefe Dpto)<br>Firma y Sello</td></tr>
        <tr><td class="texto-izquierda sin-borde" style="padding-top:10px;">24. Fecha:</td><td class="sin-borde"></td></tr>
        </table></td></tr></table>';
    $html .= '</body></html>';

    $opciones = new Options();
    $opciones->set('isHtml5ParserEnabled', true);
    $opciones->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($opciones);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    if (ob_get_length()) ob_end_clean();
    $dompdf->stream("HorarioDocente_".$cedulaDocenteSeleccionada.".pdf", ["Attachment" => false]);
    exit;

} else {
    $listaAnios = $oReporteHorario->getAniosActivos();
    $listaFases = $oReporteHorario->getFases();
    $listaDocentes = $oReporteHorario->obtenerDocentes();
    require_once('views/reportes/rhordocente.php');
}