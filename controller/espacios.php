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

        $e = new Espacio();
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setCodigo($_POST['codigoEspacio']);
            echo  json_encode($e->eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'espacios');

        } elseif ($accion == 'existe') {
            $e->setCodigo($_POST['codigoEspacio']);
            $resultado = $e->Existe($_POST['codigoEspacio']);
            echo json_encode($resultado);
        } else {
            $e->setCodigo($_POST['codigoEspacio']);
            $e->setTipo($_POST['tipoEspacio']);
            if ($accion == 'registrar') {
                echo  json_encode($e->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'espacios');

            } elseif ($accion == 'modificar') {

                $bitacora->registrarAccion($usu_id, 'modificar', 'espacios');
                
                echo  json_encode($e->modificar());
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}