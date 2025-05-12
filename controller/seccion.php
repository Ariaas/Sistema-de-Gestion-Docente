<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $s = new Seccion();

        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            echo json_encode($s->Listar());
        } elseif ($accion == 'consultarUnion') {
            echo json_encode($s->Listar());
        } elseif ($accion == 'eliminar') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
        } elseif ($accion == 'existe') {
            $resultado = $s->Existe($_POST['codigoSeccion'], $_POST['trayectoNumero'], $_POST['trayectoAnio']);
            echo json_encode($resultado);
        } elseif ($accion == 'registrar') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
            echo json_encode($s->Registrar());
        } elseif ($accion == 'modificar') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
        }
        exit;
    }

    require_once("model/trayecto.php");
    $t = new Trayecto();
    $trayectos = $t->obtenerTrayectos();

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
