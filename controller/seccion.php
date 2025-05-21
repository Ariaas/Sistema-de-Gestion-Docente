<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

$s = new Seccion();
$trayectos = $s->obtenerTrayectos();

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            echo json_encode($s->Listar());
        } elseif ($accion == 'consultarUnion') {
            echo json_encode($s->Listar());
        } elseif ($accion == 'eliminar') {
            $s->setseccionId($_POST['seccionId']);
            echo json_encode($s->Eliminar());
        } elseif ($accion == 'existe') {
            $resultado = $s->Existe($_POST['codigoSeccion'], $_POST['trayectoSeccion']);
            echo json_encode($resultado);
        } elseif ($accion == 'registrar') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
            echo json_encode($s->Registrar());
        } elseif ($accion == 'modificar') {
            $s->setseccionId($_POST['seccionId']);
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
            echo json_encode($s->Modificar());
        } elseif ($accion == 'unir') {
            echo json_encode($s->Unir($_POST['secciones'], true));
        } elseif ($accion == 'separar') {
            $s->setGrupoId($_POST['grupoId']);
            echo json_encode($s->Separar());
        } else {
            echo "Acción no válida";
        }
        exit;
    }
    
    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
