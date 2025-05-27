<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}

require_once("model/" . $pagina . ".php");

if (is_file("views/" . $pagina . ".php")) {
    if (!empty($_POST)) {
        $p = new Docente();
        $accion = $_POST['accion'];

        $usu_id = 1;
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($p->Listar());
        } elseif ($accion == 'eliminar') {
            $p->setCedula($_POST['cedulaDocente']);
            $resultado = $p->Eliminar();
            echo json_encode($resultado);

            $bitacora->registrarAccion($usu_id, 'eliminar', 'docente');

        } elseif ($accion == 'Existe') {
            $resultado = $p->Existe($_POST['cedulaDocente']);
            echo json_encode($resultado);
        } else {
            $p->setCategoriaId($_POST['categoria']);
            $p->setPrefijo($_POST['prefijoCedula']);
            $p->setCedula($_POST['cedulaDocente']);
            $p->setNombre($_POST['nombreDocente']);
            $p->setApellido($_POST['apellidoDocente']);
            $p->setCorreo($_POST['correoDocente']);
            $p->setDedicacion($_POST['dedicacion']);
            $p->setCondicion($_POST['condicion']);
            
            if ($accion == 'incluir') {
                echo json_encode($p->Registrar());

                $bitacora->registrarAccion($usu_id, 'registrar', 'docente');
            } elseif ($accion == 'modificar') {
                echo json_encode($p->Modificar());
                $bitacora->registrarAccion($usu_id, 'modificar', 'docente');
            }
        }  
        exit;
    }


    $p = new Docente();
    $categorias = $p->listacategoria();
     $titulos = $p->listatitulo();

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}