<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rodmodel.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

$oReporte = new Rod();

if (isset($_POST['generar_reporte_rod'])) {

    $anioId = $_POST['anio_id'] ?? null; // CAMBIO: Se usa anio_id para el filtro
    $oReporte->set_anio($anioId);

    $queryData = $oReporte->obtenerDatosReporte();
    $aniosLista = $oReporte->obtenerAnios();
    
    $anioTexto = '';
    foreach($aniosLista as $a){
        if($a['ani_id'] == $anioId) $anioTexto = $a['ani_anio'];
    }
    
    $lapsoTexto = 'PERIODO ' . $anioTexto; // Texto para el encabezado

    // --- Agrupar datos por docente para facilitar la creación del Excel ---
    $reportData = [];
    foreach ($queryData as $row) {
        $docenteId = $row['doc_id'];
        if (!isset($reportData[$docenteId])) {
            $reportData[$docenteId] = [
                'nombre_completo' => $row['nombre_completo'],
                'doc_cedula' => $row['doc_cedula'],
                'doc_fecha_ingreso' => $row['doc_fecha_ingreso'],
                'doc_perfil_profesional' => $row['doc_perfil_profesional'],
                'doc_dedicacion' => $row['doc_dedicacion'],
                'doc_horas_max' => (int)$row['doc_horas_max'],
                'doc_horas_descarga' => (int)$row['doc_horas_descarga'],
                'doc_observacion' => $row['doc_observacion'],
                'horas_asignadas' => 0,
                'asignaciones' => []
            ];
        }

        if ($row['uc_nombre']) {
            $reportData[$docenteId]['asignaciones'][] = [
                'uc_nombre' => $row['uc_nombre'],
                'sec_codigo' => $row['sec_codigo'],
                'uc_anio_concurso' => $row['uc_anio_concurso'] // Se almacena el dato de concurso por asignación
            ];
            $reportData[$docenteId]['horas_asignadas'] += (int)$row['uc_horas'];
        }
    }

    // --- Creación del archivo Excel ---
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("ORGANIZACION DOCENTE");

    // Estilos...
    $styleHeaderPrincipal = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]];
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCeldas = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleResumen = ['font' => ['bold' => true, 'size' => 9], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    // Cabecera del Reporte...
    $sheet->mergeCells('B2:F2')->setCellValue('B2', 'CUADRO RESUMEN ORGANIZACIÓN DOCENTE');
    $sheet->mergeCells('B3:C3')->setCellValue('B3', 'PNF: Informática');
    $sheet->mergeCells('K3:L3')->setCellValue('K3', 'LAPSO: ' . $lapsoTexto);
    $sheet->getStyle('B2:L3')->applyFromArray($styleHeaderPrincipal);

    // Cabecera de las Columnas...
    $columnas = ['N°', 'Apellidos y Nombres', 'C.I.', 'Fecha de Ingreso', 'Perfil Profesional', 'Dedicacion', 'Max Horas Acad.', 'Horas Asignadas', 'HORAS DESCARGA', 'FALTA HORAS ACAD', 'Unidad Curricular', 'Año de Concurso', 'Sección', 'Observacion'];
    $sheet->fromArray($columnas, NULL, 'A5');
    $sheet->getStyle('A5:N5')->applyFromArray($styleHeaderColumnas);
    $sheet->getRowDimension(5)->setRowHeight(40);

    $filaActual = 6;
    $itemNumber = 1;
    $totalHorasAsignadas = 0;
    $totalHorasDescarga = 0;
    $totalHorasFaltantes = 0;

    if (!empty($reportData)) {
        foreach ($reportData as $docente) {
            $rowCount = max(1, count($docente['asignaciones']));
            $horasFaltantes = $docente['doc_horas_max'] - $docente['horas_asignadas'] - $docente['doc_horas_descarga'];

            $totalHorasAsignadas += $docente['horas_asignadas'];
            $totalHorasDescarga += $docente['doc_horas_descarga'];
            $totalHorasFaltantes += ($horasFaltantes > 0) ? $horasFaltantes : 0;

            // CAMBIO: Se saca la columna 'L' (Año de Concurso) del grupo a combinar
            $celdasACombinar = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'N'];
            if ($rowCount > 1) {
                foreach ($celdasACombinar as $col) {
                    $sheet->mergeCells("{$col}{$filaActual}:{$col}" . ($filaActual + $rowCount - 1));
                }
            }
            
            // Llenar datos del docente
            $sheet->setCellValue("A{$filaActual}", $itemNumber);
            $sheet->setCellValue("B{$filaActual}", $docente['nombre_completo']);
            $sheet->setCellValue("C{$filaActual}", $docente['doc_cedula']);
            $sheet->setCellValue("D{$filaActual}", $docente['doc_fecha_ingreso']);
            $sheet->setCellValue("E{$filaActual}", $docente['doc_perfil_profesional']);
            $sheet->setCellValue("F{$filaActual}", $docente['doc_dedicacion']);
            $sheet->setCellValue("G{$filaActual}", $docente['doc_horas_max']);
            $sheet->setCellValue("H{$filaActual}", $docente['horas_asignadas']);
            $sheet->setCellValue("I{$filaActual}", $docente['doc_horas_descarga']);
            $sheet->setCellValue("J{$filaActual}", $horasFaltantes);
            $sheet->setCellValue("N{$filaActual}", $docente['doc_observacion']);

            // Llenar datos de asignaciones, incluyendo el año de concurso en su fila
            if (!empty($docente['asignaciones'])) {
                $tempFila = $filaActual;
                foreach ($docente['asignaciones'] as $asig) {
                    $sheet->setCellValue("K{$tempFila}", $asig['uc_nombre']);
                    $sheet->setCellValue("L{$tempFila}", $asig['uc_anio_concurso']); // CAMBIO: Se escribe aquí
                    $sheet->setCellValue("M{$tempFila}", $asig['sec_codigo']);
                    $tempFila++;
                }
            } else {
                 $sheet->setCellValue("K{$filaActual}", 'Sin asignación para este lapso');
            }

            $filaActual += $rowCount;
            $itemNumber++;
        }
    }

    $finDeDatos = $filaActual - 1;
    if ($finDeDatos < 6) $finDeDatos = 6;

    // Aplicar estilos y pie de página...
    $sheet->getStyle('A6:N' . $finDeDatos)->applyFromArray($styleCeldas);
    $sheet->getStyle('A6:A' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('C6:C' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('F6:J' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('L6:M' . $finDeDatos)->applyFromArray($styleCentrado); // Se incluye la L en el centrado

    $filaResumen = $finDeDatos + 2;
    $sheet->setCellValue("B{$filaResumen}", 'HORAS ACADEMICAS ASIGNADAS')->setCellValue("H{$filaResumen}", $totalHorasAsignadas);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);
    $filaResumen++;
    $sheet->setCellValue("B{$filaResumen}", 'HORAS FALTANTES')->setCellValue("H{$filaResumen}", $totalHorasFaltantes);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);
    $filaResumen++;
    $sheet->setCellValue("B{$filaResumen}", 'DESCARGAS')->setCellValue("H{$filaResumen}", $totalHorasDescarga);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);

    // Anchos de columna...
    $anchos = ['A' => 4, 'B' => 25, 'C' => 10, 'D' => 12, 'E' => 25, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 35, 'L' => 15, 'M' => 20, 'N' => 30];
    foreach($anchos as $col => $ancho) { $sheet->getColumnDimension($col)->setWidth($ancho); }

    // Salida del archivo
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Resumen_Organizacion_Docente.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $listaAnios = $oReporte->obtenerAnios(); // Se llama a la función de obtener años
    require_once("views/reportes/rod.php");
}
?>