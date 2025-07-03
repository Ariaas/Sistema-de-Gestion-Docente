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
        $archivo = new Archivo();
        $accion = $_POST['accion'] ?? '';

        require_once("model/bitacora.php");
        $usu_id = $_SESSION['usu_id'] ?? null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        switch ($accion) {
            case 'registrar_notas':
                $datos = [
                    'anio' => $_POST['anio'] ?? '',
                    'seccion_id' => $_POST['seccion'] ?? '',
                    'seccion_codigo' => $_POST['seccion_codigo'] ?? '',
                    'docente' => $_POST['docente'] ?? '',
                    'uc_nombre' => $_POST['uc_nombre'] ?? '',
                    'ucurricular' => $_POST['ucurricular'] ?? '',
                    'cantidad_per' => $_POST['cantidad_per'] ?? 0,
                    'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
                ];
                $file = $_FILES['archivo_notas'] ?? null;
                echo json_encode($archivo->guardarNotasRemedial($datos, $file));
                $bitacora->registrarAccion($usu_id, 'creó un registro de remedial', 'archivo');
                break;

            case 'registrar_per':
                $datos = [
                    'rem_id' => $_POST['rem_id'] ?? 0,
                    'cantidad_aprobados' => $_POST['cantidad_aprobados'] ?? 0,
                    'uc_nombre' => $_POST['uc_nombre'] ?? '',
                    'seccion_codigo' => $_POST['seccion_codigo'] ?? ''
                ];
                $file = $_FILES['archivo_per'] ?? null;
                echo json_encode($archivo->registrarAprobadosPer($datos, $file));
                $bitacora->registrarAccion($usu_id, 'registró resultados de remedial', 'archivo');
                break;

            case 'listar_registros':
                echo json_encode([
                    'resultado' => 'ok_registros',
                    'datos' => $archivo->listarRegistros()
                ]);
                break;
            
            case 'listar_per_por_id':
                 if (isset($_POST['rem_id'])) {
                    echo json_encode([
                        'success' => true,
                        'datos' => $archivo->listarArchivosPerPorId($_POST['rem_id'])
                    ]);
                }
                break;

            case 'eliminar_archivo_per':
                if (isset($_POST['nombre_archivo'])) {
                    echo json_encode($archivo->eliminarArchivoPer($_POST['nombre_archivo']));
                    $bitacora->registrarAccion($usu_id, 'eliminó un archivo de notas PER', 'archivo');
                }
                break;

            case 'obtener_secciones':
                $anio_id = $_POST['anio_id'] ?? 0;
                echo json_encode($archivo->obtenerSeccionesPorAnio($anio_id));
                break;
        }
        exit;
    }

    // Cargar datos para los selects de los formularios
    $obj = new Archivo();
    $docentes = $obj->obtenerdocente();
    $unidadesCurriculares = $obj->obtenerunidadcurricular();
    $anios = $obj->obtenerAnios();

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}