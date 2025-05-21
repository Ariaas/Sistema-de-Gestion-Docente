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

        if ($accion == 'consultar') {
            echo json_encode($p->Listar());
        } elseif ($accion == 'eliminar') {
            $p->setCedula($_POST['cedulaDocente']);
            $resultado = $p->Eliminar();
            echo json_encode($resultado);
        } elseif ($accion == 'existe') {
            $resultado = $p->Existe($_POST['cedulaDocente']);
            echo json_encode($resultado);
        } else {
            // Asignación de todos los campos recibidos del formulario
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
            } elseif ($accion == 'modificar') {
                echo json_encode($p->Modificar());
            }
        }  
        exit;
    }

 require_once("model/categoria.php");
    $c = new Docente();
    $categorias = $c->listacategoria();

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}