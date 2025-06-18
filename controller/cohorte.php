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

        $c = new Cohorte();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($c->Listar());
        } elseif ($accion == 'eliminar') {
            $c->setId($_POST['cohId']);
            echo  json_encode($c->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'cohorte');
        } elseif ($accion == 'existe') {
            $c->setCohorte($_POST['cohNumero']);
            $resultado = $c->Existe($_POST['cohNumero']);
            echo json_encode($resultado);
        } else {
            $c->setCohorte($_POST['cohNumero']);

            if ($accion == 'registrar') {
                echo  json_encode($c->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'cohorte');
            } elseif ($accion == 'modificar') {
                $c->setId($_POST['cohId']);
                echo  json_encode($c->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'cohorte');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
