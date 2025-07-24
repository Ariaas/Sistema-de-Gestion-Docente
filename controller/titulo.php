<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$pagina = "titulo";

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $t = new Titulo();
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null && $accion != 'consultar' && $accion != 'existe') {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        if ($accion == 'registrar') {
            $t->set_prefijo($_POST['tituloprefijo']);
            $t->set_nombreTitulo($_POST['titulonombre']);
            echo json_encode($t->Registrar());
            $bitacora->registrarAccion($usu_id, 'registrar', 'titulo');
        } elseif ($accion == 'modificar') {
            $t->set_original_prefijo($_POST['tituloprefijo_original']);
            $t->set_original_nombre($_POST['titulonombre_original']);
            $t->set_prefijo($_POST['tituloprefijo']);
            $t->set_nombreTitulo($_POST['titulonombre']);
            echo json_encode($t->Modificar());
            $bitacora->registrarAccion($usu_id, 'modificar', 'titulo');
        } elseif ($accion == 'eliminar') {
            $t->set_prefijo($_POST['tituloprefijo']);
            $t->set_nombreTitulo($_POST['titulonombre']);
            echo json_encode($t->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'titulo');
        } elseif ($accion == 'consultar') {
            echo json_encode($t->Consultar());
        } elseif ($accion == 'existe') {
            $t->set_prefijo($_POST['tituloprefijo']);
            $t->set_nombreTitulo($_POST['titulonombre']);

            if (isset($_POST['tituloprefijo_original']) && isset($_POST['titulonombre_original'])) {
                $t->set_original_prefijo($_POST['tituloprefijo_original']);
                $t->set_original_nombre($_POST['titulonombre_original']);
            }

            if ($t->Existe()) {
                echo json_encode(['resultado' => 'existe', 'mensaje' => 'El Título ya existe.']);
            } else {
                echo json_encode(['resultado' => 'no_existe']);
            }
        }
        exit;
    }
    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
