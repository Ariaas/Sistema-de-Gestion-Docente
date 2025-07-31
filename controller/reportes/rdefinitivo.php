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

// ==================================================================
// --- 1. AQUÍ AÑADIMOS LA FUNCIÓN AUXILIAR DE FORMATEO ---
// ==================================================================
function formatSectionsFromArray($sectionsArray, $wrapAfter = 2) {
    sort($sectionsArray); // Asegura un orden consistente
    $total = count($sectionsArray);
    $output = '';
    foreach ($sectionsArray as $index => $section) {
        $output .= $section;
        if ($index < $total - 1) { // Si no es el último elemento
            if (($index + 1) % $wrapAfter === 0) { // Si es el 2do, 4to, etc.
                $output .= "\n"; // Añade un salto de línea
            } else {
                $output .= ' - '; // Añade el separador de guion
            }
        }
    }
    return $output;
}

$oDefinitivo = new DefinitivoEmit();
$vistaFormulario = "views/reportes/rdefinitivo.php";

if (isset($_POST['generar_definitivo_emit'])) {
    $oDefinitivo->set_anio($_POST['anio_id'] ?? '');
    $oDefinitivo->set_fase($_POST['fase'] ?? '');
    $datosReporte = $oDefinitivo->obtenerDatosDefinitivoEmit();

     if (empty($datosReporte)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Sin Datos");
        $sheet->mergeCells('A1:E5');
        $sheet->setCellValue('A1', 'No se encontraron datos para los criterios seleccionados.');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        
        foreach(range('A','E') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $writer = new Xlsx($spreadsheet);
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Sin_Datos.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // Agrupación inicial por docente (sin cambios)
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

    // Estilos (sin cambios)
    $styleTitle = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleDataCentered = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    
    // Encabezados del documento (sin cambios)
    $currentYear = $_POST['anio_id'] ?? date('Y');
    $sheet->mergeCells('A1:D1')->setCellValue('A1', "ORGANIZACION DOCENTE $currentYear");
    $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
    $sheet->mergeCells('A2:D2')->setCellValue('A2', "PNF en Informática");
    $sheet->getStyle('A2:D2')->applyFromArray($styleTitle);

    $selectedFase = $_POST['fase'] ?? '';
    $phaseHeaderTitle = 'UNIDADES CURRICULARES';
    if ($selectedFase == '1') { $phaseHeaderTitle = 'FASE I'; }
    if ($selectedFase == '2') { $phaseHeaderTitle = 'FASE II'; }
    if ($selectedFase == 'Anual') { $phaseHeaderTitle = 'ANUAL'; }

    $sheet->mergeCells('C3:D3')->setCellValue('C3', $phaseHeaderTitle);
    $sheet->setCellValue('A3', 'DOCENTE');
    $sheet->setCellValue('B3', 'CÉDULA');
    $sheet->setCellValue('C4', 'UNIDAD CURRICULAR');
    $sheet->setCellValue('D4', 'SECCIÓN');
    $sheet->getStyle('A3:D4')->applyFromArray($styleHeader);
    $sheet->mergeCells('A3:A4');
    $sheet->mergeCells('B3:B4');

    $filaActual = 5;
    foreach ($groupedData as $teacherData) {
        $startRowTeacher = $filaActual;
        $info = $teacherData['info'];
        $assignments = $teacherData['assignments'];
        
        // ==================================================================
        // --- 2. LÓGICA DE AGRUPACIÓN ADICIONAL POR UNIDAD CURRICULAR ---
        // ==================================================================
        $ucToSectionsMap = [];
        foreach ($assignments as $assignment) {
            $ucName = $assignment['NombreUnidadCurricular'];
            $sectionName = $assignment['NombreSeccion'];
            $ucToSectionsMap[$ucName][] = $sectionName;
        }

        // ==================================================================
        // --- 3. BUCLE MODIFICADO PARA RENDERIZAR LOS DATOS AGRUPADOS ---
        // ==================================================================
        foreach ($ucToSectionsMap as $ucName => $sectionsArray) {
            // Formateamos las secciones usando la función auxiliar
            $seccionesFormateadas = formatSectionsFromArray($sectionsArray, 3); // Salto de línea cada 3 secciones
            
            $sheet->setCellValue("C{$filaActual}", $ucName);
            $sheet->setCellValue("D{$filaActual}", $seccionesFormateadas);
            $filaActual++;
        }
        
        // Unir celdas para el docente (lógica sin cambios)
        $endRowTeacher = $filaActual - 1;
        if ($startRowTeacher <= $endRowTeacher) {
            $sheet->mergeCells("A{$startRowTeacher}:A{$endRowTeacher}");
            $sheet->mergeCells("B{$startRowTeacher}:B{$endRowTeacher}");
            $sheet->setCellValue("A{$startRowTeacher}", $info['NombreCompletoDocente']);
            $sheet->setCellValue("B{$startRowTeacher}", $info['CedulaDocente']);
        }
    }

    // Aplicar estilos a las celdas (lógica sin cambios)
    $sheet->getStyle('A5:D' . ($filaActual - 1))->applyFromArray($styleData);
    $sheet->getStyle('A5:A' . ($filaActual - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('B5:D' . ($filaActual - 1))->applyFromArray($styleDataCentered);

    // Ancho de columnas (sin cambios)
    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(30); // Un poco más ancho para las secciones

    // Guardar archivo (sin cambios)
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