<?php
require_once("vendor/autoload.php");
require_once("model/reportes/rprosecucion.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

function getTrayectoFromCode($sec_codigo) {
    $primerDigito = substr($sec_codigo, 0, 1);
    switch ($primerDigito) {
        case '0': return 'Inicial';
        case '1': return 'I';
        case '2': return 'II';
        case '3': return 'III';
        case '4': return 'IV';
        default: return 'Desconocido';
    }
}

function formatSeccionCode($sec_codigo) {
    $primerDigito = substr($sec_codigo, 0, 1);
    if (in_array($primerDigito, ['0', '1', '2'])) {
        return 'IN' . $sec_codigo;
    } elseif (in_array($primerDigito, ['3', '4'])) {
        return 'IIN' . $sec_codigo;
    }
    return $sec_codigo; 
}

function procesarYAgruparDatos($datosCrudos) {
    $gruposPorDestino = [];

    foreach ($datosCrudos as $fila) {
        $claveDestino = $fila['promocion_codigo'] ?? 'SIN_PROMOCION_' . $fila['origen_codigo'];

        if (!isset($gruposPorDestino[$claveDestino])) {
            $gruposPorDestino[$claveDestino] = [
                'origenes' => [],
                'origen_cantidad_total' => 0,
                'info_promocion' => $fila 
            ];
        }
        
        $gruposPorDestino[$claveDestino]['origenes'][] = $fila['origen_codigo'];
        $gruposPorDestino[$claveDestino]['origen_cantidad_total'] += (int)$fila['origen_cantidad'];
    }

    $datosProcesados = [];
    foreach ($gruposPorDestino as $grupo) {
        $primerOrigen = $grupo['origenes'][0];
        $trayectoActual = getTrayectoFromCode($primerOrigen);
        
        sort($grupo['origenes']);
        $codigoOrigenAgrupado = implode('-', $grupo['origenes']);

        $datosProcesados[$trayectoActual]['actuales'][] = [
            'codigo' => $codigoOrigenAgrupado,
            'cantidad' => $grupo['origen_cantidad_total']
        ];
        
        $infoPromocion = $grupo['info_promocion'];
        if ($infoPromocion['promocion_codigo']) {
            $trayectoSiguiente = getTrayectoFromCode($infoPromocion['promocion_codigo']);

            $datosProcesados[$trayectoActual]['siguientes'][$infoPromocion['promocion_codigo']] = [
                'trayecto' => $trayectoSiguiente,
                'codigo' => $infoPromocion['promocion_codigo'],
                'cantidad' => $infoPromocion['promocion_cantidad']
            ];
        }
    }
    return $datosProcesados;
}


$oProsecucion = new ProsecucionReport();
$aniosAcademicos = $oProsecucion->obtenerAniosAcademicos();

if (isset($_POST['generar_reporte_prosecucion'])) {
    $selectedAnio = $_POST['anio_academico'] ?? null;
    if (!$selectedAnio) {
        die("Error: Debe seleccionar un año académico.");
    }

    $datosCrudos = $oProsecucion->obtenerDatosProsecucion($selectedAnio);
    
    $datosProcesados = procesarYAgruparDatos($datosCrudos);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Prosecución " . $selectedAnio);


    $styleHeader = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleColHeader = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleCenter = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBorders = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];


    $sheet->mergeCells('A1:G1')->setCellValue('A1', 'UPTAEB - PNF INFORMATICA');
    $sheet->getStyle('A1')->applyFromArray($styleHeader);
    $sheet->setCellValue('A3', 'Trayecto')->setCellValue('B3', 'Secciones')->setCellValue('C3', 'Cantidad')
          ->setCellValue('D3', 'Trayecto')->setCellValue('E3', 'Secciones')->setCellValue('F3', 'Cantidad')
          ->setCellValue('G3', 'Carga Académica');
    $sheet->getStyle('A3:G3')->applyFromArray($styleColHeader);


    $filaActual = 4;
    $ordenTrayectos = ['Inicial', 'I', 'II', 'III', 'IV'];

    foreach ($ordenTrayectos as $trayectoActual) {
        if (!isset($datosProcesados[$trayectoActual])) continue;

        $seccionesActuales = $datosProcesados[$trayectoActual]['actuales'];
        $numFilasActual = count($seccionesActuales);
        $filaInicioBloque = $filaActual;

        $sheet->mergeCells("A{$filaInicioBloque}:A" . ($filaInicioBloque + $numFilasActual - 1));
        $sheet->setCellValue("A{$filaInicioBloque}", $trayectoActual)->getStyle("A{$filaInicioBloque}")->applyFromArray($styleCenter);

        foreach ($seccionesActuales as $sec) {
            $codigoFormateado = formatSeccionCode($sec['codigo']);
            $sheet->setCellValue("B{$filaActual}", $codigoFormateado)->getStyle("B{$filaActual}")->applyFromArray($styleCenter);
            $sheet->setCellValue("C{$filaActual}", $sec['cantidad'])->getStyle("C{$filaActual}")->applyFromArray($styleCenter);
            $filaActual++;
        }

        $seccionesSiguientes = $datosProcesados[$trayectoActual]['siguientes'] ?? [];
        
        $sheet->mergeCells("D{$filaInicioBloque}:D" . ($filaInicioBloque + $numFilasActual - 1));
        $sheet->mergeCells("E{$filaInicioBloque}:E" . ($filaInicioBloque + $numFilasActual - 1));
        $sheet->mergeCells("F{$filaInicioBloque}:F" . ($filaInicioBloque + $numFilasActual - 1));
        $sheet->mergeCells("G{$filaInicioBloque}:G" . ($filaInicioBloque + $numFilasActual - 1));

        if (!empty($seccionesSiguientes)) {
            $trayectosDestino = array_unique(array_column($seccionesSiguientes, 'trayecto'));
            $seccionesDestinoCodigos = array_column($seccionesSiguientes, 'codigo');
            $seccionesDestinoFormateadas = array_map('formatSeccionCode', $seccionesDestinoCodigos);
            $cantidadTotalSiguiente = array_sum(array_column($seccionesSiguientes, 'cantidad'));
            $numTotalSeccionesSiguiente = count($seccionesSiguientes);
            
            $sheet->setCellValue("D{$filaInicioBloque}", implode("\n", $trayectosDestino))->getStyle("D{$filaInicioBloque}")->applyFromArray($styleCenter)->getAlignment()->setWrapText(true);
            $sheet->setCellValue("E{$filaInicioBloque}", implode("\n", $seccionesDestinoFormateadas))->getStyle("E{$filaInicioBloque}")->applyFromArray($styleCenter)->getAlignment()->setWrapText(true);
            $sheet->setCellValue("F{$filaInicioBloque}", $cantidadTotalSiguiente)->getStyle("F{$filaInicioBloque}")->applyFromArray($styleCenter);
            $sheet->setCellValue("G{$filaInicioBloque}", "{$numTotalSeccionesSiguiente} SECCIONES")->getStyle("G{$filaInicioBloque}")->applyFromArray($styleCenter);
        } else {
            $sheet->setCellValue("D{$filaInicioBloque}", "-")->getStyle("D{$filaInicioBloque}")->applyFromArray($styleCenter);
        }
    }
    

    $sheet->getStyle('A3:G' . ($filaActual - 1))->applyFromArray($styleBorders);
    foreach(range('A','G') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
    $sheet->getColumnDimension('G')->setWidth(20);

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Reporte_Prosecucion_' . $selectedAnio . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

require_once("views/reportes/rprosecucion.php");
?>