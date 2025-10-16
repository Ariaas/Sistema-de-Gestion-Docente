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
        } elseif ($accion == 'eliminar') {
            $u->setcodigoUC($_POST['codigoUC']);
            echo  json_encode($u->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'Unidad Curricular');
        } elseif ($accion == 'activar') {
            $u->setcodigoUC($_POST['codigoUC']);
            echo json_encode($u->Activar());
            $bitacora->registrarAccion($usu_id, 'activar', 'Unidad Curricular');
        } elseif ($accion == 'existe') {
            $codigoUC = $_POST['codigoUC'];
            $codigoExcluir = isset($_POST['codigoExcluir']) ? $_POST['codigoExcluir'] : null;
            $u->setcodigoUC($codigoUC);
            $resultado = $u->Existe($codigoUC, $codigoExcluir);
            echo json_encode($resultado);
        } elseif ($accion == 'verificar_horario') {
            $codigo = isset($_POST['uc_codigo']) ? $_POST['uc_codigo'] : (isset($_POST['codigoUC']) ? $_POST['codigoUC'] : null);
            if ($codigo !== null && $codigo !== '') {
                $respuesta = $u->verificarEnHorario($codigo);
            } else {
                $respuesta["resultado"] = "error";
                $respuesta["mensaje"] = "Código de UC no proporcionado.";
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
