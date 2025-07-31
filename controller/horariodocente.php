<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("model/horariodocente.php");

if (is_file("views/horariodocente.php")) {
    if (!empty($_POST)) {
        $e = new HorarioDocente();
        $accion = $_POST['accion'] ?? '';

        require_once("model/bitacora.php");
        $usu_id = $_SESSION['usu_id'] ?? null;
        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        $resultado = [];

        if ($accion == 'consultar') {
            $resultado = $e->Listar();
        } elseif ($accion == 'eliminar') {
            $e->setDocCedula($_POST['original_cedula']);
            $e->setHdoLapso($_POST['original_lapso']);
            $e->setHdoTipoactividad($_POST['original_actividad']);

            $resultado = $e->Eliminar();
            if (isset($resultado['resultado']) && $resultado['resultado'] == 'eliminar') {
                $bitacora->registrarAccion($usu_id, 'eliminar', 'horario_docente');
            }
        } elseif ($accion == 'existe') {
            $resultado = $e->Existe($_POST['docente'], $_POST['lapso'], $_POST['actividad']);
        } elseif ($accion == 'registrar') {
            $e->setDocCedula($_POST['docente']);
            $e->setHdoLapso($_POST['lapso']);
            $e->setHdoTipoactividad($_POST['actividad']);
            $e->setHdoDescripcion($_POST['descripcion']);
            $e->setHdoDependencia($_POST['dependencia']);
            $e->setHdoObservacion($_POST['observacion']);
            $e->setHdoHoras($_POST['horas']);

            $resultado = $e->Registrar();
            if (isset($resultado['resultado']) && $resultado['resultado'] == 'registrar') {
                $bitacora->registrarAccion($usu_id, 'registrar', 'horario_docente');
            }
        } elseif ($accion == 'modificar') {
            $e->setDocCedula($_POST['docente']);
            $e->setHdoLapso($_POST['lapso']);
            $e->setHdoTipoactividad($_POST['actividad']);
            $e->setHdoDescripcion($_POST['descripcion']);
            $e->setHdoDependencia($_POST['dependencia']);
            $e->setHdoObservacion($_POST['observacion']);
            $e->setHdoHoras($_POST['horas']);

            $resultado = $e->Modificar($_POST['original_cedula'], $_POST['original_lapso'], $_POST['original_actividad']);
            if (isset($resultado['resultado']) && $resultado['resultado'] == 'modificar') {
                $bitacora->registrarAccion($usu_id, 'modificar', 'horario_docente');
            }
        } elseif ($accion == 'load_docentes') {
            $resultado = $e->obtenerDocentes();
        } elseif ($accion == 'load_lapsos') {
            $resultado = $e->obtenerLapsosActivos();
        } elseif ($accion == 'consultar_horario_clases') {
            $resultado = $e->obtenerHorarioCompletoPorDocente($_POST['doc_cedula']);
        } else {
            $resultado = ['resultado' => 'error', 'mensaje' => 'Acción no reconocida.'];
        }

        echo json_encode($resultado);
        exit;
    }
    require_once("views/horariodocente.php");
} else {
    echo "Página en construcción";
}
