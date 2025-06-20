<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'coordinacion'; // Nombre de la clase del modelo

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $o = new Coordinacion();
        $accion = $_POST['accion'] ?? '';

        /*
        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        */

        try {
            if ($accion == 'consultar') {
                echo json_encode($o->Listar());
            } elseif ($accion == 'eliminar') {
                $o->setId($_POST['coordinacionId'] ?? '');
                echo json_encode($o->Eliminar());
                // $bitacora->registrarAccion($usu_id, 'eliminar', 'coordinacion');
            } elseif ($accion == 'existe') {
                $nombre = $_POST['coordinacionNombre'] ?? '';
                $id = $_POST['coordinacionId'] ?? null;
                $existe = $o->Existe($nombre);

                if ($existe && (is_null($id) || $existe['cor_id'] != $id)) {
                    echo json_encode(['resultado' => 'existe', 'mensaje' => 'El nombre de la coordinación ya existe.']);
                } else {
                    echo json_encode(['resultado' => 'no_existe']);
                }
            } else {
                $o->setNombre($_POST['coordinacionNombre'] ?? '');
                if ($accion == 'registrar') {
                    echo json_encode($o->Registrar());
                    // $bitacora->registrarAccion($usu_id, 'registrar', 'coordinacion');
                } elseif ($accion == 'modificar') {
                    $o->setId($_POST['coordinacionId'] ?? '');
                    echo json_encode($o->Modificar());
                    // $bitacora->registrarAccion($usu_id, 'modificar', 'coordinacion');
                }
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
