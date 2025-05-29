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

        $t = new Trayecto();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($t->Listar());
        } elseif ($accion == 'eliminar') {
            $t->setId($_POST['trayectoId']);
            echo  json_encode($t->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'trayecto');

        } elseif ($accion == 'existe') {
            $t->setNumero($_POST['trayectoNumero']);
            $t->setAnio($_POST['trayectoAnio']);
            $resultado = $t->Existe($_POST['trayectoNumero'], $_POST['trayectoAnio']);
            echo json_encode($resultado);
        } else {
            $t->setNumero($_POST['trayectoNumero']);
            $t->setAnio($_POST['trayectoAnio']);
          
            if ($accion == 'registrar') {
                echo  json_encode($t->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'trayecto');

            } elseif ($accion == 'modificar') {
                $t->setId($_POST['trayectoId']);
                echo  json_encode($t->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'trayecto');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
