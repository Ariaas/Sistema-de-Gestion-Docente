
<?php
require_once('model/dbconnection.php');

Class Usuario extends Connection{
    private $usuarioId;
    private $nombreUsuario;
    private $contraseñaUsuario;
    private $correoUsuario;
    private $rolUsuario;

    public function __construct( $usuarioId = null, $nombreUsuario = null, $contraseñaUsuario = null, $correoUsuario = null,$rolUsuario = null,){

        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->contraseñaUsuario = $contraseñaUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->rolUsuario = $rolUsuario;
        
    }

    public function get_usuarioId()
    {
        return $this->usuarioId;
    }

    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function get_contraseñaUsuario()
    {
        return $this->contraseñaUsuario;
    }

    public function get_correoUsuario()
    {
        return $this->correoUsuario;
    }

    public function get_rolUsuario()
    {
        return $this->rolUsuario;
    }

    // Setters
    public function set_usuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function set_contraseñaUsuario($contraseñaUsuario)
    {
        $this->contraseñaUsuario = $contraseñaUsuario;
    }

    public function set_correoUsuario($correoUsuario)
    {
        $this->correoUsuario = $correoUsuario;
    }

    public function set_rolUsuario($rolUsuario)
    {
        $this->rolUsuario = $rolUsuario;
    }


    public function Registrar(){
        $r = array();

         if (!$this->Existeusuario()) {
               if (!$this->Existecorreo()) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $contraseña_encrip = password_hash($this->contraseñaUsuario,PASSWORD_BCRYPT);

                $stmt = $co->prepare("INSERT INTO tbl_usuario( usu_nombre, usu_contrasena, usu_correo, usu_estado, usu_rol) 
                VALUES (:nombreusuario, :contrasenausuario, :correousuario, 1, :rolusuario)");

                $stmt->bindParam(':nombreusuario', $this->nombreUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':contrasenausuario',$contraseña_encrip, PDO::PARAM_STR);
                $stmt->bindParam(':correousuario', $this->correoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':rolusuario', $this->rolUsuario, PDO::PARAM_STR);
            
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el usuario correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
             } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El correo colocado ya existe!';
        
            }
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El usuario colocado ya existe!';
        
            }
             return $r;
 
  
        }


    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_usuario where usu_estado = 1");


            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }


    public function Modificar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
      
        if (!$this->Existeusuario()) {
               if (!$this->Existecorreo()) {
                try {

                    $contraseña_encrip = password_hash($this->contraseñaUsuario,PASSWORD_BCRYPT);
                    $stmt = $co->prepare("UPDATE tbl_usuario
                    SET  usu_nombre = :nombreusuario, usu_contrasena = :contrasenausuario, usu_correo = :correousuario, usu_rol= :rolusuario
                    WHERE usu_id = :usuarioid");
                    $stmt->bindParam(':usuarioid', $this->usuarioId, PDO::PARAM_INT);
                    $stmt->bindParam(':nombreusuario', $this->nombreUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':contrasenausuario',$contraseña_encrip, PDO::PARAM_STR);
                    $stmt->bindParam(':correousuario', $this->correoUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':rolusuario', $this->rolUsuario, PDO::PARAM_STR);
            

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el usuario correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }

                    } else {
                     $r['resultado'] = 'modificar';
                     $r['mensaje'] = 'ERROR! <br/> El correo colocado YA existe!';
                 }
                } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El usuario colocado YA existe!';
                }

        return $r;
    }

    /// Eliminar

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
           if (!$this->Existeusuario()) {
               if (!$this->Existecorreo()) {
            try {
                $stmt = $co->prepare("UPDATE tbl_usuario
                SET usu_estado = 0 WHERE usu_id = :usuarioid");
                $stmt->bindParam(':usuarioid', $this->usuarioId, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el usuario correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            }else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El usuario colocado NO existe!';
        }
        }else {
             $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El usuario colocado NO existe!';
        }
        return $r;
 
    }

    public function Existeusuario(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_usuario WHERE usu_nombre=:nombreusuario AND usu_estado = 1");

            $stmt->bindParam(':nombreusuario', $this->nombreUsuario, PDO::PARAM_STR);
           
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existeusuario';
                $r['mensaje'] = ' El usuario colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    public function Existecorreo(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_usuario WHERE usu_correo=:correousuario AND usu_estado = 1");

          $stmt->bindParam(':correousuario', $this->correoUsuario, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existecorreo';
                $r['mensaje'] = ' El correo colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Se cierra la conexión
        $co = null;
        return $r;
    }
}

?> 