<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/raulaAsignada.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (isset($_POST['generar_asignacion_aulas_report'])) {
    
    
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    $fase = $_POST['fase_id'] ?? '';
    
    
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';
    
    
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un año académico.");
    }
    
    
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }
    
    $oReporte = new AsignacionAulasReport();
    $aulasData = $oReporte->getAulasConAsignaciones($anio, $ani_tipo, $fase);

    $ordenDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $dataPorDia = array_fill_keys($ordenDias, []);

    if ($aulasData) {
        foreach ($aulasData as $row) {
            $dia = str_replace(['Miercoles'], ['Miércoles'], trim($row['hor_dia']));
            if (isset($dataPorDia[$dia])) {
                $dataPorDia[$dia][] = $row['aula_completa'];
            }
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    
    if ($esIntensivo) {
        $tituloHoja = "Aulas $anio Intensivo";
    } else {
        $tituloHoja = "Aulas $anio Fase $fase";
    }
    $sheet->setTitle($tituloHoja);

    
    $styleHeader = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']]];
    $styleCell = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];

    
    $col = 'A';
    foreach ($ordenDias as $dia) {
        $sheet->setCellValue($col . '1', mb_strtoupper($dia, 'UTF-8'));
        $sheet->getStyle($col . '1')->applyFromArray($styleHeader);
        $sheet->getColumnDimension($col)->setWidth(25); 
        $col++;
    }

    $maxRows = 0;
    foreach ($dataPorDia as $aulas) {
        $maxRows = max($maxRows, count($aulas));
    }

    
    if ($maxRows > 0) {
        
        for ($i = 0; $i < $maxRows; $i++) {
            $colChar = 'A';
            foreach ($dataPorDia as $dia => $aulas) {
                $cellValue = isset($aulas[$i]) ? $aulas[$i] : '';
                $sheet->setCellValue($colChar . ($i + 2), $cellValue);
                $colChar++;
            }
        }
        $range = 'A1:' . $sheet->getHighestColumn() . ($maxRows + 1);
        $sheet->getStyle($range)->applyFromArray($styleCell);
    } else {
        
        $sheet->mergeCells('A2:F2')->setCellValue('A2', 'No se encontraron aulas asignadas para el año seleccionado.');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:F2')->applyFromArray($styleCell); 
    }

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    
    
    if ($esIntensivo) {
        $fileName = "Aulas_{$anio}_Intensivo.xlsx";
    } else {
        $fileName = "Aulas_{$anio}_Fase{$fase}.xlsx";
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $oReporte = new AsignacionAulasReport();
    $anios = $oReporte->getAnios();
    $fases = $oReporte->getFases();
    require_once("views/reportes/raulaAsignada.php");
}
?>