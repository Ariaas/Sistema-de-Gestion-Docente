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
$trayectos = $s->obtenerTrayectos();
$cohortes = $s->obtenerCohorte();

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
        } elseif ($accion == 'consultarUnion') {
            echo json_encode($s->Listar());
        } elseif ($accion == 'eliminar') {
            $s->setseccionId($_POST['seccionId']);
            echo json_encode($s->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'seccion');

        } elseif ($accion == 'existe') {
            $resultado = $s->Existe($_POST['codigoSeccion'], $_POST['trayectoSeccion'], $_POST['nombreSeccion']);
            echo json_encode($resultado);
        } elseif ($accion == 'registrar') {
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
            $s->setcohorteSeccion($_POST['cohorteSeccion']);
            $s->setNombreSeccion($_POST['nombreSeccion']);

            echo json_encode($s->Registrar());

            $bitacora->registrarAccion($usu_id, 'registrar', 'seccion');

        } elseif ($accion == 'modificar') {
            $s->setseccionId($_POST['seccionId']);
            $s->setCodigoSeccion($_POST['codigoSeccion']);
            $s->setCantidadSeccion($_POST['cantidadSeccion']);
            $s->setTrayectoSeccion($_POST['trayectoSeccion']);
            $s->setcohorteSeccion($_POST['cohorteSeccion']);
            $s->setNombreSeccion($_POST['nombreSeccion']);

            echo json_encode($s->Modificar());

            $bitacora->registrarAccion($usu_id, 'modificar', 'seccion');

        } elseif ($accion == 'unir') {
            // echo json_encode($s->Unir($_POST['secciones'], true));

            $bitacora->registrarAccion($usu_id, 'unir', 'seccion');
        } elseif ($accion == 'separar') {
            $s->setGrupoId($_POST['grupoId']);
            // echo json_encode($s->Separar());
            $bitacora->registrarAccion($usu_id, 'separar', 'seccion');


        } 
        // elseif ($accion == 'obtenerSeccionesDestino') {
        //     $seccionesOrigenJSON = $_POST['seccionesOrigen'] ?? '[]';
        //     $seccionesOrigenIds = json_decode($seccionesOrigenJSON, true);
        //     if (json_last_error() !== JSON_ERROR_NONE) {
        //          echo json_encode(['resultado' => 'error', 'mensaje' => 'Error en los datos de secciones .']);
        //          exit;
        //     }
            //echo json_encode($s->ObtenerSeccionesDestinoElegibles($seccionesOrigenIds));
        // } 
        // elseif ($accion == 'promocionar') {
        //     $seccionesOrigenJSON = $_POST['seccionesOrigen'] ?? '[]';
        //     $seccionesOrigenIds = json_decode($seccionesOrigenJSON, true);
        //     $seccionDestinoId = $_POST['seccionDestinoId'] ?? null;

        //     if (json_last_error() !== JSON_ERROR_NONE || empty($seccionesOrigenIds) || $seccionDestinoId === null) {
        //         echo json_encode(['resultado' => 'error', 'mensaje' => 'Datos incompletos.']);
        //         exit;
        //     }
            //echo json_encode($s->PromocionarSecciones($seccionesOrigenIds, $seccionDestinoId));
            // $bitacora->registrarAccion($usu_id, 'promocionar', 'seccion');


        // } 
        else {
            echo "Acción no válida";
        }
        exit;
    }
    
    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}