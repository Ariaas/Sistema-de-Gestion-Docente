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
        $n = new Notificaciones();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($n->Listar());
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
