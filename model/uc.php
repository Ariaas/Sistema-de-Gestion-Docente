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
    private $electivaUC;

    public function __construct($idUC = null, $codigoUC = null, $independienteUC = null, $creditosUC = null, $nombreUC = null, $asistidaUC = null, $academicaUC = null, $trayectoUC = null, $ejeUC = null, $areaUC = null, $periodoUC = null, $electivaUC = null)
    {
        parent::__construct();

        $this->idUC = $idUC;
        $this->codigoUC = $codigoUC;
        $this->independienteUC = $independienteUC;
        $this->creditosUC = $creditosUC;
        $this->nombreUC = $nombreUC;
        $this->asistidaUC = $asistidaUC;
        $this->academicaUC = $academicaUC;
        $this->trayectoUC = $trayectoUC;
        $this->ejeUC = $ejeUC;
        $this->areaUC = $areaUC;
        $this->periodoUC = $periodoUC;
        $this->electivaUC = $electivaUC;
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

    public function getelectivaUC()
    {
        return $this->electivaUC;
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

    public function setelectivaUC($electivaUC)
    {
        $this->electivaUC = $electivaUC;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->codigoUC)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_uc (
                    eje_nombre,
                    area_nombre,
                    uc_codigo,
                    uc_nombre,
                    uc_creditos,
                    uc_trayecto,
                    uc_periodo,
                    uc_electiva,
                    uc_estado
                ) VALUES (
                    :ejeNombre,
                    :areaNombre,
                    :codigoUC,
                    :nombreUC,
                    :creditosUC,
                    :trayectoUC,
                    :periodoUC,
                    :electivaUC,
                    1
                )");
                $stmt->bindParam(':ejeNombre', $this->ejeUC, PDO::PARAM_STR);
                $stmt->bindParam(':areaNombre', $this->areaUC, PDO::PARAM_STR);
                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->bindParam(':nombreUC', $this->nombreUC, PDO::PARAM_STR);
                $stmt->bindParam(':creditosUC', $this->creditosUC, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoUC', $this->trayectoUC, PDO::PARAM_STR);
                $stmt->bindParam(':periodoUC', $this->periodoUC, PDO::PARAM_STR);
                $stmt->bindParam(':electivaUC', $this->electivaUC, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró la unidad de curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El código de la UC ya existe!';
        }

        return $r;
    }

    function Modificar($codigoOriginal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if (!$this->Existe($this->codigoUC, $codigoOriginal)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_uc
                    SET uc_codigo = :codigoUC, 
                    uc_nombre = :nombreUC,
                    uc_creditos = :creditosUC,
                    uc_trayecto = :trayectoUC,
                    uc_periodo = :periodoUC,
                    uc_electiva = :electivaUC,
                    eje_nombre = :ejeNombre,
                    area_nombre = :areaNombre
                    WHERE uc_codigo = :codigoOriginal");

                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->bindParam(':nombreUC', $this->nombreUC, PDO::PARAM_STR);
                $stmt->bindParam(':creditosUC', $this->creditosUC, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoUC', $this->trayectoUC, PDO::PARAM_STR);
                $stmt->bindParam(':periodoUC', $this->periodoUC, PDO::PARAM_STR);
                $stmt->bindParam(':electivaUC', $this->electivaUC, PDO::PARAM_INT);
                $stmt->bindParam(':ejeNombre', $this->ejeUC, PDO::PARAM_STR);
                $stmt->bindParam(':areaNombre', $this->areaUC, PDO::PARAM_STR);
                $stmt->bindParam(':codigoOriginal', $codigoOriginal, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la unidad curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El código de la unidad curricular ya existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->Existe($this->codigoUC, null)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_uc
                SET uc_estado = 0
                WHERE uc_codigo = :codigoUC");
                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la unidad curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La Unidad Curricular no existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'consultar') {
                $stmt = $co->query("SELECT 
                    uc.uc_codigo,
                    uc.uc_nombre,
                    uc.uc_creditos,
                    uc.uc_trayecto,
                    uc.uc_periodo,
                    uc.uc_electiva,
                    uc.uc_estado,
                    uc.eje_nombre,
                    uc.area_nombre
                FROM tbl_uc uc
                WHERE uc.uc_estado = 1");
            } elseif ($accion === 'consultarAsignacion') {
                $uc_codigo = $_POST['uc_codigo'] ?? null;
                $sql = "SELECT 
                    uc.uc_codigo,
                    uc.uc_nombre,
                    d.doc_cedula,
                    d.doc_nombre,
                    d.doc_apellido
                FROM tbl_uc uc
                LEFT JOIN uc_docente ud ON uc.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                LEFT JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula AND d.doc_estado = 1
                WHERE uc.uc_estado = 1";
                if ($uc_codigo) {
                    $sql .= " AND uc.uc_codigo = :uc_codigo";
                    $stmt = $co->prepare($sql);
                    $stmt->execute([':uc_codigo' => $uc_codigo]);
                } else {
                    $stmt = $co->query($sql);
                }
            } else {
                throw new Exception("Acción inválida: $accion");
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = $accion;
            $r['mensaje']   = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje']   = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Existe($codigoUC, $codigoExcluir = NULL)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_uc WHERE uc_codigo=:codigoUC AND uc_estado = 1";
            if ($codigoExcluir !== NULL) {
                $sql .= " AND uc_codigo != :codigoExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':codigoUC', $codigoUC, PDO::PARAM_STR);
            if ($codigoExcluir !== NULL) {
                $stmt->bindParam(':codigoExcluir', $codigoExcluir, PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La unidad de curricular YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function obtenerEje()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT eje_nombre FROM tbl_eje WHERE eje_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    function obtenerArea()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT area_nombre FROM tbl_area WHERE area_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    function obtenerDocente()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    function Asignar($asignacionesJSON, $ucsJSON)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $co->beginTransaction();

            $asignaciones = json_decode($asignacionesJSON, true);
            $ucCodigo = json_decode($ucsJSON, true)[0];

            if (empty($asignaciones) || empty($ucCodigo)) {
                throw new Exception("Debe seleccionar al menos un docente y una unidad curricular.");
            }

            $conflictos = [];

            foreach ($asignaciones as $asignacion) {
                $docenteCedula = $asignacion['id'];
                $stmtCheck = $co->prepare("SELECT COUNT(*) FROM uc_docente WHERE doc_cedula = :docCedula AND uc_codigo = :ucCodigo AND uc_doc_estado = 1");
                $stmtCheck->execute([':docCedula' => (int)$docenteCedula, ':ucCodigo' => $ucCodigo]);

                if ($stmtCheck->fetchColumn() > 0) {
                    $stmtGetName = $co->prepare("SELECT CONCAT(doc_nombre, ' ', doc_apellido) as nombre_completo FROM tbl_docente WHERE doc_cedula = :docCedula");
                    $stmtGetName->execute([':docCedula' => (int)$docenteCedula]);
                    $docente = $stmtGetName->fetch(PDO::FETCH_ASSOC);
                    if ($docente) {
                        $conflictos[] = $docente['nombre_completo'];
                    }
                }
            }

            if (!empty($conflictos)) {
                $nombres = implode(', ', $conflictos);
                $mensaje = count($conflictos) > 1
                    ? "Los docentes '$nombres' ya están asignados a esta unidad curricular."
                    : "El docente '$nombres' ya está asignado a esta unidad curricular.";
                throw new Exception($mensaje);
            }

            $stmtInsert = $co->prepare("INSERT INTO uc_docente (doc_cedula, uc_codigo, uc_doc_estado) VALUES (:docenteCedula, :ucCodigo, 1)");

            foreach ($asignaciones as $asignacion) {
                $stmtInsert->execute([
                    ':docenteCedula' => (int)$asignacion['id'],
                    ':ucCodigo' => $ucCodigo
                ]);
            }

            $co->commit();
            $r['resultado'] = 'asignar';
            $r['mensaje'] = '¡Docente/s asignado/s correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }

    public function Quitar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $uc_codigo = $_POST['uc_codigo'];
            $doc_cedula = $_POST['doc_cedula'];
            $stmt = $co->prepare("UPDATE uc_docente SET uc_doc_estado = 0 WHERE uc_codigo = :uc_codigo AND doc_cedula = :doc_cedula AND uc_doc_estado = 1");
            $stmt->bindParam(':uc_codigo', $uc_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'quitar';
            $r['mensaje'] = 'El docente ahora está fuera de esta unidad curricular.';
            $r['uc_codigo'] = $uc_codigo;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }

    public function obtenerDocentesPorUc($uc_codigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT d.doc_cedula, d.doc_nombre, d.doc_apellido
                                 FROM uc_docente ud
                                 JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                 WHERE ud.uc_codigo = :uc_codigo AND ud.uc_doc_estado = 1");
            $stmt->bindParam(':uc_codigo', $uc_codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function verificarEnHorario($ucCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT COUNT(*) as cantidad FROM uc_horario WHERE uc_codigo = :ucCodigo");
            $stmt->bindParam(":ucCodigo", $ucCodigo, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado['cantidad'] > 0) {
                $r["resultado"] = "en_horario";
                $r["mensaje"] = "La UC está en un horario.";
            } else {
                $r["resultado"] = "no_en_horario";
                $r["mensaje"] = "La UC no está en un horario.";
            }
        } catch (Exception $e) {
            $r["resultado"] = "error";
            $r["mensaje"] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function verificarDocenteEnHorario($ucCodigo, $docCedula)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT COUNT(*) as cantidad 
                                  FROM uc_horario uh
                                  JOIN docente_horario dh ON uh.sec_codigo = dh.sec_codigo
                                  WHERE uh.uc_codigo = :ucCodigo AND dh.doc_cedula = :docCedula");
            $stmt->bindParam(":ucCodigo", $ucCodigo, PDO::PARAM_STR);
            $stmt->bindParam(":docCedula", $docCedula, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado['cantidad'] > 0) {
                $r["resultado"] = "en_horario";
                $r["mensaje"] = "El docente imparte esta UC en un horario.";
            } else {
                $r["resultado"] = "no_en_horario";
                $r["mensaje"] = "El docente no imparte esta UC en un horario.";
            }
        } catch (Exception $e) {
            $r["resultado"] = "error";
            $r["mensaje"] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
