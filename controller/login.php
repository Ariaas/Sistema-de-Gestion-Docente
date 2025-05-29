<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta el modelo";
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {

        $o = new Login();
        $h = $_POST['accion'];
        if ($_POST['accion'] == 'acceder') {
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

    require_once("views/" . $pagina . ".php");
} else {
    echo "Falta la vista";
}
