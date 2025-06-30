<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("model/" . $pagina . ".php");

$obj4 = new Malla();


if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($obj4->Consultar());
          } else if ($accion == 'consultar_ucs') {
            echo json_encode($obj4->obtenerUnidadesCurriculares());

        } else if ($accion == 'consultar_ucs_por_malla') {
            $obj4->setMalId($_POST['mal_id']);
            echo json_encode($obj4->obtenerUnidadesPorMalla($obj4->getMalId()));

        } else if ($accion == 'registrar') {
            $unidades = isset($_POST['unidades']) ? json_decode($_POST['unidades'], true) : [];
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
            echo json_encode($obj4->Registrar($unidades));
            $bitacora->registrarAccion($usu_id, 'registrar', 'malla curricular');


        } else if ($accion == 'existe') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            if (isset($_POST['mal_id']) && !empty($_POST['mal_id'])) {
                $obj4->setMalId($_POST['mal_id']);
            }
            echo json_encode($obj4->Existecodigo());
        } else if ($accion == 'existe_nombre') {
            $obj4->setMalNombre($_POST['mal_nombre']);
            if (isset($_POST['mal_id']) && !empty($_POST['mal_id'])) {
                $obj4->setMalId($_POST['mal_id']);
            }
            echo json_encode($obj4->Existenombre());
        } else if($accion == 'modificar'){
            $unidades = isset($_POST['unidades']) ? json_decode($_POST['unidades'], true) : [];

            $obj4->setMalId($_POST['mal_id']);
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
           echo json_encode($obj4->Modificar($unidades));
            $bitacora->registrarAccion($usu_id, 'modificar', 'malla curricular');
        } elseif ($accion == 'eliminar') {
            $obj4->setMalId($_POST['mal_id']);
            echo json_encode($obj4->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'malla curricular');
        
    }
        
       

        exit;
    }

    require_once("views/". $pagina . ".php");
} else {
    echo "pagina en construccion";
}
?>