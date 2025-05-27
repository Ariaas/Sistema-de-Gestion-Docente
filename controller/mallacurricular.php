<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
   
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $obj4 = new Malla();
        $accion = $_POST['accion'];

        $usu_id = 1;
        $bitacora = new Bitacora();

        if ($accion == 'consultar') {
            echo json_encode($obj4->Consultar());
        
    
        } else if ($accion == 'registrar') {
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalAnio($_POST['mal_Anio']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);
   
            echo  json_encode($obj4->Registrar());

            $bitacora->registrarAccion($usu_id, 'registrar', 'malla curricular');
        }else if ($accion == 'existe') {

             $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            echo json_encode($obj4->Existe());

        } else if($accion == 'modificar'){
            $obj4->setMalId($_POST['mal_id']);
            $obj4->setMalCodigo($_POST['mal_codigo']);
            $obj4->setMalNombre($_POST['mal_nombre']);
            $obj4->setMalAnio($_POST['mal_Anio']);
            $obj4->setMalCohorte($_POST['mal_cohorte']);
            $obj4->setMalDescripcion($_POST['mal_descripcion']);// pila con estos setters

            echo  json_encode($obj4->Modificar());

            $bitacora->registrarAccion($usu_id, 'modificar', 'malla curricular');
        }elseif ($accion == 'eliminar') {

            $obj4->setMalId($_POST['mal_id']);
            echo  json_encode($obj4->Eliminar());
            $bitacora->registrarAccion($usu_id, 'eliminar', 'malla curricular');
        }
        exit;
    }


    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}