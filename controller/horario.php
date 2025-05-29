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
    'modificar_grupo',                
    'eliminar_por_seccion_fase',      
    'registrar_clase_individual',     
    'modificar_clase_individual',     
    'eliminar_clase_individual',      
    'ver_horario_clase_individual',
    'obtener_uc_por_docente',
    'verificar_horario_existente' 
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
                    'docentes' => $o->obtenerDocentes()
                ];
                break;
            case 'verificar_horario_existente': 
                $sec_id = $_POST['sec_id'] ?? null;
                $hor_fase = $_POST['hor_fase'] ?? null;
                if ($sec_id && $hor_fase) {
                    $existe = $o->verificarHorarioExistente($sec_id, $hor_fase);
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
                $hor_fase = $_POST['hor_fase'] ?? null;
                if ($sec_id && $hor_fase) {
                    $respuesta = $o->ConsultarDetallesParaGrupo($sec_id, $hor_fase);
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
            case 'modificar_grupo':
                $sec_id_original = $_POST['sec_id'] ?? null;
                $hor_fase_original = $_POST['hor_fase'] ?? null;
                $nueva_seccion_id = $_POST['nueva_seccion_id'] ?? null; 
                $nueva_hor_fase = $_POST['nueva_hor_fase'] ?? null;     
                $items_horario_json = $_POST['items_horario'] ?? '[]';

                if ($sec_id_original !== null && $hor_fase_original !== null && $nueva_seccion_id !== null && $nueva_hor_fase !== null && $items_horario_json !== null) {
                    $respuesta = $o->ModificarGrupo($sec_id_original, $hor_fase_original, $nueva_seccion_id, $nueva_hor_fase, $items_horario_json);
                } else {
                    $missing_params = [];
                    if ($sec_id_original === null) $missing_params[] = "sec_id (original)";
                    if ($hor_fase_original === null) $missing_params[] = "hor_fase (original)";
                    if ($nueva_seccion_id === null) $missing_params[] = "nueva_seccion_id";
                    if ($nueva_hor_fase === null) $missing_params[] = "nueva_hor_fase";
                    if ($items_horario_json === null) $missing_params[] = "items_horario";
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para modificar el grupo: ' . implode(', ', $missing_params)];
                }
                break;
            case 'eliminar_por_seccion_fase':
                $sec_id = $_POST['sec_id'] ?? null;
                $hor_fase = $_POST['hor_fase'] ?? null;
                if ($sec_id && $hor_fase) {
                    $respuesta = $o->EliminarPorSeccionFase($sec_id, $hor_fase);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan parámetros para eliminar el grupo.'];
                }
                break;
            case 'registrar_clase_individual':
                $esp_id = $_POST['esp_id'] ?? null;
                $hor_fase_clase = $_POST['hor_fase'] ?? null; 
                $dia = $_POST['dia'] ?? null;
                $hora_inicio = $_POST['hora_inicio'] ?? null;
                $hora_fin = $_POST['hora_fin'] ?? null;
                $sec_id_clase = $_POST['sec_id'] ?? null; 
                $doc_id_clase_reg = $_POST['doc_id'] ?? null; 
                $uc_id = $_POST['uc_id'] ?? null;
                $respuesta = $o->RegistrarClaseIndividual($esp_id, $hor_fase_clase, $dia, $hora_inicio, $hora_fin, $sec_id_clase, $doc_id_clase_reg, $uc_id);
                break;
            case 'ver_horario_clase_individual':
                $hor_id = $_POST['hor_id'] ?? '';
                if (!empty($hor_id)) {
                    $respuesta = $o->VerHorarioClaseIndividual($hor_id);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'ID de horario no proporcionado.'];
                }
                break;
            case 'modificar_clase_individual':
                $hor_id_clase = $_POST['hor_id'] ?? null;
                $esp_id_clase = $_POST['esp_id'] ?? null;
                $hor_fase_clase_mod = $_POST['hor_fase'] ?? null;
                $dia_clase = $_POST['dia'] ?? null;
                $hora_inicio_clase = $_POST['hora_inicio'] ?? null;
                $hora_fin_clase = $_POST['hora_fin'] ?? null;
                $sec_id_clase_mod = $_POST['sec_id'] ?? null;
                $doc_id_clase = $_POST['doc_id'] ?? null;
                $uc_id_clase = $_POST['uc_id'] ?? null;
                if ($hor_id_clase) { 
                     $respuesta = $o->ModificarClaseIndividual($hor_id_clase, $esp_id_clase, $hor_fase_clase_mod, $dia_clase, $hora_inicio_clase, $hora_fin_clase, $sec_id_clase_mod, $doc_id_clase, $uc_id_clase);
                } else {
                    $respuesta = ['resultado' => 'error', 'mensaje' => 'Faltan datos para modificar la clase.'];
                }
                break;
            case 'eliminar_clase_individual':
                $hor_id_eliminar = $_POST['hor_id'] ?? ''; 
                if (!empty($hor_id_eliminar)) {
                    $respuesta = $o->EliminarClaseIndividual($hor_id_eliminar);
                } else {
                     $respuesta = ['resultado' => 'error', 'mensaje' => 'ID de horario no proporcionado para eliminar.'];
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