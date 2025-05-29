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

        $c = new Categoria();
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        
        if ($accion == 'consultar') {
            echo json_encode($c->Listar());
        } elseif ($accion == 'eliminar') {
            $c->setId($_POST['categoriaId']);
            echo  json_encode($c->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'categoria');
        } elseif ($accion == 'existe') {
            $c->setCategoria($_POST['categoriaNombre']);
            $resultado = $c->Existe($_POST['categoriaNombre']);
            echo json_encode($resultado);
        } else {
            $c->setCategoria($_POST['categoriaNombre']);
            if ($accion == 'registrar') {
                echo  json_encode($c->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'categoria');
            } elseif ($accion == 'modificar') {
                $c->setId($_POST['categoriaId']);
                echo  json_encode($c->modificar());

                $bitacora->registrarAccion($usu_id, 'modificar', 'categoria');
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
