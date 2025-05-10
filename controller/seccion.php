<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        
        $e = new Seccion();

        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            // Listar todas las secciones
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            // Eliminar una sección
            $e->setCodigoSeccion($_POST['codigoSeccion']);
            //echo json_encode($e->Eliminar());
        } elseif ($accion == 'existe') {
            // Verificar si una sección ya existe
            $resultado = $e->Existe($_POST['codigoSeccion'], $_POST['trayectoNumero'], $_POST['trayectoAnio']);
            echo json_encode($resultado);
        } elseif ($accion == 'registrar') {
            // Registrar una nueva sección
            $e->setCodigoSeccion($_POST['codigoSeccion']);
            $e->setCantidadSeccion($_POST['cantidadSeccion']);
            $e->setTrayectoSeccion($_POST['trayectoSeccion']);
            echo json_encode($e->Registrar());
        } elseif ($accion == 'modificar') {
            // Modificar una sección existente
            $e->setCodigoSeccion($_POST['codigoSeccion']);
            $e->setCantidadSeccion($_POST['cantidadSeccion']);
            $e->setTrayectoSeccion($_POST['trayectoSeccion']);
            //echo json_encode($e->Modificar());
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
