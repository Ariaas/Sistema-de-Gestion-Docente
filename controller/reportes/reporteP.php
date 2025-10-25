<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/reportes/reporteP.php")) {
    echo "Falta definir la clase del modelo: reporteP.php";
    exit;
}
require_once("model/reportes/reporteP.php");

if (is_file("views/reportes/reporteP.php")) {

    $reporteModel = new ReporteP();

    if (!empty($_POST['accion'])) {
        header('Content-Type: application/json');
        $accion = $_POST['accion'];

       
        $anio = $_POST['anio_origen'] ?? 0;

        switch ($accion) {
            case 'generar_reporte':
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'general';
                $datos = null;

               
                if (empty($anio)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico.']);
                    exit;
                }

                
                switch ($tipo_reporte) {
                    case 'seccion':
                        $datos = $reporteModel->obtenerDatosReporteTodasLasSecciones($anio);
                        break;
                    case 'trayecto':
                        $datos = $reporteModel->obtenerDatosReportePorTrayecto($anio);
                        break;
                    default: 
                        $datos = $reporteModel->obtenerDatosReporteGeneral($anio);
                        break;
                }

                if ($datos !== false && !empty($datos) && (isset($datos[0]['cantidad']) && $datos[0]['cantidad'] !== null)) {
                    echo json_encode(['success' => true, 'datos' => $datos]);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos de prosecución para los filtros seleccionados.']);
                }
                break;
        }
        exit;
    }

    $anios_disponibles = $reporteModel->obtenerAniosDeOrigen();
    $hayDatos = $reporteModel->verificarDatosGenerales();

    require_once("views/reportes/reporteP.php");
} else {
    echo "Página en construcción: reporteP.php";
}
