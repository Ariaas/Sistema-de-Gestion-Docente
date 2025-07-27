<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once("vendor/autoload.php");
require_once("model/reportes/rhordocente.php");

use Dompdf\Dompdf;
use Dompdf\Options;

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

if (isset($_POST['generar_rhd_report'])) { 
    
  
    $anio = $_POST['anio_id'] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    $cedulaDocenteSeleccionada = $_POST['cedula_docente'] ?? ''; 
    
   
    if (empty($cedulaDocenteSeleccionada) || empty($anio) || empty($fase)) { 
        die("Error: Debe seleccionar Año, Fase y Docente."); 
    }

   
    $oReporteHorario->setAnio($anio);
    $oReporteHorario->setFase($fase);
    $oReporteHorario->set_cedula_docente($cedulaDocenteSeleccionada);

    $infoDocente = $oReporteHorario->obtenerInfoDocente();
    $asignacionesAcademicas = $oReporteHorario->obtenerAsignacionesAcademicas();
    $otrasActividades = $oReporteHorario->obtenerOtrasActividades();
    $datosParrillaHorario = $oReporteHorario->obtenerDatosParrillaHorario();

    if (!$infoDocente) { die("Error: No se encontró información para el docente seleccionado."); }
    
    $parrillaHorario = [];
    $diasDeLaSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    foreach($datosParrillaHorario as $item) {
        $dia = ucfirst(strtolower(trim($item['hor_dia'])));
        $horaInicio = trim(substr($item['hor_horainicio'], 0, 5));
        $seccion = $item['sec_codigo'];
        $primerDigito = substr($seccion, 0, 1);
        $seccionConFormato = in_array($primerDigito, ['3', '4']) ? 'IIN' . $seccion : 'IN' . $seccion;
        
        $contenidoCelda = htmlspecialchars($item['uc_nombre']) . "<br>" . $seccionConFormato;
        $parrillaHorario[$horaInicio][$dia] = $contenidoCelda;
    }

    $horasManana = []; $horasTarde = []; $horasNoche = [];
    $bloquesDeTiempo = [];
    foreach ($datosParrillaHorario as $item) {
        $bloquesDeTiempo[substr($item['hor_horainicio'], 0, 5)] = substr($item['hor_horafin'], 0, 5);
    }
    ksort($bloquesDeTiempo);

    foreach ($bloquesDeTiempo as $inicio => $fin) {
        $rangoVisible = date('g:i', strtotime($inicio)) . ' a ' . date('g:i', strtotime($fin));
        if (strtotime($inicio) < strtotime('13:00:00')) {
            $horasManana[$rangoVisible] = $inicio;
        } elseif (strtotime($inicio) < strtotime('18:00:00')) {
            $horasTarde[$rangoVisible] = $inicio;
        } else {
            $horasNoche[$rangoVisible] = $inicio;
        }
    }

    $totalHorasClase = array_sum(array_column($asignacionesAcademicas, 'totalHorasClase'));

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 25px; } body { font-family: sans-serif; font-size: 8px; } table { width: 100%; border-collapse: collapse; margin-bottom: 5px;}
        th, td { border: 1px solid black; padding: 1px 3px; text-align: center; vertical-align: middle; }
        .tabla-encabezado td { font-weight: bold; text-align: left; } .titulo-principal { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .titulo-seccion { font-weight: bold; text-align: center; background-color: #E0E0E0; } .sin-borde { border: none; }
        .texto-izquierda { text-align: left; } .texto-centro { text-align: center; } .tabla-horario th { background-color: #E0E0E0; font-size: 7px;}
        .tabla-horario td { height: 30px; font-size: 7px; line-height: 1.1; word-wrap: break-word; } .tabla-resumen td { height: auto; }
    </style></head><body>';
    
    $html .= '<div class="titulo-principal">HORARIO DEL PERSONAL DOCENTE</div>';
    $html .= '<table class="sin-borde" style="margin-bottom:0;"><tr><td width="80%" class="sin-borde" style="padding:0;"><table class="tabla-encabezado">
        <tr><td width="18%">1. PNF/CARRERA:</td><td width="32%">INFORMATICA</td><td width="15%">2. LAPSO:</td><td width="35%">'.$anio.'-'.$fase.'</td></tr>
        <tr><td>3. PROFESOR(A):</td><td>'.htmlspecialchars($infoDocente['nombreCompleto']).'</td><td>4. CÉDULA:</td><td>'.htmlspecialchars($infoDocente['doc_cedula']).'</td></tr>
        <tr><td>5. DEDICACIÓN:</td><td>'.htmlspecialchars($infoDocente['doc_dedicacion']).'</td><td>6. CONDICIÓN:</td><td>'.htmlspecialchars($infoDocente['doc_condicion']).'</td></tr>
        <tr><td>8. TITULO DE PREGRADO:</td><td colspan="3">ING EN INFORMATICA</td></tr>
        </table></td><td width="20%" class="sin-borde texto-centro"><img src="https://i.imgur.com/35i612p.png" width="80px"></td></tr>
        <tr><td colspan="2" class="sin-borde" style="padding:0;"><table class="tabla-encabezado">
        <tr><td width="15%">7. CATEGORIA:</td><td width="28%">'.htmlspecialchars($infoDocente['categoria']).'</td><td width="15%">9. POSTGRADO:</td><td width="42%">'.htmlspecialchars($infoDocente['postgrado']).'</td></tr>
        </table></td></tr></table>';

    $html .= '<table><tr><td colspan="6" class="titulo-seccion">ACTIVIDADES ACADÉMICAS</td></tr>
        <tr style="font-size:7px; font-weight:bold;"><td width="38%">10. Unidad Curricular</td><td width="12%">11. Código</td><td width="15%">12. Sección</td><td width="10%">13. Ambiente</td><td width="15%">14. Eje</td><td width="10%">15. FASE</td></tr>';
    if (!empty($asignacionesAcademicas)) {
        foreach($asignacionesAcademicas as $item) {
            $html .= '<tr><td class="texto-izquierda">'.htmlspecialchars($item['uc_nombre']).'</td><td>'.htmlspecialchars($item['uc_codigo']).'</td><td>'.nl2br(htmlspecialchars($item['secciones'])).'</td><td></td><td>'.htmlspecialchars($item['eje_nombre']).'</td><td>'.htmlspecialchars($item['uc_periodo']).'</td></tr>';
        }
    } else { $html .= '<tr><td colspan="6">No hay asignaciones académicas para este período.</td></tr>'; }
    $html .= '</table>';
    
    $html .= '<table><tr><td colspan="3" class="titulo-seccion">CREACIÓN INTELECTUAL, INTEGRACIÓN COMUNIDAD, GESTIÓN ACADÉMICA Y OTRAS ACTIVIDADES</td></tr>
        <tr style="font-weight:bold;"><td width="40%">16. Tipo de Actividad</td><td width="40%">17. Descripción (Horas)</td><td width="20%">18. Dependencia</td></tr>
        <tr><td class="texto-izquierda">CREACIÓN INTELECTUAL</td><td>'.($otrasActividades['act_creacion_intelectual'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">INTEGRACIÓN COMUNIDAD</td><td>'.($otrasActividades['act_integracion_comunidad'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">GESTIÓN ACADEMICA</td><td>'.($otrasActividades['act_gestion_academica'] ?? 0).'</td><td></td></tr>
        <tr><td class="texto-izquierda">OTRAS ACT. ACADEMICAS</td><td>'.($otrasActividades['act_otras'] ?? 0).'</td><td></td></tr>
    </table>';

    $html .= '<table class="tabla-horario"><tr><th colspan="8" class="titulo-seccion">19. HORARIO</th></tr>
        <tr><th width="15%">Hora</th><th width="12.14%">Lunes</th><th width="12.14%">Martes</th><th width="12.14%">Miércoles</th><th width="12.14%">Jueves</th><th width="12.14%">Viernes</th><th width="12.14%">Sábado</th><th width="12.14%">Observación</th></tr>';
    if (!empty($bloquesDeTiempo)) {
        if (!empty($horasManana)) { $html .= '<tr><td colspan="8" class="titulo-seccion" style="font-size: 7px;">Mañana</td></tr>' . renderizarFilasHorario($horasManana, $parrillaHorario, $diasDeLaSemana); }
        if (!empty($horasTarde)) { $html .= '<tr><td colspan="8" class="titulo-seccion" style="font-size: 7px;">Tarde</td></tr>' . renderizarFilasHorario($horasTarde, $parrillaHorario, $diasDeLaSemana); }
        if (!empty($horasNoche)) { $html .= '<tr><td colspan="8" class="titulo-seccion" style="font-size: 7px;">Noche</td></tr>' . renderizarFilasHorario($horasNoche, $parrillaHorario, $diasDeLaSemana); }
    } else { $html .= '<tr><td colspan="8">No hay horas de clase asignadas en el horario para este período.</td></tr>'; }
    $html .= '</table>';

    $html .= '<table class="sin-borde" style="margin-top: 5px; font-size:7px;"><tr><td width="50%" class="sin-borde texto-izquierda" style="vertical-align:top;"><table class="tabla-resumen">
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