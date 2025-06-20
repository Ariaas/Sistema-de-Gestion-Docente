<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'docente'; // Nombre de la clase del modelo

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $p = new Docente();
        $accion = $_POST['accion'] ?? '';

        /*
        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        */

        if ($accion == 'consultar') {
            echo json_encode($p->Listar());
        } elseif ($accion == 'eliminar') {
            $p->setCedula($_POST['cedulaDocente']);
            $resultado = $p->Eliminar();
            echo json_encode($resultado);
            // $bitacora->registrarAccion($usu_id, 'eliminar', 'docente');
        } elseif ($accion == 'Existe') {
            $resultado = $p->Existe($_POST['cedulaDocente']);
            echo json_encode(['existe' => $resultado]);
        } elseif ($accion == 'obtenerTitulosDocente') {
            $p->setCedula($_POST['cedulaDocente']);
            $titulos = $p->obtenerTitulosDocente($p->obtenerIdPorCedula($_POST['cedulaDocente']));
            echo json_encode(['resultado' => 'success', 'titulos' => $titulos]);
            // ===== INICIO DE MODIFICACIÓN =====
        } elseif ($accion == 'obtenerCoordinacionesDocente') {
            $p->setCedula($_POST['cedulaDocente']);
            $coordinaciones = $p->obtenerCoordinacionesDocente($p->obtenerIdPorCedula($_POST['cedulaDocente']));
            echo json_encode(['resultado' => 'success', 'coordinaciones' => $coordinaciones]);
            // ===== FIN DE MODIFICACIÓN =====
        } else {
            $p->setCategoriaId($_POST['categoria']);
            $p->setPrefijo($_POST['prefijoCedula']);
            $p->setCedula($_POST['cedulaDocente']);
            $p->setNombre($_POST['nombreDocente']);
            $p->setApellido($_POST['apellidoDocente']);
            $p->setCorreo($_POST['correoDocente']);
            $p->setDedicacion($_POST['dedicacion']);
            $p->setCondicion($_POST['condicion']);

            if (isset($_POST['titulos']) && is_array($_POST['titulos'])) {
                $p->setTitulos($_POST['titulos']);
            } else {
                $p->setTitulos(array());
            }

            // ===== INICIO DE MODIFICACIÓN =====
            if (isset($_POST['coordinaciones']) && is_array($_POST['coordinaciones'])) {
                $p->setCoordinaciones($_POST['coordinaciones']);
            } else {
                $p->setCoordinaciones(array());
            }
            // ===== FIN DE MODIFICACIÓN =====

            if ($accion == 'incluir') {
                echo json_encode($p->Registrar());
                // $bitacora->registrarAccion($usu_id, 'registrar', 'docente');
            } elseif ($accion == 'modificar') {
                echo json_encode($p->Modificar());
                // $bitacora->registrarAccion($usu_id, 'modificar', 'docente');
            }
        }
        exit;
    }

    $p = new Docente();
    $categorias = $p->listacategoria();
    $titulos = $p->listatitulo();
    $coordinaciones = $p->listaCoordinacion(); // ===== NUEVA LÍNEA =====

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
