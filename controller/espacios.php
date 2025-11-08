<?php

use App\Model\Espacio;
use App\Model\Bitacora;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $e = new Espacio();
        $accion = $_POST['accion'];

        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($e->Listar());
        } elseif ($accion == 'eliminar') {
            $e->setNumero($_POST['numeroEspacio']);
            $e->setEdificio($_POST['edificioEspacio']);
            $e->setTipo($_POST['tipoEspacio']);
            $resultado = $e->eliminar();
            echo json_encode($resultado);

            if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                $bitacora->registrarAccion($usu_id, 'eliminar', 'espacios');
            }
        } elseif ($accion == 'existe') {
            $numeroEspacio = $_POST['numeroEspacio'];
            $edificioEspacio = $_POST['edificioEspacio'];
            $tipoEspacio = $_POST['tipoEspacio'];
            $numeroEspacioExcluir = isset($_POST['numeroEspacioExcluir']) ? $_POST['numeroEspacioExcluir'] : null;
            $edificioEspacioExcluir = isset($_POST['edificioEspacioExcluir']) ? $_POST['edificioEspacioExcluir'] : null;
            $tipoEspacioExcluir = isset($_POST['tipoEspacioExcluir']) ? $_POST['tipoEspacioExcluir'] : null;
            $resultado = $e->Existe($numeroEspacio, $edificioEspacio, $tipoEspacio, $numeroEspacioExcluir, $edificioEspacioExcluir, $tipoEspacioExcluir);
            echo json_encode($resultado);
        } else {
            $e->setNumero($_POST['numeroEspacio']);
            $e->setEdificio($_POST['edificioEspacio']);
            $e->setTipo($_POST['tipoEspacio']);
            if ($accion == 'registrar') {
                $resultado = $e->Registrar();
                echo json_encode($resultado);

                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'registrar', 'espacios');
                }
            } elseif ($accion == 'modificar') {
                $resultado = $e->modificar(
                    $_POST['original_numeroEspacio'],
                    $_POST['original_edificioEspacio'],
                    $_POST['original_tipoEspacio']
                );
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'modificar', 'espacios');
                }
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}