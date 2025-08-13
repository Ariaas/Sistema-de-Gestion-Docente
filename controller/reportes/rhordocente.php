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

function formatAndSortSections($sectionsArray, $wrapAfter = 2) {
    if (empty($sectionsArray)) return '';
    $formattedSections = [];
    foreach($sectionsArray as $sec_codigo){
        $trayectoNum = substr($sec_codigo, 0, 1);
        if (in_array($trayectoNum, ['0', '1', '2'])) { $formattedSections[] = 'IN' . $sec_codigo; } 
        elseif (in_array($trayectoNum, ['3', '4'])) { $formattedSections[] = 'IIN' . $sec_codigo; } 
        else { $formattedSections[] = $sec_codigo; }
    }
    sort($formattedSections);
    $total = count($formattedSections);
    $output = '';
    foreach ($formattedSections as $index => $section) {
        $output .= $section;
        if ($index < $total - 1) {
            if (($index + 1) % $wrapAfter === 0) { $output .= "<br>"; } 
            else { $output .= ' - '; }
        }
    }
    return $output;
}

function renderizarFilasHorario($bloques_horarios, $datos_parrilla, $dias) {
    $html_tabla = '';
    uksort($bloques_horarios, function($a_key, $b_key) use ($bloques_horarios) {
        return strcmp($bloques_horarios[$a_key], $bloques_horarios[$b_key]);
    });
    foreach ($bloques_horarios as $rango_hora => $hora_db) {
        $html_tabla .= '<tr><td>' . $rango_hora . '</td>';
        foreach ($dias as $dia) {
            $contenido = $datos_parrilla[$hora_db][$dia] ?? '';
            $html_tabla .= '<td>' . $contenido . '</td>';
        }
        $html_tabla .= '<td></td></tr>';
    }
    return $html_tabla;
}

$oReporteHorario = new ReporteHorarioDocente(); 

$turnos_db = $oReporteHorario->getTurnos();
$TODOS_LOS_BLOQUES_MANANA = [];
$TODOS_LOS_BLOQUES_TARDE = [];
$TODOS_LOS_BLOQUES_NOCHE = [];

function generarBloques($horaInicio, $horaFin) {
    $bloques = [];
    try {
        $tiempoActual = new DateTime($horaInicio);
        $tiempoFin = new DateTime($horaFin);
        while ($tiempoActual < $tiempoFin) {
            $inicioBloque = clone $tiempoActual;
            $tiempoActual->add(new DateInterval('PT40M'));
            $finBloque = ($tiempoActual > $tiempoFin) ? $tiempoFin : clone $tiempoActual;
            $formatoDisplay = $inicioBloque->format('h:i a') . ' a ' . $finBloque->format('h:i a');
            $formatoDBKey = $inicioBloque->format('H:i');
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
    } elseif (stripos($turno['tur_nombre'], 'noche') !== false) {
        $TODOS_LOS_BLOQUES_NOCHE = generarBloques($turno['tur_horaInicio'], $turno['tur_horaFin']);
    }
}

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
    
    $horarioAgrupado = [];
    foreach($datosParrillaHorario as $item) {
        $key = trim($item['hor_horainicio']) . '_' . trim($item['hor_dia']) . '_' . trim($item['uc_nombre']) . '_' . trim($item['esp_edificio']) . '_' . trim($item['esp_numero']);
        if (!isset($horarioAgrupado[$key])) {
            $horarioAgrupado[$key] = $item;
            $horarioAgrupado[$key]['secciones_array'] = [];
        }
        $horarioAgrupado[$key]['secciones_array'][] = $item['sec_codigo'];
    }

    $parrillaHorario = [];
    $diasDeLaSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    foreach($horarioAgrupado as $item) {
        $dia = ucfirst(strtolower(trim($item['hor_dia'])));
        $horaInicio = trim(substr($item['hor_horainicio'], 0, 5));
        
        $seccionesFormateadas = formatAndSortSections($item['secciones_array']);
        
        $tipo_espacio = ($item['esp_tipo'] === 'Laboratorio') ? 'Lab:' : 'Aula:';
        $esp_codigo = ($item['esp_tipo'] === 'Laboratorio') 
                        ? $item['esp_numero'] . ' - ' . $item['esp_edificio'] 
                        : $item['esp_edificio'] . ' - ' . $item['esp_numero'];
        
        $contenidoCelda = htmlspecialchars($item['uc_nombre']) . "<br>" . $seccionesFormateadas . "<br>" . htmlspecialchars($tipo_espacio . ' ' . $esp_codigo);
        $parrillaHorario[$horaInicio][$dia] = $contenidoCelda;
    }

    $tieneClasesManana = false; $tieneClasesTarde = false; $tieneClasesNoche = false;
    foreach ($datosParrillaHorario as $item) {
        $inicio = $item['hor_horainicio'];
        if (strtotime($inicio) < strtotime('13:00:00')) { $tieneClasesManana = true; }
        elseif (strtotime($inicio) < strtotime('18:00:00')) { $tieneClasesTarde = true; }
        else { $tieneClasesNoche = true; }
    }

    $totalHorasClase = array_sum(array_column($asignacionesAcademicas, 'totalHorasClase'));
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 25px; } body { font-family: sans-serif; font-size: 10px; } table { width: 100%; border-collapse: collapse; margin-bottom: 8px;}
        th, td { border: 1px solid black; padding: 4px; text-align: center; vertical-align: middle; }
        .tabla-encabezado td { font-weight: bold; text-align: left; } .titulo-principal { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 10px; }
        .titulo-seccion { font-weight: bold; text-align: center; background-color: #E0E0E0; } .sin-borde { border: none; }
        .texto-izquierda { text-align: left; } .texto-centro { text-align: center; } .tabla-horario th { background-color: #E0E0E0; font-size: 9px;}
        .tabla-horario td { height: 45px; font-size: 9px; line-height: 1.2; word-wrap: break-word; } .tabla-resumen td { height: auto; font-size: 9px; }
        .no-margin-bottom { margin-bottom: 0; }
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
            <td style="width:15%">2. LAPSO:</td><td style="width:35%" colspan="2">' . $anio . '-' . toRoman($fase) . '</td>
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
        <tr style="font-size:9px; font-weight:bold;"><td width="38%">10. Unidad Curricular</td><td width="12%">11. Código</td><td width="15%">12. Sección</td><td width="10%">13. Ambiente</td><td width="15%">14. Eje</td><td width="10%">15. FASE</td></tr>';
    if (!empty($asignacionesAcademicas)) {
        foreach($asignacionesAcademicas as $item) {
            $html .= '<tr><td class="texto-izquierda">'.htmlspecialchars($item['uc_nombre']).'</td><td>'.htmlspecialchars($item['uc_codigo']).'</td><td>'.nl2br(htmlspecialchars($item['secciones'])).'</td><td>'.nl2br(htmlspecialchars($item['ambientes'])).'</td><td>'.htmlspecialchars($item['eje_nombre']).'</td><td>'.htmlspecialchars($item['uc_periodo']).'</td></tr>';
        }
    } else { $html .= '<tr><td colspan="6">No hay asignaciones académicas para este período.</td></tr>'; }
    $html .= '</table>';
    
    $html .= '<table><tr><td colspan="3" class="titulo-seccion">CREACIÓN INTELECTUAL, INTEGRACIÓN COMUNIDAD, GESTIÓN ACADÉMICA Y OTRAS ACTIVIDADES</td></tr>
        <tr style="font-weight:bold; font-size:9px;"><td width="40%">16. Tipo de Actividad</td><td width="40%">17. Descripción (Horas)</td><td width="20%">18. Dependencia</td></tr>
        <tr><td class="texto-izquierda">CREACIÓN INTELECTUAL</td><td>'.($otrasActividades['act_creacion_intelectual'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">INTEGRACIÓN COMUNIDAD</td><td>'.($otrasActividades['act_integracion_comunidad'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">GESTIÓN ACADEMICA</td><td>'.($otrasActividades['act_gestion_academica'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">OTRAS ACT. ACADEMICAS</td><td>'.($otrasActividades['act_otras'] ?? 0).'</td><td></td></tr>
    </table>';

$turnosActivos = [];
if ($tieneClasesManana) { $turnosActivos[] = 'MAÑANA'; }
if ($tieneClasesTarde)  { $turnosActivos[] = 'TARDE'; }
if ($tieneClasesNoche)  { $turnosActivos[] = 'NOCHE'; }
$turnosString = implode(' / ', $turnosActivos); 
    
  
$html .= '<table class="tabla-horario no-margin-bottom">
    <tr>
        <th colspan="8" class="titulo-seccion" style=" padding: 4px 8px;">
            <div style="float: left;">19.HORARIO</div>
            <div style="text-align: center;">' . $turnosString . '</div>
            <div style="clear: both;"></div>
        </th>
    </tr>
        <tr><th width="15%">Hora</th><th width="12.14%">Lunes</th><th width="12.14%">Martes</th><th width="12.14%">Miércoles</th><th width="12.14%">Jueves</th><th width="12.14%">Viernes</th><th width="12.14%">Sábado</th><th width="12.14%">Observación</th></tr>';
    
    if ($tieneClasesManana) { $html .= '</tr>' . renderizarFilasHorario($TODOS_LOS_BLOQUES_MANANA, $parrillaHorario, $diasDeLaSemana); }
    if ($tieneClasesTarde) { $html .= '</tr>' . renderizarFilasHorario($TODOS_LOS_BLOQUES_TARDE, $parrillaHorario, $diasDeLaSemana); }
    if ($tieneClasesNoche) { $html .= '</tr>' . renderizarFilasHorario($TODOS_LOS_BLOQUES_NOCHE, $parrillaHorario, $diasDeLaSemana); }
    if (!$tieneClasesManana && !$tieneClasesTarde && !$tieneClasesNoche) { $html .= '<tr><td colspan="8">No hay horas de clase asignadas en el horario para este período.</td></tr>'; }

    $html .= '</table>';

    $html .= '<table class="tabla-encabezado">
        <tr>
            <td class="titulo-seccion" style="width:25%; text-align:left;">20. OBSERVACIONES:</td>
            <td class="texto-izquierda">'.htmlspecialchars($infoDocente['doc_observacion'] ?? 'Ninguna.').'</td>
        </tr>
    </table>';
    
    $html .= '<table class="sin-borde" style="margin-top: 8px; font-size:9px;"><tr><td width="50%" class="sin-borde texto-izquierda" style="vertical-align:top;"><table class="tabla-resumen">
        <tr><td colspan="2" class="titulo-seccion">21. TOTAL (Horas Clases + Horas Adm.)</td></tr>
        <tr><td class="texto-izquierda">21.1 Horas Clases</td><td>'.$totalHorasClase.'</td></tr>
        <tr><td class="texto-izquierda">21.2 Creación Intelectual (CI)</td><td>'.($otrasActividades['act_creacion_intelectual'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">21.3 Integración Comunidad (IC)</td><td>'.($otrasActividades['act_integracion_comunidad'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">21.4 Gestión Académica (GA)</td><td>'.($otrasActividades['act_gestion_academica'] ?? 0).'</td></tr>
        <tr><td class="texto-izquierda">21.5 Otras Act. Académicas (OAA)</td><td>'.($otrasActividades['act_otras'] ?? 0).'</td></tr>
        </table></td>
        <td width="50%" class="sin-borde" style="vertical-align: bottom;"><table class="sin-borde">
        <tr><td class="texto-centro sin-borde"><br><br>____________________<br>22. Firma del Profesor</td><td class="texto-centro sin-borde"><br><br>____________________<br>24. Vo Bo (Coordinador de PNF o Jefe Dpto)<br>Firma y Sello</td></tr>
        <tr><td class="texto-izquierda sin-borde" style="padding-top:10px;">23. Fecha:</td><td class="sin-borde"></td></tr>
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