<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'convenio'; // Nombre de la clase del modelo

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $o = new Convenio();
        $accion = $_POST['accion'] ?? '';

        /*
        // Descomentar para activar la bitácora si es necesario
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
            } elseif ($accion == 'consultar_trayectos') {
                echo json_encode($o->ListarTrayectos());
            } elseif ($accion == 'eliminar') {
                $o->setId($_POST['convenioId'] ?? '');
                echo json_encode($o->Eliminar());
                // $bitacora->registrarAccion($usu_id, 'eliminar', 'convenio');
            } elseif ($accion == 'existe') {
                $nombre = $_POST['convenioNombre'] ?? '';
                $id = $_POST['convenioId'] ?? null;
                $existe = $o->Existe($nombre);

                if ($existe && (is_null($id) || $existe['con_id'] != $id)) {
                    echo json_encode(['resultado' => 'existe', 'mensaje' => 'El nombre del convenio ya está en uso.']);
                } else {
                    echo json_encode(['resultado' => 'no_existe']);
                }
            } else {
                $o->setTraId($_POST['traId'] ?? '');
                $o->setNombre($_POST['convenioNombre'] ?? '');
                $o->setInicio($_POST['convenioInicio'] ?? '');

                if ($accion == 'registrar') {
                    echo json_encode($o->Registrar());
                    // $bitacora->registrarAccion($usu_id, 'registrar', 'convenio');
                } elseif ($accion == 'modificar') {
                    $o->setId($_POST['convenioId'] ?? '');
                    echo json_encode($o->Modificar());
                    // $bitacora->registrarAccion($usu_id, 'modificar', 'convenio');
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
