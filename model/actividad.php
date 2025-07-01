<?php
require_once('model/dbconnection.php');

class Actividad extends Connection
{
    private $actId;
    private $docId;
    private $creacionIntelectual;
    private $integracionComunidad;
    private $gestionAcademica;
    private $otras;

    public function __construct()
    {
        parent::__construct();
    }

 
    public function setId($actId) { $this->actId = $actId; }
    public function setDocId($docId) { $this->docId = $docId; }
    public function setCreacionIntelectual($creacion) { $this->creacionIntelectual = $creacion; }
    public function setIntegracionComunidad($integracion) { $this->integracionComunidad = $integracion; }
    public function setGestionAcademica($gestion) { $this->gestionAcademica = $gestion; }
    public function setOtras($otras) { $this->otras = $otras; }

    private function ObtenerDedicacionDocente($doc_id) {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT doc_dedicacion FROM tbl_docente WHERE doc_id = :doc_id");
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['doc_dedicacion'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function ValidarHorasPorDedicacion() {
        $totalHoras = $this->creacionIntelectual + $this->integracionComunidad + $this->gestionAcademica + $this->otras;
        $dedicacion = $this->ObtenerDedicacionDocente($this->docId);

        if (!$dedicacion) {
            return ['resultado' => 'error', 'mensaje' => 'No se pudo encontrar la dedicación del docente seleccionado.'];
        }

        $maxHoras = 0;
        switch ($dedicacion) {
            case 'exclusiva':
                $maxHoras = 36;
                break;
            case 'ordinaria':
                $maxHoras = 24;
                break;
            case 'contratado':
                $maxHoras = 16;
                break;
        }

        if ($maxHoras > 0 && $totalHoras > $maxHoras) {
            return ['resultado' => 'error', 'mensaje' => "El total de horas ($totalHoras) excede el límite de $maxHoras para la dedicación '$dedicacion' del docente."];
        }

        return null; // No hay error
    }

    public function DocenteYaTieneActividad($docId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("SELECT 1 FROM tbl_actividad WHERE doc_id = :docId AND act_estado = 1");
            $stmt->bindParam(':docId', $docId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            return true;
        }
    }

    function Registrar()
    {
        $errorHoras = $this->ValidarHorasPorDedicacion();
        if ($errorHoras) {
            return $errorHoras;
        }

        if ($this->DocenteYaTieneActividad($this->docId)) {
            return ['resultado' => 'error', 'mensaje' => 'ERROR! <br/> El docente seleccionado ya tiene horas de actividad registradas.'];
        }

        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("INSERT INTO tbl_actividad (doc_id, act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras, act_estado) VALUES (:docId, :creacion, :integracion, :gestion, :otras, 1)");
            $stmt->bindParam(':docId', $this->docId, PDO::PARAM_INT);
            $stmt->bindParam(':creacion', $this->creacionIntelectual, PDO::PARAM_INT);
            $stmt->bindParam(':integracion', $this->integracionComunidad, PDO::PARAM_INT);
            $stmt->bindParam(':gestion', $this->gestionAcademica, PDO::PARAM_INT);
            $stmt->bindParam(':otras', $this->otras, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registraron las actividades correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    function Modificar()
    {
        $errorHoras = $this->ValidarHorasPorDedicacion();
        if ($errorHoras) {
            return $errorHoras;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            $stmt = $co->prepare("UPDATE tbl_actividad SET doc_id = :docId, act_creacion_intelectual = :creacion, act_integracion_comunidad = :integracion, act_gestion_academica = :gestion, act_otras = :otras WHERE act_id = :actId");
            $stmt->bindParam(':actId', $this->actId, PDO::PARAM_INT);
            $stmt->bindParam(':docId', $this->docId, PDO::PARAM_INT);
            $stmt->bindParam(':creacion', $this->creacionIntelectual, PDO::PARAM_INT);
            $stmt->bindParam(':integracion', $this->integracionComunidad, PDO::PARAM_INT);
            $stmt->bindParam(':gestion', $this->gestionAcademica, PDO::PARAM_INT);
            $stmt->bindParam(':otras', $this->otras, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificaron las actividades correctamente!';
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
        
        try {
            $stmt = $co->prepare("UPDATE tbl_actividad SET act_estado = 0 WHERE act_id = :actId");
            $stmt->bindParam(':actId', $this->actId, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el registro de actividad correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar, es posible que el registro esté en uso.";
        }
        
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            $stmt = $co->query("SELECT a.*, d.doc_nombre, d.doc_apellido, 
                                (a.act_creacion_intelectual + a.act_integracion_comunidad + a.act_gestion_academica + a.act_otras) AS horas_totales
                                FROM tbl_actividad a 
                                JOIN tbl_docente d ON a.doc_id = d.doc_id
                                WHERE a.act_estado = 1");
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

    public function ListarDocentes()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT doc_id, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_nombre, doc_apellido");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'listar_docentes';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}