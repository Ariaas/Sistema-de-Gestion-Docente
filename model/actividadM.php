<?php
require_once('model/dbconnection.php');

class Actividad extends Connection
{
    private $docCedula; 
    private $creacionIntelectual;
    private $integracionComunidad;
    private $gestionAcademica;
    private $otras;

    public function __construct()
    {
        parent::__construct();
    }

    public function setId($cedula) { $this->docCedula = $cedula; }
    public function setDocId($cedula) { $this->docCedula = $cedula; }
    
    public function setCreacionIntelectual($creacion) { $this->creacionIntelectual = $creacion; }
    public function setIntegracionComunidad($integracion) { $this->integracionComunidad = $integracion; }
    public function setGestionAcademica($gestion) { $this->gestionAcademica = $gestion; }
    public function setOtras($otras) { $this->otras = $otras; }

    private function ObtenerDedicacionDocente($doc_cedula) {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT doc_dedicacion FROM tbl_docente WHERE doc_cedula = :doc_cedula");
            $stmt->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['doc_dedicacion'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function ValidarHorasPorDedicacion() {
        $totalHorasActividad = $this->creacionIntelectual + $this->integracionComunidad + $this->gestionAcademica + $this->otras;
        $dedicacion = $this->ObtenerDedicacionDocente($this->docCedula);

        if (!$dedicacion) {
            return ['resultado' => 'error', 'mensaje' => 'No se pudo encontrar la dedicación del docente para validar las horas.'];
        }

        $maxHorasActividad = 0;
        switch (strtolower($dedicacion)) {
            case 'exclusiva':
                $maxHorasActividad = 29; 
                break;
            case 'tiempo completo':
                $maxHorasActividad = 23; 
                break;
            case 'medio tiempo':
                $maxHorasActividad = 13; 
                break;
            case 'tiempo convencional':
                $maxHorasActividad = 0; 
                break;
            default:
                return ['resultado' => 'error', 'mensaje' => "El tipo de dedicación ('$dedicacion') no es válido según el reglamento."];
        }

        if ($maxHorasActividad > 0 && $totalHorasActividad > $maxHorasActividad) {
            return ['resultado' => 'error', 'mensaje' => "El total de horas de actividad ($totalHorasActividad) excede el límite de $maxHorasActividad para un docente con dedicación '$dedicacion'."];
        }
        
        if ($maxHorasActividad === 0 && $totalHorasActividad > 0) {
            return ['resultado' => 'error', 'mensaje' => "Un docente con dedicación '$dedicacion' no puede tener horas de actividad adicionales registradas."];
        }

        return null;
    }

    private function buscarEstadoPorCedula($doc_cedula)
    {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT act_estado FROM tbl_actividad WHERE doc_cedula = :doc_cedula");
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['act_estado'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function DocenteYaTieneActividad($docCedula) {
        return $this->buscarEstadoPorCedula($docCedula) == '1';
    }

    private function _actualizarDatosActividad()
    {
        $co = $this->Con();
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_actividad SET act_creacion_intelectual = :creacion, act_integracion_comunidad = :integracion, act_gestion_academica = :gestion, act_otras = :otras, act_estado = 1 WHERE doc_cedula = :docCedula");
            $stmt->bindParam(':docCedula', $this->docCedula, PDO::PARAM_INT);
            $stmt->bindParam(':creacion', $this->creacionIntelectual, PDO::PARAM_INT);
            $stmt->bindParam(':integracion', $this->integracionComunidad, PDO::PARAM_INT);
            $stmt->bindParam(':gestion', $this->gestionAcademica, PDO::PARAM_INT);
            $stmt->bindParam(':otras', $this->otras, PDO::PARAM_INT);
            $stmt->execute();
            return ['resultado' => 'ok'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    function Registrar()
    {
        $errorValidacion = $this->ValidarHorasPorDedicacion();
        if ($errorValidacion) return $errorValidacion;

        $estado_actividad = $this->buscarEstadoPorCedula($this->docCedula);

        if ($estado_actividad == '1') {
            return ['resultado' => 'error', 'mensaje' => 'ERROR! <br/> El docente seleccionado ya tiene horas de actividad registradas.'];
        }

        if ($estado_actividad == '0') {
            $resultado_actualizacion = $this->_actualizarDatosActividad();
            if ($resultado_actualizacion['resultado'] === 'ok') {
                return ['resultado' => 'registrar', 'mensaje' => 'Registro Incluido!<br/>Se reactivaron y actualizaron las actividades correctamente!'];
            } else {
                return $resultado_actualizacion;
            }
        }
        
        $r = array();
        $co = $this->Con();
        try {
            $stmt = $co->prepare("INSERT INTO tbl_actividad (doc_cedula, act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras, act_estado) VALUES (:docCedula, :creacion, :integracion, :gestion, :otras, 1)");
            $stmt->bindParam(':docCedula', $this->docCedula, PDO::PARAM_INT);
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
        return $r;
    }

    function Modificar()
    {
        $errorValidacion = $this->ValidarHorasPorDedicacion();
        if ($errorValidacion) return $errorValidacion;

        $resultado_actualizacion = $this->_actualizarDatosActividad();

        if ($resultado_actualizacion['resultado'] === 'ok') {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificaron las actividades correctamente!';
        } else {
            $r = $resultado_actualizacion;
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_actividad SET act_estado = 0 WHERE doc_cedula = :docCedula");
            $stmt->bindParam(':docCedula', $this->docCedula, PDO::PARAM_INT);
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
        $r = array();
        try {
            $stmt = $co->query("SELECT a.*, d.doc_cedula, d.doc_nombre, d.doc_apellido, 
                                (a.act_creacion_intelectual + a.act_integracion_comunidad + a.act_gestion_academica + a.act_otras) AS horas_totales
                                FROM tbl_actividad a 
                                JOIN tbl_docente d ON a.doc_cedula = d.doc_cedula
                                WHERE a.act_estado = 1");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function ListarDocentes()
    {
        $co = $this->Con();
        $r = array();
        try {
            $stmt = $co->query("
                SELECT 
                    d.doc_cedula, 
                    d.doc_nombre, 
                    d.doc_apellido, 
                    d.doc_dedicacion,
                    CASE WHEN a.doc_cedula IS NOT NULL THEN 1 ELSE 0 END AS tiene_actividad
                FROM 
                    tbl_docente d
                LEFT JOIN 
                    tbl_actividad a ON d.doc_cedula = a.doc_cedula AND a.act_estado = 1
                WHERE 
                    d.doc_estado = 1 
                ORDER BY 
                    d.doc_nombre, d.doc_apellido
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'listar_docentes';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }
    public function ContarDocentesActivos() {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT COUNT(*) FROM tbl_docente WHERE doc_estado = 1");
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0; 
        }
    }
}
?>