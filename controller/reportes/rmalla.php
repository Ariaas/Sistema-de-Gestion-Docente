<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once ('vendor/autoload.php'); 
require_once ('model/reportes/rmalla.php');

use Dompdf\Dompdf;
use Dompdf\Options;


if (isset($_POST['generar_rmalla_report'])) {
    
   
    if (isset($_POST['malla_codigo']) && !empty($_POST['malla_codigo'])) {
        $mallaCodigo = $_POST['malla_codigo']; 
        $oMallaReport = new MallaReport();
        $mallasData = $oMallaReport->getMallaConUnidades($mallaCodigo);

        $reportTitle = "Plan de Estudio";

     
        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        $html .= '<title>' . htmlspecialchars($reportTitle) . '</title>';
        $html .= '<style>
            @page { margin: 25px; }
            body { font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #333; }
            .header { text-align: center; margin-bottom: 20px; }
            .header h1 { font-size: 14px; margin: 0; }
            .header h2 { font-size: 12px; margin: 0; font-weight: normal; }
            .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            .report-table th, .report-table td { border: 1px solid #000; padding: 4px; text-align: center; }
            .report-table th { background-color: #e0e0e0; font-weight: bold; font-size: 8px; }
            .trayecto-header td { background-color: #c0c0c0; text-align: left; font-weight: bold; padding-left: 5px; }
            .subtotal-row td { background-color: #f0f0f0; font-weight: bold; }
            .text-left { text-align: left; }
        </style>';
        $html .= '</head><body>';

        
        if ($mallasData && count($mallasData) > 0) {
            foreach ($mallasData as $malla) {
                $html .= '<div class="header">';
                $html .= '<h1>' . htmlspecialchars($reportTitle) . ' COHORTE ' . htmlspecialchars($malla['mal_cohorte']) . '</h1>';
                $html .= '<h2>CÓDIGO: ' . htmlspecialchars($malla['mal_codigo']) . ' PROGRAMA NACIONAL DE FORMACIÓN EN INFORMÁTICA (PNFI)</h2>';
                $html .= '</div>';

                $html .= '<table class="report-table">';
                $html .= '<thead><tr><th>CÓDIGO</th><th>UNIDADES DE FORMACIÓN</th><th>HTE</th><th>HTA</th><th>HTI</th><th>UC</th><th>EJE INTEGRADOR</th><th>Período</th><th>Horas Académicas</th></tr></thead>';
                $html .= '<tbody>';

                $totalHteGeneral = 0; $totalHtaGeneral = 0; $totalHtiGeneral = 0; $totalUcGeneral = 0; $totalHorasAcadGeneral = 0;

                foreach ($malla['unidades_por_trayecto'] as $trayectoNombre => $unidades) {
                    $html .= '<tr class="trayecto-header"><td colspan="9">' . htmlspecialchars(strtoupper($trayectoNombre)) . '</td></tr>';
                    
                    $subtotalHte = 0; $subtotalHta = 0; $subtotalHti = 0; $subtotalUc = 0; $subtotalHorasAcad = 0;

                    foreach ($unidades as $uc) {
                        $html .= '<tr>';
                        $html .= '<td>' . htmlspecialchars($uc['uc_codigo']) . '</td>';
                        $html .= '<td class="text-left">' . htmlspecialchars($uc['uc_nombre']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['hte']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['hta']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['hti']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['uc_creditos']) . '</td>';
                        $html .= '<td class="text-left">' . htmlspecialchars($uc['eje_nombre']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['uc_periodo']) . '</td>';
                        $html .= '<td>' . htmlspecialchars($uc['mal_hora_academica']) . '</td>'; 
                        $html .= '</tr>';

                        $subtotalHte += $uc['hte']; $subtotalHta += $uc['hta'];
                        $subtotalHti += $uc['hti']; $subtotalUc += $uc['uc_creditos'];
                        $subtotalHorasAcad += $uc['mal_hora_academica'];
                    }
                    
                    $html .= '<tr class="subtotal-row"><td colspan="2" class="text-left">SUB TOTAL</td><td>' . $subtotalHte . '</td><td>' . $subtotalHta . '</td><td>' . $subtotalHti . '</td><td>' . $subtotalUc . '</td><td colspan="2"></td><td>' . $subtotalHorasAcad . '</td></tr>';
                    
                    $totalHteGeneral += $subtotalHte; $totalHtaGeneral += $subtotalHta;
                    $totalHtiGeneral += $subtotalHti; $totalUcGeneral += $subtotalUc;
                    $totalHorasAcadGeneral += $subtotalHorasAcad;
                }

                $html .= '<tr class="subtotal-row" style="background-color: #a0a0a0;"><td colspan="2" class="text-left">TOTAL GENERAL</td><td>' . $totalHteGeneral . '</td><td>' . $totalHtaGeneral . '</td><td>' . $totalHtiGeneral . '</td><td>' . $totalUcGeneral . '</td><td colspan="2"></td><td>' . $totalHorasAcadGeneral . '</td></tr>';
                $html .= '</tbody></table>';
            }
        } else {
            $html .= '<p style="text-align:center;">No se encontraron datos para la malla curricular seleccionada.</p>';
        }
        
        $html .= '</body></html>';
        
      
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        if (ob_get_length()) ob_end_clean();
        $outputFileName = "Reporte_Malla_Curricular.pdf";
        $dompdf->stream($outputFileName, array("Attachment" => false));
        exit;
    } else {
      
        header('Location: ?pagina=rmalla'); 
        exit;
    }

} else {
  
    $oMallaReport = new MallaReport();
    $listaMallas = $oMallaReport->getMallasActivas();
    require_once('views/reportes/rmalla.php');
}
?>