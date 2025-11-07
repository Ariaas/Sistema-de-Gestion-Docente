<?php

use App\Model\Notificaciones;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $n = new Notificaciones();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($n->Listar());
        } else if ($accion == 'consultar_nuevas') {
            echo json_encode($n->ListarNuevas());
        } else if ($accion == 'marcar_vistas') {
            echo json_encode($n->MarcarComoVistas());
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
