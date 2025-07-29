<?php
require_once('model/dbconnection.php');

class Espacio extends Connection
{

    private $numeroEspacio;
    private $edificioEspacio;
    private $tipoEspacio;



    public function __construct($numeroEspacio = null, $edificioEspacio = null, $tipoEspacio = null)
    {
        parent::__construct();

        $this->numeroEspacio = $numeroEspacio;
        $this->edificioEspacio = $edificioEspacio;
        $this->tipoEspacio = $tipoEspacio;
    }


    public function getNumero()
    {
        return $this->numeroEspacio;
    }
    public function getEdificio()
    {
        return $this->edificioEspacio;
    }
    public function getTipo()
    {
        return $this->tipoEspacio;
    }

    public function setNumero($numeroEspacio)
    {
        $this->numeroEspacio = $numeroEspacio;
    }
    public function setEdificio($edificioEspacio)
    {
        $this->edificioEspacio = $edificioEspacio;
    }
    public function setTipo($tipoEspacio)
    {
        $this->tipoEspacio = $tipoEspacio;
    }



    function Registrar()
    {
        $r = array();

        if ($this->existeDirecto($this->numeroEspacio, $this->edificioEspacio, $this->tipoEspacio)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO colocado YA existe!';
            return $r;
        }


        if ($this->existeInactivo($this->numeroEspacio, $this->edificioEspacio, $this->tipoEspacio)) {

            return $this->Reactivar();
        }


        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("INSERT INTO tbl_espacio (
                esp_numero,
                esp_edificio,
                esp_tipo,
                esp_estado
            ) VALUES (
                :numeroEspacio,
                :edificioEspacio,
                :tipoEspacio,
                1
            )");

            $stmt->bindParam(':numeroEspacio', $this->numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $this->edificioEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el espacio correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }


    function Reactivar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_espacio
            SET esp_tipo = :tipoEspacio, esp_estado = 1
            WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio");

            $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':numeroEspacio', $this->numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $this->edificioEspacio, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el espacio correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }


    function Modificar($originalNumero, $originalEdificio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        if (($originalNumero != $this->numeroEspacio || $originalEdificio != $this->edificioEspacio) && $this->existeDirecto($this->numeroEspacio, $this->edificioEspacio, $this->tipoEspacio)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> Ya existe un espacio con el nuevo número y edificio.';
            return $r;
        }

        try {
            $stmt = $co->prepare("UPDATE tbl_espacio
            SET esp_numero = :numeroEspacio,
                esp_edificio = :edificioEspacio,
                esp_tipo = :tipoEspacio
            WHERE esp_numero = :originalNumero AND esp_edificio = :originalEdificio AND esp_estado = 1");

            $stmt->bindParam(':tipoEspacio', $this->tipoEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':numeroEspacio', $this->numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $this->edificioEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':originalNumero', $originalNumero, PDO::PARAM_STR);
            $stmt->bindParam(':originalEdificio', $originalEdificio, PDO::PARAM_STR);

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

        if ($this->existeDirecto($this->numeroEspacio, $this->edificioEspacio, $this->tipoEspacio)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_espacio
                SET esp_estado = 0
                WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio");

                $stmt->bindParam(':numeroEspacio', $this->numeroEspacio, PDO::PARAM_STR);
                $stmt->bindParam(':edificioEspacio', $this->edificioEspacio, PDO::PARAM_STR);

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

            $stmt = $co->query("SELECT CONCAT(CASE WHEN LOWER(esp_tipo) = 'laboratorio' THEN 'L' ELSE SUBSTRING(esp_edificio, 1, 1) END, '-', esp_numero) AS esp_codigo, esp_numero, esp_edificio, esp_tipo FROM tbl_espacio WHERE esp_estado = 1");
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


    public function Existe($numeroEspacio, $edificioEspacio, $tipoEspacio, $numeroEspacioExcluir = null, $edificioEspacioExcluir = null, $tipoEspacioExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT 1 FROM tbl_espacio WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio AND esp_estado = 1";
            if ($numeroEspacioExcluir !== null && $edificioEspacioExcluir !== null && $tipoEspacioExcluir !== null) {
                $sql .= " AND NOT (esp_numero = :numeroEspacioExcluir AND esp_edificio = :edificioEspacioExcluir AND esp_tipo = :tipoEspacioExcluir)";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':numeroEspacio', $numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $edificioEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':tipoEspacio', $tipoEspacio, PDO::PARAM_STR);
            if ($numeroEspacioExcluir !== null && $edificioEspacioExcluir !== null && $tipoEspacioExcluir !== null) {
                $stmt->bindParam(':numeroEspacioExcluir', $numeroEspacioExcluir, PDO::PARAM_STR);
                $stmt->bindParam(':edificioEspacioExcluir', $edificioEspacioExcluir, PDO::PARAM_STR);
                $stmt->bindParam(':tipoEspacioExcluir', $tipoEspacioExcluir, PDO::PARAM_STR);
            }
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


    private function existeDirecto($numeroEspacio, $edificioEspacio, $tipoEspacio)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_espacio WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio AND esp_estado = 1");
            $stmt->bindParam(':numeroEspacio', $numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $edificioEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':tipoEspacio', $tipoEspacio, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en existeDirecto: " . $e->getMessage());
            return false;
        }
    }


    private function existeInactivo($numeroEspacio, $edificioEspacio, $tipoEspacio)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_espacio WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio AND esp_estado = 0");
            $stmt->bindParam(':numeroEspacio', $numeroEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':edificioEspacio', $edificioEspacio, PDO::PARAM_STR);
            $stmt->bindParam(':tipoEspacio', $tipoEspacio, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en existeInactivo: " . $e->getMessage());
            return false;
        }
    }
}
