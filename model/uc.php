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
        area_id,
        uc_codigo,
        uc_nombre,
        uc_creditos,
        uc_trayecto,
        uc_periodo,
        uc_electiva,
        uc_estado
    ) VALUES (
        :ejeId,
        :areaId,
        :codigoUC,
        :nombreUC,
        :creditosUC,
        :trayectoUC,
        :periodoUC,
        :electivaUC,
        1
    )");
                $stmt->bindParam(':ejeId', $this->ejeUC, PDO::PARAM_INT);
                $stmt->bindParam(':areaId', $this->areaUC, PDO::PARAM_INT);
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
                    uc_creditos = :creditosUC,
                    uc_trayecto = :trayectoUC,
                    uc_periodo = :periodoUC,
                    uc_electiva = :electivaUC,
                    eje_id = :ejeId,
                    area_id = :areaId
                    WHERE uc_id = :idUC");

                $stmt->bindParam(':idUC', $this->idUC, PDO::PARAM_INT);
                $stmt->bindParam(':codigoUC', $this->codigoUC, PDO::PARAM_STR);
                $stmt->bindParam(':nombreUC', $this->nombreUC, PDO::PARAM_STR);
                $stmt->bindParam(':creditosUC', $this->creditosUC, PDO::PARAM_STR);
                $stmt->bindParam(':trayectoUC', $this->trayectoUC, PDO::PARAM_STR);
                $stmt->bindParam(':periodoUC', $this->periodoUC, PDO::PARAM_STR);
                $stmt->bindParam(':electivaUC', $this->electivaUC, PDO::PARAM_INT);
                $stmt->bindParam(':ejeId', $this->ejeUC, PDO::PARAM_INT);
                $stmt->bindParam(':areaId', $this->areaUC, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la unidad curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La unidad curricular colocada NO existe!';
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
                uc.uc_trayecto,
                uc.uc_periodo,
                uc.uc_electiva,
                uc.uc_estado,
                ej.eje_id,
                ej.eje_nombre,
                ar.area_id,
                ar.area_nombre
            FROM tbl_uc uc
            INNER JOIN tbl_eje ej ON uc.eje_id = ej.eje_id
            INNER JOIN tbl_area ar ON uc.area_id = ar.area_id
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
                    $sql .= " AND uc.uc_id = :uc_id";
                    $stmt = $co->prepare($sql);
                    $stmt->execute([':uc_id' => $uc_id]);
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

    function Asignar($asignacionesJSON, $ucsJSON)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $co->beginTransaction();

            $asignaciones = json_decode($asignacionesJSON, true);
            $ucId = json_decode($ucsJSON, true)[0];

            if (empty($asignaciones) || empty($ucId)) {
                throw new Exception("Debe seleccionar al menos un docente y una unidad curricular.");
            }
            
            $conflictos = [];

            // Primero, verificar todos los posibles conflictos
            foreach ($asignaciones as $asignacion) {
                $docenteId = $asignacion['id'];
                $stmtCheck = $co->prepare("SELECT COUNT(*) FROM uc_docente WHERE doc_id = :docId AND uc_id = :ucId AND uc_doc_estado = 1");
                $stmtCheck->execute([':docId' => (int)$docenteId, ':ucId' => (int)$ucId]);

                if ($stmtCheck->fetchColumn() > 0) {
                     $stmtGetName = $co->prepare("SELECT CONCAT(doc_nombre, ' ', doc_apellido) as nombre_completo FROM tbl_docente WHERE doc_id = :docId");
                     $stmtGetName->execute([':docId' => (int)$docenteId]);
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
            
            // Si no hay conflictos, proceder con la inserción
            $stmtInsert = $co->prepare("INSERT INTO uc_docente (doc_id, uc_id, uc_anio_concurso, uc_doc_estado) VALUES (:docenteId, :ucId, :fechaConcurso, 1)");

            foreach ($asignaciones as $asignacion) {
                $fechaConcursoCompleta = $asignacion['fecha'] . '-01';
                $stmtInsert->execute([
                    ':docenteId' => (int)$asignacion['id'],
                    ':ucId' => (int)$ucId,
                    ':fechaConcurso' => $fechaConcursoCompleta
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
            $uc_id = $_POST['uc_id'];
            $doc_id = $_POST['doc_id'];
            $stmt = $co->prepare("UPDATE uc_docente SET uc_doc_estado = 0 WHERE uc_id = :uc_id AND doc_id = :doc_id AND uc_doc_estado = 1");
            $stmt->bindParam(':uc_id', $uc_id, PDO::PARAM_INT);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'quitar';
            $r['mensaje'] = 'El docente ahora está fuera de esta unidad curricular.';
            $r['uc_id'] = $uc_id;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }

    public function obtenerDocentesPorUc($uc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT d.doc_id, d.doc_nombre, d.doc_apellido, ud.uc_anio_concurso 
                                 FROM uc_docente ud
                                 JOIN tbl_docente d ON ud.doc_id = d.doc_id
                                 WHERE ud.uc_id = :uc_id AND ud.uc_doc_estado = 1");
            $stmt->bindParam(':uc_id', $uc_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function verificarEnHorario($idUC) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT COUNT(*) as cantidad FROM uc_horario WHERE uc_id = :idUC");
            $stmt->bindParam(":idUC", $idUC, PDO::PARAM_INT);
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

    public function verificarDocenteEnHorario($ucId, $docId) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT COUNT(*) as cantidad FROM uc_horario WHERE uc_id = :ucId AND doc_id = :docId");
            $stmt->bindParam(":ucId", $ucId, PDO::PARAM_INT);
            $stmt->bindParam(":docId", $docId, PDO::PARAM_INT);
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
