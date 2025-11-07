<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Model\Reportes\Reporte;

if (is_file("views/reportes/reporteM.php")) {

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
             
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'dias_distribucion';
                $datos = null;

                if (empty($anio) || empty($tipo)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico activo.']);
                    exit;
                }

              
                switch ($tipo_reporte) {
                    case 'dias_distribucion':
                        $datos = $reporteModel->obtenerDatosReporteDias($anio, $tipo, 'all');
                        break;
                    case 'dias_top3':
                        $datos = $reporteModel->obtenerDatosReporteDias($anio, $tipo, 'top3');
                        break;
                    case 'dia_mas_asignado':
                        $datos = $reporteModel->obtenerDatosReporteDias($anio, $tipo, 'top1');
                        break;
                    default:
                        $datos = $reporteModel->obtenerDatosReporteDias($anio, $tipo, 'all');
                        break;
                }

                if ($datos !== false && !empty($datos)) {
                    $totalCantidad = array_sum(array_column($datos, 'cantidad'));
                    if ($totalCantidad > 0) {
                        echo json_encode(['success' => true, 'datos' => $datos]);
                    } else {
                      
                        echo json_encode(['success' => false, 'mensaje' => 'No se encontraron asignaciones para el año y período seleccionados.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos para los filtros seleccionados.']);
                }
                break;
        }
        exit;
    }

    $anio_activo = $reporteModel->obtenerAnioActivo();
    $hayDatos = $reporteModel->verificarDatosAulasAsignadas();
    require_once("views/reportes/reporteM.php");
} else {
    echo "Página en construcción: reporteM.php";
}
