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
        $objMantenimiento = new Mantenimiento();
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        switch ($accion) {
            case 'guardar_respaldo':

                echo json_encode($objMantenimiento->GuardarRespaldo());
                $bitacora->registrarAccion($usu_id, 'Guardar Respaldo', 'Respaldo');

                break;
            case 'restaurar_sistema':

                if (isset($_POST['archivo_sql'])) {
                    echo json_encode($objMantenimiento->RestaurarSistema($_POST['archivo_sql']));
                    $bitacora->registrarAccion($usu_id, 'Restaurar Sistema', 'Respaldo');
                } else {
                    echo json_encode(["status" => "error", "message" => "Falta el nombre del archivo ZIP para restaurar."]);
                }
                break;
            case 'obtener_respaldos':

                echo json_encode($objMantenimiento->ObtenerRespaldos());
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Acción no válida."]);
                break;
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
