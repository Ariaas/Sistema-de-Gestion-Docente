<?php
require_once('model/dbconnection.php');

class Trayecto extends Connection
{

    private $trayectoNumero;
    private $trayectoAnio;
    private $trayectoId;
    private $trayectoTipo;

    public function __construct($trayectoNumero = null, $trayectoAnio = null, $trayectoId = null, $trayectoTipo = null)
    {
        parent::__construct();

        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoId = $trayectoId;
        $this->trayectoTipo = $trayectoTipo;
    }

    public function getNumero()
    {
        return $this->trayectoNumero;
    }
    public function getAnio()
    {
        return $this->trayectoAnio;
    }
    public function getId()
    {
        return $this->trayectoId;
    }
    public function getTipo()
    {
        return $this->trayectoTipo;
    }
    public function setNumero($trayectoNumero)
    {
        $this->trayectoNumero = $trayectoNumero;
    }
    public function setAnio($trayectoAnio)
    {
        $this->trayectoAnio = $trayectoAnio;
    }
    public function setId($trayectoId)
    {
        $this->trayectoId = $trayectoId;
    }
    public function setTipo($trayectoTipo)
    {
        $this->trayectoTipo = $trayectoTipo;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->existe($this->trayectoNumero, $this->trayectoAnio)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_trayecto (
                    tra_numero,
                    tra_tipo,
                    ani_id,
                    tra_estado
                ) VALUES (
                    :trayectoNumero,
                    :trayectoTipo,
                    :trayectoAnio,
                    1
                )");

                $stmt->bindParam(':trayectoNumero', $this->trayectoNumero, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoAnio', $this->trayectoAnio, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoTipo', $this->trayectoTipo, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el trayecto correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado YA existe!';
        }

        return $r;
    }


    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteTrayecto($this->trayectoId)) {
            if (!$this->existeOtro($this->trayectoNumero, $this->trayectoAnio, $this->trayectoId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_trayecto
                    SET ani_id = :trayectoAnio, tra_numero = :trayectoNumero, tra_tipo = :trayectoTipo
                    WHERE tra_id = :trayectoId");

                    $stmt->bindParam(':trayectoAnio', $this->trayectoAnio, PDO::PARAM_STR);
                    $stmt->bindParam(':trayectoNumero', $this->trayectoNumero, PDO::PARAM_STR);
                    $stmt->bindParam(':trayectoTipo', $this->trayectoTipo, PDO::PARAM_STR);
                    $stmt->bindParam(':trayectoId', $this->trayectoId, PDO::PARAM_INT);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el trayecto correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado NO existe!';
        }
        return $r;
    }


    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteTrayecto($this->trayectoId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_trayecto
                SET tra_estado = 0
                WHERE tra_id = :trayectoId");
                $stmt->bindParam(':trayectoId', $this->trayectoId, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el trayecto correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado NO existe!';
        }
        return $r;
    }


    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT t.tra_numero, t.tra_id, t.tra_tipo, t.ani_id, a.ani_anio FROM tbl_trayecto t INNER JOIN tbl_anio a ON t.ani_id = a.ani_id WHERE t.tra_estado = 1");
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


    public function Existe($trayectoNumero, $trayectoAnio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_numero=:trayectoNumero AND ani_id=:trayectoAnio AND tra_estado = 1");

            $stmt->bindParam(':trayectoNumero', $trayectoNumero, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoAnio', $trayectoAnio, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El TRAYECTO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function existeOtro($trayectoNumero, $trayectoAnio, $trayectoId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_numero=:trayectoNumero AND ani_id=:trayectoAnio AND tra_id != :trayectoId AND tra_estado = 1");
            $stmt->bindParam(':trayectoNumero', $trayectoNumero, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoAnio', $trayectoAnio, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoId', $trayectoId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            return !empty($fila);
        } catch (Exception $e) {
            return false;
        }
    }

    public function ExisteTrayecto($trayectoId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_id=:trayectoId AND tra_estado = 1");

            $stmt->bindParam(':trayectoId', $trayectoId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El TRAYECTO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function obtenerAnio()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1 ORDER BY ani_anio DESC");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }
}