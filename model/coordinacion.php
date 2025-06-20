<?php
require_once('model/dbconnection.php');

class Coordinacion extends Connection
{
    private $cor_nombre;
    private $cor_id;

    public function __construct()
    {
        parent::__construct();
    }

    // Getters
    public function getNombre()
    {
        return $this->cor_nombre;
    }
    public function getId()
    {
        return $this->cor_id;
    }

    // Setters
    public function setNombre($nombre)
    {
        $this->cor_nombre = $nombre;
    }
    public function setId($id)
    {
        $this->cor_id = $id;
    }

    public function Registrar()
    {
        $r = array();

        if ($this->Existe($this->cor_nombre)) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La Coordinación ya existe!';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("INSERT INTO tbl_coordinacion (
                cor_nombre,
                cor_estado
            ) VALUES (
                :cor_nombre,
                1
            )");

            $stmt->bindParam(':cor_nombre', $this->cor_nombre, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró la coordinación correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Modificar()
    {
        $r = array();

        $existe_por_id = $this->ExisteId($this->cor_id);
        if (!$existe_por_id) {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La Coordinación que intenta modificar no existe.';
            return $r;
        }

        $existe_por_nombre = $this->Existe($this->cor_nombre);
        if ($existe_por_nombre && $existe_por_nombre['cor_id'] != $this->cor_id) {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> Ya existe otra coordinación con ese nombre.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("UPDATE tbl_coordinacion
            SET cor_nombre = :cor_nombre 
            WHERE cor_id = :cor_id");

            $stmt->bindParam(':cor_id', $this->cor_id, PDO::PARAM_INT);
            $stmt->bindParam(':cor_nombre', $this->cor_nombre, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la coordinación correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Eliminar()
    {
        $r = array();

        if (!$this->ExisteId($this->cor_id)) {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La Coordinación no existe!';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("UPDATE tbl_coordinacion
            SET cor_estado = 0
            WHERE cor_id = :cor_id");

            $stmt->bindParam(':cor_id', $this->cor_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la coordinación correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Listar()
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->query("SELECT cor_id, cor_nombre FROM tbl_coordinacion WHERE cor_estado = 1");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function ExisteId($id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT * FROM tbl_coordinacion WHERE cor_id = :id AND cor_estado = 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function Existe($nombre)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT cor_id FROM tbl_coordinacion WHERE cor_nombre = :nombre AND cor_estado = 1");
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
