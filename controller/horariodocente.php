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

        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setDocCedula($_POST['docente']);
            $e->setHdoLapso($_POST['lapso']);
            $e->setHdoTipoactividad($_POST['actividad']);
            echo json_encode($e->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'horario_docente');
        } elseif ($accion == 'existe') {
            echo json_encode($e->Existe($_POST['docente'], $_POST['lapso'], $_POST['actividad']));
        } elseif ($accion == 'registrar') {
            $e->setDocCedula($_POST['docente']);
            $e->setHdoLapso($_POST['lapso']);
            $e->setHdoTipoactividad($_POST['actividad']);
            $e->setHdoDescripcion($_POST['descripcion']);
            $e->setHdoDependencia($_POST['dependencia']);
            $e->setHdoObservacion($_POST['observacion']);
            $e->setHdoHoras($_POST['horas']);
            echo json_encode($e->Registrar());
            $bitacora->registrarAccion($usu_id, 'registrar', 'horario_docente');
        } elseif ($accion == 'modificar') {
            $e->setDocCedula($_POST['docente']);
            $e->setHdoLapso($_POST['lapso']);
            $e->setHdoTipoactividad($_POST['actividad']);
            $e->setHdoDescripcion($_POST['descripcion']);
            $e->setHdoDependencia($_POST['dependencia']);
            $e->setHdoObservacion($_POST['observacion']);
            $e->setHdoHoras($_POST['horas']);
            echo json_encode($e->Modificar($_POST['original_cedula'], $_POST['original_lapso'], $_POST['original_actividad']));
            $bitacora->registrarAccion($usu_id, 'modificar', 'horario_docente');
            // --- Acciones auxiliares ---
        } elseif ($accion == 'load_docentes') {
            echo json_encode($e->obtenerDocentes());
        } elseif ($accion == 'load_lapsos') {
            echo json_encode($e->obtenerLapsosActivos());
        } elseif ($accion == 'consultar_horario_clases') {
            echo json_encode($e->obtenerHorarioCompletoPorDocente($_POST['doc_cedula']));
        } else {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Acción no reconocida.']);
        }
        exit;
    }
    require_once("views/horariodocente.php");
} else {
    echo "Página en construcción";
}
