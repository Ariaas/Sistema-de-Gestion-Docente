<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rod.php'; // Cambiado a rodmodel.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$oReporte = new Rod(); // Cambiado a Rod

if (isset($_POST['generar_reporte_rod'])) { // Cambiado el name del botón

    $anioId = $_POST['anio_id'] ?? null;
    $fase = $_POST['fase'] ?? '';

    $oReporte->set_anio($anioId);
    $oReporte->set_fase($fase);

    $docentesData = $oReporte->obtenerDocentesBase();
    $infoAnio = $oReporte->obtenerAnios(); // Para obtener el texto del año

    $anioTexto = '';
    foreach ($infoAnio as $a) {
        if ($a['ani_id'] == $anioId) $anioTexto = $a['ani_anio'];
    }

    $faseTexto = [
        '1' => 'I',
        '2' => 'II',
        '' => 'TODAS',
        'anual' => 'ANUAL'
    ];
    $lapso = ($faseTexto[$fase] ?? '') . '-' . $anioTexto;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("ORGANIZACION DOCENTE");

    // --- Estilos ---
    $styleHeaderPrincipal = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]];
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 9], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCeldas = ['font' => ['size' => 9], 'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleResumen = ['font' => ['bold' => true, 'size' => 9], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    // --- Cabecera del Reporte ---
    $sheet->mergeCells('B2:F2')->setCellValue('B2', 'CUADRO RESUMEN ORGANIZACIÓN DOCENTE');
    $sheet->mergeCells('B3:C3')->setCellValue('B3', 'PNF: Informática');
    $sheet->mergeCells('K3:L3')->setCellValue('K3', 'LAPSO: ' . $lapso);
    $sheet->getStyle('B2:L3')->applyFromArray($styleHeaderPrincipal);

    // --- Cabecera de las Columnas ---
    $columnas = ['N°', 'Apellidos y Nombres', 'C.I.', 'Fecha de Ingreso', 'Perfil Profesional', 'Dedicacion', 'Max Horas Acad.', 'Horas Asignadas', 'HORAS DESCARGA', 'FALTA HORAS ACAD', 'Unidad Curricular', 'Año de Concurso', 'Sección', 'Observacion'];
    $sheet->fromArray($columnas, NULL, 'A5');
    $sheet->getStyle('A5:N5')->applyFromArray($styleHeaderColumnas);
    $sheet->getRowDimension(5)->setRowHeight(40);

    $filaActual = 6;
    $itemNumber = 1;
    $totalHorasAsignadas = 0;
    $totalHorasDescarga = 0;
    $totalHorasFaltantes = 0;

    if (!empty($docentesData)) {
        foreach ($docentesData as $docente) {
            $asignaciones = $oReporte->obtenerAsignacionesDocente($docente['doc_id']);
            $rowCount = max(1, count($asignaciones));

            // --- Cálculos por docente ---
            $horasAsignadas = 0;
            foreach ($asignaciones as $asig) {
                $horasAsignadas += (int)$asig['mal_hora_academica'];
            }
            $maxHoras = 16; // Valor constante según el Excel de ejemplo
            $horasFaltantes = $maxHoras - $horasAsignadas - $docente['horas_descarga'];

            // --- Mapeo de valores ---
            $dedicacionMap = ['exclusiva' => 'DE', 'ordinaria' => 'TC', 'contratado' => 'TC'];
            $dedicacion = $dedicacionMap[$docente['doc_dedicacion']] ?? 'N/A';

            // --- Acumuladores para el resumen final ---
            $totalHorasAsignadas += $horasAsignadas;
            $totalHorasDescarga += $docente['horas_descarga'];
            $totalHorasFaltantes += ($horasFaltantes > 0) ? $horasFaltantes : 0; // Solo sumar si faltan horas

            // --- Combinar celdas comunes para el docente ---
            $celdasACombinar = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'N'];
            if ($rowCount > 1) {
                foreach ($celdasACombinar as $col) {
                    $sheet->mergeCells("{$col}{$filaActual}:{$col}" . ($filaActual + $rowCount - 1));
                }
            }

            // --- Llenar datos del docente ---
            $sheet->setCellValue("A{$filaActual}", $itemNumber);
            $sheet->setCellValue("B{$filaActual}", $docente['nombre_completo']);
            $sheet->setCellValue("C{$filaActual}", $docente['doc_cedula']);
            $sheet->setCellValue("D{$filaActual}", $docente['fecha_ingreso']); // Columna sin datos en BD
            $sheet->setCellValue("E{$filaActual}", $docente['perfil_profesional']);
            $sheet->setCellValue("F{$filaActual}", $dedicacion);
            $sheet->setCellValue("G{$filaActual}", $maxHoras);
            $sheet->setCellValue("H{$filaActual}", $horasAsignadas > 0 ? $horasAsignadas : '0');
            $sheet->setCellValue("I{$filaActual}", $docente['horas_descarga'] > 0 ? $docente['horas_descarga'] : '0');
            $sheet->setCellValue("J{$filaActual}", $horasFaltantes);
            $sheet->setCellValue("L{$filaActual}", $docente['anio_concurso']); // Columna sin datos en BD
            $sheet->setCellValue("N{$filaActual}", ''); // Placeholder para Observaciones

            // --- Llenar datos de asignaciones (UC y Sección) ---
            if (!empty($asignaciones)) {
                $tempFila = $filaActual;
                foreach ($asignaciones as $asig) {
                    $sheet->setCellValue("K{$tempFila}", $asig['uc_nombre']);
                    $sheet->setCellValue("M{$tempFila}", $asig['sec_codigo']);
                    $tempFila++;
                }
            } else {
                $sheet->setCellValue("K{$filaActual}", 'Sin asignación para este lapso');
                $sheet->setCellValue("M{$filaActual}", '');
            }

            $filaActual += $rowCount;
            $itemNumber++;
        }
    } else {
        $sheet->mergeCells("A{$filaActual}:N{$filaActual}")->setCellValue("A{$filaActual}", "No se encontraron datos para los filtros seleccionados.");
        $filaActual++;
    }

    $finDeDatos = $filaActual - 1;

    // --- Aplicar estilos a todas las celdas de datos ---
    $sheet->getStyle('A6:N' . $finDeDatos)->applyFromArray($styleCeldas);
    $sheet->getStyle('A6:A' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('C6:C' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('F6:J' . $finDeDatos)->applyFromArray($styleCentrado);
    $sheet->getStyle('M6:M' . $finDeDatos)->applyFromArray($styleCentrado);

    // --- Pie de página con resúmenes ---
    $filaResumen = $finDeDatos + 2;
    $sheet->setCellValue("B{$filaResumen}", 'HORAS ACADEMICAS ASIGNADAS');
    $sheet->setCellValue("H{$filaResumen}", $totalHorasAsignadas);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);

    $filaResumen++;
    $sheet->setCellValue("B{$filaResumen}", 'HORAS FALTANTES');
    $sheet->setCellValue("H{$filaResumen}", $totalHorasFaltantes);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);

    $filaResumen++;
    $sheet->setCellValue("B{$filaResumen}", 'DESCARGAS');
    $sheet->setCellValue("H{$filaResumen}", $totalHorasDescarga);
    $sheet->getStyle("B{$filaResumen}:H{$filaResumen}")->applyFromArray($styleResumen);


    // --- Ajustar anchos de columna ---
    $anchos = ['A' => 4, 'B' => 25, 'C' => 10, 'D' => 12, 'E' => 25, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 35, 'L' => 15, 'M' => 20, 'N' => 30];
    foreach ($anchos as $col => $ancho) {
        $sheet->getColumnDimension($col)->setWidth($ancho);
    }

    // --- Salida del archivo Excel ---
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
    require_once("views/reportes/rod.php"); // Cambiado a rod.php
}
