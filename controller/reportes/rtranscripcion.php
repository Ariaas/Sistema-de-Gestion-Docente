<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rtranscripcion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$oReporte = new Transcripcion();

if (isset($_POST['generar_transcripcion'])) {

    $reportData = $oReporte->obtenerTranscripciones();

    $groupedData = [];
    if ($reportData) {
        foreach ($reportData as $row) {
            $teacherKey = $row['IDDocente'];
            if (!isset($groupedData[$teacherKey])) {
                $groupedData[$teacherKey] = [
                    'CedulaDocente' => $row['CedulaDocente'],
                    'NombreCompletoDocente' => $row['NombreCompletoDocente'],
                    'assignments' => []
                ];
            }
            $groupedData[$teacherKey]['assignments'][] = $row;
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("TRANSCRIPCION");

    $styleTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT]];
    $styleDataCenter = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER]];
 
    $sheet->mergeCells('A1:E1')->setCellValue('A1', "ASIGNACION DE SECCIONES");
    $sheet->getStyle('A1:E1')->applyFromArray($styleTitle);

    $sheet->setCellValue('A3', 'N°');
    $sheet->setCellValue('B3', 'CEDULA');
    $sheet->setCellValue('C3', 'NOMBRE Y APELLIDO');
    $sheet->setCellValue('D3', 'UNIDAD CURRICULAR SIN ABREVIATURA');
    $sheet->setCellValue('E3', 'SECCION COMPLETA');
    $sheet->getStyle('A3:E3')->applyFromArray($styleHeader);

    $filaActual = 4;
    $itemNumber = 1;

    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);

            if ($rowCount > 0) {

                if ($rowCount > 1) {
                    $sheet->mergeCells("A{$filaActual}:A" . ($filaActual + $rowCount - 1));
                    $sheet->mergeCells("B{$filaActual}:B" . ($filaActual + $rowCount - 1));
                    $sheet->mergeCells("C{$filaActual}:C" . ($filaActual + $rowCount - 1));
                }
                $sheet->setCellValue("A{$filaActual}", $itemNumber);
                $sheet->setCellValue("B{$filaActual}", $teacherData['CedulaDocente']);
                $sheet->setCellValue("C{$filaActual}", $teacherData['NombreCompletoDocente']);

                $tempFila = $filaActual;
                foreach ($assignments as $assignment) {
                    $sheet->setCellValue("D{$tempFila}", $assignment['NombreUnidadCurricular']);
                    $sheet->setCellValue("E{$tempFila}", $assignment['NombreSeccion']);
                    $tempFila++;
                }

                $filaActual += $rowCount;
                $itemNumber++;
            }
        }
    } else {
        $sheet->mergeCells("A{$filaActual}:E{$filaActual}")->setCellValue("A{$filaActual}", "No se encontraron datos.");
        $filaActual++;
    }

    $rangoTotal = 'A3:E' . ($filaActual - 1);
    $sheet->getStyle($rangoTotal)->applyFromArray($styleBordes);
    $sheet->getStyle('A4:C' . ($filaActual - 1))->applyFromArray($styleData);
    $sheet->getStyle('D4:E' . ($filaActual - 1))->applyFromArray($styleDataCenter);

    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(50);
    $sheet->getColumnDimension('E')->setWidth(20);

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Transcripcion_Asignacion_Secciones.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    require_once("views/reportes/rtranscripcion.php");
}
