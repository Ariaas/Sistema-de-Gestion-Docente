<?php
require_once('model/dbconnection.php');

class Cohorte extends Connection
{

    private $cohNumero;
    private $cohId;


    public function __construct($cohNumero = null, $cohId = null)
    {
        parent::__construct();

        $this->cohNumero = $cohNumero;
        $this->cohId = $cohId;
    }

    public function getCohorte()
    {
        return $this->cohNumero;
    }
    public function getId()
    {
        return $this->cohId;
    }
    public function setCohorte($cohNumero)
    {
        $this->cohNumero = $cohNumero;
    }
    public function setId($cohId)
    {
        $this->cohId = $cohId;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->cohNumero)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_cohorte (
                    coh_numero,
                    coh_estado
                ) VALUES (
                    :cohNumero,
                    1
                )");

                $stmt->bindParam(':cohNumero', $this->cohNumero, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró la COHORTE correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La COHORTE colocada YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->cohId)) {
            if (!$this->existe($this->cohNumero)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_cohorte
                    SET coh_numero = :cohNumero 
                    WHERE coh_id = :cohId");

                    $stmt->bindParam(':cohId', $this->cohId, PDO::PARAM_INT);
                    $stmt->bindParam(':cohNumero', $this->cohNumero, PDO::PARAM_STR);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la COHORTE correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> La COHORTE colocada YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La COHORTE colocada NO existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->cohId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_cohorte
                SET coh_estado = 0
                WHERE coh_id = :cohId");

                $stmt->bindParam(':cohId', $this->cohId, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la COHORTE correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La COHORTE colocada NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT coh_numero, coh_id FROM tbl_cohorte WHERE coh_estado = 1");
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

    public function ExisteId($cohId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_cohorte WHERE coh_id=:cohId AND coh_estado = 1");
            $stmt->bindParam(':cohId', $cohId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La COHORTE colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existe($cohNumero)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_cohorte WHERE coh_numero=:cohNumero AND coh_estado = 1");
            $stmt->bindParam(':cohNumero', $cohNumero, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La COHORTE colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
