<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/reportes/reporte.php")) {
    echo "Falta definir la clase del modelo: reporte.php";
    exit;
}
require_once("model/reportes/reporte.php");

if (is_file("views/reportes/reporte.php")) {

    $reporteModel = new Reporte();

    if (!empty($_POST['accion'])) {
        header('Content-Type: application/json');
        $accion = $_POST['accion'];

        switch ($accion) {
            case 'generar_reporte':
                $tipo_reporte = $_POST['tipo_reporte'] ?? 'general';
                $anio_id = $_POST['anio_id'] ?? 0;
                $datos = null;

                if (empty($anio_id)) {
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico.']);
                    exit;
                }

                switch ($tipo_reporte) {
                    case 'seccion':
                        $seccion_id = $_POST['seccion_id'] ?? 0;
                        if (empty($seccion_id)) {
                            echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione una sección.']);
                            exit;
                        }
                        $datos = $reporteModel->obtenerDatosEstadisticosPorSeccion($seccion_id);
                        break;
                    case 'uc':
                        $uc_id = $_POST['uc_id'] ?? 0;
                        if (empty($uc_id)) {
                            echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione una Unidad Curricular.']);
                            exit;
                        }
                        $datos = $reporteModel->obtenerDatosEstadisticosPorUC($uc_id, $anio_id);
                        break;
                    default: 
                        $datos = $reporteModel->obtenerDatosEstadisticosPorAnio($anio_id);
                        break;
                }

                if ($datos && $datos['total_estudiantes'] > 0) {
                    echo json_encode(['success' => true, 'datos' => $datos]);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos para los filtros seleccionados.']);
                }
                break;

            case 'obtener_secciones':
                $anio_id = $_POST['anio_id'] ?? 0;
                $secciones = $reporteModel->obtenerSeccionesPorAnio($anio_id);
                echo json_encode($secciones);
                break;

            case 'obtener_uc':
                $anio_id = $_POST['anio_id'] ?? 0;
                $ucs = $reporteModel->obtenerUCPorAnio($anio_id);
                echo json_encode($ucs);
                break;
        }
        exit;
    }

    $anios = $reporteModel->obtenerAnios();
    require_once("views/reportes/reporte.php");
} else {
    echo "Página en construcción: reporte.php";
}
