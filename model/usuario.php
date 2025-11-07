<?php

namespace App\Model;

use PDO;
use Exception;

class Usuario extends Connection_bitacora
{
    private $usuarioId;
    private $nombreUsuario;
    private $contraseniaUsuario;
    private $correoUsuario;
    private $superUsuario;
    private $rolId;
    private $usu_docente;
    private $usu_cedula;

    public function __construct($usuarioId = null, $nombreUsuario = null, $contraseniaUsuario = null, $correoUsuario = null, $superUsuario = 0)
    {

        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->contraseniaUsuario = $contraseniaUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->superUsuario = $superUsuario;
    }

    public function set_usu_docente($usu_docente)
    {
        $this->usu_docente = $usu_docente;
    }
    public function get_usu_docente()
    {
        return $this->usu_docente;
    }

    public function set_usu_cedula($usu_cedula)
    {
        $this->usu_cedula = $usu_cedula;
    }
    public function get_usu_cedula()
    {
        return $this->usu_cedula;
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

        if ($this->nombreUsuario === null || trim($this->nombreUsuario) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de usuario no puede estar vacío.';
            return $r;
        }

        if (strlen($this->nombreUsuario) < 3 || strlen($this->nombreUsuario) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
            return $r;
        }

        if ($this->correoUsuario === null || trim($this->correoUsuario) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El correo no puede estar vacío.';
            return $r;
        }

        if (!filter_var($this->correoUsuario, FILTER_VALIDATE_EMAIL)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El correo no tiene un formato válido.';
            return $r;
        }

        if ($this->contraseniaUsuario === null || trim($this->contraseniaUsuario) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'La contraseña no puede estar vacía.';
            return $r;
        }

        if (strlen($this->contraseniaUsuario) < 6) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'La contraseña debe tener al menos 6 caracteres.';
            return $r;
        }

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
                    rol_id,
                    usu_docente,
                    usu_cedula,
                    usu_foto
                ) VALUES (
                    :nombreUsuario,
                    :correoUsuario,
                    :contraseniaUsuario,
                    1,
                    :rolId,
                    :usu_docente,
                    :usu_cedula,
                    :usu_foto
                )");

                $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':contraseniaUsuario', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);
                $stmt->bindParam(':usu_docente', $this->usu_docente, PDO::PARAM_STR);
                $stmt->bindParam(':usu_cedula', $this->usu_cedula, PDO::PARAM_STR);
                $fotoPorDefecto = 'public/assets/profile/sinPerfil.jpg';
                $stmt->bindParam(':usu_foto', $fotoPorDefecto, PDO::PARAM_STR);

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
            $stmt = $co->query("SELECT u.usu_id, u.usu_nombre, u.usu_correo, u.usu_docente, u.usu_cedula, r.rol_nombre, u.rol_id
                                FROM tbl_usuario u 
                                LEFT JOIN tbl_rol r ON u.rol_id = r.rol_id 
                                WHERE u.usu_estado = 1");

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


    function Modificar($current_user_id, $nuevoNombre = null, $nuevoCorreo = null, $nuevaContrasenia = null)
    {
        if ($current_user_id === null) {
            return array('resultado' => 'error', 'mensaje' => 'El ID del usuario actual es requerido.');
        }
        if (trim($current_user_id) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El ID del usuario actual es requerido.');
        }

        if ($nuevoNombre !== null) {
            $this->nombreUsuario = $nuevoNombre;
        }

        if ($nuevoCorreo !== null) {
            $this->correoUsuario = $nuevoCorreo;
        }

        if ($nuevaContrasenia !== null) {
            $this->contraseniaUsuario = $nuevaContrasenia;
        }

        if ($this->nombreUsuario === null) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre no puede estar vacío.');
        }
        if (trim($this->nombreUsuario) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El nombre no puede estar vacío.');
        }

        $this->nombreUsuario = trim($this->nombreUsuario);

        if (strlen($this->nombreUsuario) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 50 caracteres.');
        }
        if (strlen($this->nombreUsuario) > 50) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 50 caracteres.');
        }

        if ($this->correoUsuario === null) {
            return array('resultado' => 'error', 'mensaje' => 'El correo no puede estar vacío.');
        }
        if (trim($this->correoUsuario) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El correo no puede estar vacío.');
        }

        if (!filter_var($this->correoUsuario, FILTER_VALIDATE_EMAIL)) {
            return array('resultado' => 'error', 'mensaje' => 'El formato del correo es inválido.');
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $existeResult = $this->verificarUsuarioExiste($this->usuarioId);
        if (isset($existeResult['resultado']) && $existeResult['resultado'] === 'existe') {
            $stmt_info = $co->prepare("
                SELECT r.rol_nombre 
                FROM tbl_usuario u 
                LEFT JOIN tbl_rol r ON u.rol_id = r.rol_id 
                WHERE u.usu_id = :usuarioId
            ");
            $stmt_info->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);
            $stmt_info->execute();
            $userInfo = $stmt_info->fetch(PDO::FETCH_ASSOC);

            if ($userInfo && $userInfo['rol_nombre'] === 'Administrador' && $this->usuarioId != $current_user_id) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'No se puede modificar a un usuario con el rol de Administrador.';
                return $r;
            }

            if (!$this->existe($this->nombreUsuario, $this->correoUsuario, $this->usuarioId)) {
                try {
                    $sql = "UPDATE tbl_usuario
                            SET usu_nombre = :nombreUsuario, usu_correo = :correoUsuario, rol_id = :rolId, usu_docente = :usu_docente, usu_cedula = :usu_cedula";

                    if (!empty($this->contraseniaUsuario)) {
                        $sql .= ", usu_contrasenia = :contraseniaUsuario";
                    }

                    $sql .= " WHERE usu_id = :usuarioId";

                    $stmt = $co->prepare($sql);

                    $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);
                    $stmt->bindParam(':usu_docente', $this->usu_docente, PDO::PARAM_STR);
                    $stmt->bindParam(':usu_cedula', $this->usu_cedula, PDO::PARAM_STR);
                    $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);

                    if (!empty($this->contraseniaUsuario)) {
                        $hashedPassword = password_hash($this->contraseniaUsuario, PASSWORD_DEFAULT);
                        $stmt->bindParam(':contraseniaUsuario', $hashedPassword, PDO::PARAM_STR);
                    }

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



    function Eliminar($usuarioEliminar = null)
    {
        if ($usuarioEliminar !== null) {
            $this->nombreUsuario = $usuarioEliminar;
        }

        if ($this->nombreUsuario === null) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre de usuario no puede estar vacío.');
        }
        if (trim($this->nombreUsuario) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El nombre de usuario no puede estar vacío.');
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        $existeResult = $this->verificarUsuarioExiste($this->usuarioId);
        if (isset($existeResult['resultado']) && $existeResult['resultado'] === 'existe') {
            try {
                $stmt_info = $co->prepare("
                    SELECT r.rol_nombre 
                    FROM tbl_usuario u 
                    LEFT JOIN tbl_rol r ON u.rol_id = r.rol_id 
                    WHERE u.usu_id = :usuarioId
                ");
                $stmt_info->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);
                $stmt_info->execute();
                $userInfo = $stmt_info->fetch(PDO::FETCH_ASSOC);

                if ($userInfo && $userInfo['rol_nombre'] === 'Administrador') {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = 'No se puede eliminar a un usuario con el rol de Administrador.';
                    return $r;
                }

                $stmt = $co->prepare("UPDATE tbl_usuario
                SET usu_estado = 0
                WHERE usu_id = :usuarioId");
                $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);
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

    private function Existe($nombreUsuario, $correoUsuario, $usuarioIdExcluir = null)
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

    function ExisteUsuario($nombreUsuario, $usuarioIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT usu_id FROM tbl_usuario WHERE usu_nombre = :nombreUsuario AND usu_estado = 1";
            if ($usuarioIdExcluir !== null) {
                $sql .= " AND usu_id != :usuarioIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
            if ($usuarioIdExcluir !== null) {
                $stmt->bindParam(':usuarioIdExcluir', $usuarioIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El nombre de usuario ya existe.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function ExisteCorreo($correoUsuario, $usuarioIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT usu_id FROM tbl_usuario WHERE usu_correo = :correoUsuario AND usu_estado = 1";
            if ($usuarioIdExcluir !== null) {
                $sql .= " AND usu_id != :usuarioIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':correoUsuario', $correoUsuario, PDO::PARAM_STR);
            if ($usuarioIdExcluir !== null) {
                $stmt->bindParam(':usuarioIdExcluir', $usuarioIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->fetch()) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El correo electrónico ya está en uso.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    private function verificarUsuarioExiste($usuarioId)
    {
        $r = array();
        try {
            $co = $this->Con();
            if (!$co) {
                return array('resultado' => 'error', 'mensaje' => 'Error de conexión');
            }
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT * FROM tbl_usuario WHERE usu_id=:usuarioId AND usu_estado = 1";
            $statement = $co->prepare($sql);
            $statement->bindParam(':usuarioId', $usuarioId, PDO::PARAM_STR);
            $statement->execute();
            $fila = $statement->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El USUARIO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
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

    public function obtenerDocentesDisponibles($cedula_actual = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT d.doc_cedula, d.doc_nombre, d.doc_apellido, d.doc_correo 
                    FROM db_orgdocente.tbl_docente d 
                    LEFT JOIN tbl_usuario u ON d.doc_cedula = u.usu_cedula AND u.usu_estado = 1
                    WHERE d.doc_estado = 1 AND (u.usu_cedula IS NULL";
            if ($cedula_actual) {
                $sql .= " OR d.doc_cedula = :cedula_actual";
            }
            $sql .= ")";

            $stmt = $co->prepare($sql);
            if ($cedula_actual) {
                $stmt->bindParam(':cedula_actual', $cedula_actual, PDO::PARAM_STR);
            }
            $stmt->execute();
            $r['resultado'] = 'ok';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function verificarCorreoDocente($correo, $usuarioId_actual = null)
    {
        $co = $this->Con();
        try {
            $sql_usuario = "SELECT usu_id FROM tbl_usuario WHERE usu_correo = :correo AND usu_estado = 1";
            if ($usuarioId_actual) {
                $sql_usuario .= " AND usu_id != :usu_id";
            }
            $stmt_usuario = $co->prepare($sql_usuario);
            $params_usuario = [':correo' => $correo];
            if ($usuarioId_actual) {
                $params_usuario[':usu_id'] = $usuarioId_actual;
            }
            $stmt_usuario->execute($params_usuario);

            if ($stmt_usuario->fetch()) {
                return ['resultado' => 'existe_usuario', 'mensaje' => 'Este correo ya está registrado para otro usuario.'];
            }

            $stmt_docente = $co->prepare("
                SELECT d.doc_nombre, d.doc_apellido 
                FROM tbl_docente d
                LEFT JOIN tbl_usuario u ON d.doc_cedula = u.usu_cedula
                WHERE d.doc_correo = :correo AND d.doc_estado = 1 AND u.usu_id IS NULL
            ");
            $stmt_docente->execute([':correo' => $correo]);

            if ($docente = $stmt_docente->fetch(PDO::FETCH_ASSOC)) {
                return ['resultado' => 'existe_docente', 'mensaje' => 'El correo pertenece al docente (' . $docente['doc_nombre'] . ' ' . $docente['doc_apellido'] . '). Asigne este docente para usar el correo.'];
            }

            return ['resultado' => 'no_existe'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
}
