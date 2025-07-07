<?php
require_once('model/dbconnection.php');

class Eje extends Connection
{

    private $ejeNombre;
    private $ejeDescripcion;


    public function __construct($ejeNombre = null, $ejeDescripcion = null)
    {
        parent::__construct();

        $this->ejeNombre = $ejeNombre;
        $this->ejeDescripcion = $ejeDescripcion;
    }

    public function getEje()
    {
        return $this->ejeNombre;
    }

    public function getDescripcion()
    {
        return $this->ejeNombre;
    }

    public function setEje($ejeNombre)
    {
        $this->ejeNombre = $ejeNombre;
    }

    public function setDescripcion($ejeDescripcion)
    {
        $this->ejeDescripcion = $ejeDescripcion;
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
                    eje_descripcion,
                    eje_estado
                ) VALUES (
                    :ejeNombre,
                    :ejeDescripcion,
                    1
                )");

                $stmt->bindParam(':ejeNombre', $this->ejeNombre, PDO::PARAM_STR);
                $stmt->bindParam(':ejeDescripcion', $this->ejeDescripcion, PDO::PARAM_STR);

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

    function Modificar($ejeOriginal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        if (!$this->existe($this->ejeNombre, $ejeOriginal)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_eje
                    SET eje_nombre = :ejeNombre, eje_descripcion = :ejeDescripcion 
                    WHERE eje_nombre = :ejeOriginal");

                $stmt->bindParam(':ejeNombre', $this->ejeNombre, PDO::PARAM_STR);
                $stmt->bindParam(':ejeDescripcion', $this->ejeDescripcion, PDO::PARAM_STR);
                $stmt->bindParam(':ejeOriginal', $ejeOriginal, PDO::PARAM_STR);

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
        $co = null;
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->Existe($this->ejeNombre, NULL)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_eje
                SET eje_estado = 0
                WHERE eje_nombre = :ejeNombre");

                $stmt->bindParam(':ejeNombre', $this->ejeNombre, PDO::PARAM_STR);

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
            $stmt = $co->query("SELECT eje_nombre, eje_descripcion FROM tbl_eje WHERE eje_estado = 1");
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

    public function Existe($ejeNombre, $ejeExcluir = NULL)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_eje WHERE eje_nombre=:ejeNombre AND eje_estado = 1";
            if ($ejeExcluir !== null) {
                $sql .= " AND eje_nombre != :ejeExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':ejeNombre', $ejeNombre, PDO::PARAM_STR);
            if ($ejeExcluir !== null) {
                $stmt->bindParam(':ejeExcluir', $ejeExcluir, PDO::PARAM_STR);
            }
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
