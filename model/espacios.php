<?php
require_once('model/dbconnection.php');

class Espacio extends Connection
{

    private $codigoEspacio;
    private $tipoEspacio;


    //Construct
    public function __construct($codigoEspacio = null, $tipoEspacio = null)
    {
        parent::__construct();

        $this->codigoEspacio = $codigoEspacio;
        $this->tipoEspacio = $tipoEspacio;
    }

    //Getters 
    public function getCodigo()
    {
        return $this->codigoEspacio;
    }
    public function getTipo()
    {
        return $this->tipoEspacio;
    }
    //Setters
    public function setCodigo($codigoEspacio)
    {
        $this->codigoEspacio = $codigoEspacio;
    }
    public function setTipo($tipoEspacio)
    {
        $this->tipoEspacio = $tipoEspacio;
    }

    //Methods

    /// Registrar

    function Registrar()
    {
        $r = array();

        if (!$this->existe($this->codigoEspacio)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_espacio (
                    esp_codigo,
                    esp_tipo,
                    esp_estado
                ) VALUES (
                    :codigoEspacio,
                    :tipoEspacio,
                    1
                )");

                $stmt->bindParam(':codigoEspacio', $this->codigoEspacio, PDO::PARAM_STR);
                $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el espacio correctamente!';
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
        if ($this->existe($this->codigoEspacio)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_espacio
                SET esp_tipo = :tipoEspacio
                WHERE esp_codigo = :codigoEspacio");

                $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);
                $stmt->bindParam(':codigoEspacio', $this->codigoEspacio, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el espacio correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO colocado NO existe!';
        }
        return $r;
    }

    /// Eliminar

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->existe($this->codigoEspacio)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_espacio
                SET esp_estado = 0
                WHERE esp_codigo = :codigoEspacio");

                $stmt->bindParam(':codigoEspacio', $this->codigoEspacio, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el espacio correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO colocado NO existe!';
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
            $stmt = $co->query("SELECT esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1");
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

    public function Existe($codigoEspacio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_espacio WHERE esp_codigo=:codigoEspacio AND esp_estado = 1");
            $stmt->bindParam(':codigoEspacio', $codigoEspacio, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El TRAYECTO colocado YA existe!';
            } 
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Se cierra la conexión
        $co = null;
        return $r;
    }
}
