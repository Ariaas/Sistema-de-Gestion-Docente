<?php

require_once('model/db_bitacora.php');

class Login extends Connection_bitacora
{
    private $nombreUsuario;
    private $contraseniaUsuario;

    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function set_contraseniaUsuario($contraseniaUsuario)
    {
        $this->contraseniaUsuario = $contraseniaUsuario;
    }

    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function get_contraseniaUsuario()
    {
        return $this->contraseniaUsuario;
    }


    function existe()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $p = $co->prepare("SELECT usu_id, usu_nombre, usu_contrasenia, usu_estado FROM tbl_usuario 
            WHERE usu_nombre = :username AND usu_estado = 1");
            $p->bindParam(':username', $this->nombreUsuario);

            $p->execute();

            $fila = $p->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                $inputPassword = trim($this->contraseniaUsuario);
                $dbPassword = trim($fila['usu_contrasenia']);

                if (password_verify($inputPassword, $dbPassword)) {
                    $r['resultado'] = 'existe';
                    $r['mensaje'] = $fila['usu_nombre'];
                    $r['usu_id'] = $fila['usu_id']; 
                } else {
                    $r['resultado'] = 'noexiste';
                    $r['mensaje'] = "Error en el usuario o contraseña!!!";
                }
            } else {
                $r['resultado'] = 'noexiste';
                $r['mensaje'] = "Error en el usuario o contraseña!!!";
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }
}
