<?php

namespace App\Model;

use PDO;
use Exception;

class Rol extends Connection_bitacora
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

    public function getNombreRol()
    {
        return $this->nombreRol;
    }

    public function getRolId()
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

    public function setNombreRol($nombreRol)
    {
        $this->nombreRol = $nombreRol;
    }

    public function setRolId($rolId)
    {
        $this->rolId = $rolId;
    }

    public function Registrar()
    {
        return $this->PostRegistrar();
    }

    private function PostRegistrar()
    {
        $r = array();

        if ($this->nombreRol === null || trim($this->nombreRol) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol no puede estar vacío.';
            return $r;
        }

        $this->nombreRol = trim($this->nombreRol);

        if (strlen($this->nombreRol) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->nombreRol) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol no puede exceder 50 caracteres.';
            return $r;
        }

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
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado YA existe!';
        }

        return $r;
    }

    public function Modificar()
    {
        return $this->PostModificar();
    }

    private function PostModificar()
    {
        $r = array();

        if ($this->rolId === null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El ID del rol es requerido.';
            return $r;
        }

        if ($this->nombreRol === null || trim($this->nombreRol) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol no puede estar vacío.';
            return $r;
        }

        $this->nombreRol = trim($this->nombreRol);

        if (strlen($this->nombreRol) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->nombreRol) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del rol no puede exceder 50 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!$this->ExisteId($this->rolId)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado NO existe!';
            return $r;
        }

        $rolInfo = $this->getRolById($this->rolId);
        if ($rolInfo && $rolInfo['rol_nombre'] === 'Administrador') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El rol "Administrador" no puede ser modificado.';
            return $r;
        }

        $existe = $this->existe($this->nombreRol);
        if (!empty($existe) && $existe['resultado'] === 'existe') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> El ROL colocado YA existe!';
            return $r;
        }

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

        return $r;
    }

    public function Eliminar()
    {
        return $this->PostEliminar();
    }

    private function PostEliminar()
    {
        $r = array();

        if ($this->rolId === null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El ID del rol es requerido.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $rolInfo = $this->getRolById($this->rolId);
        if ($rolInfo && $rolInfo['rol_nombre'] === 'Administrador') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El rol "Administrador" no puede ser eliminado.';
            return $r;
        }

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
            $r['resultado'] = 'error';
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

    public function Existe($nombreRol, $rolIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_rol WHERE rol_nombre=:nombreRol AND rol_estado = 1";
            if ($rolIdExcluir !== null) {
                $sql .= " AND rol_id != :rolIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':nombreRol', $nombreRol, PDO::PARAM_STR);
            if ($rolIdExcluir !== null) {
                $stmt->bindParam(':rolIdExcluir', $rolIdExcluir, PDO::PARAM_INT);
            }
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

    private function ExisteId($rolId)
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
            $stmtAsignados = $co->prepare("SELECT per_id, per_accion FROM rol_permisos WHERE rol_id = :rolId");
            $stmtAsignados->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmtAsignados->execute();
            $respuesta['permisosAsignados'] = $stmtAsignados->fetchAll(PDO::FETCH_ASSOC);

            $stmtDisponibles = $co->query("SELECT per_id, per_modulo FROM tbl_permisos ORDER BY CASE WHEN per_modulo = 'Reportes' THEN 0 ELSE 1 END, per_modulo ASC");
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

        $rolInfo = $this->getRolById($rolId);
        if ($rolInfo && $rolInfo['rol_nombre'] === 'Administrador') {
            return ['resultado' => 'error', 'mensaje' => 'Los permisos del rol "Administrador" no se pueden modificar.'];
        }

        try {
            $stmt = $co->prepare("DELETE FROM rol_permisos WHERE rol_id = :rolId");
            $stmt->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($permisos)) {
                $stmtInsert = $co->prepare("INSERT INTO rol_permisos (rol_id, per_id, per_accion) VALUES (:rolId, :perId, :accion)");
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

    private function getRolById($rolId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT rol_nombre FROM tbl_rol WHERE rol_id = :rolId");
            $stmt->bindParam(':rolId', $rolId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
}
