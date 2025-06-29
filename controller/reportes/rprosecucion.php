<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/reportes/rprosecucion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$modeloPath = "model/reportes/rprosecucion.php";
if (!is_file($modeloPath)) {
    die("Error: No se encuentra el archivo del modelo ($modeloPath).");
}
require_once($modeloPath);

$vistaPath = "views/reportes/rprosecucion.php";
if (!is_file($vistaPath)) {
    die("Error: No se encuentra el archivo de la vista ($vistaPath).");
}

$oProsecucion = new ProsecucionReport();
$aniosAcademicos = $oProsecucion->obtenerAniosAcademicos(); 
if (isset($_POST['generar_reporte_prosecucion'])) {
    $selectedAnio = isset($_POST['anio_academico']) ? $_POST['anio_academico'] : null;

    if (empty($selectedAnio)) {
        die("Error: Debe seleccionar un año académico.");
    }

    $datosProsecucion = $oProsecucion->obtenerDatosProsecucion($selectedAnio);

    ob_start();
    require_once($vistaPath);
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("reporte_prosecucion_" . $selectedAnio . ".pdf", array("Attachment" => 0));
    exit;
}

require_once($vistaPath);
