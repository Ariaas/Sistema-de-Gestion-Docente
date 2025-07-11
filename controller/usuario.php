<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

$u = new Usuario();
$roles = $u->obtenerRoles();

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();


        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'consultar_docentes') {
            echo json_encode($u->obtenerDocentesDisponibles());
        } elseif ($accion == 'eliminar') {
            $u->set_usuarioId($_POST['usuarioId']);
            echo  json_encode($u->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'usuario');
        } elseif ($accion == 'existe') {
            $u->set_nombreUsuario($_POST['nombreUsuario']);
            $u->set_correoUsuario($_POST['correoUsuario']);
            $usuarioId = isset($_POST['usuarioId']) ? $_POST['usuarioId'] : null;
            $resultado = $u->Existe($_POST['nombreUsuario'], $_POST['correoUsuario'], $usuarioId);
            echo json_encode($resultado);
        } else {
            $u->set_nombreUsuario($_POST['nombreUsuario']);
            $u->set_correoUsuario($_POST['correoUsuario']);
            $u->set_superUsuario($_POST['superUsuario'] ?? null);
            $u->set_usu_docente($_POST['usu_docente'] ?? null);
            if (isset($_POST['usuarioRol'])) {
                $u->set_rolId($_POST['usuarioRol']);
            }

            if ($accion == 'registrar') {
                $u->set_contraseniaUsuario($_POST['contraseniaUsuario']);
                echo  json_encode($u->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'usuario');
            } elseif ($accion == 'modificar') {
                $u->set_usuarioId($_POST['usuarioId']);

                if (!empty($_POST['contraseniaUsuario'])) {
                    $u->set_contraseniaUsuario($_POST['contraseniaUsuario']);
                }

                $resultado = $u->modificar();

                if (isset($resultado['resultado']) && $resultado['resultado'] == 'modificar') {
                    if ($_POST['usuarioId'] == $usu_id) {
                        $_SESSION['usu_docente'] = $_POST['usu_docente'] ?? null;
                    }
                    $bitacora->registrarAccion($usu_id, 'modificar', 'usuario');
                }

                echo json_encode($resultado);
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
