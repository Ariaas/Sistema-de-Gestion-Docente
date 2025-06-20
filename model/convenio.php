<?php
require_once('model/dbconnection.php');

class Convenio extends Connection
{
    private $convenio_id;
    private $tra_id;
    private $convenio_nombre;
    private $convenio_inicio;

    public function __construct()
    {
        parent::__construct();
    }

    // Getters
    public function getId()
    {
        return $this->convenio_id;
    }
    public function getTraId()
    {
        return $this->tra_id;
    }
    public function getNombre()
    {
        return $this->convenio_nombre;
    }
    public function getInicio()
    {
        return $this->convenio_inicio;
    }

    // Setters
    public function setId($convenio_id)
    {
        $this->convenio_id = $convenio_id;
    }
    public function setTraId($tra_id)
    {
        $this->tra_id = $tra_id;
    }
    public function setNombre($convenio_nombre)
    {
        $this->convenio_nombre = $convenio_nombre;
    }
    public function setInicio($convenio_inicio)
    {
        $this->convenio_inicio = $convenio_inicio;
    }

    public function Registrar()
    {
        $r = array();

        if ($this->Existe($this->convenio_nombre) > 0) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El nombre del convenio ya existe.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("INSERT INTO tbl_convenio (
                tra_id, con_nombre, con_inicio, con_estado
            ) VALUES (
                :tra_id, :con_nombre, :con_inicio, 1
            )");

            $stmt->bindParam(':tra_id', $this->tra_id, PDO::PARAM_INT);
            $stmt->bindParam(':con_nombre', $this->convenio_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':con_inicio', $this->convenio_inicio, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>El convenio se registró correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Modificar()
    {
        $r = array();

        $existe = $this->Existe($this->convenio_nombre);
        if ($existe && $existe['con_id'] != $this->convenio_id) {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> Ya existe otro convenio con ese nombre.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("UPDATE tbl_convenio SET
                tra_id = :tra_id,
                con_nombre = :con_nombre,
                con_inicio = :con_inicio
                WHERE con_id = :con_id");

            $stmt->bindParam(':tra_id', $this->tra_id, PDO::PARAM_INT);
            $stmt->bindParam(':con_nombre', $this->convenio_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':con_inicio', $this->convenio_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':con_id', $this->convenio_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>El convenio se modificó correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Eliminar()
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("UPDATE tbl_convenio SET con_estado = 0 WHERE con_id = :con_id");
            $stmt->bindParam(':con_id', $this->convenio_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el convenio correctamente!';
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
            $stmt = $co->query(
                "SELECT c.con_id, c.con_nombre, c.con_inicio, 
                        t.tra_id, t.tra_numero, a.ani_anio 
                 FROM tbl_convenio c
                 JOIN tbl_trayecto t ON c.tra_id = t.tra_id
                 JOIN tbl_anio a ON t.ani_id = a.ani_id
                 WHERE c.con_estado = 1 
                 ORDER BY c.con_inicio DESC, c.con_nombre ASC"
            );
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function ListarTrayectos()
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->query(
                "SELECT t.tra_id, t.tra_numero, t.tra_tipo, a.ani_anio 
                 FROM tbl_trayecto t
                 JOIN tbl_anio a ON t.ani_id = a.ani_id
                 WHERE t.tra_estado = 1 AND a.ani_estado = 1 
                 ORDER BY a.ani_anio DESC, t.tra_numero ASC"
            );
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar_trayectos';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Existe($nombre)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT con_id FROM tbl_convenio WHERE con_nombre = :nombre AND con_estado = 1");
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
