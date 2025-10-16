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

        switch ($accion) {
            case 'registrar_notas':
                $anio_compuesto = explode(':', $_POST['anio']);

                $modelo->setAnioAnio($anio_compuesto[0]);
                $modelo->setAnioTipo($anio_compuesto[1]);
                $modelo->setSecCodigo($_POST['seccion'] ?? null);
                $modelo->setUcCodigo($_POST['ucurricular'] ?? null);
                $modelo->setUcNombre($_POST['uc_nombre'] ?? '');
                $modelo->setDocCedula($_POST['docente'] ?? null);

                $file = $_FILES['archivo_notas'] ?? null;
                echo json_encode($modelo->guardarRegistroInicial($file));
                $bitacora->registrarAccion($usu_id, 'Registrar acta', 'Archivo');
                break;

            case 'eliminar_registro':
                $modelo->setUcCodigo($_POST['uc_codigo'] ?? null);
                $modelo->setSecCodigo($_POST['sec_codigo'] ?? null);
                $modelo->setAnioAnio($_POST['ani_anio'] ?? null);
                $modelo->setAnioTipo($_POST['ani_tipo'] ?? null);
                echo json_encode($modelo->eliminarRegistroCompleto());
                $bitacora->registrarAccion($usu_id, 'eliminar acta', 'Archivo');
                break;

           
            case 'verificar_existencia':
                $anio_compuesto = isset($_POST['anio_compuesto']) ? explode(':', $_POST['anio_compuesto']) : [null, null];
                $uc_codigo = $_POST['uc_codigo'] ?? null;
                $sec_codigo_str = $_POST['sec_codigo'] ?? '';

                
                $primera_seccion = explode(',', $sec_codigo_str)[0];

                $existe = $modelo->verificarExistencia($anio_compuesto[0], $anio_compuesto[1], $uc_codigo, $primera_seccion);
                echo json_encode(['existe' => $existe]);
                break;

            case 'listar_registros':
                echo json_encode(['resultado' => 'ok_registros', 'datos' => $modelo->listarRegistros()]);
                break;

            case 'obtener_secciones':
                $anio_compuesto = isset($_POST['anio_compuesto']) ? explode(':', $_POST['anio_compuesto']) : [null, null];
                $doc_cedula = $_POST['doc_cedula'] ?? null;
                echo json_encode($modelo->obtenerSeccionesAgrupadasPorAnio($anio_compuesto[0], $anio_compuesto[1], $doc_cedula));
                break;

            case 'obtener_uc_por_seccion':
                $sec_codigo = $_POST['sec_codigo'] ?? null;
                $doc_cedula = $_POST['doc_cedula'] ?? null;
                echo json_encode($modelo->obtenerUnidadesPorSeccion($doc_cedula, $sec_codigo));
                break;
        }
        exit;
    }

    $obj = new Archivo();
    $anios = $obj->obtenerAnios();
    $docentes = $obj->obtenerDocentes();
    $alerta_datos = "";

    $anio_seleccionado = '';
    $current_year = date('Y');
    foreach ($anios as $a) {
        if ($a['ani_anio'] == $current_year) {
            $anio_seleccionado = $a['ani_anio'] . ':' . $a['ani_tipo'];
            break;
        }
    }

    if ($anio_seleccionado == '' && !empty($anios)) {
        $anio_seleccionado = $anios[0]['ani_anio'] . ':' . $anios[0]['ani_tipo'];
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
