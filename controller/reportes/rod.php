<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("vendor/autoload.php");
require_once("model/reportes/rod.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$oReporte = new Rod();
$vistaFormulario = "views/reportes/rod.php";

if (isset($_POST['generar_reporte_rod'])) {

    $anioId = $_POST['anio_id'] ?? null;
    $faseNumero = $_POST['fase_id'] ?? null;
    
    if (empty($anioId) || empty($faseNumero)) {
       die("Error: Debe seleccionar un Año y una Fase para generar el reporte.");
    }
    
    $oReporte->set_anio($anioId);
    $oReporte->set_fase($faseNumero);
    $queryData = $oReporte->obtenerDatosReporte();

    if (empty($queryData)) {
        // ... (código para reporte sin resultados, no cambia)
    }

    $reportData = [];
    foreach ($queryData as $row) {
        $docenteId = $row['doc_cedula'];
        if (!isset($reportData[$docenteId])) {
            
            // --- CORRECCIÓN APLICADA: Se implementa la lógica de horas del reporte de ejemplo ---
            $dedicacion = $row['doc_dedicacion'];
            $horas_max = 0; // Valor por defecto

            if ($dedicacion === 'Exclusiva') {
                $horas_max = 16;
            } elseif ($dedicacion === 'Tiempo Completo') {
                $horas_max = 16;
            } elseif ($dedicacion === 'Medio Tiempo') {
                $horas_max = 8; // Valor asumido de la lógica anterior
            } elseif ($dedicacion === 'Tiempo Convencional') {
                $horas_max = 12; // Valor asumido de la lógica anterior
            }
            // --- FIN DE LA CORRECCIÓN ---

            $reportData[$docenteId] = [
                'nombre_completo' => $row['nombre_completo'],
                'doc_cedula' => $row['doc_cedula'],
                'doc_fecha_ingreso' => $row['doc_fecha_ingreso'],
                'doc_perfil_profesional' => $row['doc_perfil_profesional'],
                'doc_dedicacion' => $row['doc_dedicacion'],
                'doc_anio_concurso' => $row['doc_anio_concurso'],
                'doc_tipo_concurso' => $row['doc_tipo_concurso'],
                'doc_horas_max' => $horas_max, // Esta es la columna "HORAS ACADEMICAS"
                'doc_horas_descarga' => (int)($row['doc_horas_descarga'] ?? 0),
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
            $reportData[$docenteId]['horas_asignadas'] += (int)($row['uc_horas'] ?? 0);
        }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("ORGANIZACION DOCENTE");

    $headerStyle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $columnHeaderStyle = ['font' => ['bold' => true, 'size' => 8], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $cellStyle = ['font' => ['size' => 8], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    
    $sheet->mergeCells('A2:N2')->setCellValue('A2', 'CUADRO RESUMEN ORGANIZACIÓN DOCENTE')->getStyle('A2:N2')->applyFromArray($headerStyle);
    $sheet->mergeCells('A3:C3')->setCellValue('A3', 'PNF: Informática');
    $sheet->mergeCells('J3:M3')->setCellValue('J3', 'LAPSO: ' . $faseNumero . '-' . $anioId);
    
    $columnas = ['N°', 'APELLIDOS Y NOMBRES', 'C.I.', 'FECHA DE INGRESO', 'PERFIL PROFESIONAL', 'DEDICACION', 'HORAS ACADEMICAS', 'HORAS ASIGNADAS', 'HORAS DESCARGA', 'FALTA HORAS ACAD', 'UNIDAD CURRICULAR', 'AÑO DE CONCURSO', 'SECCIÓN', 'OBSERVACION'];
    $sheet->fromArray($columnas, NULL, 'A5');
    $sheet->getStyle('A5:N5')->applyFromArray($columnHeaderStyle);
    $sheet->getRowDimension(5)->setRowHeight(45);

    $filaActual = 6;
    $itemNumber = 1;

    foreach ($reportData as $docente) {
        $rowCount = max(1, count($docente['asignaciones']));
        $startRowTeacher = $filaActual;

        $sheet->setCellValue("A{$startRowTeacher}", $itemNumber);
        $sheet->setCellValue("B{$startRowTeacher}", $docente['nombre_completo']);
        $sheet->setCellValue("C{$startRowTeacher}", $docente['doc_cedula']);
        $sheet->setCellValue("D{$startRowTeacher}", $docente['doc_fecha_ingreso'] ? date('d/m/Y', strtotime($docente['doc_fecha_ingreso'])) : '');
        $sheet->setCellValue("E{$startRowTeacher}", $docente['doc_perfil_profesional']);
        $sheet->setCellValue("F{$startRowTeacher}", $docente['doc_dedicacion']);
        $sheet->setCellValue("G{$startRowTeacher}", $docente['doc_horas_max']);
        $sheet->setCellValue("H{$startRowTeacher}", $docente['horas_asignadas'] > 0 ? $docente['horas_asignadas'] : '0');
        $sheet->setCellValue("I{$startRowTeacher}", $docente['doc_horas_descarga'] > 0 ? $docente['doc_horas_descarga'] : '0');
        
        $horasFaltantes = $docente['doc_horas_max'] - $docente['horas_asignadas'] - $docente['doc_horas_descarga'];
        $sheet->setCellValue("J{$startRowTeacher}", $horasFaltantes);

        $anioConcurso = $docente['doc_anio_concurso'];
        $tipoConcurso = $docente['doc_tipo_concurso'];
        $concursoDisplay = ($anioConcurso && $anioConcurso !== '0000-00-00') ? date('Y', strtotime($anioConcurso)) . ' - ' . $tipoConcurso : '';
        $sheet->setCellValue("L{$startRowTeacher}", $concursoDisplay);

        $observaciones = [];
        if (!empty($docente['doc_observacion'])) { $observaciones[] = $docente['doc_observacion']; }
        if (!empty($docente['coordinaciones'])) { $observaciones[] = 'Coordinaciones: ' . $docente['coordinaciones']; }
        $sheet->setCellValue("N{$startRowTeacher}", implode('; ', $observaciones));
        
        if (!empty($docente['asignaciones'])) {
            $tempFila = $startRowTeacher;
            foreach ($docente['asignaciones'] as $asig) {
                $sheet->setCellValue("K{$tempFila}", $asig['uc_nombre']);
                $sheet->setCellValue("M{$tempFila}", $asig['sec_codigo']);
                $tempFila++;
            }
        }

        $endRowTeacher = $startRowTeacher + $rowCount - 1;
        $celdasACombinar = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'N'];
        if ($rowCount > 1) {
            foreach ($celdasACombinar as $col) {
                $sheet->mergeCells("{$col}{$startRowTeacher}:{$col}{$endRowTeacher}");
            }
        }
        
        $filaActual += $rowCount;
        $itemNumber++;
    }
    
    $finDeDatos = $filaActual - 1;
    if ($finDeDatos >= 6) {
        $sheet->getStyle('A6:N' . $finDeDatos)->applyFromArray($cellStyle);
        $sheet->getStyle("A6:D{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F6:J{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("L6:M{$finDeDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
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
    $listaAnios = $oReporte->obtenerAnios();
    require_once($vistaFormulario);
}