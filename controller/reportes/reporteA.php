<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Model\Reportes\Reporte;

if (is_file("views/reportes/reporteA.php")) {

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
                
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'aulas_distribucion';
                $datos = null;

                if (empty($anio) || empty($tipo)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico activo.']);
                    exit;
                }

                
                switch ($tipo_reporte) {
                    case 'aulas_distribucion':
                        $datos = $reporteModel->obtenerDatosReporteAulas($anio, $tipo, 'all');
                        break;
                    case 'aulas_top10':
                        $datos = $reporteModel->obtenerDatosReporteAulas($anio, $tipo, 'top10');
                        break;
                    case 'aulas_top5':
                        $datos = $reporteModel->obtenerDatosReporteAulas($anio, $tipo, 'top5');
                        break;
                    case 'aula_mas_usada':
                        $datos = $reporteModel->obtenerDatosReporteAulas($anio, $tipo, 'top1');
                        break;
                    default:
                        $datos = $reporteModel->obtenerDatosReporteAulas($anio, $tipo, 'all');
                        break;
                }

                if ($datos !== false && !empty($datos)) {
                    $totalCantidad = array_sum(array_column($datos, 'cantidad'));
                    if ($totalCantidad > 0) {
                        echo json_encode(['success' => true, 'datos' => $datos]);
                    } else {
                        echo json_encode(['success' => false, 'mensaje' => 'No se encontraron aulas con asignaciones para el año y período seleccionados.']);
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
    require_once("views/reportes/reporteA.php");
} else {
    echo "Página en construcción: reporteA.php";
}
