<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        require_once("model/bitacora.php");
        $usu_id = 1;
        $bitacora = new Bitacora();

        $u = new Usuario();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'eliminar') {
            $u->set_usuarioId($_POST['usuarioId']);
            echo  json_encode($u->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'usuario');
        } elseif ($accion == 'existe') {
            $u->set_nombreUsuario($_POST['nombreUsuario']);
            $u->set_correoUsuario($_POST['correoUsuario']);
            $resultado = $u->Existe($_POST['nombreUsuario'], $_POST['correoUsuario']);
            echo json_encode($resultado);
        } elseif ($accion == 'listarPermisos') {
            $permisos = $u->listarPermisos($_POST['usuarioId']);
            echo json_encode(['resultado' => 'listarPermisos', 'permisos' => $permisos]);
            exit;
        } elseif ($accion == 'asignarPermisos') {
            $permisos = json_decode($_POST['permisos'], true);
            $r = $u->asignarPermisos($_POST['usuarioId'], $permisos);
            echo json_encode($r);
            exit;
        } else {
            $u->set_nombreUsuario($_POST['nombreUsuario']);
            $u->set_correoUsuario($_POST['correoUsuario']);

            if ($accion == 'registrar') {
                $u->set_superUsuario($_POST['superUsuario']);
                $u->set_contraseniaUsuario($_POST['contraseniaUsuario']);
                echo  json_encode($u->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'usuario');
            } elseif ($accion == 'modificar') {
                $u->set_usuarioId($_POST['usuarioId']);
                echo  json_encode($u->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'usuario');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
