<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $e = new Eje();
        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setEje($_POST['ejeNombre']);
            echo  json_encode($e->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'eje');
        } elseif ($accion == 'existe') {
            $ejeNombre = $_POST['ejeNombre'];
            $ejeExcluir = isset($_POST['ejeExcluir']) ? $_POST['ejeExcluir'] : null;
            $resultado = $e->Existe($ejeNombre, $ejeExcluir);
            echo json_encode($resultado);
        } else {
            $e->setEje($_POST['ejeNombre']);
            $e->setDescripcion($_POST['ejeDescripcion']);

            if ($accion == 'registrar') {
                echo  json_encode($e->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'eje');
            } elseif ($accion == 'modificar') {
                $e->setEje($_POST['ejeNombre']);
                $e->setDescripcion($_POST['ejeDescripcion']);
                echo  json_encode($e->modificar($_POST['ejeNombreOriginal']));

                $bitacora->registrarAccion($usu_id, 'modificar', 'eje');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
