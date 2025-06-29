<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rhordocente.php';

use Dompdf\Dompdf;
use Dompdf\Options;


$modeloPath = "model/reportes/rhordocente.php"; 
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/rhordocente.php"; 
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}

$oHorarioDocente = new Reporthorariodocente(); 
$listaDocentes = $oHorarioDocente->getDocentes();


function format_time_short_rhd($time_str) { 
    if (empty($time_str) || strlen($time_str) < 5) return '';
    return substr($time_str, 0, 5);
}

if (isset($_POST['generar_rhd_report'])) { 
    
    $selectedDocenteId = isset($_POST['docente_rhd_name']) ? $_POST['docente_rhd_name'] : ''; 

    if (empty($selectedDocenteId)) {
        die("Error: Debe seleccionar un docente. Regrese y seleccione uno.");
    }

    $oHorarioDocente->set_docente_id($selectedDocenteId);
    $horarioDataRaw = $oHorarioDocente->getHorarioDataByDocente();
    $distinctDbTimeSlots = $oHorarioDocente->getDistinctTimeSlotsForDocente();
    $docenteNombreCompleto = $oHorarioDocente->getDocenteNameById($selectedDocenteId);
    if (!$docenteNombreCompleto) $docenteNombreCompleto = "Docente Desconocido";

    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    $gridData = [];
    if ($horarioDataRaw) {
        foreach ($horarioDataRaw as $item) {
            $dia = $item['hor_dia'];
            $horaInicioBD = $item['hor_inicio'];

            if (!isset($gridData[$dia][$horaInicioBD])) {
                $gridData[$dia][$horaInicioBD] = []; 
            }

            $event_parts = [];
            $displayUC = $item['UnidadDisplay'];
            if (isset($item['NombreCompletoUC']) && (strtoupper($item['NombreCompletoUC']) === 'TRAYECTO INICIAL' || stripos($item['NombreCompletoUC'], 'PST-') === 0)) {
                 $displayUC = $item['NombreCompletoUC'];
            }
            $event_parts[] = htmlspecialchars($displayUC);

            if (!empty($item['NombreSeccion'])) {
                $event_parts[] = htmlspecialchars($item['NombreSeccion']);
            }
            if (!empty($item['NombreEspacio'])) { 
                $event_parts[] = "Aula: " . htmlspecialchars($item['NombreEspacio']);
            }
            
            $formatted_event_string = implode('<br>', $event_parts);
            
            if (!in_array($formatted_event_string, $gridData[$dia][$horaInicioBD])) {
                 $gridData[$dia][$horaInicioBD][] = $formatted_event_string;
            }
        }
    }
    
    $morning_slots_render = [];
    $afternoon_slots_render = [];
    if ($distinctDbTimeSlots) {
        foreach ($distinctDbTimeSlots as $slot) {
            $db_inicio_key = $slot['hor_inicio'];
            $db_fin_key = $slot['hor_fin'];
            $display_string = format_time_short_rhd($db_inicio_key) . " a " . format_time_short_rhd($db_fin_key); // usa función renombrada
            
            if (strcmp($db_inicio_key, "13:00:00") < 0) {
                $morning_slots_render[$display_string] = $db_inicio_key;
            } else {
                $afternoon_slots_render[$display_string] = $db_inicio_key;
            }
        }
    }

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>Horario Docente - ' . htmlspecialchars($docenteNombreCompleto) . '</title>';
    $html .= '<style>
        @page { margin: 20px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #000; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .subheader-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 0px; margin-bottom: 15px; table-layout: fixed; }
        th, td { 
            border: 1px solid #000; padding: 2px; text-align: center; 
            vertical-align: top; word-wrap: break-word;
        }
        th { background-color: #D0E4F5; font-weight: bold; font-size: 10px; }
        td.time-slot { 
            font-weight: bold; width: 90px; background-color: #FFFFFF; 
            font-size: 9px; text-align: center; vertical-align: middle;
        }
        td.schedule-cell { 
            font-size: 7px; text-align: left; 
            line-height: 1.1; vertical-align: top;
        }
    </style>';
    $html .= '</head><body>';

    $renderScheduleTable = function($title_suffix, $slots_to_render) use ($days_of_week, $gridData, $docenteNombreCompleto) {
        if (empty($slots_to_render)) return '';

        $tableHtml = '<div class="header-title">' . htmlspecialchars($docenteNombreCompleto) . '</div>';
        $tableHtml .= '<div class="subheader-title">' . $title_suffix . '</div>';
        $tableHtml .= '<table><thead><tr>';
        $tableHtml .= '<th style="width: 90px;">Hora</th>';
        foreach ($days_of_week as $day) {
            $tableHtml .= '<th>' . htmlspecialchars($day) . '</th>';
        }
        $tableHtml .= '</tr></thead><tbody>';
        
        uksort($slots_to_render, function($a_display, $b_display) use ($slots_to_render) {
            return strcmp($slots_to_render[$a_display], $slots_to_render[$b_display]);
        });

        foreach ($slots_to_render as $displaySlot => $dbStartTimeKey) {
            $tableHtml .= '<tr>';
            $tableHtml .= '<td class="time-slot">' . htmlspecialchars($displaySlot) . '</td>';
            foreach ($days_of_week as $day) {
                $cellContent = '';
                if (isset($gridData[$day][$dbStartTimeKey]) && !empty($gridData[$day][$dbStartTimeKey])) {
                    $cellContent = implode('<br><br>', $gridData[$day][$dbStartTimeKey]);
                }
                $tableHtml .= '<td class="schedule-cell">' . $cellContent . '</td>';
            }
            $tableHtml .= '</tr>';
        }
        $tableHtml .= '</tbody></table>';
        return $tableHtml;
    };

    $morningHtml = $renderScheduleTable("MAÑANA", $morning_slots_render);
    $html .= $morningHtml;

    if (!empty($afternoon_slots_render)) {
        if (!empty($morningHtml)) {
             $html .= '<div style="margin-top: 15px;"></div>';
        }
        $html .= $renderScheduleTable("TARDE", $afternoon_slots_render);
    }
    
    if (empty($distinctDbTimeSlots)) { 
        $html .= '<div class="header-title">' . htmlspecialchars($docenteNombreCompleto) . '</div>';
        $html .= '<p style="text-align:center; margin-top:20px;">Este docente no tiene franjas horarias programadas.</p>';
    }

    $html .= '</body></html>';
    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    if (ob_get_length()) ob_end_clean();
    $safeDocenteName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $docenteNombreCompleto);
    $outputFileName = "HorarioDocente_" . $safeDocenteName . ".pdf"; 
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;

} else {
    require_once($vistaPath);
}
?>