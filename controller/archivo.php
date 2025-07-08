<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!is_file("model/" . $pagina . ".php")) { echo "Falta definir la clase " . $pagina; exit; }
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    $modelo = new Archivo();
    $acceso_denegado = $modelo->verificarAcceso();

    if (!empty($_POST)) {
        $accion = $_POST['accion'] ?? '';
        $doc_cedula = 12345678;
        
        switch ($accion) {
            case 'registrar_notas':
                $anio_compuesto = explode(':', $_POST['anio']);
                $modelo->setAnioAnio($anio_compuesto[0]);
                $modelo->setAnioTipo($anio_compuesto[1]);
                $modelo->setSecCodigo($_POST['seccion'] ?? null);
                $modelo->setUcCodigo($_POST['ucurricular'] ?? null);
                $modelo->setAproCantidad($_POST['cantidad_aprobados'] ?? 0);
                $modelo->setPerCantidad($_POST['cantidad_per'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $file = $_FILES['archivo_notas'] ?? null;
                echo json_encode($modelo->guardarRegistroInicial($file));
                break;

            case 'registrar_per':
                $modelo->setUcCodigo($_POST['uc_codigo'] ?? null);
                $modelo->setSecCodigo($_POST['sec_codigo'] ?? null);
                $modelo->setPerAprobados($_POST['cantidad_aprobados_per'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setAnioAnio($_POST['anio_anio'] ?? null);
                $modelo->setAnioTipo($_POST['anio_tipo'] ?? null);
                $file = $_FILES['archivo_per'] ?? null;
                echo json_encode($modelo->registrarAprobadosPer($file));
                break;
            
            case 'eliminar_registro':
                $modelo->setUcCodigo($_POST['uc_codigo'] ?? null);
                $modelo->setSecCodigo($_POST['sec_codigo'] ?? null);
                echo json_encode($modelo->eliminarRegistroCompleto());
                break;

            case 'listar_registros':
                echo json_encode(['resultado' => 'ok_registros', 'datos' => $modelo->listarRegistros()]);
                break;

            case 'obtener_secciones':
                $anio_compuesto = isset($_POST['anio_compuesto']) ? explode(':', $_POST['anio_compuesto']) : [null, null];
                echo json_encode($modelo->obtenerSeccionesPorAnio($anio_compuesto[0], $anio_compuesto[1], $doc_cedula));
                break;
        }
        exit;
    }

    $obj = new Archivo();
    $anios = $obj->obtenerAnios();
    $fase_actual = $obj->obtenerFaseActual();
    $fase_remedial = $obj->determinarFaseParaRemedial($fase_actual);
    $doc_cedula = 12345678;
    $unidadesCurriculares = [];
    $secciones = [];
    $alerta_datos = "";

    if (!$acceso_denegado) {
        if ($doc_cedula && $fase_remedial) {
            $unidadesCurriculares = $obj->obtenerUnidadesParaRemedial($doc_cedula, $fase_remedial['fase']);
            $secciones = $obj->obtenerSeccionesPorAnio($fase_remedial['anio'], $fase_remedial['tipo'], $doc_cedula);
        }
        if (empty($unidadesCurriculares)) {
            $alerta_datos .= "<div class='alert alert-warning' style='max-width: 1200px;'><strong>Atención:</strong> No tiene Unidades Curriculares disponibles para registrar notas remediales en este período.</div>";
        }
        if (empty($secciones)) {
             $alerta_datos .= "<div class='alert alert-warning' style='max-width: 1200px;'><strong>Atención:</strong> No tiene secciones asignadas en su horario para el período académico correspondiente.</div>";
        }
    }
    
    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
?>