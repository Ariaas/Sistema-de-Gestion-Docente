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

    //Construct
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

    // Getters
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

    // Setters

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

    //Methods

    /// Registrar

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

            // Cerrar la conexión
            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
        }

        return $r;
    }

    /// Actualizar

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

    /// Eliminar

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

    // /// Listar

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

    /// Consultar exitencia

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
        // Se cierra la conexión
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
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    /// Obtener selects 

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

    // Asignar

    // function Unir($secciones)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];

    //     try {
    //         $co->beginTransaction();

    //         $seccionesArray = json_decode($secciones, true);

    //         $in = implode(',', array_map('intval', $seccionesArray));
    //         $stmtTrayecto = $co->query("
    //             SELECT tra_id 
    //             FROM tbl_seccion 
    //             WHERE sec_id IN ($in) AND sec_estado = 1
    //             GROUP BY tra_id
    //         ");
    //         $trayectos = $stmtTrayecto->fetchAll(PDO::FETCH_COLUMN);

    //         if (count($trayectos) !== 1) {
    //             throw new Exception("Las secciones seleccionadas NO pertenecen al mismo trayecto.");
    //         }

    //         $stmtCheck = $co->prepare("
    //             SELECT COUNT(*) AS cnt
    //             FROM seccion_grupo sg
    //             INNER JOIN tbl_grupo g ON sg.gro_id = g.gro_id
    //             WHERE sg.sec_id = :seccionId AND g.grupo_estado = 1
    //         ");

    //         foreach ($seccionesArray as $seccionId) {
    //             $stmtCheck->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
    //             $stmtCheck->execute();
    //             $count = (int)$stmtCheck->fetchColumn();

    //             if ($count > 0) {
    //                 throw new Exception("Al menos UNA de las SECCIONES <br/> YA pertenece a un grupo activo.");
    //             }
    //         }

    //         $stmtGrupo = $co->prepare("
    //             INSERT INTO tbl_grupo (grupo_estado) 
    //             VALUES (1)
    //         ");
    //         $stmtGrupo->execute();
    //         $grupoId = $co->lastInsertId();

    //         $stmtLink = $co->prepare("
    //             INSERT INTO seccion_grupo (gro_id, sec_id) 
    //             VALUES (:grupoId, :seccionId)
    //         ");
    //         $stmtLink->bindParam(':grupoId', $grupoId, PDO::PARAM_INT);

    //         foreach ($seccionesArray as $seccionId) {
    //             $stmtLink->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
    //             $stmtLink->execute();
    //         }

    //         $co->commit();

    //         $r['resultado'] = 'unir';
    //         $r['mensaje']   = 'Secciones unidas!<br/>Se unieron las secciones correctamente!';
    //     } catch (Exception $e) {
    //         $co->rollBack();
    //         $r['resultado'] = 'error';
    //         $r['mensaje']   = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }

    //     return $r;
    // }

    // function Separar()
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];

    //     try {
    //         $stmt = $co->prepare("UPDATE tbl_grupo
    //         SET grupo_estado = 0
    //         WHERE gro_id = :grupoId");
    //         $stmt->bindParam(':grupoId', $this->grupoId, PDO::PARAM_INT);
    //         $stmt->execute();

    //         $r['resultado'] = 'separar';
    //         $r['mensaje'] = 'Secciones separadas!<br/>Se separaron las secciones correctamente!';
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }

    //     return $r;
    // }
}
