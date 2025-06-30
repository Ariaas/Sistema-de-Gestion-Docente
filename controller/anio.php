<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
$c = new Anio();
$c->Notificaciones();
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
            
            echo json_encode($c->Listar());
        } elseif ($accion == 'eliminar') {
            $c->setId($_POST['aniId']);
            echo  json_encode($c->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'anio');
        } elseif ($accion == 'existe') {
            $c->setAnio($_POST['aniAnio']);
            $resultado = $c->Existe($_POST['aniAnio']);
            echo json_encode($resultado);
        } elseif ($accion == 'activar') {
            $c->setId($_POST['aniId']);
            $c->setActivo($_POST['aniActivo']);
            echo json_encode($c->Activar());
            $bitacora->registrarAccion($usu_id, 'activar', 'anio');
        } else {
            $c->setAnio($_POST['aniAnio']);
            $c->setAperturaFase1($_POST['aniAperturaFase1']);
            $c->setCierraFase1($_POST['aniCierraFase1']);
            $c->setAperturaFase2($_POST['aniAperturaFase2']);
            $c->setCierraFase2($_POST['aniCierraFase2']);

            if ($accion == 'registrar') {
                echo  json_encode($c->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'anio');
            } elseif ($accion == 'modificar') {
                $c->setId($_POST['aniId']);
                echo  json_encode($c->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'anio');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
