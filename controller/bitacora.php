<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
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
