<?php
// controller/reportes/raularioreportcon.php

require_once 'public/lib/dompdf/vendor/autoload.php'; // Adjust path

use Dompdf\Dompdf;
use Dompdf\Options;

$modeloPath = "model/reportes/raulario.php"; // Adjust path
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/raulario.php"; // Adjust path
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}
$oAulario = new AularioReport();
$listaEspacios = $oAulario->getEspacios();

function format_time_short($time_str) {
    if (empty($time_str) || strlen($time_str) < 5) return '';
    return substr($time_str, 0, 5); // HH:MM
}

if (isset($_POST['generar_aulario_report'])) {
    $selectedEspacioId = isset($_POST['espacio_aul']) ? $_POST['espacio_aul'] : '';

    if (empty($selectedEspacioId)) {
        die("Error: Debe seleccionar un aula. Regrese y seleccione una.");
    }

    $oAulario->set_espacio_id($selectedEspacioId);
    $horarioDataRaw = $oAulario->getHorarioDataByEspacio();
    $distinctDbTimeSlots = $oAulario->getDistinctTimeSlotsForEspacio();
    $espacioCodigo = $oAulario->getEspacioCodigoById($selectedEspacioId);
    if (!$espacioCodigo) $espacioCodigo = "Aula Desconocida";

    // CORRECCIÓN: Usar Sábado con tilde para coincidir con ENUM de BD
    $days_of_week = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    // Nueva lógica de agregación para $gridData
    $gridData = [];
    if ($horarioDataRaw) {
        foreach ($horarioDataRaw as $item) {
            $dia = $item['hor_dia'];
            $horaInicioBD = $item['hor_inicio'];

            if (!isset($gridData[$dia][$horaInicioBD])) {
                // Estructura para cada slot: lista de eventos y lista de docentes únicos del slot
                $gridData[$dia][$horaInicioBD] = ['events_uc_sec' => [], 'all_teachers_in_slot' => []];
            }

            $ucDisplay = $item['UnidadDisplay'];
            if (isset($item['NombreCompletoUC']) && (strtoupper($item['NombreCompletoUC']) === 'TRAYECTO INICIAL' || stripos($item['NombreCompletoUC'], 'PST-') === 0)) {
                 $ucDisplay = $item['NombreCompletoUC'];
            }
            
            // Crear par UC/Sección
            $uc_sec_pair_html = htmlspecialchars($ucDisplay);
            if (!empty($item['NombreSeccion'])) {
                $uc_sec_pair_html .= "<br>" . htmlspecialchars($item['NombreSeccion']);
            } else {
                // Opcional: $uc_sec_pair_html .= "<br>[Sin Sección]";
            }

            // Añadir par UC/Sección a la lista de eventos del slot (evitando duplicados exactos de este par)
            if (!in_array($uc_sec_pair_html, $gridData[$dia][$horaInicioBD]['events_uc_sec'])) {
                $gridData[$dia][$horaInicioBD]['events_uc_sec'][] = $uc_sec_pair_html;
            }

            // Coleccionar todos los docentes únicos para este slot
            if (!empty($item['NombreCompletoDocente']) && !in_array(htmlspecialchars($item['NombreCompletoDocente']), $gridData[$dia][$horaInicioBD]['all_teachers_in_slot'])) {
                $gridData[$dia][$horaInicioBD]['all_teachers_in_slot'][] = htmlspecialchars($item['NombreCompletoDocente']);
            }
        }
    }
    
    $morning_slots_render = [];
    $afternoon_slots_render = [];
    if ($distinctDbTimeSlots) {
        foreach ($distinctDbTimeSlots as $slot) {
            $db_inicio_key = $slot['hor_inicio'];
            $db_fin_key = $slot['hor_fin'];
            $display_string = format_time_short($db_inicio_key) . " a " . format_time_short($db_fin_key);
            
            if (strcmp($db_inicio_key, "13:00:00") < 0) {
                $morning_slots_render[$display_string] = $db_inicio_key;
            } else {
                $afternoon_slots_render[$display_string] = $db_inicio_key;
            }
        }
    }

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>Aulario - ' . htmlspecialchars($espacioCodigo) . '</title>';
    $html .= '<style>
        @page { margin: 20px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #000; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .subheader-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 0px; margin-bottom: 15px; table-layout: fixed; }
        th, td { 
            border: 1px solid #000;
            padding: 2px; 
            text-align: center; 
            vertical-align: top; 
            word-wrap: break-word;
        }
        th { background-color: #D0E4F5; font-weight: bold; font-size: 10px; }
        td.time-slot { 
            font-weight: bold; 
            width: 90px; 
            background-color: #FFFFFF; 
            font-size: 9px; 
            text-align: center;
            vertical-align: middle;
        }
        td.schedule-cell { 
            font-size: 7.5px; /* Ajustado para legibilidad */
            text-align: left; 
            line-height: 1.15; /* Ajustado */
            vertical-align: top;
        }
    </style>';
    $html .= '</head><body>';

    $renderScheduleTable = function($title_suffix, $slots_to_render) use ($days_of_week, $gridData, $espacioCodigo) {
        if (empty($slots_to_render)) return '';

        $tableHtml = '<div class="header-title">' . htmlspecialchars($espacioCodigo) . '</div>';
        $tableHtml .= '<div class="subheader-title">' . $title_suffix . '</div>';
        $tableHtml .= '<table><thead><tr>';
        $tableHtml .= '<th style="width: 90px;">Hora</th>';
        foreach ($days_of_week as $day) {
            $tableHtml .= '<th>' . htmlspecialchars($day) . '</th>';
        }
        $tableHtml .= '</tr></thead><tbody>';
        
        uksort($slots_to_render, function($a_display, $b_display) use ($slots_to_render) {
            $a_db_time = $slots_to_render[$a_display];
            $b_db_time = $slots_to_render[$b_display];
            return strcmp($a_db_time, $b_db_time);
        });

        foreach ($slots_to_render as $displaySlot => $dbStartTimeKey) {
            $tableHtml .= '<tr>';
            $tableHtml .= '<td class="time-slot">' . htmlspecialchars($displaySlot) . '</td>';
            foreach ($days_of_week as $day) {
                $cellContent = '';
                if (isset($gridData[$day][$dbStartTimeKey])) {
                    $slot_data = $gridData[$day][$dbStartTimeKey];
                    $cellParts = [];

                    if (!empty($slot_data['events_uc_sec'])) {
                        // Cada par UC/Sección ya tiene <br> si la sección existe y no está vacía.
                        // Si hay múltiples pares UC/Sección, se unen con un <br> entre ellos.
                        $cellParts[] = implode('<br>', $slot_data['events_uc_sec']);
                    }

                    if (!empty($slot_data['all_teachers_in_slot'])) {
                        // Si ya hay UCs/Secciones, y hay docentes, se añade un <br> antes de la lista de docentes.
                        // Si solo hay docentes, se añaden directamente.
                        $teachers_string = implode('<br>', $slot_data['all_teachers_in_slot']);
                        if (!empty($cellParts) && !empty($teachers_string)) {
                            $cellParts[] = $teachers_string; // Ya hay un <br> implícito por ser nuevo elemento de $cellParts
                        } elseif (empty($cellParts) && !empty($teachers_string)) {
                            $cellParts[] = $teachers_string;
                        }
                    }
                    $cellContent = implode('<br>', $cellParts); // Unir partes de UC/Sec y la lista de Docentes
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
        $html .= '<div class="header-title">' . htmlspecialchars($espacioCodigo) . '</div>';
        $html .= '<p style="text-align:center; margin-top:20px;">No hay franjar horarias definidas para esta aula.</p>';
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
    $outputFileName = "Aulario_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $espacioCodigo) . ".pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;

} else {
    require_once($vistaPath);
}
?>