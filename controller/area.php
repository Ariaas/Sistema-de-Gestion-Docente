<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $o = new Area();
        $accion = $_POST['accion'] ?? '';

        $usu_id = 1;
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
                if ($accion == 'registrar') {
                    echo json_encode($o->Registrar());

                    $bitacora->registrarAccion($usu_id, 'registrar', 'area');
                } elseif ($accion == 'modificar') {
                    $o->setId($_POST['areaId'] ?? '');
                     $o->setArea($_POST['areaNombre'] ?? ''); 
                    echo json_encode($o->Modificar());

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