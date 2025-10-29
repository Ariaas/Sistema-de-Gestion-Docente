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

function formatSectionsFromArray($sectionsArray, $wrapAfter = 2)
{
    sort($sectionsArray);
    $total = count($sectionsArray);
    $output = '';
    foreach ($sectionsArray as $index => $section) {
        $output .= $section;
        if ($index < $total - 1) {
            if (($index + 1) % $wrapAfter === 0) {
                $output .= "\n";
            } else {
                $output .= ' - ';
            }
        }
    }
    return $output;
}

$oDefinitivo = new DefinitivoEmit();
$vistaFormulario = "views/reportes/rdefinitivo.php";

if (isset($_POST['generar_definitivo_emit'])) {
    // Separar el año y tipo del campo combinado
    $anioCompleto = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anioCompleto);
    $anio = $partes[0] ?? '';
    $aniTipo = $partes[1] ?? '';
    
    $oDefinitivo->set_anio($anio);
    $oDefinitivo->set_ani_tipo($aniTipo);
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

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Sin_Datos.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
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
    $styleData = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleDataCentered = ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $tipoTexto = ($aniTipo === 'intensivo') ? ' - INTENSIVO' : '';
    $sheet->mergeCells('A1:D1')->setCellValue('A1', "ORGANIZACION DOCENTE $anio$tipoTexto");
    $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
    $sheet->mergeCells('A2:D2')->setCellValue('A2', "PNF en Informática");
    $sheet->getStyle('A2:D2')->applyFromArray($styleTitle);

    $selectedFase = $_POST['fase'] ?? '';
    $phaseHeaderTitle = 'UNIDADES CURRICULARES';
    if ($selectedFase == '1') {
        $phaseHeaderTitle = 'FASE I';
    }
    if ($selectedFase == '2') {
        $phaseHeaderTitle = 'FASE II';
    }
    if ($selectedFase == 'Anual') {
        $phaseHeaderTitle = 'ANUAL';
    }

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

        $ucToSectionsMap = [];
        foreach ($assignments as $assignment) {
            $ucName = $assignment['NombreUnidadCurricular'];
            $sectionName = $assignment['NombreSeccion'];
            $ucToSectionsMap[$ucName][] = $sectionName;
        }

        foreach ($ucToSectionsMap as $ucName => $sectionsArray) {
            $seccionesFormateadas = formatSectionsFromArray($sectionsArray, 3);

            $sheet->setCellValue("C{$filaActual}", $ucName);
            $sheet->setCellValue("D{$filaActual}", $seccionesFormateadas);
            $filaActual++;
        }

        $endRowTeacher = $filaActual - 1;
        if ($startRowTeacher <= $endRowTeacher) {
            $sheet->mergeCells("A{$startRowTeacher}:A{$endRowTeacher}");
            $sheet->mergeCells("B{$startRowTeacher}:B{$endRowTeacher}");
            $sheet->setCellValue("A{$startRowTeacher}", $info['NombreCompletoDocente']);
            $sheet->setCellValue("B{$startRowTeacher}", $info['CedulaDocente']);
        }
    }

    $sheet->getStyle('A5:D' . ($filaActual - 1))->applyFromArray($styleData);
    $sheet->getStyle('A5:A' . ($filaActual - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('B5:D' . ($filaActual - 1))->applyFromArray($styleDataCentered);

    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(30);

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    
    $fileName = "Definitivo_" . $anio;
    
    // Agregar fase si fue seleccionada (solo para años regulares)
    $selectedFase = $_POST['fase'] ?? '';
    if (!empty($selectedFase) && $aniTipo !== 'intensivo') {
        $fileName .= "_Fase" . $selectedFase;
    }
    
    if ($aniTipo === 'intensivo') {
        $fileName .= "_Intensivo";
    }
    $fileName .= ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    $listaAnios = $oDefinitivo->obtenerAnios();
    require_once($vistaFormulario);
}
