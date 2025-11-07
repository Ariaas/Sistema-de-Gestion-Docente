<?php

use App\Model\Bitacora;
if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {

        $b = new Bitacora();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($b->Listar());
        }
        exit;
    }
    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
