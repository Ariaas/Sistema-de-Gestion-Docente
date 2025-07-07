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

        if (!$this->Existe($this->areaNombre)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El Área ya existe!';
        }

        return $r;
    }

    function Modificar($areaOriginal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        if (!$this->Existe($this->areaNombre, $areaOriginal)) {
            try {
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
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $r = array();
    
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