<?php

use App\Model\Turno;
use App\Model\Bitacora;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $obj1 = new Turno();
        $accion = $_POST['accion'];

        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        switch ($accion) {
            case 'consultar':
                echo json_encode($obj1->Consultar());
                break;
            
            case 'registrar':
                $obj1->setNombreTurno($_POST['turnonombre']);
                $obj1->setHoraInicio($_POST['horaInicio']); 
                $obj1->setHoraFin($_POST['horafin']);
                $resultado = $obj1->Registrar();
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'registrar', 'turno');
                }
                break;

            case 'modificar':
                $obj1->setNombreTurno($_POST['turnonombre']);
                $obj1->setNombreTurnoOriginal($_POST['turnonombre_original']);
                $obj1->setHoraInicio($_POST['horaInicio']); 
                $obj1->setHoraFin($_POST['horafin']);
                $resultado = $obj1->Modificar();
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'modificar', 'turno');
                }
                break;
            
            case 'eliminar':
                $obj1->setNombreTurno($_POST['turnoid']);
                $resultado = $obj1->Eliminar();
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'eliminar', 'turno');
                }
                break;

            case 'chequear_solapamiento':
                $obj1->setHoraInicio($_POST['horaInicio']);
                $obj1->setHoraFin($_POST['horaFin']);
                if (!empty($_POST['turnoid'])) {
                    $obj1->setNombreTurno($_POST['turnoid']);
                }
                echo json_encode($obj1->chequearSolapamiento());
                break;
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
?>