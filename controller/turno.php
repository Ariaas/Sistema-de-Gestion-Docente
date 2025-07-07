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
        $obj1 = new Turno();
        $accion = $_POST['accion'];

        require_once("model/bitacora.php");
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
                echo json_encode($obj1->Registrar());
                $bitacora->registrarAccion($usu_id, 'registrar', 'turno');
                break;

            case 'modificar':
                $obj1->setNombreTurno($_POST['turnoid']);
                $obj1->setHoraInicio($_POST['horaInicio']); 
                $obj1->setHoraFin($_POST['horafin']);
                echo json_encode($obj1->Modificar());
                $bitacora->registrarAccion($usu_id, 'modificar', 'turno');
                break;
            
            case 'eliminar':
                $obj1->setNombreTurno($_POST['turnoid']);
                echo json_encode($obj1->Eliminar());
                $bitacora->registrarAccion($usu_id, 'eliminar', 'turno');
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