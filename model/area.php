<?php
require_once('model/dbconnection.php');

class Area extends Connection
{
    private $areaNombre;
    private $areaDescripcion;

    public function __construct($areaNombre = null, $areaDescripcion = null)
    {
        parent::__construct();
        $this->areaNombre = $areaNombre;
        $this->areaDescripcion = $areaDescripcion;
    }

    public function getArea()
    {
        return $this->areaNombre;
    }
    public function getDescripcion()
    {
        return $this->areaDescripcion;
    }

    public function setArea($areaNombre)
    {
        $this->areaNombre = $areaNombre;
    }
    public function setDescripcion($areaDescripcion)
    {
        $this->areaDescripcion = $areaDescripcion;
    }

    function Registrar()
    {
        $r = array();

        if ($this->areaNombre === null || trim($this->areaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede estar vacío.';
            return $r;
        }

        $this->areaNombre = trim($this->areaNombre);

        if (strlen($this->areaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->areaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->areaDescripcion !== null) {
            $this->areaDescripcion = trim($this->areaDescripcion);
            if (strlen($this->areaDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $co->prepare("SELECT * FROM tbl_area WHERE area_nombre = :areaNombre AND area_estado = 1");
        $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
        $stmt->execute();
        $existeActiva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existeActiva) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El Área ya existe!';
            $co = null;
            return $r;
        }

        $stmt = $co->prepare("SELECT * FROM tbl_area WHERE area_nombre = :areaNombre AND area_estado = 0");
        $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
        $stmt->execute();
        $existeInactiva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existeInactiva) {
            $stmtReactivar = $co->prepare("UPDATE tbl_area SET area_descripcion = :areaDescripcion, area_estado = 1 WHERE area_nombre = :areaNombre");
            $stmtReactivar->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
            $stmtReactivar->bindParam(':areaDescripcion', $this->areaDescripcion, PDO::PARAM_STR);
            $stmtReactivar->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el área correctamente!';
            $co = null;
            return $r;
        }

        try {
            $stmt = $co->prepare("INSERT INTO tbl_area (
                area_nombre,
                area_descripcion,
                area_estado
            ) VALUES (
                :areaNombre,
                :areaDescripcion,
                1
            )");

            $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
            $stmt->bindParam(':areaDescripcion', $this->areaDescripcion, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el área correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    function Modificar($areaOriginal)
    {
        $r = array();

        if ($areaOriginal === null || trim($areaOriginal) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre original del área es requerido.';
            return $r;
        }

        if ($this->areaNombre === null || trim($this->areaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede estar vacío.';
            return $r;
        }

        $this->areaNombre = trim($this->areaNombre);

        if (strlen($this->areaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->areaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->areaDescripcion !== null) {
            $this->areaDescripcion = trim($this->areaDescripcion);
            if (strlen($this->areaDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!$this->Existe($this->areaNombre, $areaOriginal)) {
            try {
                $stmtDel = $co->prepare("DELETE FROM tbl_area WHERE area_nombre = :areaNombre AND area_estado = 0");
                $stmtDel->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
                $stmtDel->execute();

                $stmt = $co->prepare("UPDATE tbl_area
                SET area_nombre = :areaNombre, area_descripcion = :areaDescripcion
                WHERE area_nombre = :areaOriginal");

                $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
                $stmt->bindParam(':areaDescripcion', $this->areaDescripcion, PDO::PARAM_STR);
                $stmt->bindParam(':areaOriginal', $areaOriginal, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el área correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El ÁREA ya existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $r = array();

        if ($this->areaNombre === null || trim($this->areaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede estar vacío.';
            return $r;
        }

        $this->areaNombre = trim($this->areaNombre);

        if (strlen($this->areaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->areaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del área no puede exceder 100 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->Existe($this->areaNombre, NULL)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_area
            SET area_estado = 0
            WHERE area_nombre = :areaNombre");

                $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el área correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El Área no existe!';
        }

        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $stmt = $co->query("SELECT area_nombre, area_descripcion FROM tbl_area WHERE area_estado = 1");
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

    public function Existe($areaNombre, $areaExcluir = NULL)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $sql = "SELECT * FROM tbl_area WHERE area_nombre=:areaNombre AND area_estado = 1";
            if ($areaExcluir !== null) {
                $sql .= " AND area_nombre != :areaExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':areaNombre', $areaNombre, PDO::PARAM_STR);
            if ($areaExcluir !== null) {
                $stmt->bindParam(':areaExcluir', $areaExcluir, PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);

            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El Área ya existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }
}
