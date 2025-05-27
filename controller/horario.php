<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

// Solo incluir la vista si no hay una petición POST AJAX que requiera una respuesta JSON
if (empty($_POST) || (isset($_POST['accion']) && !in_array($_POST['accion'], ['obtener_datos_selects', 'consultar', 'ver_horario', 'eliminar', 'registrar', 'modificar']))) {
    if (is_file("views/" . $pagina . ".php")) {
        require_once("views/" . $pagina . ".php");
    } else {
        echo "Página en construcción";
    }
} else {
    // Si hay una petición POST AJAX, manejarla y salir
    $o = new Horario();
    $accion = $_POST['accion'] ?? '';
    
    try {
        if ($accion == 'obtener_datos_selects') {
            // Obtener datos para los selects
            $datos = [
                'ucs' => $o->obtenerUnidadesCurriculares(),
                'secciones' => $o->obtenerSecciones(),
                'espacios' => $o->obtenerEspacios(),
                 'docentes' => $o->obtenerDocentes()
            ];
            echo json_encode($datos);
            exit;
        }
        elseif ($accion == 'consultar') {
            echo json_encode($o->Listar());
        } elseif ($accion == 'ver_horario') {
            // No es necesario setear el ID aquí, se pasa directamente a VerHorario
            echo json_encode($o->VerHorario($_POST['hor_id'] ?? ''));
        } elseif ($accion == 'eliminar') {
            $o->setId($_POST['hor_id'] ?? '');
            echo json_encode($o->Eliminar());
        } else { // Acciones de registro y modificación
            // Asegúrate de que los IDs de las relaciones se pasen correctamente al modelo
            $o->setEspacio($_POST['esp_id'] ?? null); // Usar null para valores vacíos
            $o->setFase($_POST['hor_fase'] ?? null);
            
            $dia = $_POST['dia'] ?? null;
            $hora_inicio = $_POST['hora_inicio'] ?? null;
            $hora_fin = $_POST['hora_fin'] ?? null;
            $sec_id = $_POST['sec_id'] ?? null;
            $doc_id = $_POST['doc_id'] ?? null;
            $uc_id = $_POST['uc_id'] ?? null;
            
            if ($accion == 'registrar') {
                echo json_encode($o->Registrar($dia, $hora_inicio, $hora_fin, $sec_id, $doc_id, $uc_id));
            } elseif ($accion == 'modificar') {
                $o->setId($_POST['hor_id'] ?? null); // El ID es crucial para modificar
                echo json_encode($o->Modificar($dia, $hora_inicio, $hora_fin, $sec_id, $doc_id, $uc_id));
            }
        }
    } catch (Exception $e) {
        // Captura cualquier excepción y devuelve un mensaje de error JSON
        error_log("Error en horarioC.php: " . $e->getMessage()); // Para depuración en el servidor
        echo json_encode(['resultado' => 'error', 'mensaje' => "Error del servidor: " . $e->getMessage()]); // Línea corregida
    }
    exit; // Asegurarse de que el script termina aquí para no imprimir más contenido
}