<?php
require_once('model/dbconnection.php');

class Usuario extends Connection
{
    private $usuarioId;
    private $nombreUsuario;
    private $contraseniaUsuario;
    private $correoUsuario;
    private $superUsuario;
    private $rolId;

    public function __construct($usuarioId = null, $nombreUsuario = null, $contraseniaUsuario = null, $correoUsuario = null, $superUsuario = 0)
    {

        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->contraseniaUsuario = $contraseniaUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->superUsuario = $superUsuario;
    }

    public function get_usuarioId()
    {
        return $this->usuarioId;
    }

    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function get_contraseniaUsuario()
    {
        return $this->contraseniaUsuario;
    }

    public function get_correoUsuario()
    {
        return $this->correoUsuario;
    }

    public function get_superUsuario()
    {
        return $this->superUsuario;
    }

    public function set_rolId($rolId)
    {
        $this->rolId = $rolId;
    }

    public function get_rolId()
    {
        return $this->rolId;
    }

    
    public function set_usuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function set_contraseniaUsuario($contraseniaUsuario)
    {
        $this->contraseniaUsuario = $contraseniaUsuario;
    }

    public function set_correoUsuario($correoUsuario)
    {
        $this->correoUsuario = $correoUsuario;
    }

    public function set_superUsuario($superUsuario)
    {
        $this->superUsuario = $superUsuario;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->existe($this->nombreUsuario, $this->correoUsuario)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $hashedPassword = password_hash($this->contraseniaUsuario, PASSWORD_DEFAULT);

                $stmt = $co->prepare("INSERT INTO tbl_usuario (
                    usu_nombre,
                    usu_correo,
                    usu_contrasenia,
                    usu_estado,
                    rol_id
                ) VALUES (
                    :nombreUsuario,
                    :correoUsuario,
                    :contraseniaUsuario,
                    1,
                    :rolId
                )");

                $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':contraseniaUsuario', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);

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
            $r['mensaje'] = 'ERROR! <br/> El USUARIO colocado YA existe!';
        }

        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT u.*, r.rol_nombre FROM tbl_usuario u LEFT JOIN tbl_rol r ON u.rol_id = r.rol_id WHERE u.usu_estado = 1");

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


    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->usuarioId)) {
            if (!$this->existe($this->nombreUsuario, $this->correoUsuario, $this->usuarioId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_usuario
                    SET usu_nombre = :nombreUsuario, usu_correo = :correoUsuario, rol_id = :rolId
                    WHERE usu_id = :usuarioId");

                    $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);
                    $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el usuario correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El USUARIO colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El USUARIO colocado NO existe!';
        }
        return $r;
    }

    

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->usuarioId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_usuario
                SET usu_estado = 0
                WHERE usu_id = :usuarioId");
                $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el usuario correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El USUARIO colocado NO existe!';
        }
        return $r;
    }

    function Existe($nombreUsuario, $correoUsuario, $usuarioIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_usuario WHERE usu_nombre=:nombreUsuario AND usu_correo=:correoUsuario AND usu_estado = 1";
            if ($usuarioIdExcluir !== null) {
                $sql .= " AND usu_id != :usuarioIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':correoUsuario', $correoUsuario, PDO::PARAM_STR);
            if ($usuarioIdExcluir !== null) {
                $stmt->bindParam(':usuarioIdExcluir', $usuarioIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El USUARIO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function ExisteId($usuarioId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_usuario WHERE usu_id=:usuarioId AND usu_estado = 1");

            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El USUARIO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
     
        $co = null;
        return $r;
    }

    function obtenerRoles()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT rol_nombre, rol_id FROM tbl_rol WHERE rol_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }
}
