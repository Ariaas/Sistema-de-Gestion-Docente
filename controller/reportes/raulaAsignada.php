<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/raulaAsignada.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$modeloPath = "model/reportes/raulaAsignada.php"; 
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/raulaAsignada.php"; 
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}


function format_time_asign_aula($time_str) {
    if (empty($time_str) || strlen($time_str) < 5) return '';
    return substr($time_str, 0, 5); 
}

if (isset($_POST['generar_asignacion_aulas_report'])) {
    $oAsignacionAulas = new AsignacionAulasReport();
    $aulasData = $oAsignacionAulas->getAulasConAsignaciones();

    $reportTitle = "Reporte de Asignación de Aulas";

    $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    $html .= '<title>' . htmlspecialchars($reportTitle) . '</title>';
    $html .= '<style>
        @page { margin: 25px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .report-main-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #000; }
        .aula-block { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .aula-title { font-size: 14px; font-weight: bold; color: #0056b3; margin-bottom: 8px; }
        .dia-title { font-size: 12px; font-weight: bold; margin-top: 10px; margin-bottom: 5px; color: #333; }
        .horario-list { list-style-type: none; padding-left: 20px; margin-bottom: 5px; }
        .horario-list li { font-size: 10px; }
        .no-asignaciones { font-style: italic; color: #777; }
        hr.aula-separator { border: 0; border-top: 1px dashed #ccc; margin: 20px 0; }
        .footer { text-align: center; font-size: 8px; color: #777; position: fixed; bottom: 0px; width:100%; }
    </style>';
    $html .= '</head><body>';
    $html .= '<div class="report-main-title">' . htmlspecialchars($reportTitle) . '</div>';

    if ($aulasData && count($aulasData) > 0) {
        
        $ordenDias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        foreach ($aulasData as $aula) {
            $html .= '<div class="aula-block">';
            $html .= '<div class="aula-title">Aula: ' . htmlspecialchars($aula['esp_codigo']) . ' (Tipo: ' . htmlspecialchars($aula['esp_tipo']) . ')</div>';
            
            $tieneAsignacionesEstaAula = false;
            
            foreach ($ordenDias as $diaNombre) {
                if (isset($aula['horarios_por_dia'][$diaNombre]) && !empty($aula['horarios_por_dia'][$diaNombre])) {
                    $tieneAsignacionesEstaAula = true;
                    $html .= '<div class="dia-title">' . htmlspecialchars($diaNombre) . ':</div>';
                    $html .= '<ul class="horario-list">';
                    foreach ($aula['horarios_por_dia'][$diaNombre] as $horario) {
                        $html .= '<li>' . format_time_asign_aula($horario['inicio']) . ' - ' . format_time_asign_aula($horario['fin']) . '</li>';
                    }
                    $html .= '</ul>';
                }
            }

            if (!$tieneAsignacionesEstaAula) {
                $html .= '<p class="no-asignaciones">Esta aula no tiene asignaciones programadas.</p>';
            }
            $html .= '</div>';
           
        }
    } else {
        $html .= '<p style="text-align:center;">No se encontraron datos de asignación de aulas.</p>';
    }
    
    
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
    $outputFileName = "Reporte_Asignacion_Aulas.pdf";
    $dompdf->stream($outputFileName, array("Attachment" => false));
    exit;

} else {
  
    require_once($vistaPath);
}
?>