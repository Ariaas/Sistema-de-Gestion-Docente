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
                $resultado_uc = $o->obtenerUcPorDocente($_POST['doc_id'] ?? null);
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
        error_log("Error en horarioC.php: " . $e->getMessage());
        $respuesta = ['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()];
    }
    
    header('Content-Type: application/json; charset=utf-8');
    
    array_walk_recursive($respuesta, function(&$item, $key){
        if(is_string($item)){ $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8'); }
    });

    $json_respuesta = json_encode($respuesta, JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $json_respuesta = json_encode(['resultado' => 'error', 'mensaje' => 'Error al codificar JSON: ' . json_last_error_msg()]);
    }

    echo $json_respuesta;
    exit; 
}
?>