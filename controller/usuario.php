<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $usu = new Titulo();
        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            echo json_encode($usu->Consultar());
        
        
        } else if ($accion == 'registrar') {
            $usu->set_prefijo($_POST['tituloprefijo']);// pila con estos setters
            $usu->set_nombreTitulo($_POST['titulonombre']);
            echo  json_encode($usu->Registrar());
            
        
        
        }else if ($accion == 'existe') {

            $usu->set_prefijo($_POST['tituloprefijo']);// pila con estos setters
            $usu->set_nombreTitulo($_POST['titulonombre']);
            echo json_encode($usu->Existe());

        } else if($accion == 'modificar'){
            $usu->set_tituloId($_POST['tituloid']);
            $usu->set_prefijo($_POST['tituloprefijo']);// pila con estos setters
            $usu->set_nombreTitulo($_POST['titulonombre']);

            echo  json_encode($usu->Modificar());
        }elseif ($accion == 'eliminar') {

           $usu->set_tituloId($_POST['tituloid']);
            echo  json_encode($usu->Eliminar());
            }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
