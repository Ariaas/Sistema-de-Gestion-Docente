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

    $anioId = $_POST['anio_id'] ?? null;
    $fase = $_POST['fase'] ?? '';

    if (empty($anioId)) {
        die("Error: Debe seleccionar un año académico para generar el reporte.");
    }

    $oReporte->set_anio($anioId);
    $oReporte->set_fase($fase);

    $reportData = $oReporte->obtenerTranscripciones();
    $unassignedData = $oReporte->obtenerCursosSinDocente();

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
    $styleData = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true]];
    $styleDataCenter = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER]];
 
    $sheet->mergeCells('A1:E1')->setCellValue('A1', "ASIGNACION DE SECCIONES");
    $sheet->getStyle('A1:E1')->applyFromArray($styleTitle);

    $sheet->setCellValue('A3', 'N°');
    $sheet->setCellValue('B3', 'CEDULA');
    $sheet->setCellValue('C3', 'NOMBRE Y APELLIDO');
    $sheet->setCellValue('D3', 'UNIDAD CURRICULAR');
    $sheet->setCellValue('E3', 'SECCION');
    $sheet->getStyle('A3:E3')->applyFromArray($styleHeader);

    $filaActual = 4;
    $itemNumber = 1;

    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);

            if ($rowCount > 0) {
                // Combinar celdas para la información del docente
                $startMergeRow = $filaActual;
                $endMergeRow = $filaActual + $rowCount - 1;
                if ($rowCount > 1) {
                    $sheet->mergeCells("A{$startMergeRow}:A{$endMergeRow}");
                    $sheet->mergeCells("B{$startMergeRow}:B{$endMergeRow}");
                    $sheet->mergeCells("C{$startMergeRow}:C{$endMergeRow}");
                }
                $sheet->setCellValue("A{$startMergeRow}", $itemNumber);
                $sheet->setCellValue("B{$startMergeRow}", $teacherData['CedulaDocente']);
                $sheet->setCellValue("C{$startMergeRow}", $teacherData['NombreCompletoDocente']);

                // ▼▼▼ LÓGICA MEJORADA PARA COMBINAR UNIDADES CURRICULARES ▼▼▼

                // 1. Pre-calcular los rangos de celdas a combinar para las Unidades Curriculares
                $ucMergeInfo = [];
                foreach ($assignments as $index => $assignment) {
                    $ucName = $assignment['NombreUnidadCurricular'];
                    if (!isset($ucMergeInfo[$ucName])) {
                        $ucMergeInfo[$ucName] = ['start_row' => $filaActual + $index, 'count' => 0];
                    }
                    $ucMergeInfo[$ucName]['count']++;
                }

                // 2. Escribir los datos de las asignaciones (UC y Sección)
                $tempFila = $filaActual;
                foreach ($assignments as $assignment) {
                    $sheet->setCellValue("D{$tempFila}", $assignment['NombreUnidadCurricular']);
                    $sheet->setCellValue("E{$tempFila}", $assignment['NombreSeccion']);
                    $tempFila++;
                }

                // 3. Aplicar la combinación de celdas para las Unidades Curriculares
                foreach ($ucMergeInfo as $info) {
                    if ($info['count'] > 1) {
                        $start = $info['start_row'];
                        $end = $start + $info['count'] - 1;
                        $sheet->mergeCells("D{$start}:D{$end}");
                    }
                }
                
                $filaActual += $rowCount;
                $itemNumber++;
            }
        }
    } else {
        $sheet->mergeCells("A{$filaActual}:E{$filaActual}")->setCellValue("A{$filaActual}", "No se encontraron asignaciones para los filtros seleccionados.");
        $filaActual++;
    }

    // El resto del código para aplicar estilos y generar el archivo sigue igual
    $rangoTotal = 'A3:E' . max(3, $filaActual - 1);
    $sheet->getStyle($rangoTotal)->applyFromArray($styleBordes);
    $sheet->getStyle("A4:C" . ($filaActual - 1))->applyFromArray($styleDataCenter);
    $sheet->getStyle("D4:E" . ($filaActual - 1))->applyFromArray($styleData);
    $sheet->getStyle("D4:D" . ($filaActual - 1))->applyFromArray($styleDataCenter); // Centrar también la UC

    // Sección "Faltan Docentes"
    $filaActual += 2;
    if (!empty($unassignedData)) {
        $startUnassignedRow = $filaActual;
        $sheet->setCellValue("A{$filaActual}", "FALTAN DOCENTES POR ASIGNAR");
        $sheet->getStyle("A{$filaActual}")->applyFromArray(['font' => ['bold' => true]]);
        $filaActual++;
        foreach ($unassignedData as $item) {
            $sheet->setCellValue("A{$filaActual}", $item['NombreUnidadCurricular']);
            $filaActual++;
        }
        $unassignedRange = "A{$startUnassignedRow}:E" . ($filaActual - 1);
        $sheet->getStyle($unassignedRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
        $sheet->getStyle($unassignedRange)->applyFromArray($styleBordes);
    }
    
    // Ajuste de columnas y descarga
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
    $listaAnios = $oReporte->obtenerAnios();
    require_once("views/reportes/rtranscripcion.php");
}