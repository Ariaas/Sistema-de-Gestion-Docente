<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

$u = new UC();
$trayectos = $u->obtenerTrayecto();
$ejes = $u->obtenerEje();
$areas = $u->obtenerArea();
$docentes = $u->obtenerDocente();

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

       
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'consultarAsignacion') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'asignar') {
            echo  json_encode($u->Asignar($_POST['docentes'], $_POST['ucs']));
        } elseif ($accion == 'quitar') {
            echo json_encode($u->Quitar());
        }elseif ($accion == 'eliminar') {
            $u->setidUC($_POST['idUC']);
            echo  json_encode($u->Eliminar());
        } elseif ($accion == 'existe') {
            $u->setcodigoUC($_POST['codigoUC']);
            $resultado = $u->Existe($_POST['codigoUC']);
            echo json_encode($resultado);
        } else {
            $u->setcodigoUC($_POST['codigoUC']);
            $u->setnombreUC($_POST['nombreUC']);
            $u->setcreditosUC($_POST['creditosUC']);
            $u->setasistidaUC($_POST['asistidaUC']);
            $u->setacademicaUC($_POST['academicaUC']);
            $u->setindependienteUC($_POST['independienteUC']);
            $u->settrayectoUC($_POST['trayectoUC']);
            $u->setejeUC($_POST['ejeUC']);
            $u->setareaUC($_POST['areaUC']);
            $u->setperiodoUC($_POST['periodoUC']);
            $u->setelectivaUC($_POST['electivaUC']);

            if ($accion == 'registrar') {
                echo  json_encode($u->Registrar());
            } elseif ($accion == 'modificar') {
                $u->setidUC($_POST['idUC']);
                echo  json_encode($u->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
