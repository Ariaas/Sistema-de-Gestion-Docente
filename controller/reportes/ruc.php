<?php


require_once 'public/lib/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


if (!is_file("model/reportes/ruc.php")) {
    die("Error crítico: No se encuentra el archivo del modelo (rucm.php). Contacte al administrador.");
}
require_once("model/reportes/ruc.php");

$vistaFormularioUc = "views/reportes/ruc.php";
if (!is_file($vistaFormularioUc)) {
    die("Error crítico: No se encuentra el archivo de la vista del formulario (ruc.php). Contacte al administrador.");
}

$oUc = new Ruc(); 
$trayectos = $oUc->obtenerTrayectos(); 
$unidadesc = $oUc->obtenerUc();       


if (isset($_POST['generar_uc'])) {
    $oUc->set_trayecto(isset($_POST['trayecto']) ? $_POST['trayecto'] : '');
    
    $oUc->set_nombreUnidad(isset($_POST['ucurricular']) ? $_POST['ucurricular'] : '');

    $unidadesOriginal = $oUc->obtenerUnidadesCurriculares();

   
    $reportTitle = "Unidad Curricular"; 
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
    
    if (!empty($_POST['ucurricular']) && $unidadesc) {
        $nombreUcFiltrada = "";
        foreach ($unidadesc as $ucSelect) {
            if ($ucSelect['uc_id'] == $_POST['ucurricular']) {
                $nombreUcFiltrada = $ucSelect['uc_nombre'];
                break;
            }
        }
        if ($nombreUcFiltrada) {
            $reportTitle .= ($nombreTrayectoFiltrado ? " - " : " UNIDAD: ") . mb_strtoupper($nombreUcFiltrada);
        }
    }


    
    $ucGroupedData = [];
    if ($unidadesOriginal && count($unidadesOriginal) > 0) {
        foreach ($unidadesOriginal as $row) {
            $ucNombre = $row['Nombre de la Unidad Curricular'];
            $docenteNombre = $row['Nombre Completo del Docente'];
            $seccionCodigo = $row['Código de Sección'];
            $ucGroupedData[$ucNombre][$docenteNombre][] = $seccionCodigo;
        }
    }

  
    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>' . htmlspecialchars($reportTitle) . '</title>';
    $html .= '<style>
        @page { margin: 20px; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #000; }
        .report-title { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; padding: 5px 0;}
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { 
            border: 1px solid #000; 
            padding: 3px 4px; 
            text-align: left; 
            vertical-align: middle;
        }
        th { 
            background-color: #FFFFFF; 
            font-weight: bold; 
            text-align: center; 
            font-size: 10px;
        }
        .seccion-col { width: 25%; text-align: center; } 
        .uc-col { width: 35%; text-align: center; font-weight: bold; }
        .docente-col { width: 40%; }
    </style>';
    $html .= '</head><body>';
    $html .= '<div class="report-title">' . htmlspecialchars($reportTitle) . '</div>';

    $html .= '<table><thead><tr>';
    $html .= '<th class="seccion-col">SECCIÓN</th>';
    $html .= '<th class="uc-col">UNIDAD CURRICULAR</th>';
    $html .= '<th class="docente-col">DOCENTE</th>';
    $html .= '</tr></thead><tbody>';

    if (!empty($ucGroupedData)) {
        foreach ($ucGroupedData as $ucNombre => $docentesData) {
            $ucRowspan = 0;
            foreach ($docentesData as $seccionesDelDocente) {
                $ucRowspan += count($seccionesDelDocente);
            }
            $isFirstRowForUc = true;

            foreach ($docentesData as $docenteNombre => $seccionesDelDocente) {
                $docenteRowspan = count($seccionesDelDocente);
                $isFirstRowForDocente = true;

                foreach ($seccionesDelDocente as $seccionCodigo) {
                    $html .= '<tr>';
                    $html .= '<td class="seccion-col">' . htmlspecialchars($seccionCodigo) . '</td>';
                    if ($isFirstRowForUc) {
                        $html .= '<td class="uc-col" rowspan="' . $ucRowspan . '">' . htmlspecialchars($ucNombre) . '</td>';
                        $isFirstRowForUc = false;
                    }
                    if ($isFirstRowForDocente) {
                        $html .= '<td class="docente-col" rowspan="' . $docenteRowspan . '">' . htmlspecialchars($docenteNombre) . '</td>';
                        $isFirstRowForDocente = false;
                    }
                    $html .= '</tr>';
                }
            }
        }
    } else {
        $html .= '<tr><td colspan="3" style="text-align:center; padding: 20px;">No se encontraron datos con los criterios seleccionados.</td></tr>';
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

    $outputFileName = "Unidad_Curricular_" . ($nombreTrayectoFiltrado ? str_replace(array(' ', '/'), '_', $nombreTrayectoFiltrado) : "General") . ".pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;
} else {
    require_once($vistaFormularioUc);
}
