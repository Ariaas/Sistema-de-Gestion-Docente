<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Model\Reportes\Reporte;

if (is_file("views/reportes/reporteG.php")) {

    $reporteModel = new Reporte();

    if (!empty($_POST['accion'])) {
        header('Content-Type: application/json');
        $accion = $_POST['accion'];

        $anio_completo = $_POST['anio_completo'] ?? '';
        $anio_parts = explode('|', $anio_completo);
        $anio = $anio_parts[0] ?? 0;
        $tipo = $anio_parts[1] ?? '';

        switch ($accion) {
            case 'generar_reporte':
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'general';
                $datos = null;

                if (empty($anio) || empty($tipo)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico.']);
                    exit;
                }

                switch ($tipo_reporte) {
                    case 'seccion':
                        $datos = $reporteModel->obtenerDatosReporteTodasLasSecciones($anio, $tipo);
                        break;
                    case 'trayecto':
                        $datos = $reporteModel->obtenerDatosReportePorTrayecto($anio, $tipo);
                        break;
                    default: 
                        $datos = $reporteModel->obtenerDatosReporteGeneral($anio, $tipo);
                        break;
                }

                if ($datos !== false && !empty($datos) && (isset($datos[0]['cantidad']) && $datos[0]['cantidad'] !== null)) {
                    echo json_encode(['success' => true, 'datos' => $datos]);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos para los filtros seleccionados.']);
                }
                break;
        }
        exit;
    }

    $anio_activo = $reporteModel->obtenerAnioActivo();
    $hayDatos = $reporteModel->verificarDatosGenerales();
    require_once("views/reportes/reporteG.php");
} else {
    echo "Página en construcción: reporteG.php";
}
