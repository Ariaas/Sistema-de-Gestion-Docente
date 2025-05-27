<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $obj1 = new Titulo();
        $accion = $_POST['accion'];

        $usu_id = 1;
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($obj1->Consultar());
        
        
        } else if ($accion == 'registrar') {
            $obj1->set_prefijo($_POST['tituloprefijo']);
            $obj1->set_nombreTitulo($_POST['titulonombre']);
            echo  json_encode($obj1->Registrar());

            $bitacora->registrarAccion($usu_id, 'registrar', 'titulo');
        }else if ($accion == 'existe') {

            $obj1->set_prefijo($_POST['tituloprefijo']);
            $obj1->set_nombreTitulo($_POST['titulonombre']);
            echo json_encode($obj1->Existe());

        } else if($accion == 'modificar'){
            $obj1->set_tituloId($_POST['tituloid']);
            $obj1->set_prefijo($_POST['tituloprefijo']);
            $obj1->set_nombreTitulo($_POST['titulonombre']);

            echo  json_encode($obj1->Modificar());

            $bitacora->registrarAccion($usu_id, 'modificar', 'titulo');
        }elseif ($accion == 'eliminar') {

           $obj1->set_tituloId($_POST['tituloid']);
            echo  json_encode($obj1->Eliminar());

            $bitacora->registrarAccion($usu_id, 'eliminar', 'titulo');
        }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
