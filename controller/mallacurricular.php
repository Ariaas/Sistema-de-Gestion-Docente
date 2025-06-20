<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/*
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}*/
require_once("model/" . $pagina . ".php");

$obj4 = new Malla();

$unidades_curriculares_disponibles = $obj4->obtenerUnidadesCurricularesActivas();
$certificados_disponibles = $obj4->obtenerCertificadosActivos();
$anios = $obj4->obtenerAnios();
$cohortes= $obj4->obtenerCohorte();

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
        } else if ($accion == 'registrar') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalAnio($_POST['mal_Anio']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
            echo json_encode($obj4->Registrar());

            $bitacora->registrarAccion($usu_id, 'registrar', 'malla curricular');

        } else if ($accion == 'existe') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            
            // AÑADE ESTA LÓGICA: Asigna el ID al objeto si se está modificando
            if (isset($_POST['mal_id']) && !empty($_POST['mal_id'])) {
                $obj4->setMalId($_POST['mal_id']);
            }
            
            echo json_encode($obj4->Existecodigo());

        
        } 
        else if ($accion == 'existe_nombre') {
            $obj4->setMalNombre($_POST['mal_nombre']);

            // AÑADE ESTA LÓGICA: Asigna el ID al objeto si se está modificando
            if (isset($_POST['mal_id']) && !empty($_POST['mal_id'])) {
                $obj4->setMalId($_POST['mal_id']);
            }

            echo json_encode($obj4->Existenombre());
        
        } else if($accion == 'modificar'){
            $obj4->setMalId($_POST['mal_id']);
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalAnio($_POST['mal_Anio']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
            echo json_encode($obj4->Modificar());

            $bitacora->registrarAccion($usu_id, 'modificar', 'malla curricular');
        } elseif ($accion == 'eliminar') {

            $obj4->setMalId($_POST['mal_id']);
            echo json_encode($obj4->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'malla curricular');

        } elseif ($accion == 'asignar_uc_malla') {
            $mallaId  =$_POST['mal_id'];// usar setters y getter
            $ucIds = json_decode($_POST['uc_ids'], true);
            echo json_encode($obj4->AsignarUCsAMalla($mallaId, $ucIds));

            $bitacora->registrarAccion($usu_id, 'asignar', 'malla curricular');


        } elseif ($accion == 'quitar_uc_malla') { 
            $mallaId = $_POST['mal_id']; // usar setters y getter
            $ucId = $_POST['uc_id'];
            echo json_encode($obj4->QuitarUCDeMalla($mallaId, $ucId));

            $bitacora->registrarAccion($usu_id, 'quitar', 'malla curricular');
        } elseif ($accion == 'asignar_certificado_malla') {
            $mallaId = $_POST['mal_id'];
            $certIds = json_decode($_POST['cert_ids'], true);// usar setters y getter
            echo json_encode($obj4->AsignarCertificadosAMalla($mallaId, $certIds));

            $bitacora->registrarAccion($usu_id, 'asignar certificado', ' malla curricular');
        } elseif ($accion == 'quitar_certificado_malla') { // usar setters y getter
            $mallaId = $_POST['mal_id'];
            $certId = $_POST['cert_id'];
            echo json_encode($obj4->QuitarCertificadoDeMalla($mallaId, $certId));

            $bitacora->registrarAccion($usu_id, 'quitar certificado', 'malla curricular');
        } elseif ($accion == 'consultar_asignaciones_uc') {// usar setters y getter
            echo json_encode($obj4->ListarAsignacionesUC());
        } elseif ($accion == 'consultar_asignaciones_certificados') {
            echo json_encode($obj4->ListarAsignacionesCertificados());
        }
        exit;
    }

    require_once("views/". $pagina . ".php");
} else {
    echo "pagina en construccion";
}
?>