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

    public function existeCorreo($correo, $usuarioId = null)
    {
        $co = $this->Con();
        try {
            $sql_usuario = "SELECT usu_id FROM tbl_usuario WHERE usu_correo = :correo AND usu_estado = 1";
            $stmt_usuario = $co->prepare($sql_usuario);
            $stmt_usuario->execute([':correo' => $correo]);
            $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if ($usuarioId && $usuario['usu_id'] == $usuarioId) {
                    return ['resultado' => 'no_existe'];
                } else {
                    return [
                        'resultado' => 'existe_usuario',
                        'mensaje' => 'Este correo ya está en uso por otro usuario.'
                    ];
                }
            }

            $sql_docente = "SELECT doc_nombre, doc_apellido FROM tbl_docente WHERE doc_correo = :correo AND doc_estado = 1";
            $stmt_docente = $co->prepare($sql_docente);
            $stmt_docente->execute([':correo' => $correo]);
            $docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);

            if ($docente) {
                return [
                    'resultado' => 'existe_docente',
                    'mensaje' => 'El correo ya está asignado al docente (' . $docente['doc_nombre'] . ' ' . $docente['doc_apellido'] . ').'
                ];
            }

            return ['resultado' => 'no_existe'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
}
