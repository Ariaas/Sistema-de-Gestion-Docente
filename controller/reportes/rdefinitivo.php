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
    $selectedDocente = $_POST['docente_id'] ?? '';

    $oDefinitivo->set_docente($selectedDocente);
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

    $styleTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleSubtitle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE7E6E6']]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]];

    $currentYear = date('Y');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', "ORGANIZACION DOCENTE $currentYear");
    $sheet->mergeCells('A2:D2')->setCellValue('A2', "PNF en Informática");
    $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
    $sheet->getStyle('A2:D2')->applyFromArray($styleSubtitle);

    $sheet->setCellValue('A4', 'DOCENTE');
    $sheet->setCellValue('B4', 'CEDULA');
    $sheet->setCellValue('C4', 'UNIDAD CURRICULAR');
    $sheet->setCellValue('D4', 'SECCION');
    $sheet->getStyle('A4:D4')->applyFromArray($styleHeader);

    $filaActual = 5;
    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);

            if ($rowCount > 0) {
                if ($rowCount > 1) {
                    $sheet->mergeCells("A{$filaActual}:A" . ($filaActual + $rowCount - 1));
                    $sheet->mergeCells("B{$filaActual}:B" . ($filaActual + $rowCount - 1));
                }
                $sheet->setCellValue("A{$filaActual}", $teacherData['NombreCompletoDocente']);
                $sheet->setCellValue("B{$filaActual}", $teacherData['CedulaDocente']);

                $tempFila = $filaActual;
                foreach ($assignments as $assignment) {
                    $sheet->setCellValue("C{$tempFila}", $assignment['NombreUnidadCurricular']);
                    $sheet->setCellValue("D{$tempFila}", $assignment['NombreSeccion']);
                    $tempFila++;
                }
                $filaActual += $rowCount;
            }
        }
    } else {
        $sheet->mergeCells("A{$filaActual}:D{$filaActual}")->setCellValue("A{$filaActual}", "No se encontraron datos para los filtros seleccionados.");
        $filaActual++;
    }

    $rangoTotal = 'A1:D' . ($filaActual - 1);
    $sheet->getStyle($rangoTotal)->applyFromArray($styleBordes);
    $sheet->getStyle('A5:D' . ($filaActual - 1))->applyFromArray($styleData);
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(20);

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Definitivo_EMIT_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    $listaDocentes = $oDefinitivo->obtenerDocentes();
    require_once("views/reportes/rdefinitivo.php");
}
