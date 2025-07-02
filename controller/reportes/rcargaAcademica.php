<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rcargaAcademica.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (!is_file("model/reportes/rcargaAcademica.php")) {
    die("Error crítico: No se encuentra el archivo del modelo.");
}

$vistaFormularioUc = "views/reportes/rcargaAcademica.php";
if (!is_file($vistaFormularioUc)) {
    die("Error crítico: No se encuentra el archivo de la vista del formulario (ruc.php).");
}

$oUc = new Carga();

if (isset($_POST['generar_uc'])) {

    $oUc->set_trayecto($_POST['trayecto'] ?? '');
    $oUc->set_seccion($_POST['seccion'] ?? '');
    $datosReporte = $oUc->obtenerUnidadesCurriculares();

    $datosAgrupados = [];
    if ($datosReporte) {
        foreach ($datosReporte as $fila) {
            $trayecto = $fila['Número de Trayecto'];
            $seccion = $fila['Código de Sección'];
            $datosAgrupados[$trayecto][$seccion][] = $fila;
        }
    } 

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("CARGA ACADEMICA");

    $styleHeaderTrayecto = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeaderColumnas = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentradoVertical = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]];

    $columnaInicial = 2; 

    foreach ($datosAgrupados as $numTrayecto => $secciones) {
        $filaActual = 3; 

        $celdaInicio = Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual;
        $celdaFin = Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual;
        $sheet->mergeCells("{$celdaInicio}:{$celdaFin}");
        $sheet->setCellValue($celdaInicio, "TRAYECTO " . $numTrayecto);
        $sheet->getStyle("{$celdaInicio}:{$celdaFin}")->applyFromArray($styleHeaderTrayecto);
        $filaActual++;

        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual, "SECCION");
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 1) . $filaActual, "UNIDAD CURRICULAR");
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual, "DOCENTE");
        $sheet->getStyle(Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual . ':' . Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual)->applyFromArray($styleHeaderColumnas);
        $filaActual++;

        $filaInicioDatos = $filaActual;

        foreach ($secciones as $codSeccion => $unidades) {
            $numFilasSeccion = count($unidades);
            if ($numFilasSeccion > 1) {
                $rangoMergeSeccion = Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual . ':' . Coordinate::stringFromColumnIndex($columnaInicial) . ($filaActual + $numFilasSeccion - 1);
                $sheet->mergeCells($rangoMergeSeccion);
            }
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual, $codSeccion);

            foreach ($unidades as $item) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 1) . $filaActual, $item['Nombre de la Unidad Curricular']);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual, $item['Nombre Completo del Docente']);
                $filaActual++;
            }
        }

        $rangoTabla = Coordinate::stringFromColumnIndex($columnaInicial) . ($filaInicioDatos - 1) . ':' . Coordinate::stringFromColumnIndex($columnaInicial + 2) . ($filaActual - 1);
        $sheet->getStyle($rangoTabla)->applyFromArray($styleBordes);
        $sheet->getStyle($rangoTabla)->applyFromArray($styleCentradoVertical);

        $columnaInicial += 4; 
    }

    foreach (range('A', Coordinate::stringFromColumnIndex($columnaInicial)) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Carga_Academica_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    $trayectos = $oUc->obtenerTrayectos();
    $secciones = $oUc->obtenerSecciones();
    require_once($vistaFormularioUc);
}
