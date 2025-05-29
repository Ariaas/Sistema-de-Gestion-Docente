<?php
// controller/reportes/ruccon.php

require_once 'public/lib/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


if (!is_file("model/reportes/rcargaAcademica.php")) { // Cambiado a rucmodel.php si ese es el nombre de tu archivo de modelo
    die("Error crítico: No se encuentra el archivo del modelo (rucmodel.php). Contacte al administrador.");
}
require_once("model/reportes/rcargaAcademica.php"); // Cambiado a rucmodel.php

$vistaFormularioUc = "views/reportes/rcargaAcademica.php";
if (!is_file($vistaFormularioUc)) {
    die("Error crítico: No se encuentra el archivo de la vista del formulario (ruc.php). Contacte al administrador.");
}

$oUc = new Carga();
$trayectos = $oUc->obtenerTrayectos();
$secciones = $oUc->obtenerSecciones(); 

if (isset($_POST['generar_uc'])) {
    $oUc->set_trayecto(isset($_POST['trayecto']) ? $_POST['trayecto'] : '');
    $oUc->set_seccion(isset($_POST['seccion']) ? $_POST['seccion'] : '');

    $unidades = $oUc->obtenerUnidadesCurriculares();

    $reportTitle = "CARGA ACADÉMICA GENERAL";
    $nombreTrayectoFiltrado = null;
    if (!empty($_POST['trayecto'])) {
        foreach ($trayectos as $t) {
            if ($t['tra_id'] == $_POST['trayecto']) {
                $nombreTrayectoFiltrado = $t['tra_numero'];
                break;
            }
        }
        if ($nombreTrayectoFiltrado) {
            $reportTitle = "TRAYECTO " . mb_strtoupper($nombreTrayectoFiltrado);
        }
    }

    // --- Pre-procesamiento de datos para rowspan ---
    $groupedData = [];
    if ($unidades && count($unidades) > 0) {
        foreach ($unidades as $uc) {
            $trayectoNum = $uc['Número de Trayecto'];
            $seccionCod = $uc['Código de Sección'];
            // Guardar la fila completa para tener todos los datos necesarios
            $groupedData[$trayectoNum][$seccionCod][] = $uc;
        }
    }

    // --- INICIO DE GENERACIÓN DE HTML PARA EL PDF ---
    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>' . htmlspecialchars($reportTitle) . '</title>';
    $html .= '<style>
        @page { margin: 20px 25px; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #000; }
        .report-title { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { 
            border: 1px solid #000; /* Bordes negros y delgados */
            padding: 2px 4px;     /* Padding reducido */
            text-align: left; 
            vertical-align: middle; /* Centrado vertical para celdas con rowspan */
        }
        th { 
            background-color: #FFFFFF; /* Fondo blanco para encabezados como en RED.jpg */
            font-weight: bold; 
            text-align: center; 
            font-size: 9px;
        }
        td.trayecto-cell, td.seccion-cell {
            text-align: center; /* Centrado horizontal para celdas de Trayecto y Sección */
        }
        .uc-column { width: 40%; }
        .docente-column { width: 30%; }
        .seccion-column { width: 15%; }
        .trayecto-column { width: 15%; }
    </style>';
    $html .= '</head><body>';
    $html .= '<div class="report-title">' . htmlspecialchars($reportTitle) . '</div>';

    $html .= '<table><thead><tr>';
    $html .= '<th class="trayecto-column">Trayecto</th>';
    $html .= '<th class="seccion-column">Sección</th>';
    $html .= '<th class="uc-column">Unidad curricular</th>';
    $html .= '<th class="docente-column">Docente</th>';
    $html .= '</tr></thead><tbody>';

    if (!empty($groupedData)) {
        foreach ($groupedData as $trayectoNum => $seccionesData) {
            $trayectoRowCount = 0;
            foreach ($seccionesData as $unidadesEnSeccion) {
                $trayectoRowCount += count($unidadesEnSeccion);
            }
            $isFirstRowOfTrayecto = true;

            foreach ($seccionesData as $seccionCod => $unidadesEnSeccion) {
                $seccionRowCount = count($unidadesEnSeccion);
                $isFirstRowOfSeccion = true;

                foreach ($unidadesEnSeccion as $uc) {
                    $html .= '<tr>';
                    if ($isFirstRowOfTrayecto && $isFirstRowOfSeccion) {
                        $html .= '<td class="trayecto-cell" rowspan="' . $trayectoRowCount . '">' . htmlspecialchars($trayectoNum) . '</td>';
                    }
                    if ($isFirstRowOfSeccion) {
                        $html .= '<td class="seccion-cell" rowspan="' . $seccionRowCount . '">' . htmlspecialchars($seccionCod) . '</td>';
                    }
                    $html .= '<td>' . htmlspecialchars($uc['Nombre de la Unidad Curricular']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($uc['Nombre Completo del Docente']) . '</td>';
                    $html .= '</tr>';

                    $isFirstRowOfSeccion = false;
                    $isFirstRowOfTrayecto = false;
                }
            }
        }
    } else {
        $html .= '<tr><td colspan="4" style="text-align:center; padding: 20px;">No se encontraron unidades curriculares con los criterios seleccionados.</td></tr>';
    }

    $html .= '</tbody></table>';
    $html .= '</body></html>';

    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    // Habilitar `isPhpEnabled` podría ser necesario si el HTML contiene scripts PHP, pero aquí no es el caso.
    // $options->set('isPhpEnabled', true); // Usualmente no se necesita para HTML generado

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if (ob_get_length()) ob_end_clean();

    $outputFileName = "CargaAcademica_" . ($nombreTrayectoFiltrado ? str_replace(array(' ', '/'), '_', $nombreTrayectoFiltrado) : "General") . ".pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;
} else {
    require_once($vistaFormularioUc);
}
