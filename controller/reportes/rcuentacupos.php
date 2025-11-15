<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("vendor/autoload.php");

use App\Model\Reportes\CuentaCupos;
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
            $formattedSections[] = $sec_codigo;
        } elseif (in_array($trayectoNum, ['3', '4'])) {
            $formattedSections[] = $sec_codigo;
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
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anioId = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    $faseNumero = $_POST['fase_id'] ?? null;
    
    
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';

    
    if (empty($anioId) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año Académico.");
    }
    
   
    if (!$esIntensivo && empty($faseNumero)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oCuentaCupos->set_anio($anioId);
    $oCuentaCupos->set_ani_tipo($ani_tipo);
    $oCuentaCupos->set_fase($faseNumero);
    $datosCrudos = $oCuentaCupos->obtenerCuentaCupos();

    if (empty($datosCrudos)) {
       
    }

   
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

   
    $gruposPorFirma = [];
    foreach ($seccionesConFirma as $sec_codigo => $data) {
        $firma = $data['firma'];
       
        $key = ($firma === '') ? 'no-unida-'.$sec_codigo : $firma;

        if (!isset($gruposPorFirma[$key])) {
            $gruposPorFirma[$key] = ['secciones' => [], 'cantidad_total' => 0];
        }
        $gruposPorFirma[$key]['secciones'][] = $sec_codigo;
        $gruposPorFirma[$key]['cantidad_total'] += $data['cantidad'];
    }

   
    $datosReporte = [];
    foreach ($gruposPorFirma as $grupo) {
        $primer_sec_codigo = $grupo['secciones'][0];
        $datosReporte[] = [
            'Trayecto' => substr($primer_sec_codigo, 0, 1),
            'Seccion' => $grupo['secciones'], 
            'Cantidad' => $grupo['cantidad_total']
        ];
    }
  

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
    
    
    if ($esIntensivo) {
        $tituloReporte = 'MATRICULA TRAYECTO ' . htmlspecialchars($anioId) . ' - INTENSIVO';
    } else {
        $tituloReporte = 'MATRICULA TRAYECTO ' . htmlspecialchars($anioId) . ' - FASE ' . htmlspecialchars($faseNumero);
    }
    
    $sheet->mergeCells('A1:C1')->setCellValue('A1', $tituloReporte);
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
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    
    if ($esIntensivo) {
        $fileName = "Cuenta_Cupos_{$anioId}_Intensivo.xlsx";
    } else {
        $fileName = "Cuenta_Cupos_{$anioId}_Fase{$faseNumero}.xlsx";
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} else {
    $anios = $oCuentaCupos->obtenerAnios();
    $fases = $oCuentaCupos->obtenerFases();
    require_once("views/reportes/rcuentacupos.php");
}
?>

