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

// Función para convertir números a romanos
function toRoman($number) {
    if ($number == 0) return 'INICIAL';
    $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}

$vistaFormularioUc = "views/reportes/rcargaAcademica.php";
if (!is_file($vistaFormularioUc)) {
    die("Error crítico: No se encuentra el archivo de la vista.");
}

$oUc = new Carga();

if (isset($_POST['generar_uc'])) {

    $oUc->set_trayecto($_POST['trayecto'] ?? '');
    $oUc->set_seccion($_POST['seccion'] ?? '');
    $datosReporte = $oUc->obtenerUnidadesCurriculares();
    
    if (empty($datosReporte)) {
        $errorMessage = "No se encontraron datos de Carga Académica para los filtros seleccionados.";
        $trayectos = $oUc->obtenerTrayectos();
        $secciones = $oUc->obtenerSecciones();
        require_once($vistaFormularioUc);
        exit;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("CARGA ACADEMICA");

    // --- Estilos ---
    $styleHeaderPrincipal = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];
    $styleIzquierda = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];

    // --- Lógica de Agrupación de Datos (Nueva) ---
    $datosFinales = [];
    $intermediateGroup = [];

    // 1. Crear una estructura intermedia que agrupa las UCs por sección
    foreach ($datosReporte as $fila) {
        $trayecto = $fila['Número de Trayecto'];
        $seccion = $fila['Código de Sección'];
        $uc = $fila['Nombre de la Unidad Curricular'];
        
        $intermediateGroup[$trayecto][$seccion]['uc_list'][] = $uc;
        $intermediateGroup[$trayecto][$seccion]['data'][] = $fila;
    }

    // 2. Agrupar las secciones que tienen la misma carga académica
    foreach ($intermediateGroup as $trayecto => $secciones) {
        $gruposPorCarga = [];
        foreach ($secciones as $codSeccion => $data) {
            $ucList = $data['uc_list'];
            sort($ucList); // Ordenar para que la firma sea consistente
            $signature = implode('|', $ucList); // Crear una "firma" única para la carga
            
            $gruposPorCarga[$signature]['secciones'][] = $codSeccion;
            if (!isset($gruposPorCarga[$signature]['unidades'])) {
                $gruposPorCarga[$signature]['unidades'] = $data['data'];
            }
        }
        $datosFinales[$trayecto] = array_values($gruposPorCarga);
    }

    // --- Renderizado del Excel ---
    
    // Título Principal
    $numTrayectoTitulo = array_key_first($datosFinales);
    $sheet->mergeCells('A1:D1')->setCellValue('A1', 'TRAYECTO ' . toRoman($numTrayectoTitulo));
    $sheet->getStyle('A1:D1')->applyFromArray($styleHeaderPrincipal);

    // Cabeceras de columna
    $sheet->setCellValue('A3', 'Trayecto');
    $sheet->setCellValue('B3', 'Seccion');
    $sheet->setCellValue('C3', 'Unidad curricular');
    $sheet->setCellValue('D3', 'Docente');
    $sheet->getStyle('A3:D3')->applyFromArray($styleHeaderColumnas);

    $filaActual = 4;
    $startRowTrayecto = $filaActual;

    foreach ($datosFinales as $numTrayecto => $gruposDeCarga) {
        foreach ($gruposDeCarga as $grupo) {
            $startRowSeccion = $filaActual;
            
            // Formatear el nombre de las secciones agrupadas
            $seccionLabel = implode(" - \n", $grupo['secciones']);
            
            // Escribir los datos
            foreach ($grupo['unidades'] as $item) {
                $sheet->setCellValue('C'.$filaActual, $item['Nombre de la Unidad Curricular']);
                $sheet->setCellValue('D'.$filaActual, $item['Nombre Completo del Docente']);
                $filaActual++;
            }
            
            // Escribir y combinar celdas de Trayecto y Sección
            $endRowSeccion = $filaActual - 1;
            $sheet->setCellValue('B'.$startRowSeccion, $seccionLabel);
            if ($startRowSeccion < $endRowSeccion) {
                $sheet->mergeCells('B'.$startRowSeccion.':B'.$endRowSeccion);
            }
        }
        
        // Combinar la celda del Trayecto para todas sus filas
        $endRowTrayecto = $filaActual - 1;
        $sheet->setCellValue('A'.$startRowTrayecto, toRoman($numTrayecto));
        if ($startRowTrayecto < $endRowTrayecto) {
            $sheet->mergeCells('A'.$startRowTrayecto.':A'.$endRowTrayecto);
        }
    }
    
    // --- Aplicar estilos finales ---
    $rangoTabla = 'A3:D' . ($filaActual - 1);
    $sheet->getStyle($rangoTabla)->applyFromArray($styleBordes);
    $sheet->getStyle('A4:B' . ($filaActual - 1))->applyFromArray($styleCentrado);
    $sheet->getStyle('C4:D' . ($filaActual - 1))->applyFromArray($styleIzquierda);
    
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(45);
    $sheet->getColumnDimension('D')->setWidth(45);

    // --- Generar y descargar el archivo ---
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Carga_Academica_por_Trayecto.xlsx";
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