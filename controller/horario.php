<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");


$acciones_json_validas = [
    'obtener_datos_selects', 
    'consultar_agrupado',            
    'consultar_detalles_para_grupo',  
    'registrar_grupo', 
    'modificar_grupo',                
    'eliminar_por_seccion_fase',      
    'obtener_uc_por_docente',
    'verificar_horario_existente',
    'verificar_conflicto_espacio',
    'verificar_conflicto_docente'
];

if (empty($_POST) || (isset($_POST['accion']) && !in_array($_POST['accion'], $acciones_json_validas))) {
    if (is_file("views/" . $pagina . ".php")) {
        require_once("views/" . $pagina . ".php");
    } else {
        echo "Página en construcción: " . "views/" . $pagina . ".php";
    }
} else {
    $o = new Horario(); 
    $accion = $_POST['accion'] ?? '';
    $respuesta = ['resultado' => 'error', 'mensaje' => 'Acción no reconocida o faltante.']; 

    try {
        switch ($accion) {
            case 'obtener_datos_selects':
                $respuesta = [
                    'resultado' => 'ok', 
                    'ucs' => $o->obtenerUnidadesCurriculares(),
                    'secciones' => $o->obtenerSecciones(),
                    'espacios' => $o->obtenerEspacios(),
                    'docentes' => $o->obtenerDocentes(),
                    'turnos' => $o->obtenerTurnos(),
                    'fases' => $o->obtenerFases()
                ];
                break;

            case 'verificar_conflicto_espacio':
               
                $esp_id = $_POST['esp_id'] ?? null;
                $dia = $_POST['dia'] ?? null;
                $hora_inicio = $_POST['hora_inicio'] ?? null;
                $sec_id_actual = $_POST['sec_id_actual'] ?? null;
                $fase_id_actual = $_POST['fase_id_actual'] ?? null;
                $sec_id_original = $_POST['sec_id_original'] ?? null;
                $fase_id_original = $_POST['fase_id_original'] ?? null;
                if($esp_id && $dia && $hora_inicio && $sec_id_actual && $fase_id_actual) {
                    $respuesta = $o->verificarConflictoEspacioIndividual($esp_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original);
                } else {
                    $respuesta['mensaje'] = 'Faltan datos para la verificación de conflicto de espacio.';
                }
                break;

            case 'verificar_conflicto_docente':
                 
                $doc_id = $_POST['doc_id'] ?? null;
                $dia = $_POST['dia'] ?? null;
                $hora_inicio = $_POST['hora_inicio'] ?? null;
                $sec_id_actual = $_POST['sec_id_actual'] ?? null;
                $fase_id_actual = $_POST['fase_id_actual'] ?? null;
                $sec_id_original = $_POST['sec_id_original'] ?? null;
                $fase_id_original = $_POST['fase_id_original'] ?? null;
                if($doc_id && $dia && $hora_inicio && $sec_id_actual && $fase_id_actual) {
                    $respuesta = $o->verificarConflictoDocenteIndividual($doc_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original);
                } else {
                    $respuesta['mensaje'] = 'Faltan datos para la verificación de conflicto de docente.';
                }
                break;

            case 'verificar_horario_existente': 
             
                $sec_id = $_POST['sec_id'] ?? null;
                $fase_id = $_POST['fase_id'] ?? null;
                if ($sec_id && $fase_id) {
                    $existe = $o->verificarHorarioExistente($sec_id, $fase_id);
                    $respuesta = ['resultado' => 'ok', 'existe' => $existe];
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros (sección, fase) para la verificación.'];
                }
                break;

            case 'consultar_agrupado':
                $respuesta = $o->ListarAgrupado();
                break;

            case 'consultar_detalles_para_grupo':
                $sec_id = $_POST['sec_id'] ?? null;
                $fase_id = $_POST['fase_id'] ?? null;
                if ($sec_id && $fase_id) {
                    $respuesta = $o->ConsultarDetallesParaGrupo($sec_id, $fase_id);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para consultar detalles del grupo.'];
                }
                break;

            case 'obtener_uc_por_docente':
                $doc_id = $_POST['doc_id'] ?? null;
                $tra_id = $_POST['tra_id'] ?? null; 
                if ($doc_id) {
                    $ucs_docente = $o->obtenerUcPorDocenteYTrayecto($doc_id, $tra_id); 
                    $respuesta = ['resultado' => 'ok', 'ucs_docente' => $ucs_docente];
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'ID de docente no proporcionado.'];
                }
                break;
            
            case 'registrar_grupo':
                $nueva_seccion_id = $_POST['nueva_seccion_id'] ?? null; 
                $nueva_fase_id = $_POST['nueva_fase_id'] ?? null;
                $items_horario_json = $_POST['items_horario'] ?? '[]';

                if ($nueva_seccion_id !== null && $nueva_fase_id !== null) {
                    $respuesta = $o->RegistrarGrupo($nueva_seccion_id, $nueva_fase_id, $items_horario_json);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para registrar el grupo.'];
                }
                break;

            case 'modificar_grupo':
                $sec_id_original = $_POST['sec_id_original'] ?? null;
                $fase_id_original = $_POST['fase_id_original'] ?? null;
                $nueva_seccion_id = $_POST['nueva_seccion_id'] ?? null; 
                $nueva_fase_id = $_POST['nueva_fase_id'] ?? null;
                $items_horario_json = $_POST['items_horario'] ?? '[]';

                if ($sec_id_original !== null && $fase_id_original !== null && $nueva_seccion_id !== null && $nueva_fase_id !== null) {
                    $respuesta = $o->ModificarGrupo($sec_id_original, $fase_id_original, $nueva_seccion_id, $nueva_fase_id, $items_horario_json);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para modificar el grupo.'];
                }
                break;

            case 'eliminar_por_seccion_fase':
                $sec_id = $_POST['sec_id'] ?? null;
                $fase_id = $_POST['fase_id'] ?? null;
                if ($sec_id && $fase_id) {
                    $respuesta = $o->EliminarPorSeccionFase($sec_id, $fase_id);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para eliminar el grupo.'];
                }
                break;
        }
    } catch (Exception $e) {
        error_log("Error en horarioC.php: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
        $respuesta = ['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()];
    }
    
    echo json_encode($respuesta);
    exit; 
}
?>