<?php
require_once('model/dbconnection.php');

class Trayecto extends Connection
{

    private $trayectoNumero;
    private $trayectoAnio;
    private $trayectoId;


    //Construct
    public function __construct($trayectoNumero = null, $trayectoAnio = null, $trayectoId = null)
    {
        parent::__construct();

        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoId = $trayectoId;
    }

    //Getters 
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
    //Setters
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

    //Methods

    /// Registrar

    function Registrar()
    {
        $r = array();

        if (!$this->existe($this->trayectoNumero, $this->trayectoAnio)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_trayecto (
                    tra_numero,
                    tra_anio,
                    tra_estado
                ) VALUES (
                    :trayectoNumero,
                    :trayectoAnio,
                    1
                )");

                $stmt->bindParam(':trayectoNumero', $this->trayectoNumero, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoAnio', $this->trayectoAnio, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el trayecto correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // 6. Cerrar la conexión
            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado YA existe!';
        }

        return $r;
    }

    /// Actualizar

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteTrayecto($this->trayectoId)) {
            if (!$this->existe($this->trayectoNumero, $this->trayectoAnio)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_trayecto
                    SET tra_anio = :trayectoAnio, tra_numero = :trayectoNumero
                    WHERE tra_id = :trayectoId");

                    $stmt->bindParam(':trayectoAnio', $this->trayectoAnio, PDO::PARAM_STR);
                    $stmt->bindParam(':trayectoNumero', $this->trayectoNumero, PDO::PARAM_STR);
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

    /// Eliminar

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

    /// Listar

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT tra_numero, tra_anio, tra_id FROM tbl_trayecto WHERE tra_estado = 1");
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

    /// Consultar exitencia

    public function Existe($trayectoNumero, $trayectoAnio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_numero=:trayectoNumero AND tra_anio=:trayectoAnio AND tra_estado = 1");

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
        // Se cierra la conexión
        $co = null;
        return $r;
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
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    public function obtenerTrayectos()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_estado = 1");
        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }
}