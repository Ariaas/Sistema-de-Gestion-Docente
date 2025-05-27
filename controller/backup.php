<?php

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {


if (!empty($_POST)) {
    $objMantenimiento = new Mantenimiento();
    $accion = $_POST['accion'];

    switch ($accion) {
        case 'guardar_respaldo':
            
            echo json_encode($objMantenimiento->GuardarRespaldo());
            break;
        case 'restaurar_sistema':
            
            if (isset($_POST['archivo_sql'])) { 
                echo json_encode($objMantenimiento->RestaurarSistema($_POST['archivo_sql']));
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