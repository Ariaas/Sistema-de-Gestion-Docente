<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rdefinitivo.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$oDefinitivo = new DefinitivoEmit();

if (isset($_POST['generar_definitivo_emit'])) {
    $selectedDocente = $_POST['doc_cedula'] ?? '';
    $selectedSeccion = $_POST['sec_codigo'] ?? '';
    $selectedFase = $_POST['fase'] ?? '';

    $oDefinitivo->set_docente($selectedDocente);
    $oDefinitivo->set_seccion($selectedSeccion);
    $oDefinitivo->set_fase($selectedFase);
    $datosReporte = $oDefinitivo->obtenerDatosDefinitivoEmit();

    $groupedData = [];
    if ($datosReporte) {
        foreach ($datosReporte as $row) {
            $teacherKey = $row['IDDocente'];
            if (!isset($groupedData[$teacherKey])) {
                $groupedData[$teacherKey] = [
                    'NombreCompletoDocente' => $row['NombreCompletoDocente'],
                    'CedulaDocente' => $row['CedulaDocente'],
                    'assignments' => []
                ];
            }
            $groupedData[$teacherKey]['assignments'][] = $row;
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("DEFINITIVO EMITC");

    // --- ESTILOS ---
    $styleTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleDataCentered = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    // --- ENCABEZADOS DEL REPORTE ---
    $currentYear = date('Y');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', "ORGANIZACION DOCENTE $currentYear");
    $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
    $sheet->mergeCells('A2:D2')->setCellValue('A2', "PNF en Informática");
    $sheet->getStyle('A2:D2')->applyFromArray($styleTitle);

    $phaseHeaderTitle = 'UNIDADES CURRICULARES';
    if ($selectedFase == '1') { $phaseHeaderTitle = 'FASE I'; }
    if ($selectedFase == '2') { $phaseHeaderTitle = 'FASE II'; }
    if ($selectedFase == 'anual') { $phaseHeaderTitle = 'ANUAL'; }

    $sheet->mergeCells('C3:D3')->setCellValue('C3', $phaseHeaderTitle);
    $sheet->getStyle('C3:D3')->applyFromArray($styleHeader);

    $sheet->setCellValue('A4', 'DOCENTE');
    $sheet->setCellValue('B4', 'CEDULA');
    $sheet->setCellValue('C4', 'UNIDAD CURRICULAR');
    $sheet->setCellValue('D4', 'SECCION');
    $sheet->getStyle('A3:D4')->applyFromArray($styleHeader);
    $sheet->mergeCells('A3:A4');
    $sheet->mergeCells('B3:B4');

    // --- CUERPO DEL REPORTE ---
    $filaActual = 5;
    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $startRowTeacher = $filaActual;
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);

            foreach ($assignments as $assignment) {
                $sheet->setCellValue("C{$filaActual}", $assignment['NombreUnidadCurricular']);
                $sheet->setCellValue("D{$filaActual}", $assignment['NombreSeccion']);
                $filaActual++;
            }

            if ($rowCount > 1) {
                $sheet->mergeCells("A{$startRowTeacher}:A" . ($filaActual - 1));
                $sheet->mergeCells("B{$startRowTeacher}:B" . ($filaActual - 1));
            }
            $sheet->setCellValue("A{$startRowTeacher}", $teacherData['NombreCompletoDocente']);
            $sheet->setCellValue("B{$startRowTeacher}", $teacherData['CedulaDocente']);
        }
    } else {
        $sheet->mergeCells("A{$filaActual}:D{$filaActual}")->setCellValue("A{$filaActual}", "No se encontraron datos para los filtros seleccionados.");
        $filaActual++;
    }

    // --- APLICAR ESTILOS FINALES Y DIMENSIONES ---
    $rangoTotal = 'A3:D' . ($filaActual - 1);
    $sheet->getStyle($rangoTotal)->applyFromArray($styleData);
    $sheet->getStyle('B5:B' . ($filaActual - 1))->applyFromArray($styleDataCentered);
    $sheet->getStyle('D5:D' . ($filaActual - 1))->applyFromArray($styleDataCentered);

    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(20);

    // --- SALIDA DEL ARCHIVO ---
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Definitivo_EMIT_" . date('Y-m-d_H-i') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $listaDocentes = $oDefinitivo->obtenerDocentes();
    $listaSecciones = $oDefinitivo->obtenerSecciones();
    require_once("views/reportes/rdefinitivo.php");
}