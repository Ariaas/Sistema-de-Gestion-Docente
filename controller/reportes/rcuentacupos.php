<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("vendor/autoload.php");
require_once("model/reportes/rcuentacupos.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


function convertirTrayectoARomano($numeroTrayecto) {
    switch ($numeroTrayecto) {
        case '1': return 'I';
        case '2': return 'II';
        case '3': return 'III';
        case '4': return 'IV';
        default: return 'INICIAL'; 
    }
}

function formatSectionsFromArray($sectionsArray, $wrapAfter = 2) {
    if (empty($sectionsArray)) return '';
    
    $formattedSections = [];
    foreach($sectionsArray as $sec_codigo){
        $trayectoNum = substr($sec_codigo, 0, 1);
        if (in_array($trayectoNum, ['0', '1', '2'])) {
            $formattedSections[] = 'IN' . $sec_codigo;
        } elseif (in_array($trayectoNum, ['3', '4'])) {
            $formattedSections[] = 'IIN' . $sec_codigo;
        } else {
            $formattedSections[] = $sec_codigo;
        }
    }
    sort($formattedSections);

    $total = count($formattedSections);
    $output = '';
    foreach ($formattedSections as $index => $section) {
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

$oCuentaCupos = new CuentaCupos();

if (isset($_POST['generar_reporte'])) {
    $oCuentaCupos->set_anio($_POST['anio'] ?? '');
    $datosCrudos = $oCuentaCupos->obtenerCuentaCupos();

    if (empty($datosCrudos)) {
        // ... (código para reporte sin resultados)
    }

    // --- LÓGICA DE AGRUPACIÓN EN PHP ---
    // 1. Crear firmas de horario para cada sección
    $seccionesConFirma = [];
    foreach ($datosCrudos as $fila) {
        $sec_codigo = $fila['sec_codigo'];
        if (!isset($seccionesConFirma[$sec_codigo])) {
            $seccionesConFirma[$sec_codigo] = [
                'cantidad' => $fila['sec_cantidad'],
                'partes_horario' => []
            ];
        }
        if ($fila['uc_codigo']) {
            $seccionesConFirma[$sec_codigo]['partes_horario'][] = $fila['uc_codigo'] . '-' . $fila['hor_dia'] . '-' . $fila['hor_horainicio'];
        }
    }
    foreach ($seccionesConFirma as $sec_codigo => &$data) {
        sort($data['partes_horario']);
        $data['firma'] = implode('|', $data['partes_horario']);
    }
    unset($data);

    // 2. Agrupar secciones por su firma de horario
    $gruposPorFirma = [];
    foreach ($seccionesConFirma as $sec_codigo => $data) {
        $firma = $data['firma'];
        // Si no hay horario (firma vacía), usar el propio código de sección para que no se agrupe
        $key = ($firma === '') ? 'no-unida-'.$sec_codigo : $firma;

        if (!isset($gruposPorFirma[$key])) {
            $gruposPorFirma[$key] = ['secciones' => [], 'cantidad_total' => 0];
        }
        $gruposPorFirma[$key]['secciones'][] = $sec_codigo;
        $gruposPorFirma[$key]['cantidad_total'] += $data['cantidad'];
    }

    // 3. Preparar datos finales para el reporte
    $datosReporte = [];
    foreach ($gruposPorFirma as $grupo) {
        $primer_sec_codigo = $grupo['secciones'][0];
        $datosReporte[] = [
            'Trayecto' => substr($primer_sec_codigo, 0, 1),
            'Seccion' => $grupo['secciones'], // Ahora es un array de códigos
            'Cantidad' => $grupo['cantidad_total']
        ];
    }
    // --- FIN DE LA LÓGICA DE AGRUPACIÓN ---

    $datosAgrupados = [];
    $totalGeneral = 0;
    foreach ($datosReporte as $fila) {
        $trayectoRomano = convertirTrayectoARomano($fila['Trayecto']);
        $datosAgrupados[$trayectoRomano][] = $fila;
        $totalGeneral += $fila['Cantidad'];
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Matricula Trayecto");

    $styleHeader = ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]];
    
    $sheet->mergeCells('A1:C1')->setCellValue('A1', 'MATRICULA TRAYECTO ' . htmlspecialchars($_POST['anio'] ?? ''));
    $sheet->getStyle('A1')->applyFromArray($styleHeader);
    $sheet->mergeCells('A2:C2')->setCellValue('A2', 'UPTAEB');
    $sheet->getStyle('A2')->applyFromArray($styleHeader);

    $filaActual = 4;
    $filaInicioTabla = $filaActual;
    $sheet->setCellValue('A'.$filaActual, 'TRAYECTO');
    $sheet->setCellValue('B'.$filaActual, 'SECCION');
    $sheet->setCellValue('C'.$filaActual, 'CANTIDAD');
    $sheet->getStyle('A'.$filaActual.':C'.$filaActual)->applyFromArray($styleHeaderColumnas);
    $filaActual++;

    if(!empty($datosAgrupados)){
        foreach ($datosAgrupados as $trayectoRomano => $seccionesDelTrayecto) {
            $filaInicioTrayecto = $filaActual;
            $numFilas = count($seccionesDelTrayecto);

            if ($numFilas > 0) {
                $sheet->mergeCells('A'.$filaInicioTrayecto.':A'.($filaInicioTrayecto + $numFilas - 1));
                $sheet->setCellValue('A'.$filaInicioTrayecto, $trayectoRomano);
                $sheet->getStyle('A'.$filaInicioTrayecto)->applyFromArray($styleCentrado);
            }
            
            foreach ($seccionesDelTrayecto as $seccion) {
                // Se formatean las secciones usando la función auxiliar
                $seccionesFormateadas = formatSectionsFromArray($seccion['Seccion']);
                $sheet->setCellValue('B'.$filaActual, $seccionesFormateadas);
                $sheet->getStyle('B'.$filaActual)->applyFromArray($styleCentrado);

                $sheet->setCellValue('C'.$filaActual, $seccion['Cantidad']);
                $sheet->getStyle('C'.$filaActual)->applyFromArray($styleCentrado);
                $filaActual++;
            }
        }
    }
    
    $sheet->mergeCells('A'.$filaActual.':B'.$filaActual);
    $sheet->setCellValue('A'.$filaActual, 'TOTAL');
    $sheet->getStyle('A'.$filaActual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A'.$filaActual)->getFont()->setBold(true);

    $sheet->setCellValue('C'.$filaActual, $totalGeneral);
    $sheet->getStyle('C'.$filaActual)->applyFromArray($styleCentrado)->getFont()->setBold(true);
    
    $sheet->getStyle('A'.$filaInicioTabla.':C'.$filaActual)->applyFromArray($styleBordes);
    
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(12);
    
    $writer = new Xlsx($spreadsheet);
    if (ob_get_length()) ob_end_clean();
    $fileName = "Reporte_Cuenta_Cupos_" . date('Y-m-d') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $anios = $oCuentaCupos->obtenerAnios();
    require_once("views/reportes/rcuentacupos.php");
}
?>

