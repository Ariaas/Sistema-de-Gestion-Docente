<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

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

if (empty($_POST) || (isset($_POST['accion']) && !in_array($_POST['accion'], $acciones_json_validas))) {
    $o = new Seccion();
    
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
                    'secciones' => $o->obtenerSecciones(),
                    'espacios' => $o->obtenerEspacios(),
                    'docentes' => $o->obtenerDocentes(),
                    'turnos' => $o->obtenerTurnos()
                ];
                break;

            case 'registrar_seccion':
                $respuesta = $o->RegistrarSeccion(
                    $_POST['codigoSeccion'] ?? null, 
                    $_POST['cantidadSeccion'] ?? null, 
                    $_POST['anioId'] ?? null
                );
                break;

            case 'consultar_agrupado':
                $respuesta = $o->ListarAgrupado();
                break;

            case 'consultar_detalles':
                $respuesta = $o->ConsultarDetalles($_POST['sec_id'] ?? null);
                break;

            case 'obtener_uc_por_docente':
                $doc_id = $_POST['doc_id'] ?? null;
                $sec_id_actual = $_POST['sec_id_actual'] ?? null; 
                $trayecto_seccion = null;

                
                if ($sec_id_actual) {
                
                    $secciones_data = $o->obtenerSecciones();
                    foreach ($secciones_data as $sec) {
                        if ($sec['sec_id'] == $sec_id_actual) {
                            $trayecto_seccion = substr($sec['sec_codigo'], 0, 1);
                            break;
                        }
                    }
                }
                
                $resultado_uc = $o->obtenerUcPorDocente($doc_id, $trayecto_seccion); // Pasar trayecto
                $respuesta = [
                    'resultado' => 'ok', 
                    'ucs_docente' => $resultado_uc['data'],
                    'mensaje_uc' => $resultado_uc['mensaje']
                ];
                break;

            case 'modificar':
                $respuesta = $o->Modificar($_POST['seccion_id'] ?? null, $_POST['items_horario'] ?? '[]');
                break;

            case 'eliminar_seccion_y_horario':
                $respuesta = $o->EliminarSeccionYHorario($_POST['sec_id'] ?? null);
                break;
            
            case 'validar_clase_en_vivo':
                $respuesta = $o->ValidarClaseEnVivo(
                    $_POST['doc_id'] ?? null,
                    $_POST['esp_id'] ?? null,
                    $_POST['dia'] ?? null,
                    $_POST['hora_inicio'] ?? null,
                    $_POST['sec_id'] ?? null
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
        error_log("Error en seccionC.php: " . $e->getMessage()); // Registro más específico
        $respuesta = ['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()];
    }
    
    header('Content-Type: application/json; charset=utf-8');
    
    // Asegurar que el encoding sea UTF-8 para JSON
    array_walk_recursive($respuesta, function(&$item, $key){
        if(is_string($item)){ 
            // Detectar si ya es UTF-8 antes de convertir, para evitar doble codificación o errores
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
?>