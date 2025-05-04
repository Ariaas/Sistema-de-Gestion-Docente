<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $e = new Eje();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setId($_POST['ejeId']);
            echo  json_encode($e->Eliminar());
        } elseif ($accion == 'existe') {
            $e->setEje($_POST['ejeNombre']);
            $resultado = $e->Existe($_POST['ejeNombre']);
            echo json_encode($resultado);
        } else {
            $e->setEje($_POST['ejeNombre']);
            
            if ($accion == 'registrar') {
                echo  json_encode($e->Registrar());
            } elseif ($accion == 'modificar') {
                $e->setId($_POST['ejeId']);
                echo  json_encode($e->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
