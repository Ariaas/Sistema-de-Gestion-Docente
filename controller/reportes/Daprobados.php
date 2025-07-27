<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!is_file("model/reportes/Daprobados.php")) {
    echo "Falta definir la clase del modelo: Daprobadosm.php";
    exit;
}
require_once("model/reportes/Daprobados.php");


if (is_file("views/reportes/Daprobados.php")) {

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
                    echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione un año académico válido.']);
                    exit;
                }

                switch ($tipo_reporte) {
                    case 'seccion':
                        $seccion_codigo = $_POST['seccion_codigo'] ?? 0;
                        if (empty($seccion_codigo)) {
                            echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione una sección.']);
                            exit;
                        }
                        $datos = $reporteModel->obtenerDatosEstadisticosPorSeccion($seccion_codigo);
                        break;
                    case 'uc':
                        $uc_codigo = $_POST['uc_codigo'] ?? '';
                        if (empty($uc_codigo)) {
                            echo json_encode(['success' => false, 'mensaje' => 'Por favor, seleccione una Unidad Curricular.']);
                            exit;
                        }
                        $datos = $reporteModel->obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo);
                        break;
                    default:
                        $datos = $reporteModel->obtenerDatosEstadisticosPorAnio($anio, $tipo);
                        break;
                }
 
                if ($datos) {
                    echo json_encode(['success' => true, 'datos' => $datos]);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'No se encontraron datos para los filtros seleccionados.']);
                }
                break;

            case 'obtener_secciones':
                if (!empty($anio) && !empty($tipo)) {
                    $secciones = $reporteModel->obtenerSeccionesPorAnio($anio, $tipo);
                    echo json_encode($secciones);
                } else {
                    echo json_encode([]);
                }
                break;

            case 'obtener_uc':
                if (!empty($anio) && !empty($tipo)) {
                    $ucs = $reporteModel->obtenerUCPorAnio($anio, $tipo);
                    echo json_encode($ucs);
                } else {
                    echo json_encode([]);
                }
                break;
        }
        exit;
    }

    $anios = $reporteModel->obtenerAnios();
    require_once("views/reportes/Daprobados.php");
} else {
    echo "Página en construcción: Daprobadosv.php";
}
