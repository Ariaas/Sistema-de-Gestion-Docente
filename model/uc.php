<?php
require_once('model/dbconnection.php');

class UC extends Connection
{
    private $idUC;
    private $codigoUC;
    private $nombreUC;
    private $creditosUC;
    private $independienteUC;
    private $asistidaUC;
    private $academicaUC;
    private $trayectoUC;
    private $ejeUC;
    private $areaUC;
    private $periodoUC;

    public function __construct()
    {
        parent::__construct();
    }

    public function getidUC()
    {
        return $this->idUC;
    }

    public function getcodigoUC()
    {
        return $this->codigoUC;
    }

    public function getnombreUC()
    {
        return $this->nombreUC;
    }

    public function getcreditosUC()
    {
        return $this->creditosUC;
    }

    public function getasistidaUC()
    {
        return $this->asistidaUC;
    }

    public function getacademicaUC()
    {
        return $this->academicaUC;
    }

    public function getindependienteUC()
    {
        return $this->independienteUC;
    }

    public function gettrayectoUC()
    {
        return $this->trayectoUC;
    }

    public function getejeUC()
    {
        return $this->ejeUC;
    }

    public function getareaUC()
    {
        return $this->areaUC;
    }

    public function getperiodoUC()
    {
        return $this->periodoUC;
    }

    public function setidUC($idUC)
    {
        $this->idUC = $idUC;
    }

    public function setcodigoUC($codigoUC)
    {
        $this->codigoUC = $codigoUC;
    }

    public function setnombreUC($nombreUC)
    {
        $this->nombreUC = $nombreUC;
    }

    public function setcreditosUC($creditosUC)
    {
        $this->creditosUC = $creditosUC;
    }

    public function setasistidaUC($asistidaUC)
    {
        $this->asistidaUC = $asistidaUC;
    }

    public function setacademicaUC($academicaUC)
    {
        $this->academicaUC = $academicaUC;
    }

    public function setindependienteUC($independienteUC)
    {
        $this->independienteUC = $independienteUC;
    }

    public function settrayectoUC($trayectoUC)
    {
        $this->trayectoUC = $trayectoUC;
    }

    public function setejeUC($ejeUC)
    {
        $this->ejeUC = $ejeUC;
    }

    public function setareaUC($areaUC)
    {
        $this->areaUC = $areaUC;
    }

    public function setperiodoUC($periodoUC)
    {
        $this->periodoUC = $periodoUC;
    }

    public function Registrar()
    {
        if ($this->codigoUC === null) {
            return array('resultado' => 'error', 'mensaje' => 'El código de la UC no puede estar vacío.');
        }
        if (trim($this->codigoUC) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El código de la UC no puede estar vacío.');
        }

        if ($this->nombreUC === null) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre de la UC no puede estar vacío.');
        }
        if (trim($this->nombreUC) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El nombre de la UC no puede estar vacío.');
        }

        if (strlen($this->codigoUC) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }
        if (strlen($this->codigoUC) > 20) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }

        if (strlen($this->nombreUC) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 200 caracteres.');
        }
        if (strlen($this->nombreUC) > 200) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 200 caracteres.');
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT uc_estado FROM tbl_uc WHERE uc_codigo = :codigoUC");
            $stmt->execute([':codigoUC' => $this->codigoUC]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                if ($existe['uc_estado'] == 1) {
                    return ['resultado' => 'registrar', 'mensaje' => 'ERROR! <br/> El código de la UC ya existe!'];
                }

                $stmt = $co->prepare("UPDATE tbl_uc SET 
                    uc_nombre = :nombreUC, uc_creditos = :creditosUC, uc_trayecto = :trayectoUC,
                    uc_periodo = :periodoUC, eje_nombre = :ejeNombre, area_nombre = :areaNombre, uc_estado = 1
                    WHERE uc_codigo = :codigoUC");
            } else {
                $stmt = $co->prepare("INSERT INTO tbl_uc 
                    (eje_nombre, area_nombre, uc_codigo, uc_nombre, uc_creditos, uc_trayecto, uc_periodo, uc_estado) 
                    VALUES (:ejeNombre, :areaNombre, :codigoUC, :nombreUC, :creditosUC, :trayectoUC, :periodoUC, 1)");
            }

            $stmt->execute([
                ':ejeNombre' => $this->ejeUC,
                ':areaNombre' => $this->areaUC,
                ':codigoUC' => $this->codigoUC,
                ':nombreUC' => $this->nombreUC,
                ':creditosUC' => $this->creditosUC,
                ':trayectoUC' => $this->trayectoUC,
                ':periodoUC' => $this->periodoUC
            ]);

            return ['resultado' => 'registrar', 'mensaje' => 'Registro Incluido!<br/>Se registró la unidad de curricular correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Modificar($codigoOriginal, $nuevoNombre = null)
    {
        if ($codigoOriginal === null) {
            return array('resultado' => 'error', 'mensaje' => 'El código original es requerido.');
        }
        if (trim($codigoOriginal) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El código original es requerido.');
        }

        if ($this->codigoUC === null) {
            return array('resultado' => 'error', 'mensaje' => 'El nuevo código no puede estar vacío.');
        }
        if (trim($this->codigoUC) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El nuevo código no puede estar vacío.');
        }

        $this->codigoUC = trim($this->codigoUC);

        if (strlen($this->codigoUC) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }
        if (strlen($this->codigoUC) > 20) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }

        if ($nuevoNombre !== null) {
            $this->nombreUC = $nuevoNombre;
        }

        if ($this->nombreUC === null) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre no puede estar vacío.');
        }
        if (trim($this->nombreUC) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El nombre no puede estar vacío.');
        }

        $this->nombreUC = trim($this->nombreUC);

        if (strlen($this->nombreUC) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 200 caracteres.');
        }
        if (strlen($this->nombreUC) > 200) {
            return array('resultado' => 'error', 'mensaje' => 'El nombre debe tener entre 3 y 200 caracteres.');
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $sql = "SELECT uc_codigo, uc_nombre, uc_creditos, uc_trayecto, uc_periodo, eje_nombre, area_nombre
                    FROM tbl_uc WHERE uc_codigo = :codigoOriginal AND uc_estado = 1";
            $stmt = $co->prepare($sql);
            $stmt->execute([':codigoOriginal' => $codigoOriginal]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datosOriginales) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> La unidad curricular no existe!'];
            }

            $noHayCambios =
                $datosOriginales['uc_codigo'] === $this->codigoUC &&
                $datosOriginales['uc_nombre'] === $this->nombreUC &&
                (int)$datosOriginales['uc_creditos'] === (int)$this->creditosUC &&
                (string)$datosOriginales['uc_trayecto'] === (string)$this->trayectoUC &&
                (string)$datosOriginales['uc_periodo'] === (string)$this->periodoUC &&
                trim($datosOriginales['eje_nombre']) === trim($this->ejeUC) &&
                trim($datosOriginales['area_nombre']) === trim($this->areaUC);

            if ($noHayCambios) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }

            if ($this->codigoUC !== $codigoOriginal && $this->Existe($this->codigoUC, null)) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> El código de la unidad curricular ya existe!'];
            }

            if ($this->codigoUC !== $codigoOriginal) {
                $co->prepare("DELETE FROM tbl_uc WHERE uc_codigo = :codigoUC AND uc_estado = 0")
                    ->execute([':codigoUC' => $this->codigoUC]);
            }

            $stmt = $co->prepare("UPDATE tbl_uc SET 
                uc_codigo = :codigoUC, uc_nombre = :nombreUC, uc_creditos = :creditosUC,
                uc_trayecto = :trayectoUC, uc_periodo = :periodoUC, eje_nombre = :ejeNombre, area_nombre = :areaNombre
                WHERE uc_codigo = :codigoOriginal");

            $stmt->execute([
                ':codigoUC' => $this->codigoUC,
                ':nombreUC' => $this->nombreUC,
                ':creditosUC' => $this->creditosUC,
                ':trayectoUC' => $this->trayectoUC,
                ':periodoUC' => $this->periodoUC,
                ':ejeNombre' => $this->ejeUC,
                ':areaNombre' => $this->areaUC,
                ':codigoOriginal' => $codigoOriginal
            ]);

            return ['resultado' => 'modificar', 'mensaje' => 'Registro Modificado!<br/>Se modificó la unidad curricular correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Eliminar()
    {
        if ($this->codigoUC === null) {
            return array('resultado' => 'error', 'mensaje' => 'El código no puede estar vacío.');
        }
        if (trim($this->codigoUC) === '') {
            return array('resultado' => 'error', 'mensaje' => 'El código no puede estar vacío.');
        }

        $this->codigoUC = trim($this->codigoUC);

        if (strlen($this->codigoUC) < 3) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }
        if (strlen($this->codigoUC) > 20) {
            return array('resultado' => 'error', 'mensaje' => 'El código debe tener entre 3 y 20 caracteres.');
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!$this->Existe($this->codigoUC, null)) {
            return ['resultado' => 'eliminar', 'mensaje' => 'ERROR! <br/> La Unidad Curricular no existe!'];
        }

        try {
            $co->prepare("UPDATE tbl_uc SET uc_estado = 0 WHERE uc_codigo = :codigoUC")
                ->execute([':codigoUC' => $this->codigoUC]);

            return ['resultado' => 'eliminar', 'mensaje' => 'Registro Eliminado!<br/>Se eliminó la unidad curricular correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->query("SELECT uc_codigo, uc_nombre, uc_creditos, uc_trayecto, 
                uc_periodo, uc_estado, eje_nombre, area_nombre FROM tbl_uc");

            return ['resultado' => 'consultar', 'mensaje' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Existe($codigoUC, $codigoExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $sql = "SELECT COUNT(*) FROM tbl_uc WHERE uc_codigo = :codigoUC AND uc_estado = 1";
            $params = [':codigoUC' => $codigoUC];

            if ($codigoExcluir !== null) {
                $sql .= " AND uc_codigo != :codigoExcluir";
                $params[':codigoExcluir'] = $codigoExcluir;
            }

            $stmt = $co->prepare($sql);
            $stmt->execute($params);

            if ($stmt->fetchColumn() > 0) {
                return ['resultado' => 'existe', 'mensaje' => 'La unidad de curricular YA existe!'];
            }
            return [];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function obtenerEje()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            return $co->query("SELECT eje_nombre FROM tbl_eje WHERE eje_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        } finally {
            $co = null;
        }
    }

    public function obtenerArea()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            return $co->query("SELECT area_nombre FROM tbl_area WHERE area_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        } finally {
            $co = null;
        }
    }

    public function verificarEnHorario($ucCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT COUNT(*) FROM uc_horario WHERE uc_codigo = :ucCodigo");
            $stmt->execute([':ucCodigo' => $ucCodigo]);
            $cantidad = $stmt->fetchColumn();

            return [
                'resultado' => $cantidad > 0 ? 'en_horario' : 'no_en_horario',
                'mensaje' => $cantidad > 0 ? 'La UC está en un horario.' : 'La UC no está en un horario.'
            ];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Activar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $co->prepare("UPDATE tbl_uc SET uc_estado = 1 WHERE uc_codigo = :codigoUC")
                ->execute([':codigoUC' => $this->codigoUC]);

            return ['resultado' => 'activar', 'mensaje' => 'Registro Reactivado!<br/>Se activó la unidad curricular correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }
}
