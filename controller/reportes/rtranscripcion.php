<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("vendor/autoload.php");

use App\Model\Reportes\Transcripcion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$oReporte = new Transcripcion();

if (isset($_POST['generar_transcripcion'])) {

    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    $anio_completo = $_POST['anio_completo'] ?? '';
    $partes = explode('|', $anio_completo);
    $anio = $partes[0] ?? '';
    $ani_tipo = $partes[1] ?? '';
    
    $fase = $_POST['fase_id'] ?? '';

    
    $esIntensivo = strtolower($ani_tipo) === 'intensivo';
    
    
    if (empty($anio) || empty($ani_tipo)) {
        die("Error: Debe seleccionar un Año y Tipo.");
    }
    
    
    if (!$esIntensivo && empty($fase)) {
        die("Error: Debe seleccionar una Fase para años regulares.");
    }

    $oReporte->setAnio($anio);
    $oReporte->setAniTipo($ani_tipo);
    $oReporte->setFase($fase);
    $reportData = $oReporte->obtenerTranscripciones();
    
    $groupedData = [];
    if ($reportData) {
        foreach ($reportData as $row) {
            $groupedData[$row['IDDocente']]['info'] = [
                'CedulaDocente' => $row['CedulaDocente'],
                'NombreCompletoDocente' => $row['NombreCompletoDocente']
            ];
            $groupedData[$row['IDDocente']]['assignments'][] = $row;
        }
    }


    function formatSectionsWithWrapping($sectionString, $wrapAfter = 3) {
        if (empty($sectionString)) {
            return '';
        }
        $sections = explode(',', $sectionString);
        $total = count($sections);
        $output = '';
        foreach ($sections as $index => $section) {
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


    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("TRANSCRIPCION");

    $styleTitle = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleHeader = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    $styleData = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true]];
    $styleDataCenter = ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]];

    $sheet->mergeCells('A1:E1')->setCellValue('A1', "ASIGNACION DE SECCIONES");
    $sheet->getStyle('A1:E1')->applyFromArray($styleTitle)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getRowDimension(1)->setRowHeight(20);

    
    $sheet->setCellValue('A2', 'N°');
    $sheet->setCellValue('B2', 'CEDULA');
    $sheet->setCellValue('C2', 'NOMBRE Y APELLIDO');
    $sheet->setCellValue('D2', 'UNIDAD CURRICULAR SIN ABREVIATURA');
    $sheet->setCellValue('E2', 'SECCION COMPLETA');
    $sheet->getStyle('A2:E2')->applyFromArray($styleHeader);

    $filaActual = 3;
    $itemNumber = 1;

    if (!empty($groupedData)) {
        foreach ($groupedData as $teacherData) {
            $info = $teacherData['info'];
            $assignments = $teacherData['assignments'];
            $rowCount = count($assignments);
            
            if ($rowCount > 0) {
                $startMergeRow = $filaActual;
                
                foreach ($assignments as $assignment) {
                    $sheet->setCellValue("D{$filaActual}", $assignment['NombreUnidadCurricular']);
                    

                    $seccionesFormateadas = formatSectionsWithWrapping($assignment['NombreSeccion'], 3);
                    $sheet->setCellValue("E{$filaActual}", $seccionesFormateadas);

                    $filaActual++;
                }
                
                $endMergeRow = $filaActual - 1;

                $sheet->mergeCells("A{$startMergeRow}:A{$endMergeRow}");
                $sheet->mergeCells("B{$startMergeRow}:B{$endMergeRow}");
                $sheet->mergeCells("C{$startMergeRow}:C{$endMergeRow}");
                
                $sheet->setCellValue("A{$startMergeRow}", $itemNumber);
                $sheet->setCellValue("B{$startMergeRow}", $info['CedulaDocente']);
                $sheet->setCellValue("C{$startMergeRow}", $info['NombreCompletoDocente']);
                
                $itemNumber++;
            }
        }
    } else {
        $sheet->mergeCells("A3:E3")->setCellValue("A3", "No se encontraron asignaciones para los filtros seleccionados.");
    }

    $rangoTotal = 'A2:E' . max(2, $filaActual - 1);
    $sheet->getStyle($rangoTotal)->applyFromArray($styleBordes);
    $sheet->getStyle("A3:C" . ($filaActual - 1))->applyFromArray($styleDataCenter);
    $sheet->getStyle("D3:E" . ($filaActual - 1))->applyFromArray($styleData);
    
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(50);
    $sheet->getColumnDimension('E')->setWidth(25); 

    $writer = new Xlsx($spreadsheet);
    
if (ob_get_level() > 0) {
    ob_end_clean();
    }

    $fileName = "Transcripcion_Asignacion_Secciones";
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
    $listaAnios = $oReporte->getAniosActivos();
    $listaFases = $oReporte->getFases();
    require_once("views/reportes/rtranscripcion.php");
}