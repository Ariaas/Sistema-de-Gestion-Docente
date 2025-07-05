<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $o = new Area();
        $accion = $_POST['accion'] ?? '';

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        
        try {
            if ($accion == 'consultar') {
                echo json_encode($o->Listar());
            } elseif ($accion == 'eliminar') {
                $o->setArea($_POST['areaNombre'] ?? ''); 
                echo json_encode($o->Eliminar());
                $bitacora->registrarAccion($usu_id, 'eliminar', 'area');
            } elseif ($accion == 'existe') {
                $o->setArea($_POST['areaNombre'] ?? '');  
                echo json_encode($o->Existe($_POST['areaNombre'] ?? ''));
            } else {
                $o->setArea($_POST['areaNombre'] ?? '');
                $o->setDescripcion($_POST['areaDescripcion'] ?? '');
                if ($accion == 'registrar') {
                    echo json_encode($o->Registrar());
                    $bitacora->registrarAccion($usu_id, 'registrar', 'area');
                } elseif ($accion == 'modificar') {
                    echo json_encode($o->Modificar($_POST['areaNombreOriginal'] ?? ''));
                    $bitacora->registrarAccion($usu_id, 'modificar', 'area');
                }
            }
        } catch (Exception $e) {
            echo json_encode(['resultado' => 'error', 'mensaje' => $e->getMessage()]);
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}