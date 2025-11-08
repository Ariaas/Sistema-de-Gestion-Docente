<?php

use App\Model\Anio;
use App\Model\Bitacora;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();
        $c = new Anio();

        $accion = $_POST['accion'];
        if ($accion == 'consultar') {
            echo json_encode($c->Listar());
        } elseif ($accion == 'verificar_condiciones_registro') {
            echo json_encode($c->Verificar());
        } elseif ($accion == 'consultar_per') {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['aniTipo']);
            echo json_encode($c->consultarPer());
        } elseif ($accion == 'eliminar') {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['tipoAnio']);
            $resultado = $c->Eliminar();
            echo json_encode($resultado);
            
            if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                $bitacora->registrarAccion($usu_id, 'eliminar', 'anio');
            }
        } elseif ($accion == 'existe') {
            $anioOriginal = isset($_POST['anioOriginal']) ? $_POST['anioOriginal'] : null;
            $tipoOriginal = isset($_POST['tipoOriginal']) ? $_POST['tipoOriginal'] : null;
            $existe = $c->Existe($_POST['aniAnio'], $_POST['tipoAnio'], $anioOriginal, $tipoOriginal);
            if ($existe) {
                echo json_encode(['resultado' => 'existe', 'mensaje' => 'El AÑO colocado YA existe!']);
            } else {
                echo json_encode(['resultado' => 'no_existe']);
            }
        } elseif ($accion == 'duplicar_secciones') {
            $anioOrigen = isset($_POST['anioOrigen']) ? (int)$_POST['anioOrigen'] : 0;
            $anioDestino = isset($_POST['anioDestino']) ? (int)$_POST['anioDestino'] : 0;
            $aniTipo = isset($_POST['aniTipo']) ? $_POST['aniTipo'] : '';
            $aniTipoDestino = isset($_POST['aniTipoDestino']) ? $_POST['aniTipoDestino'] : $aniTipo;
            $resultado = $c->duplicarSecciones($anioOrigen, $aniTipo, $anioDestino, $aniTipoDestino);
            echo json_encode($resultado);
            
            if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                $bitacora->registrarAccion($usu_id, 'duplicar_secciones', 'anio');
            }
        } elseif ($accion == 'duplicar_horarios') {
            $anioOrigen = isset($_POST['anioOrigen']) ? (int)$_POST['anioOrigen'] : 0;
            $anioDestino = isset($_POST['anioDestino']) ? (int)$_POST['anioDestino'] : 0;
            $aniTipo = isset($_POST['aniTipo']) ? $_POST['aniTipo'] : '';
            $aniTipoDestino = isset($_POST['aniTipoDestino']) ? $_POST['aniTipoDestino'] : $aniTipo;
            $faseObjetivo = isset($_POST['faseObjetivo']) && $_POST['faseObjetivo'] !== '' ? (int)$_POST['faseObjetivo'] : null;
            $resultado = $c->duplicarHorarios($anioOrigen, $aniTipo, $anioDestino, $aniTipoDestino, $faseObjetivo);
            echo json_encode($resultado);
            
            if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                $bitacora->registrarAccion($usu_id, 'duplicar_horarios', 'anio');
            }
        } else {
            $c->setAnio($_POST['aniAnio']);
            $c->setTipo($_POST['tipoAnio']);
            $fases = [
                ['numero' => 1, 'apertura' => $_POST['aniAperturaFase1'], 'cierre' => $_POST['aniCierraFase1']],
                ['numero' => 2, 'apertura' => $_POST['aniAperturaFase2'], 'cierre' => $_POST['aniCierraFase2']]
            ];
            $c->setFases($fases);

            if ($accion == 'registrar') {
                $resultado = $c->Registrar();
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'registrar', 'anio');
                }
            } elseif ($accion == 'modificar') {
                $resultado = $c->modificar($_POST['anioOriginal'], $_POST['tipoOriginal']);
                echo json_encode($resultado);
                
                if (isset($resultado['resultado']) && $resultado['resultado'] !== 'error') {
                    $bitacora->registrarAccion($usu_id, 'modificar', 'anio');
                }
            }
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
