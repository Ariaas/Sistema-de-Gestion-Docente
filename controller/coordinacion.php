<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'coordinacion';


if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina . ".php"]);
    exit;
}
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $o = new Coordinacion();
        $accion = $_POST['accion'] ?? '';

        try {
            switch ($accion) {
                case 'consultar':
                    echo json_encode($o->Listar());
                    break;

                case 'eliminar':
                    $o->setNombre($_POST['coordinacionNombre'] ?? '');
                    echo json_encode($o->Eliminar());
                    $bitacora->registrarAccion($usu_id, 'eliminar', 'Coordinacion');
                    break;

                case 'existe':
                    $nombre_actual = $_POST['coordinacionNombre'] ?? '';
                    $nombre_original = $_POST['coordinacionOriginalNombre'] ?? null;

                    if ($nombre_actual === $nombre_original) {
                        echo json_encode(['resultado' => 'no_existe']);
                        break;
                    }

                    if ($o->Existe($nombre_actual)) {
                        echo json_encode(['resultado' => 'existe', 'mensaje' => 'El nombre de la coordinación ya existe.']);
                    } else {
                        echo json_encode(['resultado' => 'no_existe']);
                    }
                    break;

                case 'registrar':
                    $o->setNombre($_POST['coordinacionNombre'] ?? '');
                    echo json_encode($o->Registrar());
                    $bitacora->registrarAccion($usu_id, 'registrar', 'Coordinacion');
                    break;

                case 'modificar':
                    $o->setNombre($_POST['coordinacionNombre'] ?? '');
                    $o->setOriginalNombre($_POST['coordinacionOriginalNombre'] ?? '');
                    echo json_encode($o->Modificar());
                    $bitacora->registrarAccion($usu_id, 'modificar', 'Coordinacion');
                    break;

                default:
                    echo json_encode(['resultado' => 'error', 'mensaje' => 'Acción no reconocida.']);
                    break;
            }
        } catch (Exception $e) {
            echo json_encode(['resultado' => 'error', 'mensaje' => $e->getMessage()]);
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
