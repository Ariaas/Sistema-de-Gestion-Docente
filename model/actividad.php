<?php
require_once('model/dbconnection.php');

class Actividad extends Connection
{
    // Se utiliza docCedula como identificador único, ya que no hay act_id
    private $docCedula; 
    private $creacionIntelectual;
    private $integracionComunidad;
    private $gestionAcademica;
    private $otras;

    public function __construct()
    {
        parent::__construct();
    }

    // Setter para el identificador único (cédula del docente)
    public function setId($cedula) { $this->docCedula = $cedula; }
    public function setDocId($cedula) { $this->docCedula = $cedula; }
    
    // Setters para las horas de actividad
    public function setCreacionIntelectual($creacion) { $this->creacionIntelectual = $creacion; }
    public function setIntegracionComunidad($integracion) { $this->integracionComunidad = $integracion; }
    public function setGestionAcademica($gestion) { $this->gestionAcademica = $gestion; }
    public function setOtras($otras) { $this->otras = $otras; }

    private function ObtenerDedicacionDocente($doc_cedula) {
        $co = $this->Con();
        try {
            // Se busca por doc_cedula en lugar de doc_id
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
        $totalHoras = $this->creacionIntelectual + $this->integracionComunidad + $this->gestionAcademica + $this->otras;
        // La dedicación se obtiene con la cédula del docente
        $dedicacion = $this->ObtenerDedicacionDocente($this->docCedula);

        if (!$dedicacion) {
            return ['resultado' => 'error', 'mensaje' => 'No se pudo encontrar la dedicación del docente seleccionado.'];
        }

        $maxHoras = 0;
        switch ($dedicacion) {
            case 'exclusiva': $maxHoras = 36; break;
            case 'ordinaria': $maxHoras = 24; break;
            case 'contratado': $maxHoras = 16; break;
        }

        if ($maxHoras > 0 && $totalHoras > $maxHoras) {
            return ['resultado' => 'error', 'mensaje' => "El total de horas ($totalHoras) excede el límite de $maxHoras para la dedicación '$dedicacion' del docente."];
        }

        return null; 
    }

    public function DocenteYaTieneActividad($docCedula)
    {
        $co = $this->Con();
        try {
            // Se busca por doc_cedula y se elimina la condición de act_estado
            $stmt = $co->prepare("SELECT 1 FROM tbl_actividad WHERE doc_cedula = :docCedula");
            $stmt->bindParam(':docCedula', $docCedula, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            // Si hay un error, se asume que existe para prevenir duplicados
            return true; 
        }
    }

    function Registrar()
    {
        $errorHoras = $this->ValidarHorasPorDedicacion();
        if ($errorHoras) return $errorHoras;

        if ($this->DocenteYaTieneActividad($this->docCedula)) {
            return ['resultado' => 'error', 'mensaje' => 'ERROR! <br/> El docente seleccionado ya tiene horas de actividad registradas.'];
        }

        $r = array();
        $co = $this->Con();
        try {
            // La inserción ya no incluye act_estado
            $stmt = $co->prepare("INSERT INTO tbl_actividad (doc_cedula, act_creacion_intelectual, act_integracion_comunidad, act_gestion_academica, act_otras) VALUES (:docCedula, :creacion, :integracion, :gestion, :otras)");
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
        $errorHoras = $this->ValidarHorasPorDedicacion();
        if ($errorHoras) return $errorHoras;

        $co = $this->Con();
        $r = array();
        
        try {
            // Se actualiza el registro buscando por doc_cedula
            $stmt = $co->prepare("UPDATE tbl_actividad SET act_creacion_intelectual = :creacion, act_integracion_comunidad = :integracion, act_gestion_academica = :gestion, act_otras = :otras WHERE doc_cedula = :docCedula");
            $stmt->bindParam(':docCedula', $this->docCedula, PDO::PARAM_INT);
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
        $r = array();
        
        try {
            // Se realiza un borrado físico (DELETE) usando doc_cedula
            $stmt = $co->prepare("DELETE FROM tbl_actividad WHERE doc_cedula = :docCedula");
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
            // El JOIN se hace con doc_cedula y se quita la condición de act_estado
            $stmt = $co->query("SELECT a.*, d.doc_cedula, d.doc_nombre, d.doc_apellido, 
                                (a.act_creacion_intelectual + a.act_integracion_comunidad + a.act_gestion_academica + a.act_otras) AS horas_totales
                                FROM tbl_actividad a 
                                JOIN tbl_docente d ON a.doc_cedula = d.doc_cedula");
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
            // Se selecciona doc_cedula como el valor para el <select>
            $stmt = $co->query("SELECT doc_cedula, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_nombre, doc_apellido");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'listar_docentes';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }
}