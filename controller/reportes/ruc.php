<?php 
 if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
 } 

 require_once("vendor/autoload.php"); 
 require_once("model/reportes/ruc.php"); 


 use PhpOffice\PhpSpreadsheet\Spreadsheet; 
 use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
 use PhpOffice\PhpSpreadsheet\Style\Alignment; 
 use PhpOffice\PhpSpreadsheet\Style\Border; 
 use PhpOffice\PhpSpreadsheet\Cell\Coordinate; 

 if (isset($_POST['action']) && $_POST['action'] == 'filtrar_uc') {
    header('Content-Type: application/json');
    $oUc = new Ruc();
    $trayectoId = $_POST['trayecto_id'] ?? '';
    $unidades = $oUc->obtenerUcPorTrayecto($trayectoId);
    echo json_encode($unidades);
    exit; 
 }
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

 
 $oUc = new Ruc(); 
 $vistaFormularioUc = "views/reportes/ruc.php"; 

 if (isset($_POST['generar_uc'])) { 

    $oUc->set_anio($_POST['anio_id'] ?? ''); 
    $oUc->set_trayecto($_POST['trayecto'] ?? ''); 
    $oUc->set_nombreUnidad($_POST['ucurricular'] ?? ''); 
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
        $fileName = "Reporte_Sin_Resultados.xlsx"; 
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
        header('Content-Disposition: attachment;filename="' . $fileName . '"'); 
        header('Cache-Control: max-age=0'); 
        $writer->save('php://output'); 
        exit; 
    } 

     
    $spreadsheet = new Spreadsheet(); 
    $sheet = $spreadsheet->getActiveSheet(); 
    $sheet->setTitle("UNIDAD CURRICULAR"); 
 
    $styleHeaderTrayecto = ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]; 
    $styleHeaderColumnas = ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]; 
    $styleBordes = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]; 
   
    $styleCentrado = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]]; 

   
    $datosAgrupados = []; 
    foreach ($datosReporte as $fila) { 
        $trayecto = $fila['Número de Trayecto']; 
        $uc = $fila['Nombre de la Unidad Curricular']; 
        $docente = $fila['Nombre Completo del Docente'] ?? 'NO ASIGNADO'; 
       
        $seccionesAgrupadas = $fila['Código de Sección']; 
        
        if ($seccionesAgrupadas) { 
            
            $datosAgrupados[$trayecto][$uc][$docente] = $seccionesAgrupadas; 
        } 
    } 
    ksort($datosAgrupados, SORT_NUMERIC); 

    $rowOffset = 1; $colOffset = 1; $bloquesEnFila = 0; $alturaMaximaFila = 0; 

   
    function renderizarBloqueUC($sheet, $numTrayecto, $unidades, $startRow, $startCol, &$styles) { 
        $filaActual = $startRow; 
        $label = "TRAYECTO " . toRoman($numTrayecto); 

        $colSeccion = Coordinate::stringFromColumnIndex($startCol); 
        $colUC      = Coordinate::stringFromColumnIndex($startCol + 1); 
        $colDocente = Coordinate::stringFromColumnIndex($startCol + 2); 

        $rangeTitulo = "{$colSeccion}{$filaActual}:{$colDocente}{$filaActual}"; 
        $sheet->mergeCells($rangeTitulo)->setCellValue($colSeccion.$filaActual, $label); 
        $sheet->getStyle($rangeTitulo)->applyFromArray($styles['header_trayecto']); 
         
        $filaActual++; 

        $filaCabeceras = $filaActual; 
        $sheet->setCellValue($colSeccion.$filaActual, "SECCION"); 
        $sheet->setCellValue($colUC.$filaActual, "UNIDAD CURRICULAR"); 
        $sheet->setCellValue($colDocente.$filaActual, "DOCENTE"); 
        $sheet->getStyle("{$colSeccion}{$filaActual}:{$colDocente}{$filaActual}")->applyFromArray($styles['header_columnas']); 
        $filaActual++; 
         
        foreach ($unidades as $nombreUC => $docentes) { 
            $filaInicioUC = $filaActual; 
            
            foreach ($docentes as $nombreDocente => $seccionesConcatenadas) { 
                
                $sheet->setCellValue($colSeccion.$filaActual, $seccionesConcatenadas); 
                
                $sheet->setCellValue($colDocente.$filaActual, $nombreDocente);
                
                
                $filaActual++;
            } 
            $filaFinUC = $filaActual - 1; 
            $sheet->setCellValue($colUC.$filaInicioUC, $nombreUC); 
            
            if($filaInicioUC < $filaFinUC) { 
                $sheet->mergeCells("{$colUC}{$filaInicioUC}:{$colUC}{$filaFinUC}"); 
            } 
        } 

        $rangoTabla = "{$colSeccion}{$filaCabeceras}:{$colDocente}".($filaActual - 1); 
        $sheet->getStyle($rangoTabla)->applyFromArray($styles['bordes']); 
        $sheet->getStyle("{$colSeccion}".($filaCabeceras + 1).":{$colDocente}".($filaActual - 1))->applyFromArray($styles['centrado']); 
         
        return $filaActual - $startRow; 
    } 

    $estilos = [ 
        'header_trayecto' => $styleHeaderTrayecto, 'header_columnas' => $styleHeaderColumnas, 
        'bordes' => $styleBordes, 'centrado' => $styleCentrado 
    ]; 
     
    foreach ($datosAgrupados as $numTrayecto => $unidades) { 
        if ($numTrayecto > 0 && $bloquesEnFila >= 2) { 
            $rowOffset += $alturaMaximaFila + 2; 
            $colOffset = 1; 
            $bloquesEnFila = 0; 
            $alturaMaximaFila = 0; 
        } 

        $alturaBloqueActual = renderizarBloqueUC($sheet, $numTrayecto, $unidades, $rowOffset, $colOffset, $estilos); 

        $alturaMaximaFila = max($alturaMaximaFila, $alturaBloqueActual); 
        $colOffset += 4; 
        $bloquesEnFila++; 
    } 

    $sheet->getColumnDimension('A')->setWidth(15); 
    $sheet->getColumnDimension('B')->setWidth(45); 
    $sheet->getColumnDimension('C')->setWidth(35); 
    $sheet->getColumnDimension('E')->setWidth(15); 
    $sheet->getColumnDimension('F')->setWidth(45); 
    $sheet->getColumnDimension('G')->setWidth(35); 
    $sheet->getColumnDimension('I')->setWidth(15); 
    $sheet->getColumnDimension('J')->setWidth(45); 
    $sheet->getColumnDimension('K')->setWidth(35); 

    $writer = new Xlsx($spreadsheet); 
    if (ob_get_length()) ob_end_clean(); 
    $fileName = "Reporte_Unidad_Curricular.xlsx"; 
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
    header('Content-Disposition: attachment;filename="' . $fileName . '"'); 
    header('Cache-Control: max-age=0'); 
    $writer->save('php://output'); 
    exit; 

 } else { 
    $listaAnios = $oUc->obtenerAnios(); 
    $trayectos = $oUc->obtenerTrayectos(); 
    $unidadesc = $oUc->obtenerUc(); 
    require_once("views/reportes/ruc.php"); 
 }