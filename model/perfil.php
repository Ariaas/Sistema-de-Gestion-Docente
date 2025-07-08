<?php
require_once('model/dbconnection.php');

class Perfil extends Connection
{
    private $usuarioId;
    private $nombreUsuario;
    private $correoUsuario;
    private $fotoPerfil;

    public function __construct($usuarioId = null, $nombreUsuario = null, $correoUsuario = null, $fotoPerfil = null)
    {
        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->fotoPerfil = $fotoPerfil;
    }

    public function get_usuarioId()
    {
        return $this->usuarioId;
    }
    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }
    public function get_correoUsuario()
    {
        return $this->correoUsuario;
    }
    public function get_fotoPerfil()
    {
        return $this->fotoPerfil;
    }

    public function set_usuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }
    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }
    public function set_correoUsuario($correoUsuario)
    {
        $this->correoUsuario = $correoUsuario;
    }
    public function set_fotoPerfil($fotoPerfil)
    {
        $this->fotoPerfil = $fotoPerfil;
    }

    public function Listar($usuarioId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT usu_id, usu_nombre, usu_correo, usu_foto FROM tbl_usuario WHERE usu_id = :usuarioId AND usu_estado = 1");
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $query = "UPDATE tbl_usuario SET usu_correo = :correoUsuario";
            if (!empty($this->fotoPerfil)) {
                $query .= ", usu_foto = :fotoPerfil";
            }
            $query .= " WHERE usu_id = :usuarioId";

            $stmt = $co->prepare($query);

            $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
            if (!empty($this->fotoPerfil)) {
                $stmt->bindParam(':fotoPerfil', $this->fotoPerfil, PDO::PARAM_STR);
            }
            $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);

            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Perfil actualizado correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
