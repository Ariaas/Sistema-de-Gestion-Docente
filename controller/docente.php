<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = 'docente';

if (!is_file("model/" . $pagina . ".php")) {
    echo json_encode(['resultado' => 'error', 'mensaje' => "Falta definir la clase " . $pagina]);
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {
        $p = new Docente();
        $accion = $_POST['accion'] ?? '';

        if ($accion !== 'consultar' && $accion !== 'Existe' && $accion !== 'consultar_paso2' && $accion !== 'consultar_datos_adicionales') {
            require_once("model/bitacora.php");
            $usu_id = $_SESSION['usu_id'] ?? null;
            if ($usu_id === null) {
                echo json_encode(['resultado' => 'error', 'mensaje' => 'Usuario no autenticado.']);
                exit;
            }
            $bitacora = new Bitacora();
        }

        if ($accion == 'consultar') {
            echo json_encode($p->Listar());
        } elseif ($accion == 'consultar_paso2') {
            $doc_cedula = $_POST['doc_cedula'] ?? 0;
            $horas = $p->ObtenerHorasActividad($doc_cedula);
            echo json_encode([
                'resultado' => 'ok_paso2',
                'horas' => $horas['mensaje']
            ]);
        } elseif ($accion == 'consultar_datos_adicionales') {
            $doc_cedula = $_POST['doc_cedula'] ?? 0;
            $horas = $p->ObtenerHorasActividad($doc_cedula);
            echo json_encode([
                'resultado' => 'ok_datos_adicionales',
                'horas' => $horas['mensaje']
            ]);
        } elseif ($accion == 'eliminar') {
            $p->setCedula($_POST['cedulaDocente']);
            echo json_encode($p->Eliminar());
            if (isset($bitacora)) $bitacora->registrarAccion($usu_id, 'eliminar', 'docente');
        } elseif ($accion == 'activar') {
            $p->setCedula($_POST['cedulaDocente']);
            echo json_encode($p->Activar());
            if (isset($bitacora)) $bitacora->registrarAccion($usu_id, 'activar', 'docente');
        } elseif ($accion == 'Existe') {
            echo json_encode(['resultado' => 'Existe', 'existe' => $p->Existe($_POST['cedulaDocente'])]);
            exit;
        } elseif ($accion == 'existe_correo') {
            echo json_encode(['resultado' => 'existe_correo', 'existe' => $p->existeCorreo($_POST['correoDocente'], $_POST['cedulaDocente'] ?? null)]);
            exit;
        } elseif ($accion == 'incluir' || $accion == 'modificar') {
            $p->setCategoriaNombre($_POST['categoria']);
            $p->setPrefijo($_POST['prefijoCedula']);
            $p->setCedula($_POST['cedulaDocente']);
            $p->setNombre($_POST['nombreDocente']);
            $p->setApellido($_POST['apellidoDocente']);
            $p->setCorreo($_POST['correoDocente']);
            $p->setDedicacion($_POST['dedicacion']);
            
          
            $p->setCondicion(!empty($_POST['condicion']) ? $_POST['condicion'] : 'No Especificada');
            
   
            $p->setIngreso(!empty($_POST['fechaIngreso']) ? $_POST['fechaIngreso'] : date('Y-m-d'));

    
            $anioConcurso = !empty($_POST['anioConcurso']) ? $_POST['anioConcurso'] . '-01' : '';
            $p->setAnioConcurso($anioConcurso);

            $p->setTipoConcurso($_POST['tipoConcurso'] ?? '');
            $p->setObservacion($_POST['observacionesDocente']);
            $p->setTitulos($_POST['titulos'] ?? []);
            $p->setCoordinaciones($_POST['coordinaciones'] ?? []);


            $p->setHorasAcademicas((int)($_POST['actAcademicas'] ?? 0));
            $p->setCreacionIntelectual((int)($_POST['actCreacion'] ?? 0));
            $p->setIntegracionComunidad((int)($_POST['actIntegracion'] ?? 0));
            $p->setGestionAcademica((int)($_POST['actGestion'] ?? 0));
            $p->setOtras((int)($_POST['actOtras'] ?? 0));

            if ($accion == 'incluir') {
                echo json_encode($p->Registrar());
                if (isset($bitacora)) $bitacora->registrarAccion($usu_id, 'registrar', 'docente');
            } elseif ($accion == 'modificar') {
                echo json_encode($p->Modificar());
                if (isset($bitacora)) $bitacora->registrarAccion($usu_id, 'modificar', 'docente');
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