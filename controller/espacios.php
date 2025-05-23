<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $e = new Espacio();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setCodigo($_POST['codigoEspacio']);
            echo  json_encode($e->eliminar());
        } elseif ($accion == 'existe') {
            $e->setCodigo($_POST['codigoEspacio']);
            $resultado = $e->Existe($_POST['codigoEspacio']);
            echo json_encode($resultado);
        } else {
            $e->setCodigo($_POST['codigoEspacio']);
            $e->setTipo($_POST['tipoEspacio']);
            if ($accion == 'registrar') {
                echo  json_encode($e->Registrar());
            } elseif ($accion == 'modificar') {
                echo  json_encode($e->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}