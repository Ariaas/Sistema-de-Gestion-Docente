<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");
require_once("model/reportes/rcargaAcademica.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function toRoman($number)
{
    if ($number == 0) return 'INICIAL';
    $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if ($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}

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

$vistaFormularioUc = "views/reportes/rcargaAcademica.php";
$oUc = new Carga();

if (isset($_POST['generar_uc'])) {

    // Separar año y tipo del valor combinado
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';
    $trayecto_filtrado = $_POST['trayecto'] ?? '';

    // Verificar si es intensivo
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';

    // Validar campos requeridos
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año y Tipo.");
    }

    // Solo requerir fase si NO es intensivo
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oUc->setAnio($anio);
    $oUc->setAniTipo($ani_tipo);
    $oUc->setFase($fase);
    $oUc->set_trayecto($trayecto_filtrado);
    $datosReporte = $oUc->obtenerUnidadesCurriculares();

    if (empty($datosReporte)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Sin Resultados");
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'No hay registros disponibles para los filtros seleccionados.');
        $style = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];
        $sheet->getStyle('A1')->applyFromArray($style);
        $sheet->getRowDimension(1)->setRowHeight(30);
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        $writer = new Xlsx($spreadsheet);
        if (ob_get_length()) ob_end_clean();
        $fileName = "Reporte_Carga_Academica_Sin_Resultados.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Agregar (Intensivo) al título si el año es intensivo
    $tituloHoja = "CARGA ACADEMICA";
    if ($esIntensivo) {
        $tituloHoja .= " (Intensivo)";
    }
    $sheet->setTitle($tituloHoja);

    $styleHeaderPrincipal = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordesDelgados = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];
    $styleIzquierda = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];
    $styleBordeGruesoExterior = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]];
    $styleHeaderInicial = [
        'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
    ];

    $cargaPorSeccion = [];
    foreach ($datosReporte as $fila) {
        $seccion = $fila['Código de Sección'];
        $trayecto = $fila['Número de Trayecto'];
        $cargaPorSeccion[$seccion]['trayecto'] = $trayecto;
        $cargaPorSeccion[$seccion]['unidades'][] = [
            'uc' => $fila['Nombre de la Unidad Curricular'],
            'docente' => $fila['Nombre Completo del Docente'] ?? 'S/A'
        ];
    }

    $gruposPorCarga = [];
    foreach ($cargaPorSeccion as $codigoSeccion => $data) {
        $trayecto = $data['trayecto'];
        $unidades = $data['unidades'];
        usort($unidades, function ($a, $b) {
            return strcmp($a['uc'], $b['uc']);
        });

        $firma = [];
        foreach ($unidades as $unidad) {
            $firma[] = $unidad['uc'] . '-' . $unidad['docente'];
        }
        $firmaString = md5(implode('|', $firma));

        $gruposPorCarga[$trayecto][$firmaString]['secciones'][] = $codigoSeccion;
        if (!isset($gruposPorCarga[$trayecto][$firmaString]['unidades'])) {
            $gruposPorCarga[$trayecto][$firmaString]['unidades'] = $unidades;
        }
    }

    $datosParaRenderizar = [];
    foreach ($gruposPorCarga as $trayecto => $grupos) {
        foreach ($grupos as $grupoData) {

            $etiquetaSeccion = formatSectionsFromArray($grupoData['secciones'], 2);

            $datosParaRenderizar[$trayecto][$etiquetaSeccion] = $grupoData['unidades'];
        }
    }
    ksort($datosParaRenderizar, SORT_NUMERIC);

    $rowOffset = 1;
    $colOffset = 1;
    $bloquesEnFila = 0;
    $alturaMaximaFila = 0;

    function renderizarBloque($sheet, $numTrayecto, $secciones, $startRow, $startCol, &$styles)
    {
        $filaActual = $startRow;

        $colTrayecto = Coordinate::stringFromColumnIndex($startCol);
        $colSeccion  = Coordinate::stringFromColumnIndex($startCol + 1);
        $colUC       = Coordinate::stringFromColumnIndex($startCol + 2);
        $colDocente  = Coordinate::stringFromColumnIndex($startCol + 3);

        $rangeTitulo = "{$colTrayecto}{$filaActual}:{$colDocente}{$filaActual}";
        $sheet->mergeCells($rangeTitulo)->setCellValue($colTrayecto . $filaActual, 'TRAYECTO ' . toRoman($numTrayecto));

        if ($numTrayecto == 0) {
            $sheet->getStyle($rangeTitulo)->applyFromArray($styles['inicial_header']);
        } else {
            $sheet->getStyle($rangeTitulo)->applyFromArray($styles['principal']);
        }

        $filaActual++;

        $headerStartRow = $filaActual;
        $sheet->setCellValue($colTrayecto . $filaActual, 'Trayecto');
        $sheet->setCellValue($colSeccion . $filaActual, 'Sección');
        $sheet->setCellValue($colUC . $filaActual, 'Unidad curricular');
        $sheet->setCellValue($colDocente . $filaActual, 'Docente');
        $sheet->getStyle("{$colTrayecto}{$filaActual}:{$colDocente}{$filaActual}")->applyFromArray($styles['columnas']);
        $filaActual++;

        $trayectoStartRow = $filaActual;
        foreach ($secciones as $codSeccion => $unidades) {
            $seccionStartRow = $filaActual;
            foreach ($unidades as $item) {
                $sheet->setCellValue($colUC . $filaActual, $item['uc']);
                $sheet->setCellValue($colDocente . $filaActual, $item['docente'] ?? 'NO ASIGNADO');
                $filaActual++;
            }
            $endRowSeccion = $filaActual - 1;
            $sheet->setCellValue($colSeccion . $seccionStartRow, $codSeccion);
            if ($seccionStartRow < $endRowSeccion) $sheet->mergeCells("{$colSeccion}{$seccionStartRow}:{$colSeccion}{$endRowSeccion}");
        }
        $endRowTrayecto = $filaActual - 1;
        $sheet->setCellValue($colTrayecto . $trayectoStartRow, toRoman($numTrayecto));
        if ($trayectoStartRow < $endRowTrayecto) $sheet->mergeCells("{$colTrayecto}{$trayectoStartRow}:{$colTrayecto}{$endRowTrayecto}");

        $rangoTabla = "{$colTrayecto}{$headerStartRow}:{$colDocente}" . ($filaActual - 1);
        $sheet->getStyle($rangoTabla)->applyFromArray($styles['delgados']);
        $sheet->getStyle("{$colTrayecto}{$trayectoStartRow}:{$colSeccion}" . ($filaActual - 1))->applyFromArray($styles['centrado']);
        $sheet->getStyle("{$colUC}{$trayectoStartRow}:{$colDocente}" . ($filaActual - 1))->applyFromArray($styles['izquierda']);

        $rangoBloqueCompleto = "{$colTrayecto}{$startRow}:{$colDocente}" . ($filaActual - 1);
        $sheet->getStyle($rangoBloqueCompleto)->applyFromArray($styles['grueso']);

        return $filaActual - $startRow;
    }

    $estilos = [
        'principal' => $styleHeaderPrincipal,
        'columnas' => $styleHeaderColumnas,
        'delgados' => $styleBordesDelgados,
        'grueso' => $styleBordeGruesoExterior,
        'centrado' => $styleCentrado,
        'izquierda' => $styleIzquierda,
        'inicial_header' => $styleHeaderInicial
    ];

    foreach ($datosParaRenderizar as $numTrayecto => $secciones) {
        if ($numTrayecto > 0 && $bloquesEnFila >= 2) {
            $rowOffset += $alturaMaximaFila + 2;
            $colOffset = 1;
            $bloquesEnFila = 0;
            $alturaMaximaFila = 0;
        }

        $alturaBloqueActual = renderizarBloque($sheet, $numTrayecto, $secciones, $rowOffset, $colOffset, $estilos);

        $alturaMaximaFila = max($alturaMaximaFila, $alturaBloqueActual);
        $colOffset += 5;
        $bloquesEnFila++;
    }

    $highestColumn = $sheet->getHighestDataColumn();
    $lastColIndex = Coordinate::columnIndexFromString($highestColumn);

    for ($i = 1; $i <= $lastColIndex; $i++) {
        $colLetter = Coordinate::stringFromColumnIndex($i);
        $mod = ($i - 1) % 5;

        switch ($mod) {
            case 0:
                $sheet->getColumnDimension($colLetter)->setWidth(10);
                break;
            case 1:
                $sheet->getColumnDimension($colLetter)->setWidth(18);
                break;
            case 2:
                $sheet->getColumnDimension($colLetter)->setWidth(38);
                break;
            case 3:
                $sheet->getColumnDimension($colLetter)->setWidth(30);
                break;
            case 4:
                $sheet->getColumnDimension($colLetter)->setWidth(4);
                break;
        }
    }

    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    
    // Agregar _Intensivo al nombre del archivo si es intensivo
    $fileName = "Carga_Academica";
    if ($esIntensivo) {
        $fileName .= "_Intensivo";
    }
    $fileName .= ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    $listaAnios = $oUc->getAniosActivos();
    $listaFases = $oUc->getFases();
    $trayectos = $oUc->obtenerTrayectos();
    require_once($vistaFormularioUc);
}
