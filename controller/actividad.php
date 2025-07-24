<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'actividad'; 

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $o = new Actividad();
        $accion = $_POST['accion'] ?? '';

        if ($accion !== 'consultar' && $accion !== 'listar_docentes' && $accion !== 'verificar_docente') {
            require_once("model/bitacora.php");
            $usu_id = $_SESSION['usu_id'] ?? null;

            if ($usu_id === null) {
                echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
                exit;
            }
            $bitacora = new Bitacora();
        }
        
        try {
            if ($accion == 'consultar') {
                echo json_encode($o->Listar());

            } elseif ($accion == 'listar_docentes') {
                echo json_encode($o->ListarDocentes());

            } elseif ($accion == 'verificar_docente') {
                $docenteId = $_POST['docId'] ?? 0;
                $existe = $o->DocenteYaTieneActividad($docenteId);
                echo json_encode(['existe' => $existe]);

            } elseif ($accion == 'eliminar') {
                $o->setId($_POST['actId'] ?? ''); 
                echo json_encode($o->Eliminar());
                if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'eliminar', 'actividad');

            } else {
                $o->setDocId($_POST['docId'] ?? '');
                $o->setCreacionIntelectual((int)($_POST['actCreacion'] ?? 0));
                $o->setIntegracionComunidad((int)($_POST['actIntegracion'] ?? 0));
                $o->setGestionAcademica((int)($_POST['actGestion'] ?? 0));
                $o->setOtras((int)($_POST['actOtras'] ?? 0));
                
                if ($accion == 'registrar') {
                    echo json_encode($o->Registrar());
                    if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'registrar', 'actividad');
                } elseif ($accion == 'modificar') {
                    $o->setId($_POST['actId'] ?? '');
                    echo json_encode($o->Modificar());
                    if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'modificar', 'actividad');
                }
            }
        } catch (Exception $e) {
            echo json_encode(['resultado' => 'error', 'mensaje' => $e->getMessage()]);
        }
        exit;
    }

   
    $o = new Actividad();
    $totalDocentes = $o->ContarDocentesActivos();

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}