<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");

   
if (is_file("views/" . $pagina . ".php")) {

        
    if (!empty($_POST)) {
        $obj2 = new Certificado();
        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            echo json_encode($obj2->Consultar());
        
        
        } else if ($accion == 'registrar') {
            $obj2->set_nombreCertificado($_POST['certificadonombre']);// pila con estos setters
            $obj2->set_trayecto($_POST['trayecto']);
            echo  json_encode($obj2->Registrar());
            
        
        
        }else if ($accion == 'existe') {

            $obj2->set_nombreCertificado($_POST['certificadonombre']);// pila con estos setters
            $obj2->set_trayecto($_POST['trayecto']);
            echo json_encode($obj2->Existe());

        } else if($accion == 'modificar'){
            $obj2->set_certificadoId($_POST['certificadoid']);
            $obj2->set_nombreCertificado($_POST['certificadonombre']);// pila con estos setters
            $obj2->set_trayecto($_POST['trayecto']);

            echo  json_encode($obj2->Modificar());
        }elseif ($accion == 'eliminar') {

           $obj2->set_certificadoId($_POST['certificadoid']);
            echo  json_encode($obj2->Eliminar());
            }
        exit;
    }
    $cer = new Certificado();
    $trayectos = $cer->obtenerTrayectos();
    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
