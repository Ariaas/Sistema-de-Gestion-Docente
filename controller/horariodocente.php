<?php
require_once("model/" . $pagina . ".php"); 

if (is_file("views/" . $pagina . ".php")) {

    $accion = $_POST['action'] ?? $_GET['action'] ?? $_POST['accion'] ?? $_GET['accion'] ?? '';

    if (!empty($accion)) { 
        $e = new Horariodocente(); 

        header('Content-Type: application/json'); 

        switch ($accion) {
            case 'load_aux_data':
                echo json_encode($e->obtenerDocentes());
                break;
            
            case 'obtener_lapsos_docente':
                $doc_id = $_POST['doc_id'] ?? null;
                if ($doc_id) {
                    echo json_encode($e->obtenerLapsosParaDocente($doc_id));
                } else {
                    echo json_encode(['success' => false, 'lapsos' => [], 'message' => 'ID de docente no proporcionado.']);
                }
                break;
            
            case 'load_schedule_display_data':
                $respuesta = [
                    'success' => true,
                    'ucs' => $e->obtenerUnidadesCurriculares(),
                    'espacios' => $e->obtenerEspacios(),
                    'secciones' => $e->obtenerSecciones()
                ];
                echo json_encode($respuesta);
                break;

            case 'consultar_horario_docente_especifico':
                $doc_id = $_POST['doc_id'] ?? null;
                $fase_filtro = $_POST['fase_filtro'] ?? null;
                $anio_filtro = $_POST['anio_filtro'] ?? null;

                if ($doc_id && $fase_filtro && $anio_filtro) {
                    echo json_encode($e->obtenerHorarioCompletoPorDocente($doc_id, $fase_filtro, $anio_filtro)); 
                } else {
                    echo json_encode(['resultado' => 'error', 'mensaje' => 'Faltan parámetros (docente, fase o año) para ver el horario.']);
                }
                break;

            case 'consultar':
                echo json_encode($e->Consultar()); 
                break;

            case 'eliminar':
                $e->setHdoId($_POST['hdoId'] ?? null); 
                echo json_encode($e->Eliminar()); 
                break;

            case 'registrar':
            case 'modificar':
                $doc_id_seleccionado = $_POST['docente'] ?? null;
                $lapso_compuesto = $_POST['lapso'] ?? null;
                
                $e->setHdoTipoactividad($_POST['actividad'] ?? null); 
                $e->setHdoDescripcion($_POST['descripcion'] ?? null); 
                $e->setHdoDependencia($_POST['dependencia'] ?? null); 
                $e->setHdoObservacion($_POST['observacion'] ?? ''); 
                $e->setHdoHora($_POST['horas'] ?? null); 

                if ($accion == 'registrar') { 
                    echo json_encode($e->Registrar($doc_id_seleccionado, $lapso_compuesto));
                } elseif ($accion == 'modificar') { 
                    $e->setHdoId($_POST['hdoId'] ?? null); 
                    echo json_encode($e->Modificar($doc_id_seleccionado, $lapso_compuesto));
                }
                break;
            
            default:
                echo json_encode(['resultado' => 'error', 'mensaje' => 'Acción no reconocida.']);
                break;
        }
        exit; 
    }
    require_once("views/" . $pagina . ".php"); 
} else {
    echo "Error: La página solicitada no se encuentra en construcción o no existe."; 
}
?>