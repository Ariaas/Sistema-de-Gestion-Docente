<?php
// controller/reportes/rtranscripcioncon.php

require_once 'public/lib/dompdf/vendor/autoload.php'; // Asegúrate que la ruta a Dompdf es correcta

use Dompdf\Dompdf;
use Dompdf\Options;

// Ruta al modelo de transcripción
$modeloTranscripcionPath = "model/reportes/rtranscripcion.php";
if (!is_file($modeloTranscripcionPath)) {
    die("Error crítico: No se encuentra el archivo del modelo (rtranscripcion.php). Contacte al administrador.");
}
require_once($modeloTranscripcionPath);

// Ruta a la vista del formulario de transcripción
$vistaFormularioTranscripcion = "views/reportes/rtranscripcion.php";
if (!is_file($vistaFormularioTranscripcion)) {
    die("Error crítico: No se encuentra el archivo de la vista del formulario (rtranscripcion.php). Contacte al administrador.");
}
$oReporteAsignacion = new Transcripcion(); // Using the class from rtranscripcionaaa.php
$anios = $oReporteAsignacion->obtenerAnios();
$fases = $oReporteAsignacion->obtenerFases();

if (isset($_POST['generar_transcripcion'])) { // Button name from the form
    $selectedAnio = isset($_POST['anio']) ? $_POST['anio'] : '';
    $selectedFase = isset($_POST['fase']) ? $_POST['fase'] : ''; // Can be empty for "Todas las Fases"

    if (empty($selectedAnio)) {
        // Server-side validation fallback, though JS should catch it.
        // Consider a more user-friendly error display than die().
        die("Error: El Año es un filtro requerido. Por favor, regrese y seleccione un año.");
    }

    $oReporteAsignacion->set_anio($selectedAnio);
    $oReporteAsignacion->set_fase($selectedFase);

    $reportData = $oReporteAsignacion->obtenerTranscripciones();

    $groupedData = [];
    if ($reportData && count($reportData) > 0) {
        foreach ($reportData as $row) {
            $teacherKey = $row['IDDocente'];
            if (!isset($groupedData[$teacherKey])) {
                $groupedData[$teacherKey] = [
                    'CedulaDocente' => $row['CedulaDocente'],
                    'NombreCompletoDocente' => $row['NombreCompletoDocente'],
                    'assignments' => []
                ];
            }
            $groupedData[$teacherKey]['assignments'][] = [
                'NombreUnidadCurricular' => $row['NombreUnidadCurricular'],
                'NombreSeccion' => $row['NombreSeccion'],
                'FaseHorario' => $row['FaseHorario'] // Crucial: Store FaseHorario for each assignment
            ];
        }
    }

    $isAllPhasesMode = empty($selectedFase);
    $reportMainTitle = "ASIGNACIÓN DE SECCIONES";
    $reportSubTitleParts = [];
    if (!empty($selectedAnio)) {
        $reportSubTitleParts[] = "AÑO: " . htmlspecialchars($selectedAnio);
    }
    if (!$isAllPhasesMode) {
        $reportSubTitleParts[] = "FASE: " . htmlspecialchars($selectedFase);
    } else {
        $reportSubTitleParts[] = "TODAS LAS FASES";
    }
    $reportSubTitle = implode(" - ", $reportSubTitleParts);

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>' . htmlspecialchars($reportMainTitle) . '</title>';
    $html .= '<style>
        @page { margin: 20px 25px; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #000; }
        .report-main-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        .report-sub-title { text-align: center; font-size: 11px; font-weight: normal; margin-bottom: 15px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #333; padding: 2px 4px; text-align: left; vertical-align: middle; font-size: 8px; }
        th { background-color: #E0E0E0; font-weight: bold; text-align: center; font-size: 9px; }
        td.text-center { text-align: center; }
        td.cell-seccion { text-align: center; }
        td.cell-fase { text-align: center; }

        /* Column widths - adjusted for potential new Fase column */
        .col-numero { width: 5%; }
        .col-cedula { width: ' . ($isAllPhasesMode ? "11%" : "12%") . '; }
        .col-nombre { width: ' . ($isAllPhasesMode ? "24%" : "28%") . '; }
        .col-uc { width: ' . ($isAllPhasesMode ? "30%" : "35%") . '; }
        .col-seccion { width: ' . ($isAllPhasesMode ? "20%" : "20%") . '; }
        .col-fase-data { width: 10%; } /* Only for all phases mode */
    </style>';
    $html .= '</head><body>';
    $html .= '<div class="report-main-title">' . htmlspecialchars($reportMainTitle) . '</div>';
    if (!empty($reportSubTitle)) {
        $html .= '<div class="report-sub-title">' . htmlspecialchars($reportSubTitle) . '</div>';
    }

    $html .= '<table><thead><tr>';
    $html .= '<th class="col-numero">N°</th>';
    $html .= '<th class="col-cedula">CÉDULA</th>';
    $html .= '<th class="col-nombre">NOMBRE Y APELLIDO</th>';
    $html .= '<th class="col-uc">UNIDAD CURRICULAR SIN ABREVIATURA</th>';
    $html .= '<th class="col-seccion">SECCIÓN COMPLETA</th>';
    if ($isAllPhasesMode) {
        $html .= '<th class="col-fase-data">FASE</th>';
    }
    $html .= '</tr></thead><tbody>';

    if (!empty($groupedData)) {
        $itemNumber = 1;
        foreach ($groupedData as $teacherData) {
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);
            $isFirstRowOfTeacher = true;

            if ($rowCount > 0) {
                foreach ($assignments as $assignment) {
                    $html .= '<tr>';
                    if ($isFirstRowOfTeacher) {
                        $html .= '<td rowspan="' . $rowCount . '" class="text-center">' . $itemNumber . '</td>';
                        $html .= '<td rowspan="' . $rowCount . '">' . htmlspecialchars($teacherData['CedulaDocente']) . '</td>';
                        $html .= '<td rowspan="' . $rowCount . '">' . htmlspecialchars($teacherData['NombreCompletoDocente']) . '</td>';
                    }
                    $html .= '<td>' . htmlspecialchars($assignment['NombreUnidadCurricular']) . '</td>';
                    $html .= '<td class="cell-seccion">' . htmlspecialchars($assignment['NombreSeccion']) . '</td>';
                    if ($isAllPhasesMode) {
                        $html .= '<td class="cell-fase">' . htmlspecialchars($assignment['FaseHorario']) . '</td>';
                    }
                    $html .= '</tr>';
                    $isFirstRowOfTeacher = false;
                }
                $itemNumber++;
            }
        }
    } else {
        $colspan = $isAllPhasesMode ? 6 : 5;
        $html .= '<tr><td colspan="' . $colspan . '" style="text-align:center; padding: 20px; font-size: 10px;">No se encontraron asignaciones con los criterios seleccionados.</td></tr>';
    }

    $html .= '</tbody></table>';
    $html .= '</body></html>';

    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    if (ob_get_length()) ob_end_clean();
    $outputFileName = "AsignacionSecciones";
    if (!empty($selectedAnio)) $outputFileName .= "_" . $selectedAnio;
    if (!$isAllPhasesMode) {
        $outputFileName .= "_Fase_" . htmlspecialchars($selectedFase);
    } else {
        $outputFileName .= "_Todas_Fases";
    }
    $outputFileName .= ".pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;

} else {
    require_once($vistaFormularioTranscripcion);
}
?>