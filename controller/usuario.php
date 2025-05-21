<?php
if (!is_file("model/" . $pagina . ".php")) {
    echo "Falta definir la clase " . $pagina;
    exit;
}
require_once("model/" . $pagina . ".php");
if (is_file("views/" . $pagina . ".php")) {

    if (!empty($_POST)) {

        $usu = new Usuario();
        $accion = $_POST['accion'];

        if ($accion == 'consultar') {
            echo json_encode($usu->Consultar());
        
        
        } else if ($accion == 'registrar') {
            $usu->set_nombreUsuario($_POST['usuarionombre']);// pila con estos setters
            $usu->set_contraseñaUsuario($_POST['contraseña']);
            $usu->set_correoUsuario($_POST['correo']);// pila con estos setters
            $usu->set_rolUsuario($_POST['rol']);
            echo  json_encode($usu->Registrar());
            
        
        
        }else if ($accion == 'existeusuario') {

            $usu->set_nombreUsuario($_POST['usuarionombre']);// pila con estos setters
           
            echo json_encode($usu->Existeusuario());

        } else if ($accion == 'existecorreo') {

           $usu->set_correoUsuario($_POST['correo']);
            echo json_encode($usu->Existecorreo());

        } else if($accion == 'modificar'){
            $usu->set_usuarioId($_POST['usuarioid']);
            $usu->set_nombreUsuario($_POST['usuarionombre']);// pila con estos setters
            $usu->set_contraseñaUsuario($_POST['contraseña']);
            $usu->set_correoUsuario($_POST['correo']);// pila con estos setters
            $usu->set_rolUsuario($_POST['rol']);
            // pila con estos setters
            

            echo  json_encode($usu->Modificar());
        }elseif ($accion == 'eliminar') {

            $usu->set_usuarioId($_POST['usuarioid']);
            echo  json_encode($usu->Eliminar());
            }
        exit;
    }

    require_once("views/" . $pagina . ".php");
} else {
    echo "pagina en construccion";
}
