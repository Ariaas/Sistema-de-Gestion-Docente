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

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $r = new Rol();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($r->Listar());
        } elseif ($accion == 'eliminar') {
            $r->setId($_POST['rolId']);
            echo  json_encode($r->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'rol');
        } elseif ($accion == 'existe') {
            $r->setNombre($_POST['nombreRol']);
            $resultado = $r->Existe($_POST['nombreRol']);
            echo json_encode($resultado);
        } elseif ($accion == 'listarPermisos') {
            $data = $r->listarPermisos($_POST['rolId']);
            echo json_encode(['resultado' => 'listarPermisos', 'data' => $data]);
            exit;
        } elseif ($accion == 'asignarPermisos') {
            $permisos = json_decode($_POST['permisos'], true);
            $res = $r->asignarPermisos($_POST['rolId'], $permisos);
            echo json_encode($res);
            exit;
        } else {
            $r->setNombre($_POST['nombreRol']);

            if ($accion == 'registrar') {
                echo  json_encode($r->Registrar());
                $r->setNombre($_POST['nombreRol']);

                $bitacora->registrarAccion($usu_id, 'registrar', 'rol');
            } elseif ($accion == 'modificar') {
                $r->setId($_POST['rolId']);
                echo  json_encode($r->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'rol');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
