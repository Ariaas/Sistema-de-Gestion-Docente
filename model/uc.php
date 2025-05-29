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
            eje_id,
            tra_id,
            area_id,
            uc_codigo,
            uc_nombre,
            uc_hora_independiente,
            uc_hora_asistida,
            uc_hora_academica,
            uc_creditos,
            uc_periodo,
            uc_electiva,
            uc_estado
        ) VALUES (
            :ejeId,
            :trayectoUC,
            :areaId,
            :codigoUC,
            :nombreUC,
            :independienteUC,
            :asistidaUC,
            :academicaUC,
            :creditosUC,
            :periodoUC,
            :electivaUC,
            1
        )");
                $stmt->bindParam(':ejeId', $this->ejeUC, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoUC', $this->trayectoUC, PDO::PARAM_STR);
                $stmt->bindParam(':areaId', $this->areaUC, PDO::PARAM_STR);
                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->bindParam(':nombreUC', $this->nombreUC, PDO::PARAM_STR);
                $stmt->bindParam(':independienteUC', $this->independienteUC, PDO::PARAM_STR);
                $stmt->bindParam(':asistidaUC', $this->asistidaUC, PDO::PARAM_STR);
                $stmt->bindParam(':academicaUC', $this->academicaUC, PDO::PARAM_STR);
                $stmt->bindParam(':creditosUC', $this->creditosUC, PDO::PARAM_STR);
                $stmt->bindParam(':periodoUC', $this->periodoUC, PDO::PARAM_STR);
                $stmt->bindParam(':electivaUC', $this->electivaUC, PDO::PARAM_STR);

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
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->idUC)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_uc
                    SET uc_codigo = :codigoUC , 
                    uc_nombre = :nombreUC,
                    uc_hora_independiente = :independienteUC,
                    uc_hora_asistida = :asistidaUC,
                    uc_hora_academica = :academicaUC,
                    uc_creditos = :creditosUC,
                    uc_periodo = :periodoUC,
                    uc_electiva = :electivaUC,
                    eje_id = :ejeId,
                    tra_id = :trayectoId,
                    area_id = :areaId
                    WHERE uc_id = :idUC");

                $stmt->bindParam(':idUC', $this->idUC, PDO::PARAM_STR);
                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->bindParam(':nombreUC', $this->nombreUC, PDO::PARAM_STR);
                $stmt->bindParam(':independienteUC', $this->independienteUC, PDO::PARAM_STR);
                $stmt->bindParam(':asistidaUC', $this->asistidaUC, PDO::PARAM_STR);
                $stmt->bindParam(':academicaUC', $this->academicaUC, PDO::PARAM_STR);
                $stmt->bindParam(':creditosUC', $this->creditosUC, PDO::PARAM_STR);
                $stmt->bindParam(':periodoUC', $this->periodoUC, PDO::PARAM_STR);
                $stmt->bindParam(':electivaUC', $this->electivaUC, PDO::PARAM_STR);
                $stmt->bindParam(':ejeId', $this->ejeUC, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoId', $this->trayectoUC, PDO::PARAM_STR);
                $stmt->bindParam(':areaId', $this->areaUC, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la unidad curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La unidad curricular colocado NO existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->idUC, $this->codigoUC)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_uc
                SET uc_estado = 0
                WHERE uc_id = :idUC");
                $stmt->bindParam(':idUC', $this->idUC, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la unidad curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocada NO existe!';
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
                uc.uc_id,
                uc.uc_codigo,
                uc.uc_nombre,
                uc.uc_creditos,
                uc.uc_hora_academica,
                uc.uc_hora_asistida,
                uc.uc_hora_independiente,
                uc.uc_periodo,
                uc.uc_electiva,
                uc.uc_estado,
                ej.eje_id,
                ej.eje_nombre,
                ar.area_id,
                ar.area_nombre,
                tr.tra_id,
                tr.tra_numero,
                tr.tra_anio
            FROM tbl_uc uc
            INNER JOIN tbl_eje ej ON uc.eje_id = ej.eje_id
            INNER JOIN tbl_area ar ON uc.area_id = ar.area_id
            INNER JOIN tbl_trayecto tr ON uc.tra_id = tr.tra_id
            WHERE uc.uc_estado = 1");
            } elseif ($accion === 'consultarAsignacion') {
                $uc_id = $_POST['uc_id'] ?? null;
                $sql = "SELECT 
                    uc.uc_id,
                    uc.uc_codigo,
                    uc.uc_nombre,
                    d.doc_id,
                    d.doc_nombre,
                    d.doc_apellido
                FROM tbl_uc uc
                LEFT JOIN uc_docente ud ON uc.uc_id = ud.uc_id AND ud.uc_doc_estado = 1
                LEFT JOIN tbl_docente d ON ud.doc_id = d.doc_id AND d.doc_estado = 1
                WHERE uc.uc_estado = 1";
                if ($uc_id) {
                    $sql .= " AND uc.uc_id = " . intval($uc_id);
                }
                $stmt = $co->query($sql);
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

    public function Existe($codigoUC)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_uc WHERE uc_codigo=:codigoUC AND uc_estado = 1");
            $stmt->bindParam(':codigoUC', $codigoUC, PDO::PARAM_STR);
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

    public function ExisteId($idUC)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_uc WHERE uc_id=:idUC AND uc_estado = 1");
            $stmt->bindParam(':idUC', $idUC, PDO::PARAM_STR);
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

    function obtenerTrayecto()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT tra_id, tra_numero, tra_anio FROM tbl_trayecto WHERE tra_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
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
            $stmt = $co->query("SELECT eje_id, eje_nombre FROM tbl_eje WHERE eje_estado = 1");
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
            $stmt = $co->query("SELECT area_id, area_nombre FROM tbl_area WHERE area_estado = 1");
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
            $stmt = $co->query("SELECT doc_id, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    function Asignar($docentes, $ucs)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $co->beginTransaction();

            $docentesArray = json_decode($docentes, true);
            $ucsArray = json_decode($ucs, true);

            if (empty($docentesArray) || empty($ucsArray)) {
                throw new Exception("Debe seleccionar al menos un docente y una unidad curricular.");
            }

            $stmtCheck = $co->prepare("
            SELECT COUNT(*) FROM uc_docente 
            WHERE doc_id = :docenteId AND uc_id = :ucId AND uc_doc_estado = 1
             ");

            $stmtInsert = $co->prepare("
            INSERT INTO uc_docente (doc_id, uc_id, uc_doc_estado) VALUES (:docenteId, :ucId, 1)
            ");

            foreach ($docentesArray as $docenteId) {
                foreach ($ucsArray as $ucId) {
                    $stmtCheck->execute([
                        ':docenteId' => (int)$docenteId,
                        ':ucId' => (int)$ucId
                    ]);
                    $exists = $stmtCheck->fetchColumn();
                    if ($exists) {
                        throw new Exception("Ya hay un docente asignado a esta unidad curricular.");
                    }
                    $stmtInsert->execute([
                        ':docenteId' => (int)$docenteId,
                        ':ucId' => (int)$ucId
                    ]);
                }
            }

            $co->commit();
            $r['resultado'] = 'asignar';
            $r['mensaje'] = 'Docentes asignados correctamente a las unidades curriculares!';
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
            $uc_id = $_POST['uc_id'];
            $doc_id = $_POST['doc_id'];
            $stmt = $co->prepare("UPDATE uc_docente SET uc_doc_estado = 0 WHERE uc_id = :uc_id AND doc_id = :doc_id AND uc_doc_estado = 1");
            $stmt->bindParam(':uc_id', $uc_id, PDO::PARAM_INT);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'quitar';
            $r['mensaje'] = 'El docente ahora está fuera de esta unidad curricular.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }
}
