<?php
require_once('model/dbconnection.php');

class Espacio extends Connection
{

    private $numeroEspacio;
    private $edificioEspacio;
    private $tipoEspacio;
    private $numeroEspacioOriginal;
    private $edificioEspacioOriginal;
    private $tipoEspacioOriginal;

    public function __construct()
    {
        parent::__construct();
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
        $this->numeroEspacio = trim($numeroEspacio);
    }
    public function setEdificio($edificioEspacio)
    {
        $this->edificioEspacio = trim($edificioEspacio);
    }
    public function setTipo($tipoEspacio)
    {
        $this->tipoEspacio = trim($tipoEspacio);
    }
    public function setNumeroOriginal($numero)
    {
        $this->numeroEspacioOriginal = trim($numero);
    }
    public function setEdificioOriginal($edificio)
    {
        $this->edificioEspacioOriginal = trim($edificio);
    }
    public function setTipoOriginal($tipo)
    {
        $this->tipoEspacioOriginal = trim($tipo);
    }



    function Registrar()
    {
        $r = [];

        if ($this->numeroEspacio === null || trim($this->numeroEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->edificioEspacio === null || trim($this->edificioEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->tipoEspacio === null || trim($this->tipoEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo del espacio no puede estar vacío.';
            return $r;
        }

        if (strlen($this->numeroEspacio) < 1 || strlen($this->numeroEspacio) > 10) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio debe tener entre 1 y 10 caracteres.';
            return $r;
        }

        if (strlen($this->edificioEspacio) < 1 || strlen($this->edificioEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio debe tener entre 1 y 50 caracteres.';
            return $r;
        }

        if (strlen($this->tipoEspacio) < 3 || strlen($this->tipoEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo debe tener entre 3 y 50 caracteres.';
            return $r;
        }

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
            $co->prepare("INSERT INTO tbl_espacio (esp_numero, esp_edificio, esp_tipo, esp_estado) VALUES (:numeroEspacio, :edificioEspacio, :tipoEspacio, 1)")
                ->execute([
                    ':numeroEspacio' => $this->numeroEspacio,
                    ':edificioEspacio' => $this->edificioEspacio,
                    ':tipoEspacio' => $this->tipoEspacio
                ]);

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el espacio correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }


    function Reactivar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = [];
        try {
            $co->prepare("UPDATE tbl_espacio SET esp_tipo = :tipoEspacio, esp_estado = 1 WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio")
                ->execute([
                    ':tipoEspacio' => $this->tipoEspacio,
                    ':numeroEspacio' => $this->numeroEspacio,
                    ':edificioEspacio' => $this->edificioEspacio
                ]);

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el espacio correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }


    function Modificar($originalNumero, $originalEdificio, $originalTipo)
    {
        $r = [];

        if ($originalNumero === null || trim($originalNumero) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número original del espacio es requerido.';
            return $r;
        }

        if ($originalEdificio === null || trim($originalEdificio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio original del espacio es requerido.';
            return $r;
        }

        if ($originalTipo === null || trim($originalTipo) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo original del espacio es requerido.';
            return $r;
        }

        if ($this->numeroEspacio === null || trim($this->numeroEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->edificioEspacio === null || trim($this->edificioEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->tipoEspacio === null || trim($this->tipoEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo del espacio no puede estar vacío.';
            return $r;
        }

        if (strlen($this->numeroEspacio) < 1 || strlen($this->numeroEspacio) > 10) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio debe tener entre 1 y 10 caracteres.';
            return $r;
        }

        if (strlen($this->edificioEspacio) < 1 || strlen($this->edificioEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio debe tener entre 1 y 50 caracteres.';
            return $r;
        }

        if (strlen($this->tipoEspacio) < 3 || strlen($this->tipoEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo debe tener entre 3 y 50 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT esp_numero, esp_edificio, esp_tipo FROM tbl_espacio WHERE esp_numero = :numero AND esp_edificio = :edificio AND esp_tipo = :tipo AND esp_estado = 1");
            $stmt->execute([
                ':numero' => $originalNumero,
                ':edificio' => $originalEdificio,
                ':tipo' => $originalTipo
            ]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datosOriginales) {
                return ['resultado' => 'error', 'mensaje' => 'El espacio no existe.'];
            }

            if (
                $datosOriginales['esp_numero'] === $this->numeroEspacio &&
                $datosOriginales['esp_edificio'] === $this->edificioEspacio &&
                $datosOriginales['esp_tipo'] === $this->tipoEspacio
            ) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }

        if ($this->existeDirecto($this->numeroEspacio, $this->edificioEspacio, $this->tipoEspacio)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR! <br/> El ESPACIO colocado YA existe!';
            return $r;
        }

        try {
            $co->prepare("DELETE FROM tbl_espacio WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio AND esp_estado = 0")
                ->execute([
                    ':numeroEspacio' => $this->numeroEspacio,
                    ':edificioEspacio' => $this->edificioEspacio,
                    ':tipoEspacio' => $this->tipoEspacio
                ]);

            $stmt = $co->prepare("UPDATE tbl_espacio SET esp_numero = :numeroEspacio, esp_edificio = :edificioEspacio, esp_tipo = :tipoEspacio WHERE esp_numero = :originalNumero AND esp_edificio = :originalEdificio AND esp_tipo = :originalTipo AND esp_estado = 1");
            $stmt->execute([
                ':tipoEspacio' => $this->tipoEspacio,
                ':numeroEspacio' => $this->numeroEspacio,
                ':edificioEspacio' => $this->edificioEspacio,
                ':originalNumero' => $originalNumero,
                ':originalEdificio' => $originalEdificio,
                ':originalTipo' => $originalTipo
            ]);

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
        } finally {
            $co = null;
        }
        return $r;
    }


    function Eliminar()
    {
        $r = [];

        if ($this->numeroEspacio === null || trim($this->numeroEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->edificioEspacio === null || trim($this->edificioEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio del espacio no puede estar vacío.';
            return $r;
        }

        if ($this->tipoEspacio === null || trim($this->tipoEspacio) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo del espacio no puede estar vacío.';
            return $r;
        }

        if (strlen($this->numeroEspacio) < 1 || strlen($this->numeroEspacio) > 10) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El número del espacio debe tener entre 1 y 10 caracteres.';
            return $r;
        }

        if (strlen($this->edificioEspacio) < 1 || strlen($this->edificioEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El edificio debe tener entre 1 y 50 caracteres.';
            return $r;
        }

        if (strlen($this->tipoEspacio) < 3 || strlen($this->tipoEspacio) > 50) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo debe tener entre 3 y 50 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT esp_estado FROM tbl_espacio WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio");
            $stmt->execute([
                ':numeroEspacio' => $this->numeroEspacio,
                ':edificioEspacio' => $this->edificioEspacio,
                ':tipoEspacio' => $this->tipoEspacio
            ]);
            $espacio = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$espacio) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'ERROR! <br/> El ESPACIO a eliminar NO existe!';
                return $r;
            }

            if ($espacio['esp_estado'] == 0) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'El espacio ya está desactivado.';
                return $r;
            }

            $co->prepare("UPDATE tbl_espacio SET esp_estado = 0 WHERE esp_numero = :numeroEspacio AND esp_edificio = :edificioEspacio AND esp_tipo = :tipoEspacio")
                ->execute([
                    ':numeroEspacio' => $this->numeroEspacio,
                    ':edificioEspacio' => $this->edificioEspacio,
                    ':tipoEspacio' => $this->tipoEspacio
                ]);

            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el espacio correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = [];
        try {
            $stmt = $co->query("SELECT CONCAT(CASE WHEN LOWER(esp_tipo) = 'laboratorio' THEN 'L' ELSE SUBSTRING(esp_edificio, 1, 1) END, '-', esp_numero) AS esp_codigo, esp_numero, esp_edificio, esp_tipo, esp_estado FROM tbl_espacio ORDER BY esp_edificio, esp_numero");
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }


    public function Existe($numeroEspacio, $edificioEspacio, $tipoEspacio, $numeroEspacioExcluir = null, $edificioEspacioExcluir = null, $tipoEspacioExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = [];
        try {
            if (
                $numeroEspacioExcluir !== null &&
                $edificioEspacioExcluir !== null &&
                $tipoEspacioExcluir !== null &&
                $numeroEspacio == $numeroEspacioExcluir &&
                $edificioEspacio == $edificioEspacioExcluir &&
                $tipoEspacio == $tipoEspacioExcluir
            ) {
                $r['resultado'] = 'no_existe';
                $r['mensaje'] = 'El espacio no existe.';
                return $r;
            }

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
