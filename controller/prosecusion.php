<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
$p = new Prosecusion();
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
            echo json_encode($p->Listar());
        } else if ($accion == 'obtenerOpcionesDestinoManual') {
            echo json_encode($p->obtenerOpcionesDestinoManual($_POST['seccionOrigenId']));
            exit;
        } else if ($accion == 'calcularCantidadProsecusion') {
            echo json_encode($p->calcularCantidadProsecusion($_POST['seccionId']));
            exit;
        } else if ($accion == 'consultarSeccionesOrigen') {
            echo json_encode($p->ListarSeccionesOrigen());
            exit;
        } else if ($accion == 'eliminar') {
            $p->setProId($_POST['pro_id']);
            echo json_encode($p->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'prosecusion');
            exit;
        } else if ($accion == 'prosecusion') {
            $seccionOrigenId = $_POST['seccionOrigenId'];
            $cantidad = $_POST['cantidad'];
            $seccionDestinoId = isset($_POST['seccionDestinoId']) ? $_POST['seccionDestinoId'] : null;

            $resultado = $p->RealizarProsecusion($seccionOrigenId, $cantidad, $seccionDestinoId);

            if (isset($resultado['resultado']) && $resultado['resultado'] === 'prosecusion') {
                $bitacora->registrarAccion($usu_id, 'realizó una prosecusion', 'prosecusion');
            }

            echo json_encode($resultado);
            exit;
        }
        exit;
    }
    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
