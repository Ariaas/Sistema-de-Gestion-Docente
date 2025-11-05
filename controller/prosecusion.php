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
        } else if ($accion == 'verificar_estado') {
            echo json_encode($p->VerificarEstado());
            exit;
        } else if ($accion == 'verificarDestinoAutomatico') {
            echo json_encode($p->verificarDestinoAutomatico($_POST['seccionOrigenCodigo']));
            exit;
        } else if ($accion == 'obtenerOpcionesDestinoManual') {
            echo json_encode($p->obtenerOpcionesDestinoManual($_POST['seccionOrigenCodigo']));
            exit;
        } else if ($accion == 'calcularCantidadProsecusion') {
            echo json_encode($p->calcularCantidadProsecusion($_POST['seccionCodigo']));
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
            $seccionOrigenCodigo = $_POST['seccionOrigenCodigo'];
            $cantidad = $_POST['cantidad'];
            $seccionDestinoCodigo = isset($_POST['seccionDestinoCodigo']) ? $_POST['seccionDestinoCodigo'] : null;
            $confirmarExcesoRaw = $_POST['confirmar_exceso'] ?? $_POST['confirmarExceso'] ?? 'false';
            $confirmarExceso = filter_var($confirmarExcesoRaw, FILTER_VALIDATE_BOOLEAN);

            $resultado = $p->RealizarProsecusion($seccionOrigenCodigo, $cantidad, $seccionDestinoCodigo, $confirmarExceso);

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
