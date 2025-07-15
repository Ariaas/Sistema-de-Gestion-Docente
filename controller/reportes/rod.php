<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rod.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$oReporte = new Rod();

if (isset($_POST['generar_reporte_rod'])) {

    $faseId = $_POST['fase_id'] ?? null;
    if (empty($faseId)) {
        die("Error: Debe seleccionar una fase y año para generar el reporte.");
    }
    
    $oReporte->set_fase_y_anio($faseId);
    
    $partesFase = explode('-', $faseId);
    $faseNumero = $partesFase[0] ?? '';
    $faseAnio = $partesFase[1] ?? '';

    $queryData = $oReporte->obtenerDatosReporte();

    $reportData = [];
    if (!empty($queryData)) {
        foreach ($queryData as $row) {
            $docenteId = $row['doc_cedula'];
            if (!isset($reportData[$docenteId])) {
                $reportData[$docenteId] = [
                    'nombre_completo' => $row['nombre_completo'],
                    'doc_cedula' => $row['doc_cedula'],
                    'doc_fecha_ingreso' => $row['doc_fecha_ingreso'],
                    'doc_perfil_profesional' => $row['doc_perfil_profesional'],
                    'doc_dedicacion' => $row['doc_dedicacion'],
                    'doc_anio_concurso' => $row['doc_anio_concurso'],
                    'doc_tipo_concurso' => $row['doc_tipo_concurso'],
                    'doc_horas_max' => (int)$row['doc_horas_max'],
                    'doc_horas_descarga' => (int)$row['doc_horas_descarga'],
                    'doc_observacion' => $row['doc_observacion'],
                    'coordinaciones' => $row['coordinaciones'],
                    'horas_asignadas' => 0,
                    'asignaciones' => []
                ];
            }
            if ($row['uc_nombre']) {
                $reportData[$docenteId]['asignaciones'][] = [
                    'uc_nombre' => $row['uc_nombre'],
                    'sec_codigo' => $row['sec_codigo']
                ];
                $reportData[$docenteId]['horas_asignadas'] += (int)$row['uc_horas'];
            }
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("ORGANIZACION DOCENTE");

    // Estilos
    $headerStyle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $columnHeaderStyle = ['font' => ['bold' => true, 'size' => 8], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $cellStyle = ['font' => ['size' => 8], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $summaryStyle = ['font' => ['bold' => true, 'size' => 8], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $summaryLabelStyle = ['font' => ['bold' => true, 'size' => 8], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
    $summaryValueStyle = ['font' => ['size' => 8], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    
    // Cabecera del Reporte
    $sheet->mergeCells('A2:N2')->setCellValue('A2', 'CUADRO RESUMEN ORGANIZACIÓN DOCENTE')->getStyle('A2:N2')->applyFromArray($headerStyle);
    $sheet->mergeCells('A3:C3')->setCellValue('A3', 'PNF: Informática');
    $sheet->mergeCells('J3:M3')->setCellValue('J3', 'LAPSO: ' . $faseNumero . '-' . $faseAnio);
    
    // Cabecera de las Columnas
    $columnas = ['N°', 'APELLIDOS Y NOMBRES', 'C.I.', 'FECHA DE INGRESO', 'PERFIL PROFESIONAL', 'DEDICACION', 'HORAS ACADEMICAS', 'HORAS ASIGNADAS', 'HORAS DESCARGA', 'FALTA HORAS ACAD', 'UNIDAD CURRICULAR', 'AÑO DE CONCURSO', 'SECCIÓN', 'OBSERVACION'];
    $sheet->fromArray($columnas, NULL, 'A5');
    $sheet->getStyle('A5:N5')->applyFromArray($columnHeaderStyle);
    $sheet->getRowDimension(5)->setRowHeight(45);

    $filaActual = 6;
    $itemNumber = 1;
    $totalHorasAsignadas = 0;
    $totalHorasDescarga = 0;
    $totalHorasFaltantes = 0;
    $resumenDedicacion = ['Exclusivo' => ['total_docentes' => 0, 'total_horas' => 0], 'Tiempo Completo' => ['total_docentes' => 0, 'total_horas' => 0], 'Medio Tiempo' => ['total_docentes' => 0, 'total_horas' => 0], 'Tiempo Convencional' => ['total_docentes' => 0, 'total_horas' => 0]];

    if (!empty($reportData)) {
        foreach ($reportData as $docente) {
            $rowCount = max(1, count($docente['asignaciones']));
            $horasFaltantes = $docente['doc_horas_max'] - $docente['horas_asignadas'] - $docente['doc_horas_descarga'];
            $totalHorasAsignadas += $docente['horas_asignadas'];
            $totalHorasDescarga += $docente['doc_horas_descarga'];
            $totalHorasFaltantes += ($horasFaltantes > 0) ? $horasFaltantes : 0;
            
            $dedicacion = $docente['doc_dedicacion'];
            if (isset($resumenDedicacion[$dedicacion])) {
                $resumenDedicacion[$dedicacion]['total_docentes']++;
                $resumenDedicacion[$dedicacion]['total_horas'] += $docente['horas_asignadas'];
            }

            // Combinar celdas de la información del docente
            $celdasACombinar = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'N'];
            if ($rowCount > 1) {
                foreach ($celdasACombinar as $col) {
                    $sheet->mergeCells("{$col}{$filaActual}:{$col}" . ($filaActual + $rowCount - 1));
                }
            }
            
            // Escribir información del docente
            $sheet->setCellValue("A{$filaActual}", $itemNumber);
            $sheet->setCellValue("B{$filaActual}", $docente['nombre_completo']);
            // ... (resto de la información del docente) ...
            $sheet->setCellValue("C{$filaActual}", $docente['doc_cedula']);
            $sheet->setCellValue("D{$filaActual}", $docente['doc_fecha_ingreso'] ? date('d/m/Y', strtotime($docente['doc_fecha_ingreso'])) : '');
            $sheet->setCellValue("E{$filaActual}", $docente['doc_perfil_profesional']);
            $sheet->setCellValue("F{$filaActual}", $docente['doc_dedicacion']);
            $sheet->setCellValue("G{$filaActual}", $docente['doc_horas_max']);
            $sheet->setCellValue("H{$filaActual}", $docente['horas_asignadas']);
            $sheet->setCellValue("I{$filaActual}", $docente['doc_horas_descarga']);
            $sheet->setCellValue("J{$filaActual}", $horasFaltantes);

            $anioConcurso = $docente['doc_anio_concurso'];
            $tipoConcurso = $docente['doc_tipo_concurso'];
            $concursoDisplay = ($anioConcurso && $anioConcurso !== '0000-00-00') ? date('Y', strtotime($anioConcurso)) . ' - ' . $tipoConcurso : 'N/A';
            $sheet->setCellValue("L{$filaActual}", $concursoDisplay);

            $observaciones = [];
            if (!empty($docente['doc_observacion'])) { $observaciones[] = $docente['doc_observacion']; }
            if (!empty($docente['coordinaciones'])) { $observaciones[] = 'Coordinaciones: ' . $docente['coordinaciones']; }
            $sheet->setCellValue("N{$filaActual}", implode('; ', $observaciones));
            
            // CORRECCIÓN: Escribir cada asignación en su propia fila
            if (!empty($docente['asignaciones'])) {
                $tempFila = $filaActual;
                foreach ($docente['asignaciones'] as $asig) {
                    $sheet->setCellValue("K{$tempFila}", $asig['uc_nombre']);
                    $sheet->setCellValue("M{$tempFila}", $asig['sec_codigo']);
                    $tempFila++;
                }
            }

            $filaActual += $rowCount;
            $itemNumber++;
        }
    }
    
    $finDeDatos = $filaActual > 6 ? $filaActual - 1 : 6;
    $sheet->getStyle('A6:N' . $finDeDatos)->applyFromArray($cellStyle);
    // ... (resto de estilos y resúmenes sin cambios)
    $sheet->getStyle("K6:K{$finDeDatos}")->getAlignment()->setWrapText(true);
    $sheet->getStyle("M6:M{$finDeDatos}")->getAlignment()->setWrapText(true);
    $sheet->getStyle("E6:E{$finDeDatos}")->getAlignment()->setWrapText(true);
    $sheet->getStyle("N6:N{$finDeDatos}")->getAlignment()->setWrapText(true);
    $sheet->getStyle("A6:D{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("F6:J{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("L6:M{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $filaInicioResumen = $filaActual + 2;
    $fila = $filaInicioResumen;
    $sheet->mergeCells("B{$fila}:D{$fila}")->setCellValue("B{$fila}", 'RESUMEN POR DEDICACIÓN')->getStyle("B{$fila}")->getFont()->setBold(true);
    $fila++;
    $sheet->setCellValue("B{$fila}", 'DEDICACIÓN')->getStyle("B{$fila}")->applyFromArray($summaryStyle);
    $sheet->setCellValue("C{$fila}", 'TOTAL DOCENTES')->getStyle("C{$fila}")->applyFromArray($summaryStyle);
    $sheet->setCellValue("D{$fila}", 'TOTAL HORAS ASIGNADAS')->getStyle("D{$fila}")->applyFromArray($summaryStyle);
    $fila++;
    $totalGeneralDocentes = 0;
    foreach ($resumenDedicacion as $nombreDedicacion => $data) {
        $sheet->setCellValue("B{$fila}", $nombreDedicacion)->getStyle("B{$fila}:D{$fila}")->applyFromArray($cellStyle);
        $sheet->setCellValue("C{$fila}", $data['total_docentes'])->getStyle("C{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue("D{$fila}", $data['total_horas'])->getStyle("D{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $totalGeneralDocentes += $data['total_docentes'];
        $fila++;
    }
    $sheet->setCellValue("B{$fila}", 'TOTAL GENERAL')->getStyle("B{$fila}:D{$fila}")->applyFromArray($summaryStyle);
    $sheet->setCellValue("C{$fila}", $totalGeneralDocentes)->getStyle("C{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("D{$fila}", $totalHorasAsignadas)->getStyle("D{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila = $filaInicioResumen;
    $sheet->mergeCells("H{$fila}:I{$fila}")->setCellValue("H{$fila}", 'HORAS ACADEMICAS ASIGNADAS:')->getStyle("H{$fila}")->applyFromArray($summaryLabelStyle);
    $sheet->setCellValue("J{$fila}", $totalHorasAsignadas)->getStyle("J{$fila}")->applyFromArray($summaryValueStyle);
    $fila++;
    $sheet->mergeCells("H{$fila}:I{$fila}")->setCellValue("H{$fila}", 'HORAS FALTANTES:')->getStyle("H{$fila}")->applyFromArray($summaryLabelStyle);
    $sheet->setCellValue("J{$fila}", $totalHorasFaltantes)->getStyle("J{$fila}")->applyFromArray($summaryValueStyle);
    $fila++;
    $sheet->mergeCells("H{$fila}:I{$fila}")->setCellValue("H{$fila}", 'DESCARGAS:')->getStyle("H{$fila}")->applyFromArray($summaryLabelStyle);
    $sheet->setCellValue("J{$fila}", $totalHorasDescarga)->getStyle("J{$fila}")->applyFromArray($summaryValueStyle);

    $anchos = ['A'=>4, 'B'=>22, 'C'=>10, 'D'=>10, 'E'=>22, 'F'=>10, 'G'=>10, 'H'=>10, 'I'=>10, 'J'=>10, 'K'=>28, 'L'=>15, 'M'=>15, 'N'=>25];
    foreach($anchos as $col => $ancho) { $sheet->getColumnDimension($col)->setWidth($ancho); }

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Resumen_Organizacion_Docente.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $listaFases = $oReporte->obtenerFasesActivas();
    require_once("views/reportes/rod.php");
}