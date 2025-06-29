<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
$s = new Seccion();
$anios = $s->obtenerAnios();
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
            echo json_encode($s->Listar());
        } elseif ($accion == 'eliminar') {
            $s->setSeccionId($_POST['seccionId']);
            echo  json_encode($s->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'anio');
        } elseif ($accion == 'existe') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setAnioId($_POST['anioId']);
            $resultado = $s->Existe($_POST['codigoSeccion'], $_POST['anioId']);
            echo json_encode($resultado);
        }else {
            $s->setAnioId($_POST['anioId']);
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);

            if ($accion == 'registrar') {
                echo  json_encode($s->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'anio');
            } elseif ($accion == 'modificar') {
                $s->setSeccionId($_POST['seccionId']);
                echo  json_encode($s->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'anio');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
