<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $c = new Eje();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($c->Listar());
        } elseif ($accion == 'eliminar') {
            $c->setId($_POST['categoriaId']);
            echo  json_encode($c->Eliminar());
        } elseif ($accion == 'existe') {
            $c->setEje($_POST['categoriaNombre']);
            $resultado = $c->Existe($_POST['categoriaNombre']);
            echo json_encode($resultado);
        } else {
            $c->setEje($_POST['categoriaNombre']);
            if ($accion == 'registrar') {
                echo  json_encode($c->Registrar());
            } elseif ($accion == 'modificar') {
                $c->setId($_POST['categoriaId']);
                echo  json_encode($c->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
