<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'docente';

if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $p = new Docente();
        $accion = $_POST['accion'] ?? '';

        if ($accion !== 'consultar' && $accion !== 'Existe') {
            require_once("model/bitacora.php");
            $usu_id = $_SESSION['usu_id'] ?? null;

            if ($usu_id === null) {
                echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado para realizar esta acción.']);
                exit;
            }
            $bitacora = new Bitacora();
        }
        
        if ($accion == 'consultar') {
            echo json_encode($p->Listar());

        } elseif ($accion == 'consultar_horas') {
            $doc_cedula = $_POST['doc_cedula'] ?? 0;
            echo json_encode($p->ObtenerHorasActividad($doc_cedula));

        } elseif ($accion == 'eliminar') {
            $p->setCedula($_POST['cedulaDocente']);
            $resultado = $p->Eliminar();
            echo json_encode($resultado);
            if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'eliminar', 'docente');

        } elseif ($accion == 'Existe') {
            $resultado = $p->Existe($_POST['cedulaDocente']);
            echo json_encode(['existe' => $resultado]);
        } else {
          
            $p->setCategoriaNombre($_POST['categoria']);
            $p->setPrefijo($_POST['prefijoCedula']);
            $p->setCedula($_POST['cedulaDocente']);
            $p->setNombre($_POST['nombreDocente']);
            $p->setApellido($_POST['apellidoDocente']);
            $p->setCorreo($_POST['correoDocente']);
            $p->setDedicacion($_POST['dedicacion']);
            $p->setCondicion($_POST['condicion']);
            $p->setTipoConcurso($_POST['tipoConcurso'] ?? '');
            $p->setIngreso($_POST['fechaIngreso']);
            $p->setAnioConcurso($_POST['anioConcurso'] ?? '');
            $p->setObservacion($_POST['observacionesDocente']);
            

            if (isset($_POST['titulos']) && is_array($_POST['titulos'])) {
                $p->setTitulos($_POST['titulos']);
            } else {
                $p->setTitulos(array());
            }

            if (isset($_POST['coordinaciones']) && is_array($_POST['coordinaciones'])) {
                $p->setCoordinaciones($_POST['coordinaciones']);
            } else {
                $p->setCoordinaciones(array());
            }

            if ($accion == 'incluir') {
                echo json_encode($p->Registrar());
                if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'registrar', 'docente');
            } elseif ($accion == 'modificar') {
                echo json_encode($p->Modificar());
                if(isset($bitacora)) $bitacora->registrarAccion($usu_id, 'modificar', 'docente');
            }
        }
        exit;
    }

    $p = new Docente();
    $categorias = $p->listacategoria();
    $titulos = $p->listatitulo();
    $coordinaciones = $p->listaCoordinacion();

    require_once("views/" . $pagina . ".php");
} else {
    echo "Página en construcción";
}
?>