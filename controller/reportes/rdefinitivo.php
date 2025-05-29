<?php
// controller/reportes/rdefinitivoemitcon.php

require_once 'public/lib/dompdf/vendor/autoload.php'; // Adjust path

use Dompdf\Dompdf;
use Dompdf\Options;

$modeloPath = "model/reportes/rdefinitivo.php"; // Adjust path
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/rdefinitivo.php"; // Adjust path
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}

$oDefinitivo = new DefinitivoEmit();
$listaAnios = $oDefinitivo->obtenerAnios();
$listaFases = $oDefinitivo->obtenerFases();

if (isset($_POST['generar_definitivo_emit'])) {
    $selectedAnio = isset($_POST['anio_def']) ? $_POST['anio_def'] : '';
    $selectedFase = isset($_POST['fase_def']) ? $_POST['fase_def'] : ''; // Puede estar vacío

    if (empty($selectedAnio)) {
        // Esto debería ser manejado por la validación del form, pero como fallback:
        die("Error: El año es un filtro requerido. Por favor, regrese y seleccione un año.");
    }

    $oDefinitivo->set_anio($selectedAnio);
    $oDefinitivo->set_fase($selectedFase); // Pasamos la fase (vacía o con valor)

    $datosReporte = $oDefinitivo->obtenerDatosDefinitivoEmit();

    $groupedData = [];
    if ($datosReporte && count($datosReporte) > 0) {
        foreach ($datosReporte as $row) {
            $teacherKey = $row['IDDocente'];
            if (!isset($groupedData[$teacherKey])) {
                $groupedData[$teacherKey] = [
                    'NombreCompletoDocente' => $row['NombreCompletoDocente'],
                    'CedulaDocente' => $row['CedulaDocente'],
                    'assignments' => []
                ];
            }
            $groupedData[$teacherKey]['assignments'][] = [
                'NombreUnidadCurricular' => $row['NombreUnidadCurricular'],
                'NombreSeccion' => $row['NombreSeccion'],
                'FaseHorario' => $row['FaseHorario'] // Importante para el modo "Todas las Fases"
            ];
        }
    }

    $reportMainTitle = "ORGANIZACIÓN DOCENTE " . htmlspecialchars($selectedAnio);
    $reportSubTitle = "PNF en Informática";
    $faseHeaderDisplay = ""; // Para el encabezado especial de fase única
    $isAllPhasesMode = empty($selectedFase);

    if (!$isAllPhasesMode) {
        $faseHeaderDisplay = "FASE " . htmlspecialchars($selectedFase);
    } else {
        $reportSubTitle .= " - TODAS LAS FASES"; // O ajustar el título principal
    }

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>Definitivo EMIT ' . htmlspecialchars($selectedAnio) . (!empty($selectedFase) ? ' Fase ' . htmlspecialchars($selectedFase) : ' Todas las Fases') . '</title>';
    $html .= '<style>
        @page { margin: 25px 30px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; }
        .report-main-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .report-sub-title { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: middle; }
        th { background-color: #E7E6E6; font-weight: bold; text-align: center; }
        td.docente-cell { font-size: 9px; }
        td.cedula-cell { text-align: right; font-size: 9px; }
        td.uc-cell { font-size: 9px; }
        td.seccion-cell { text-align: center; font-size: 9px; }
        td.fase-cell { text-align: center; font-size: 9px; }

        /* Column widths */
        .col-docente { width: ' . ($isAllPhasesMode ? "28%" : "30%") . '; }
        .col-cedula { width: ' . ($isAllPhasesMode ? "12%" : "15%") . '; }
        .col-fase-header { text-align: center; font-weight: bold; } /* Solo para modo fase única */
        .col-uc { width: ' . ($isAllPhasesMode ? "30%" : "35%") . '; }
        .col-seccion { width: ' . ($isAllPhasesMode ? "20%" : "20%") . '; }
        .col-fase-data { width: 10%; } /* Solo para modo todas las fases */
    </style>';
    $html .= '</head><body>';
    $html .= '<div class="report-main-title">' . $reportMainTitle . '</div>';
    $html .= '<div class="report-sub-title">' . $reportSubTitle . '</div>';

    $html .= '<table><thead>';
    if (!$isAllPhasesMode) { // Estilo helli.PNG para fase única
        $html .= '<tr>';
        $html .= '<th rowspan="2" class="col-docente">DOCENTE</th>';
        $html .= '<th rowspan="2" class="col-cedula">CÉDULA</th>';
        $html .= '<th colspan="2" class="col-fase-header">' . $faseHeaderDisplay . '</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<th class="col-uc">UNIDAD CURRICULAR</th>';
        $html .= '<th class="col-seccion">SECCIÓN</th>';
        $html .= '</tr>';
    } else { // Estilo para todas las fases (con columna Fase)
        $html .= '<tr>';
        $html .= '<th class="col-docente">DOCENTE</th>';
        $html .= '<th class="col-cedula">CÉDULA</th>';
        $html .= '<th class="col-uc">UNIDAD CURRICULAR</th>';
        $html .= '<th class="col-seccion">SECCIÓN</th>';
        $html .= '<th class="col-fase-data">FASE</th>';
        $html .= '</tr>';
    }
    $html .= '</thead><tbody>';

    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);
            $isFirstRowOfTeacher = true;

            if ($rowCount > 0) {
                foreach ($assignments as $assignment) {
                    $html .= '<tr>';
                    if ($isFirstRowOfTeacher) {
                        $html .= '<td rowspan="' . $rowCount . '" class="docente-cell">' . htmlspecialchars($teacherData['NombreCompletoDocente']) . '</td>';
                        $html .= '<td rowspan="' . $rowCount . '" class="cedula-cell">' . htmlspecialchars($teacherData['CedulaDocente']) . '</td>';
                    }
                    $html .= '<td class="uc-cell">' . htmlspecialchars($assignment['NombreUnidadCurricular']) . '</td>';
                    $html .= '<td class="seccion-cell">' . htmlspecialchars($assignment['NombreSeccion']) . '</td>';
                    if ($isAllPhasesMode) {
                        $html .= '<td class="fase-cell">' . htmlspecialchars($assignment['FaseHorario']) . '</td>';
                    }
                    $html .= '</tr>';
                    $isFirstRowOfTeacher = false;
                }
            }
        }
    } else {
        $colspan = $isAllPhasesMode ? 5 : 4;
        $html .= '<tr><td colspan="' . $colspan . '" style="text-align:center; padding: 20px;">No se encontraron datos para los filtros seleccionados (Año: ' . htmlspecialchars($selectedAnio) . (!empty($selectedFase) ? ', Fase: ' . htmlspecialchars($selectedFase) : '') . ').</td></tr>';
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
    $outputFileName = "Definitivo_EMIT_" . str_replace(' ', '_', $selectedAnio) . ($isAllPhasesMode ? "_Todas_Fases" : "_Fase_" . str_replace(' ', '_', $selectedFase)) . ".pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;

} else {
    require_once($vistaPath);
}
?>