<?php
require_once('model/dbconnection.php');

class Fase extends Connection
{
    private $fase_id;
    private $tra_id;
    private $fase_numero;
    private $fase_apertura;
    private $fase_cierre;

    public function __construct()
    {
        parent::__construct();
    }

    // Getters
    public function getId()
    {
        return $this->fase_id;
    }
    public function getTraId()
    {
        return $this->tra_id;
    }
    public function getFaseNumero()
    {
        return $this->fase_numero;
    }
    public function getFaseApertura()
    {
        return $this->fase_apertura;
    }
    public function getFaseCierre()
    {
        return $this->fase_cierre;
    }

    // Setters
    public function setId($fase_id)
    {
        $this->fase_id = $fase_id;
    }
    public function setTraId($tra_id)
    {
        $this->tra_id = $tra_id;
    }
    public function setFaseNumero($fase_numero)
    {
        $this->fase_numero = $fase_numero;
    }
    public function setFaseApertura($fase_apertura)
    {
        $this->fase_apertura = $fase_apertura;
    }
    public function setFaseCierre($fase_cierre)
    {
        $this->fase_cierre = $fase_cierre;
    }

    public function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->tra_id, $this->fase_numero)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_fase (
                    tra_id, fase_numero, fase_apertura, fase_cierre, fase_estado
                ) VALUES (
                    :tra_id, :fase_numero, :fase_apertura, :fase_cierre, 1
                )");

                $stmt->bindParam(':tra_id', $this->tra_id, PDO::PARAM_INT);
                $stmt->bindParam(':fase_numero', $this->fase_numero, PDO::PARAM_STR);
                $stmt->bindParam(':fase_apertura', $this->fase_apertura, PDO::PARAM_STR);
                $stmt->bindParam(':fase_cierre', $this->fase_cierre, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>La fase se registró correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La fase ya existe para el trayecto seleccionado.';
        }

        return $r;
    }

    public function Modificar()
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            // Verificar si ya existe otra fase con el mismo número para el mismo trayecto, excluyendo la actual
            $checkStmt = $co->prepare("SELECT COUNT(*) FROM tbl_fase WHERE tra_id = :tra_id AND fase_numero = :fase_numero AND fase_id != :fase_id AND fase_estado = 1");
            $checkStmt->bindParam(':tra_id', $this->tra_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':fase_numero', $this->fase_numero, PDO::PARAM_STR);
            $checkStmt->bindParam(':fase_id', $this->fase_id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> Ya existe una fase con ese número para el trayecto seleccionado.';
                return $r;
            }

            $stmt = $co->prepare("UPDATE tbl_fase SET
                tra_id = :tra_id,
                fase_numero = :fase_numero,
                fase_apertura = :fase_apertura,
                fase_cierre = :fase_cierre
                WHERE fase_id = :fase_id");

            $stmt->bindParam(':tra_id', $this->tra_id, PDO::PARAM_INT);
            $stmt->bindParam(':fase_numero', $this->fase_numero, PDO::PARAM_STR);
            $stmt->bindParam(':fase_apertura', $this->fase_apertura, PDO::PARAM_STR);
            $stmt->bindParam(':fase_cierre', $this->fase_cierre, PDO::PARAM_STR);
            $stmt->bindParam(':fase_id', $this->fase_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>La fase se modificó correctamente!';
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
            $stmt = $co->prepare("UPDATE tbl_fase SET fase_estado = 0 WHERE fase_id = :fase_id");
            $stmt->bindParam(':fase_id', $this->fase_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la fase correctamente!';
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
            $stmt = $co->query("SELECT f.fase_id, f.fase_numero, f.fase_apertura, f.fase_cierre, t.tra_id, t.tra_numero, a.ani_anio 
                                FROM tbl_fase f
                                JOIN tbl_trayecto t ON f.tra_id = t.tra_id
                                JOIN tbl_anio a ON t.ani_id = a.ani_id
                                WHERE f.fase_estado = 1 ORDER BY a.ani_anio DESC, t.tra_numero, f.fase_numero ASC");
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
            $stmt = $co->query("SELECT t.tra_id, t.tra_numero, t.tra_tipo, a.ani_anio 
                                FROM tbl_trayecto t
                                JOIN tbl_anio a ON t.ani_id = a.ani_id
                                WHERE t.tra_estado = 1 AND a.ani_estado = 1 
                                ORDER BY a.ani_anio DESC, t.tra_numero ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar_trayectos';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Existe($tra_id, $fase_numero)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT * FROM tbl_fase WHERE tra_id = :tra_id AND fase_numero = :fase_numero AND fase_estado = 1");
            $stmt->bindParam(':tra_id', $tra_id, PDO::PARAM_INT);
            $stmt->bindParam(':fase_numero', $fase_numero, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
