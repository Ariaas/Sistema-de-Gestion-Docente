<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/ruc.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (!is_file("model/reportes/ruc.php")) {
    die("Error crítico: No se encuentra el archivo del modelo (rucm.php).");
}
$vistaFormularioUc = "views/reportes/ruc.php";
if (!is_file($vistaFormularioUc)) {
    die("Error crítico: No se encuentra el archivo de la vista del formulario (ruc.php).");
}

$oUc = new Ruc();

if (isset($_POST['generar_uc'])) {

    $oUc->set_trayecto($_POST['trayecto'] ?? '');
    $oUc->set_nombreUnidad($_POST['ucurricular'] ?? '');

    $datosReporte = $oUc->obtenerUnidadesCurriculares();

    $datosAgrupados = [];
    foreach ($datosReporte as $fila) {
        $trayecto = $fila['Número de Trayecto'];
        if (!isset($datosAgrupados[$trayecto])) {
            $datosAgrupados[$trayecto] = [];
        }
        $datosAgrupados[$trayecto][] = $fila;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("UNIDAD CURRICULAR");

    $styleHeaderTrayecto = [
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $styleHeaderColumnas = [
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $styleBordes = [
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ];
    $styleCentradoVertical = [
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
    ];

    $columnaInicial = 2; 

    foreach ($datosAgrupados as $numTrayecto => $datos) {
        $filaActual = 2; 

        $celdaInicio = Coordinate::stringFromColumnIndex($columnaInicial) . ($filaActual);
        $celdaFin = Coordinate::stringFromColumnIndex($columnaInicial + 2) . ($filaActual);
        $sheet->mergeCells("{$celdaInicio}:{$celdaFin}");
        $sheet->setCellValue($celdaInicio, "TRAYECTO " . $numTrayecto);
        $sheet->getStyle("{$celdaInicio}:{$celdaFin}")->applyFromArray($styleHeaderTrayecto);
        $filaActual++;

        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual, "SECCION");
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 1) . $filaActual, "UNIDAD CURRICULAR");
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual, "DOCENTE");

        $rangoEncabezados = Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual . ':' . Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual;
        $sheet->getStyle($rangoEncabezados)->applyFromArray($styleHeaderColumnas);


        $filaActual++;

        $ultimaUnidad = null;
        $filaInicioDatos = $filaActual;

        foreach ($datos as $item) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial) . $filaActual, $item['Código de Sección']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 2) . $filaActual, $item['Nombre Completo del Docente']);

            if ($item['Nombre de la Unidad Curricular'] !== $ultimaUnidad) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnaInicial + 1) . $filaActual, $item['Nombre de la Unidad Curricular']);
                $ultimaUnidad = $item['Nombre de la Unidad Curricular'];
            }
            $filaActual++;
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
    $fileName = "Resumen_Unidades_Curriculares_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    $trayectos = $oUc->obtenerTrayectos();
    $unidadesc = $oUc->obtenerUc();
    require_once($vistaFormularioUc);
}
