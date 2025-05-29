<?php
require_once('model/db_bitacora.php');

class Usuario extends Connection_bitacora
{
    private $usuarioId;
    private $nombreUsuario;
    private $contraseniaUsuario;
    private $correoUsuario;
    private $superUsuario;

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

    // Setters
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
                    usu_super
                ) VALUES (
                    :nombreUsuario,
                    :correoUsuario,
                    :contraseniaUsuario,
                    1,
                    :superUsuario
                )");

                $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                $stmt->bindParam(':contraseniaUsuario', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':superUsuario', $this->superUsuario, PDO::PARAM_INT);

                $stmt->execute();

                if ($this->superUsuario == 1) {
                    $todosLosPermisos = range(1, 18);
                    $this->asignarPermisos($co->lastInsertId(), $todosLosPermisos);
                }

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el usuario correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // Cerrar la conexión
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


    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->usuarioId)) {
            if (!$this->existe($this->nombreUsuario, $this->correoUsuario)) {
                try {
                    $hashedPassword = password_hash($this->contraseniaUsuario, PASSWORD_DEFAULT);

                    $stmt = $co->prepare("UPDATE tbl_usuario
                    SET usu_nombre = :nombreUsuario, usu_correo = :correoUsuario
                    WHERE usu_id = :usuarioId");

                    $stmt->bindParam(':correoUsuario', $this->correoUsuario, PDO::PARAM_STR);
                    $stmt->bindParam(':nombreUsuario', $this->nombreUsuario, PDO::PARAM_STR);
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

    /// Eliminar

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

    function Existe($nombreUsuario, $correoUsuario)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_usuario WHERE usu_nombre=:nombreUsuario AND usu_correo=:correoUsuario AND usu_estado = 1");

            $stmt->bindParam(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
            $stmt->bindParam(':correoUsuario', $correoUsuario, PDO::PARAM_STR);
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
        // Se cierra la conexión
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
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    public function listarPermisos($usuarioId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $permisos = [];
        try {
            $stmt = $co->prepare("SELECT per_permisos FROM tbl_permisos WHERE usu_id = :usuarioId AND per_estado = 1");
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $permisos = [];
        }
        $co = null;
        return $permisos;
    }

    public function asignarPermisos($usuarioId, $permisos)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $modulos = [
            1 => 'Area',
            2 => 'Respaldo',
            3 => 'Categorias',
            4 => 'Certificados',
            5 => 'Docentes',
            6 => 'Eje',
            7 => 'Espacios',
            8 => 'Seccion',
            9 => 'Titulo',
            10 => 'Trayecto',
            11 => 'Unidad Curricular',
            12 => 'Horario',
            13 => 'Horario Docente',
            14 => 'Malla Curricular',
            15 => 'Archivos',
            16 => 'Reportes',
            17 => 'Bitacora',
            18 => 'Usuarios'
        ];

        try {
            $stmt = $co->prepare("UPDATE tbl_permisos SET per_estado = 0 WHERE usu_id = :usuarioId");
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($permisos)) {
                $stmtInsert = $co->prepare("INSERT INTO tbl_permisos (usu_id, per_permisos, per_modulo, per_estado) VALUES (:usuarioId, :permiso, :modulo, 1)");
                foreach ($permisos as $permiso) {
                    $check = $co->prepare("SELECT COUNT(*) FROM tbl_permisos WHERE usu_id = :usuarioId AND per_permisos = :permiso AND per_estado = 1");
                    $check->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
                    $check->bindParam(':permiso', $permiso, PDO::PARAM_INT);
                    $check->execute();
                    if ($check->fetchColumn() > 0) {
                        return ['resultado' => 'error', 'mensaje' => 'No se pueden asignar permisos repetidos.'];
                    }
                    $modulo = isset($modulos[$permiso]) ? $modulos[$permiso] : '';
                    $stmtInsert->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':permiso', $permiso, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':modulo', $modulo, PDO::PARAM_STR);
                    $stmtInsert->execute();
                }
            }
            $co = null;
            return ['resultado' => 'ok', 'mensaje' => 'Permisos asignados correctamente'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }
}
