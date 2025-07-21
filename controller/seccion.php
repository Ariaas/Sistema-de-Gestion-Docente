<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}
require_once("model/" . $pagina . ".php");

$acciones_json_validas = [
    'obtener_datos_selects',
    'consultar_agrupado',
    'consultar_detalles',
    'modificar',
    'obtener_uc_por_docente',
    'registrar_seccion',
    'eliminar_seccion_y_horario',
    'validar_clase_en_vivo',
    'unir_horarios'
];
// Controlador seccion

if (empty($_POST) || (isset($_POST['accion']) && !in_array($_POST['accion'], $acciones_json_validas))) {
    $o = new Seccion();


    $countDocentes = $o->contarDocentes();
    $countEspacios = $o->contarEspacios();
    $countTurnos = $o->contarTurnos();
    $countAnios = $o->contarAniosActivos();
    $countMallas = $o->contarMallasActivas();


    $reporte_promocion = $o->EjecutarPromocionAutomatica();
    if ($reporte_promocion !== null) {
        $_SESSION['reporte_promocion'] = $reporte_promocion;
    }

    $anios = $o->obtenerAnios();
    if (is_file("views/" . $pagina . ".php")) {
        require_once("views/" . $pagina . ".php");
    } else {
        echo "Página en construcción: " . "views/" . $pagina . ".php";
    }
} else {
    $accion = $_POST['accion'] ?? '';
    $respuesta = ['resultado' => 'error', 'mensaje' => 'Acción no reconocida o faltante.'];

    try {
        $o = new Seccion();

        switch ($accion) {

            case 'obtener_datos_selects':
                $respuesta = [
                    'resultado' => 'ok',
                    'ucs' => $o->obtenerUnidadesCurriculares(),
                    'espacios' => $o->obtenerEspacios(),
                    'docentes' => $o->obtenerDocentes(),
                    'turnos' => $o->obtenerTurnos(),
                    'cohortes' => $o->obtenerCohortesMalla()
                ];
                break;
            case 'registrar_seccion':
                $anioCompuesto = $_POST['anioId'] ?? null;
                list($anio_anio, $anio_tipo) = explode('|', $anioCompuesto . '|');
                $codigoSeccion = $_POST['codigoSeccion'] ?? null;
                $cantidadSeccion = $_POST['cantidadSeccion'] ?? null;
                $trayecto = is_string($codigoSeccion) ? substr($codigoSeccion, 0, 1) : null;
                $respuesta = $o->RegistrarSeccion(
                    $codigoSeccion,
                    $cantidadSeccion,
                    $anio_anio,
                    $anio_tipo
                );
                // Si la sección se registró correctamente, generar el horario aleatorio
                if ($respuesta['resultado'] === 'registrar_seccion_ok' && $codigoSeccion && $trayecto) {
                    $resultado_horario = $o->CrearHorarioAleatorio($codigoSeccion, $trayecto);
                    $respuesta['horario_aleatorio'] = $resultado_horario;
                }
                break;

            case 'consultar_agrupado':
                $respuesta = $o->ListarAgrupado();
                break;

            case 'consultar_detalles':
                $respuesta = $o->ConsultarDetalles($_POST['sec_codigo'] ?? null);
                break;

            case 'obtener_uc_por_docente':
                $doc_cedula = $_POST['doc_cedula'] ?? null;
                $sec_codigo_actual = $_POST['sec_codigo_actual'] ?? null;
                $trayecto_seccion = null;

                if ($sec_codigo_actual) {
                    $trayecto_seccion = substr($sec_codigo_actual, 0, 1);
                }

                $resultado_uc = $o->obtenerUcPorDocente($doc_cedula, $trayecto_seccion);
                $respuesta = [
                    'resultado' => 'ok',
                    'ucs_docente' => $resultado_uc['data'],
                    'mensaje_uc' => $resultado_uc['mensaje']
                ];
                break;

            case 'modificar':
                $respuesta = $o->Modificar($_POST['sec_codigo'] ?? null, $_POST['items_horario'] ?? '[]');
                break;

            case 'eliminar_seccion_y_horario':
                $respuesta = $o->EliminarSeccionYHorario($_POST['sec_codigo'] ?? null);
                break;

            case 'validar_clase_en_vivo':
                $respuesta = $o->ValidarClaseEnVivo(
    $_POST['doc_cedula'] ?? null,
    // Recibe los 3 campos desde el JavaScript
    $_POST['esp_numero'] ?? null,
    $_POST['esp_tipo'] ?? null,
    $_POST['esp_edificio'] ?? null,
    $_POST['dia'] ?? null,
    $_POST['hora_inicio'] ?? null,
    $_POST['sec_codigo'] ?? null,
    $_POST['uc_codigo'] ?? null
);
                break;

            case 'unir_horarios':
                $respuesta = $o->UnirHorarios(
                    $_POST['id_seccion_origen'] ?? null,
                    $_POST['secciones_a_unir'] ?? []
                );
                break;
        }
    } catch (Exception $e) {
        error_log("Error en seccionC.php: " . $e->getMessage());
        $respuesta = ['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()];
    }

    header('Content-Type: application/json; charset=utf-8');

    array_walk_recursive($respuesta, function (&$item, $key) {
        if (is_string($item)) {
            if (!mb_check_encoding($item, 'UTF-8')) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item, 'UTF-8, ISO-8859-1', true));
            }
        }
    });

    $json_respuesta = json_encode($respuesta, JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al codificar JSON en seccionC.php: " . json_last_error_msg() . " - Data: " . print_r($respuesta, true));
        $json_respuesta = json_encode(['resultado' => 'error', 'mensaje' => 'Error al codificar JSON: ' . json_last_error_msg()]);
    }

    echo $json_respuesta;
    exit;
}
