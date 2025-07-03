<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $modelo = new Archivo();
        $accion = $_POST['accion'] ?? '';

        require_once("model/bitacora.php");
        $usu_id = $_SESSION['usu_id'] ?? null;
        $doc_id =  1 /* $_SESSION['doc_id'] ?? null */;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        switch ($accion) {
            case 'registrar_notas':
                if ($doc_id === null) {
                    echo json_encode(['success' => false, 'mensaje' => 'Error: Su sesión no tiene un docente asociado.']);
                    exit;
                }
                $modelo->setAnioId($_POST['anio'] ?? null);
                $modelo->setSecId($_POST['seccion'] ?? null);
                $modelo->setUcId($_POST['ucurricular'] ?? null);
                $modelo->setDocId($doc_id);
                $modelo->setRemCantidad($_POST['cantidad_reprobados'] ?? 0);
                $modelo->setPerCantidad($_POST['cantidad_per'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setSeccionCodigo($_POST['seccion_codigo'] ?? '');

                $fecha_para_archivo = $_POST['fecha'] ?? date('Y-m-d');
                $file = $_FILES['archivo_notas'] ?? null;

                echo json_encode($modelo->guardarNotasRemedial($file, $fecha_para_archivo));
                $bitacora->registrarAccion($usu_id, 'creó un registro de remedial', 'archivo');
                break;

            case 'registrar_per':
                $modelo->setRemId($_POST['rem_id'] ?? 0);
                $modelo->setPerAprobados($_POST['cantidad_aprobados'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setSeccionCodigo($_POST['seccion_codigo'] ?? '');

                $file = $_FILES['archivo_per'] ?? null;

                echo json_encode($modelo->registrarAprobadosPer($file));
                $bitacora->registrarAccion($usu_id, 'registró resultados de remedial', 'archivo');
                break;
            
            case 'eliminar_registro':
                if (isset($_POST['rem_id'])) {
                    $modelo->setRemId($_POST['rem_id']);
                    echo json_encode($modelo->eliminarRegistroRemedial($_POST['rem_id']));
                    $bitacora->registrarAccion($usu_id, 'eliminó un registro de remedial completo', 'archivo');
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'ID de registro no proporcionado.']);
                }
                break;

            case 'listar_registros':
                echo json_encode(['resultado' => 'ok_registros', 'datos' => $modelo->listarRegistros()]);
                break;

            case 'listar_per_por_id':
                if (isset($_POST['rem_id'])) {
                    echo json_encode(['success' => true, 'datos' => $modelo->listarArchivosPerPorId($_POST['rem_id'])]);
                }
                break;

            case 'eliminar_archivo_per':
                if (isset($_POST['nombre_archivo'])) {
                    echo json_encode($modelo->eliminarArchivoPer($_POST['nombre_archivo']));
                    $bitacora->registrarAccion($usu_id, 'eliminó un archivo de notas PER', 'archivo');
                }
                break;

            case 'obtener_secciones':
                $anio_id = $_POST['anio_id'] ?? 0;
                echo json_encode($modelo->obtenerSeccionesPorAnio($anio_id));
                break;
        }
        exit;
    }

    $obj = new Archivo();
    $anios = $obj->obtenerAnios();

    $doc_id =  1;// $_SESSION['doc_id'] ?? null;
    $unidadesCurriculares = [];
    if ($doc_id) {
        $unidadesCurriculares = $obj->obtenerUnidadesPorDocente($doc_id);
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
?>