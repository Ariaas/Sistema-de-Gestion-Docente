<?php
require_once('model/dbconnection.php');

class Eje extends Connection
{

    private $ejeNombre;
    private $ejeId;


    public function __construct($ejeNombre = null, $ejeId = null)
    {
        parent::__construct();

        $this->ejeNombre = $ejeNombre;
        $this->ejeId = $ejeId;
    }

    public function getEje()
    {
        return $this->ejeNombre;
    }
    public function getId()
    {
        return $this->ejeId;
    }
    public function setEje($ejeNombre)
    {
        $this->ejeNombre = $ejeNombre;
    }
    public function setId($ejeId)
    {
        $this->ejeId = $ejeId;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->ejeNombre)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_eje (
                    eje_nombre,
                    eje_estado
                ) VALUES (
                    :ejeNombre,
                    1
                )");

                $stmt->bindParam(':ejeNombre', $this->ejeNombre, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el EJE correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El EJE colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->ejeId)) {
            if (!$this->existe($this->ejeNombre)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_eje
                    SET eje_nombre = :ejeNombre 
                    WHERE eje_id = :ejeId");

                    $stmt->bindParam(':ejeId', $this->ejeId, PDO::PARAM_INT);
                    $stmt->bindParam(':ejeNombre', $this->ejeNombre, PDO::PARAM_STR);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el EJE correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El EJE colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El EJE colocado NO existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->ejeId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_eje
                SET eje_estado = 0
                WHERE eje_id = :ejeId");

                $stmt->bindParam(':ejeId', $this->ejeId, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el EJE correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El EJE colocado NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT eje_nombre, eje_id FROM tbl_eje WHERE eje_estado = 1");
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

    public function ExisteId($ejeId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_eje WHERE eje_id=:ejeId AND eje_estado = 1");
            $stmt->bindParam(':ejeId', $ejeId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El EJE colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existe($ejeNombre)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_eje WHERE eje_nombre=:ejeNombre AND eje_estado = 1");
            $stmt->bindParam(':ejeNombre', $ejeNombre, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El EJE colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
