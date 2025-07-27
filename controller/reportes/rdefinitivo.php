<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/rdefinitivo.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$oDefinitivo = new DefinitivoEmit();
$vistaFormulario = "views/reportes/rdefinitivo.php";

if (isset($_POST['generar_definitivo_emit'])) {
    $oDefinitivo->set_anio($_POST['anio_id'] ?? '');
    $oDefinitivo->set_fase($_POST['fase'] ?? '');
    $datosReporte = $oDefinitivo->obtenerDatosDefinitivoEmit();

    if (empty($datosReporte)) {
        // ... (El código para el Excel de "Sin Datos" no cambia)
    }

    $groupedData = [];
    foreach ($datosReporte as $row) {
        $groupedData[$row['IDDocente']]['info'] = [
            'NombreCompletoDocente' => $row['NombreCompletoDocente'],
            'CedulaDocente' => $row['CedulaDocente']
        ];
        $groupedData[$row['IDDocente']]['assignments'][] = $row;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("DEFINITIVO EMITC");

    $styleTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleDataCentered = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleBordesDelgados = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $currentYear = $_POST['anio_id'] ?? date('Y');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', "ORGANIZACION DOCENTE $currentYear");
    $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
    $sheet->mergeCells('A2:D2')->setCellValue('A2', "PNF en Informática");
    $sheet->getStyle('A2:D2')->applyFromArray($styleTitle);

    // --- CAMBIO: Se aplican bordes a los títulos ---
    $sheet->getStyle('A1:D2')->applyFromArray($styleBordesDelgados);
    
    $selectedFase = $_POST['fase'] ?? '';
    $phaseHeaderTitle = 'UNIDADES CURRICULARES';
    if ($selectedFase == '1') { $phaseHeaderTitle = 'FASE I'; }
    if ($selectedFase == '2') { $phaseHeaderTitle = 'FASE II'; }
    if ($selectedFase == 'Anual') { $phaseHeaderTitle = 'ANUAL'; }

    $sheet->mergeCells('C3:D3')->setCellValue('C3', $phaseHeaderTitle);
    $sheet->setCellValue('A3', 'DOCENTE');
    $sheet->setCellValue('B3', 'CEDULA');
    $sheet->setCellValue('C4', 'UNIDAD CURRICULAR');
    $sheet->setCellValue('D4', 'SECCION');
    $sheet->getStyle('A3:D4')->applyFromArray($styleHeader);
    $sheet->mergeCells('A3:A4');
    $sheet->mergeCells('B3:B4');

    $filaActual = 5;
    foreach ($groupedData as $teacherData) {
        $startRowTeacher = $filaActual;
        $info = $teacherData['info'];
        $assignments = $teacherData['assignments'];
        
        // --- NUEVO: Lógica para agrupar Unidades Curriculares idénticas ---
        $ucMergeInfo = [];
        foreach ($assignments as $index => $assignment) {
            $ucName = $assignment['NombreUnidadCurricular'];
            if (!isset($ucMergeInfo[$ucName])) {
                $ucMergeInfo[$ucName] = ['start_row' => $filaActual + $index, 'count' => 0];
            }
            $ucMergeInfo[$ucName]['count']++;
        }

        // Escribir los datos
        foreach ($assignments as $assignment) {
            $sheet->setCellValue("C{$filaActual}", $assignment['NombreUnidadCurricular']);
            $sheet->setCellValue("D{$filaActual}", $assignment['NombreSeccion']);
            $filaActual++;
        }
        
        // Combinar celdas de UC
        foreach($ucMergeInfo as $ucName => $infoMerge) {
            if ($infoMerge['count'] > 1) {
                $start = $infoMerge['start_row'];
                $end = $start + $infoMerge['count'] - 1;
                $sheet->mergeCells("C{$start}:C{$end}");
                $sheet->setCellValue("C{$start}", $ucName);
            }
        }
        
        // Combinar celdas de Docente y Cédula
        $endRowTeacher = $filaActual - 1;
        if ($startRowTeacher <= $endRowTeacher) {
            $sheet->mergeCells("A{$startRowTeacher}:A{$endRowTeacher}");
            $sheet->mergeCells("B{$startRowTeacher}:B{$endRowTeacher}");
            $sheet->setCellValue("A{$startRowTeacher}", $info['NombreCompletoDocente']);
            $sheet->setCellValue("B{$startRowTeacher}", $info['CedulaDocente']);
        }
    }

    $rangoTotal = 'A3:D' . ($filaActual - 1);
    $sheet->getStyle('A5:'. 'D' .($filaActual - 1))->applyFromArray($styleData);
    $sheet->getStyle('B5:B' . ($filaActual - 1))->applyFromArray($styleDataCentered);
    $sheet->getStyle('D5:D' . ($filaActual - 1))->applyFromArray($styleDataCentered);
    $sheet->getStyle('C5:C' . ($filaActual - 1))->applyFromArray($styleDataCentered); // Centrar también las UC agrupadas

    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(20);

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Definitivo_EMIT_" . date('Y-m-d_H-i') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $listaAnios = $oDefinitivo->obtenerAnios();
    require_once($vistaFormulario);
}