<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta el modelo";
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST) && isset($_POST['accion'])) {
        $o = new Login();
        $h = $_POST['accion'];
        if ($h == 'acceder') {
            $o->set_nombreUsuario($_POST['nombreUsuario']);
            $o->set_contraseniaUsuario($_POST['contraseniaUsuario']);
            $m = $o->existe();
            if ($m['resultado'] == 'existe') {
                session_destroy();
                session_start();
                $_SESSION['name'] = $m['mensaje'];
                $_SESSION['usu_id'] = $m['usu_id']; 

                require_once("model/permisos.php");
                $permisosModel = new Permisos();
                $permisos = $permisosModel->obtenerPermisosPorUsuario($m['usu_id']);
                $_SESSION['permisos'] = $permisos;

                header('Location: ?pagina=principal');
                die();
            } else {
                $mensaje = $m['mensaje'];
            }
        }
    }

    if (!empty($_POST) && isset($_GET['accion'])) {
        $o = new Login();

        if ($_GET['accion'] == 'recuperar') {
            $usuario = $_POST['usuario'];
            echo $o->enviarCodigoRecuperacionPorUsuario($usuario);
            exit;
        }
        if ($_GET['accion'] == 'validarCodigo') {
            $usuario = $_POST['usuario'];
            $codigo = $_POST['codigo'];
            echo $o->validarCodigoRecuperacion($usuario, $codigo);
            exit;
        }
        if ($_GET['accion'] == 'cambiarClave') {
            $usuario = $_POST['usuario'];
            $codigo = $_POST['codigo'];
            $nuevaClave = $_POST['nuevaClave'];
            echo $o->cambiarClaveConToken($usuario, $codigo, $nuevaClave);
            exit;
        }
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Falta la vista";
}
