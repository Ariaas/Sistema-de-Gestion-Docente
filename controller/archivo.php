<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    $modelo = new Archivo();

    if (!empty($_POST)) {
        require_once("model/bitacora.php");
        $usu_id = isset($_SESSION['usu_id']) ? $_SESSION['usu_id'] : null;

        if ($usu_id === null) {
            echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
            exit;
        }
        $bitacora = new Bitacora();

        $accion = $_POST['accion'] ?? '';
        $usu_cedula = $_SESSION['usu_cedula'] ?? null;
        $rol_nombre = $_SESSION['rol_nombre'] ?? null;

        switch ($accion) {
            case 'registrar_notas':
                $anio_compuesto = explode(':', $_POST['anio']);
                $fase_actual = $modelo->obtenerFaseActual();

                $modelo->setAnioAnio($anio_compuesto[0]);
                $modelo->setAnioTipo($anio_compuesto[1]);
                $modelo->setSecCodigo($_POST['seccion'] ?? null);
                $modelo->setUcCodigo($_POST['ucurricular'] ?? null);
                $modelo->setAproCantidad($_POST['cantidad_aprobados'] ?? 0);
                $modelo->setPerCantidad($_POST['cantidad_per'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setDocCedula($usu_cedula);
                if ($fase_actual) {
                    $modelo->setFaseNumero($fase_actual['fase_numero']);
                }
                $file = $_FILES['archivo_notas'] ?? null;
                echo json_encode($modelo->guardarRegistroInicial($file));
                $bitacora->registrarAccion($usu_id, 'Registrar notas', 'Archivo');
                break;

            case 'registrar_per':
                $modelo->setUcCodigo($_POST['uc_codigo'] ?? null);
                $modelo->setSecCodigo($_POST['sec_codigo'] ?? null);
                $modelo->setPerAprobados($_POST['cantidad_aprobados_per'] ?? 0);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setAnioAnio($_POST['anio_anio'] ?? null);
                $modelo->setAnioTipo($_POST['ani_tipo'] ?? null);
                $modelo->setFaseNumero($_POST['fase_numero'] ?? null);
                $file = $_FILES['archivo_per'] ?? null;
                echo json_encode($modelo->registrarAprobadosPer($file));
                break;

            case 'eliminar_registro':
                $modelo->setUcCodigo($_POST['uc_codigo'] ?? null);
                $modelo->setSecCodigo($_POST['sec_codigo'] ?? null);
                $modelo->setAnioAnio($_POST['ani_anio'] ?? null);
                $modelo->setAnioTipo($_POST['ani_tipo'] ?? null);
                $modelo->setFaseNumero($_POST['fase_numero'] ?? null);
                echo json_encode($modelo->eliminarRegistroCompleto());
                $bitacora->registrarAccion($usu_id, 'eliminar', 'Archivo');
                break;

            case 'listar_registros':
                $filtrar_propios = isset($_POST['filtrar_propios']) && $_POST['filtrar_propios'] === 'true';
                echo json_encode(['resultado' => 'ok_registros', 'datos' => $modelo->listarRegistros($usu_cedula, $rol_nombre, $filtrar_propios)]);
                break;

            case 'obtener_secciones':
                $anio_compuesto = isset($_POST['anio_compuesto']) ? explode(':', $_POST['anio_compuesto']) : [null, null];
                echo json_encode($modelo->obtenerSeccionesPorAnio($anio_compuesto[0], $anio_compuesto[1], $usu_cedula));
                break;

            case 'obtener_uc_por_seccion':
                $sec_codigo = $_POST['sec_codigo'] ?? null;
                echo json_encode($modelo->obtenerUnidadesPorSeccion($usu_cedula, $sec_codigo));
                break;
        }
        exit;
    }

    $obj = new Archivo();
    $anios = $obj->obtenerAnios();
    $fase_actual = $obj->obtenerFaseActual();

    $rol_permitido = true;


    $doc_cedula = $_SESSION['usu_cedula'] ?? null;
    $fase_remedial = $obj->determinarFaseParaRemedial($fase_actual);

    $unidadesCurriculares = [];
    $secciones = [];
    $alerta_datos = "";

    if ($doc_cedula && $fase_remedial) {
        $unidadesCurriculares = $obj->obtenerUnidadesParaRemedial($doc_cedula, $fase_remedial['fase_uc']);
        $secciones = $obj->obtenerSeccionesPorAnio($fase_remedial['anio'], $fase_remedial['tipo'], $doc_cedula);
    }

    if (!$rol_permitido) {
        $alerta_datos .= "<div class='alert alert-danger' style='max-width: 1200px;'><strong>Acceso Denegado:</strong> No tiene los permisos necesarios para gestionar notas.</div>";
    } elseif (empty($fase_actual)) {
        $alerta_datos .= "<div class='alert alert-warning' style='max-width: 1200px;'><strong>Atención:</strong> Todavía no ha iniciado la fase de registro.</div>";
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
