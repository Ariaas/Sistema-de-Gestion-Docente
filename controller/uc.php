<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

$u = new UC();
$ejes = $u->obtenerEje();
$areas = $u->obtenerArea();
$docentes = $u->obtenerDocente();

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'consultarAsignacion') {
            echo json_encode($u->Listar());
        } elseif ($accion == 'ver_docentes') {
            $docentes = $u->obtenerDocentesPorUc($_POST['codigo']);
            echo json_encode(['resultado' => 'ok', 'mensaje' => $docentes]);
        } elseif ($accion == 'asignar') {
            echo  json_encode($u->Asignar($_POST['asignaciones'], $_POST['ucs']));

            $bitacora->registrarAccion($usu_id, 'Asignar', 'Unidad Curricular');
        } elseif ($accion == 'quitar') {
            echo json_encode($u->Quitar());

            $bitacora->registrarAccion($usu_id, 'Quitar', 'Unidad Curricular');
        } elseif ($accion == 'eliminar') {
            $u->setcodigoUC($_POST['codigoUC']);
            echo  json_encode($u->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'Unidad Curricular');
        } elseif ($accion == 'existe') {
            $u->setcodigoUC($_POST['codigoUC']);
            $resultado = $u->Existe($_POST['codigoUC']);
            echo json_encode($resultado);
        } elseif ($accion == 'verificar_horario') {
            if (isset($_POST['uc_codigo'])) {
                $respuesta = $u->verificarEnHorario($_POST['uc_codigo']);
            } else {
                $respuesta["resultado"] = "error";
                $respuesta["mensaje"] = "Código de UC no proporcionado.";
            }
            echo json_encode($respuesta);
        } elseif ($accion == 'verificar_docente_horario') {
            if (isset($_POST['uc_codigo']) && isset($_POST['doc_cedula'])) {
                $respuesta = $u->verificarDocenteEnHorario($_POST['uc_codigo'], $_POST['doc_cedula']);
            } else {
                $respuesta["resultado"] = "error";
                $respuesta["mensaje"] = "Código de UC o Cédula de Docente no proporcionado.";
            }
            echo json_encode($respuesta);
        } else {
            $u->setcodigoUC($_POST['codigoUC']);
            $u->setnombreUC($_POST['nombreUC']);
            $u->setcreditosUC($_POST['creditosUC']);
            $u->settrayectoUC($_POST['trayectoUC']);
            $u->setejeUC($_POST['ejeUC']);
            $u->setareaUC($_POST['areaUC']);
            $u->setperiodoUC($_POST['periodoUC']);
            $u->setelectivaUC($_POST['electivaUC']);

            if ($accion == 'registrar') {
                echo  json_encode($u->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'Unidad Curricular');
            } elseif ($accion == 'modificar') {
                echo  json_encode($u->modificar($_POST['codigoUCOriginal']));

                $bitacora->registrarAccion($usu_id, 'modificar', 'Unidad Curricular');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
