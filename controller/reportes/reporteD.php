<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Model\Reportes\ReporteD;

if (is_file("views/reportes/reporteD.php")) {

    $reporteModel = new ReporteD();

    if (!empty($_POST['accion'])) {
        header('Content-Type: application/json');
        $accion = $_POST['accion'];

        $anio_completo = $_POST['anio_completo'] ?? '';
        $anio_parts = explode('|', $anio_completo);
        $anio = $anio_parts[0] ?? 0;
        $tipo = $anio_parts[1] ?? '';

        switch ($accion) {
            case 'generar_reporte':
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'docente_distribucion'; 
                $datos = null;

                if (empty($anio) || empty($tipo)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico activo.']);
                    exit;
                }

                switch ($tipo_reporte) {
                    case 'docente_distribucion':
                        $datos = $reporteModel->obtenerDatosReporteHorasDocente($anio, $tipo, 0); 
                        break;
                    case 'docente_mayor_a_diez':
                        $datos = $reporteModel->obtenerDatosReporteHorasDocente($anio, $tipo, 10);
                        break;
                    default: 
                        $datos = $reporteModel->obtenerDatosReporteHorasDocente($anio, $tipo, 0);
                        break;
                }

                if ($datos !== false && !empty($datos)) {
       
                    $totalCantidad = array_sum(array_column($datos, 'cantidad'));
                    if ($totalCantidad > 0) {
                        echo json_encode(['success' => true, 'datos' => $datos]);
                    } else {
                        echo json_encode(['success' => false, 'mensaje' => 'No se encontraron docentes con horas asignadas para los filtros seleccionados.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos para los filtros seleccionados.']);
                }
                break;
        }
        exit;
    }

    $anio_activo = $reporteModel->obtenerAnioActivo();
   
    $hayDatos = $reporteModel->verificarDatosDocentesConHoras(); 
    require_once("views/reportes/reporteD.php");
} else {
    echo "Página en construcción: reporteG.php";
}
?>