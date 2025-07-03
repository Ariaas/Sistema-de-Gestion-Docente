<?php
require_once('model/dbconnection.php');

class Espacio extends Connection
{

    private $codigoEspacio;
    private $tipoEspacio;



    public function __construct($codigoEspacio = null, $tipoEspacio = null)
    {
        parent::__construct();

        $this->codigoEspacio = $codigoEspacio;
        $this->tipoEspacio = $tipoEspacio;
    }


    public function getCodigo()
    {
        return $this->codigoEspacio;
    }
    public function getTipo()
    {
        return $this->tipoEspacio;
    }
    
    public function setCodigo($codigoEspacio)
    {
        $this->codigoEspacio = $codigoEspacio;
    }
    public function setTipo($tipoEspacio)
    {
        $this->tipoEspacio = $tipoEspacio;
    }



    function Registrar()
    {
        $r = array();

       
        if (!$this->existeDirecto($this->codigoEspacio)) { 

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

            $co = null;
        } else {
            $r['resultado'] = 'error'; 
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            $stmt = $co->prepare("UPDATE tbl_espacio
            SET esp_tipo = :tipoEspacio
            WHERE esp_codigo = :codigoEspacio AND esp_estado = 1"); 

            $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':codigoEspacio', $this->codigoEspacio, PDO::PARAM_STR);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el espacio correctamente!';
            } else {
                $r['resultado'] = 'error'; 
                $r['mensaje'] = 'ERROR! <br/> El ESPACIO a modificar NO existe o no hubo cambios.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }


    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
      
        if ($this->existeDirecto($this->codigoEspacio)) { 
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
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO a eliminar NO existe!';
        }
        return $r;
    }

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


    public function Existe($codigoEspacio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT 1 FROM tbl_espacio WHERE esp_codigo = :codigoEspacio AND esp_estado = 1");
            $stmt->bindParam(':codigoEspacio', $codigoEspacio, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC); 

            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El ESPACIO colocado YA existe!';
            } else {
                $r['resultado'] = 'no_existe'; 
                $r['mensaje'] = 'El espacio no existe.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

   
    private function existeDirecto($codigoEspacio) {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_espacio WHERE esp_codigo = :codigoEspacio AND esp_estado = 1");
            $stmt->bindParam(':codigoEspacio', $codigoEspacio, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en existeDirecto: " . $e->getMessage());
            return false;
        }
    }
}
?>