<?php
require_once('model/dbconnection.php');

class Area extends Connection
{
    private $areaNombre;
    private $areaId;

    public function __construct($areaNombre = null, $areaId = null)
    {
        parent::__construct();
        $this->areaNombre = $areaNombre;
        $this->areaId = $areaId;
    }

    // Getters
    public function getArea()
    {
        return $this->areaNombre;
    }
    public function getId()
    {
        return $this->areaId;
    }

    // Setters
    public function setArea($areaNombre)
    {
        $this->areaNombre = $areaNombre;
    }
    public function setId($areaId)
    {
        $this->areaId = $areaId;
    }

    // Methods
    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->areaNombre)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_area (
                    area_nombre,
                    area_estado
                ) VALUES (
                    :areaNombre,
                    1
                )");

                $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
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

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        if ($this->ExisteId($this->areaId)) {
            if (!$this->Existe($this->areaNombre)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_area
                    SET area_nombre = :areaNombre 
                    WHERE area_id = :areaId");

                    $stmt->bindParam(':areaId', $this->areaId, PDO::PARAM_INT);
                    $stmt->bindParam(':areaNombre', $this->areaNombre, PDO::PARAM_STR);
                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el área correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El Área ya existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El Área no existe!';
        }
        return $r;
    }

    function Eliminar()
{
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $r = array();
    
    $existe = $this->Existe($this->areaNombre);
    
    if ($existe['resultado'] == 'existe') {
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
            $stmt = $co->query("SELECT area_nombre, area_id FROM tbl_area WHERE area_estado = 1");
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

    public function ExisteId($areaId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_area WHERE area_id=:areaId AND area_estado = 1");
            $stmt->bindParam(':areaId', $areaId, PDO::PARAM_STR);
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

    public function Existe($areaNombre)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_area WHERE area_nombre=:areaNombre AND area_estado = 1");
            $stmt->bindParam(':areaNombre', $areaNombre, PDO::PARAM_STR);
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