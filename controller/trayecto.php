<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $t = new Trayecto();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($t->Listar());
        } elseif ($accion == 'eliminar') {
            $t->setNumero($_POST['trayectoNumero']);
            $t->setAnio($_POST['trayectoAnio']);
            echo  json_encode($t->Eliminar());
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
            } elseif ($accion == 'modificar') {
                echo  json_encode($t->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
