<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("model/mallacurricular.php");

$obj4 = new Malla();

if (is_file("views/mallacurricular.php")) {

    if (!empty($_POST)) {
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null && in_array($accion, ['registrar', 'activar', 'modificar', 'eliminar'])) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($obj4->Consultar());

        } else if($accion == 'verificar_condiciones') {
            echo json_encode($obj4->verificarCondicionesParaRegistrar());
        
        } else if ($accion == 'consultar_ucs') {
            $response = $obj4->obtenerUnidadesCurriculares();
            $response['accion'] = 'consultar_ucs'; 
            echo json_encode($response);

        } else if ($accion == 'consultar_ucs_por_malla') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $response = $obj4->obtenerUnidadesPorMalla();
            $response['accion'] = 'consultar_ucs_por_malla';
            echo json_encode($response);

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
            echo json_encode($obj4->Existecodigo());

        } else if ($accion == 'existe_cohorte') {
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $is_modificar = isset($_POST['mal_codigo']);
            if($is_modificar){
                $obj4->setMalCodigo($_POST['mal_codigo']);
            }
            echo json_encode($obj4->ExisteCohorte($is_modificar));

         } else if ($accion == 'cambiar_estado_activo') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $response = $obj4->cambiarEstadoActivo();
            if (isset($response['resultado']) && $response['resultado'] === 'ok') {
                $bitacora->registrarAccion($usu_id, $response['accion_bitacora'], 'malla curricular');
            }
            echo json_encode($response);
        } else if ($accion == 'modificar') {
            $unidades = isset($_POST['unidades']) ? json_decode($_POST['unidades'], true) : [];

            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
            echo json_encode($obj4->Modificar($unidades));
            $bitacora->registrarAccion($usu_id, 'modificar', 'malla curricular');

        } elseif ($accion == 'eliminar') {
             $obj4->setMalCodigo($_POST['mal_codigo']);
            echo json_encode($obj4->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'malla curricular');
        }
        exit;
    }
    require_once("views/mallacurricular.php");
} else {
    echo "pagina en construccion";
}
?>