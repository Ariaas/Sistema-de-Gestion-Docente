<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        $c = new Anio();

        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($c->Listar());
        } elseif ($accion == 'verificar_condiciones_registro') {
            echo json_encode($c->Verificar());
        } elseif ($accion == 'consultar_per') {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['aniTipo']);
            echo json_encode($c->consultarPer());
        } elseif ($accion == 'eliminar') {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['tipoAnio']);
            echo  json_encode($c->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'anio');
        } elseif ($accion == 'existe') {
            $anioOriginal = isset($_POST['anioOriginal']) ? $_POST['anioOriginal'] : null;
            $tipoOriginal = isset($_POST['tipoOriginal']) ? $_POST['tipoOriginal'] : null;
            $existe = $c->Existe($_POST['aniAnio'], $_POST['tipoAnio'], $anioOriginal, $tipoOriginal);
            if ($existe) {
                echo json_encode(['resultado' => 'existe', 'mensaje' => 'El AÑO colocado YA existe!']);
            } else {
                echo json_encode(['resultado' => 'no_existe']);
            }
        } else {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['tipoAnio']);
            $fases = [
                ['numero' => 1, 'apertura' => $_POST['aniAperturaFase1'], 'cierre' => $_POST['aniCierraFase1']],
                ['numero' => 2, 'apertura' => $_POST['aniAperturaFase2'], 'cierre' => $_POST['aniCierraFase2']]
            ];
            $c->setFases($fases);

            if ($accion == 'registrar') {
                echo  json_encode($c->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'anio');
            } elseif ($accion == 'modificar') {
                echo  json_encode($c->modificar($_POST['anioOriginal'], $_POST['tipoOriginal']));
                $bitacora->registrarAccion($usu_id, 'modificar', 'anio');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
