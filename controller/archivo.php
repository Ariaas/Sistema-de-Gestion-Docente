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
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        switch ($accion) {
            case 'subir':
                if (isset($_FILES['archivo'])) {
                 
                    $docente = $_POST['docente'] ?? '';       
                    $ucurricular = $_POST['ucurricular'] ?? ''; 
                    $fecha = $_POST['fecha'] ?? date('Y-m-d');

                    echo json_encode($archivo->guardarArchivo(
                        $_FILES['archivo'],
                        $docente,
                        $ucurricular,
                        $fecha
                    ));
                    $bitacora->registrarAccion($usu_id, 'subió un archivo', 'archivo');
                }
                break;

            case 'listar':
                echo json_encode([
                    'resultado' => 'listar',
                    'datos' => $archivo->listarArchivosLocales()
                ]);
                break;

            case 'listar_docentes_con_archivos':
                echo json_encode([
                    'resultado' => 'ok_docentes',
                    'datos' => $archivo->obtenerDocentesConArchivos()
                ]);
                break;

            case 'eliminar':
                if (isset($_POST['nombre_archivo'])) {
                    echo json_encode($archivo->eliminarArchivo($_POST['nombre_archivo']));
                }
                break;
        }
        exit;
    }
    $obj2 = new Archivo();

    $docentes = $obj2->obtenerdocente();
    $unidadcurriculares = $obj2->obtenerunidadcurricular();


    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
