<?php
require_once('model/dbconnection.php');

class Rol extends Connection
{

    private $nombreRol;
    private $rolId;

    public function __construct($nombreRol = null, $rolId = null)
    {
        parent::__construct();

        $this->nombreRol = $nombreRol;
        $this->rolId = $rolId;
    }

    public function getNombre()
    {
        return $this->nombreRol;
    }

    public function getId()
    {
        return $this->rolId;
    }

    public function setNombre($nombreRol)
    {
        $this->nombreRol = $nombreRol;
    }

    public function setId($rolId)
    {
        $this->rolId = $rolId;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->existe($this->nombreRol)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_rol (
                    rol_nombre,
                    rol_estado
                ) VALUES (
                    :nombreRol,
                    1
                )");

                $stmt->bindParam(':nombreRol', $this->nombreRol, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el rol correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->rolId)) {
            $existe = $this->existe($this->nombreRol);
            if (empty($existe) || $this->nombreRol == $this->getNombre()) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_rol
                    SET rol_nombre = :nombreRol
                    WHERE rol_id = :rolId");

                    $stmt->bindParam(':nombreRol', $this->nombreRol, PDO::PARAM_STR);
                    $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el rol correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El ROL colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado NO existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->rolId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_rol
                SET rol_estado = 0
                WHERE rol_id = :rolId");

                $stmt->bindParam(':rolId', $this->rolId, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el rol correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT rol_id, rol_nombre FROM tbl_rol WHERE rol_estado = 1");
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

    public function Existe($nombreRol)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_rol WHERE rol_nombre=:nombreRol AND rol_estado = 1");
            $stmt->bindParam(':nombreRol', $nombreRol, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El ROL colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ExisteId($rolId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_rol WHERE rol_id=:rolId AND rol_estado = 1");
            $stmt->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El ROL colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function listarPermisos($rolId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $respuesta = [
            'permisosAsignados' => [],
            'modulosDisponibles' => []
        ];
        try {
            $stmtAsignados = $co->prepare("SELECT per_id, per_accion FROM rol_permisos WHERE rol_id = :rolId AND rol_per_estado = 1");
            $stmtAsignados->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmtAsignados->execute();
            $respuesta['permisosAsignados'] = $stmtAsignados->fetchAll(PDO::FETCH_ASSOC);

            $stmtDisponibles = $co->query("SELECT per_id, per_modulo FROM tbl_permisos WHERE per_estado = 1 ORDER BY CASE WHEN per_modulo = 'Reportes' THEN 0 ELSE 1 END, per_modulo ASC");
            $respuesta['modulosDisponibles'] = $stmtDisponibles->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
        }
        $co = null;
        return $respuesta;
    }

    public function asignarPermisos($rolId, $permisos)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("DELETE FROM rol_permisos WHERE rol_id = :rolId");
            $stmt->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($permisos)) {
                $stmtInsert = $co->prepare("INSERT INTO rol_permisos (rol_id, per_id, per_accion, rol_per_estado) VALUES (:rolId, :perId, :accion, 1)");
                foreach ($permisos as $permiso) {
                    $stmtInsert->bindParam(':rolId', $rolId, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':perId', $permiso['per_id'], PDO::PARAM_INT);
                    $stmtInsert->bindParam(':accion', $permiso['per_accion'], PDO::PARAM_STR);
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
