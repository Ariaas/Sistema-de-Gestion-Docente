<?php
require_once('model/dbconnection.php');

class ReporteHorarioDocente extends Connection 
{
    private $cedula_docente;

    public function __construct() {
        parent::__construct();
    }

    public function set_cedula_docente($valor) {
        $this->cedula_docente = trim($valor);
    }

    public function obtenerDocentes() {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT doc_cedula, CONCAT(doc_apellido, ', ', doc_nombre) as nombreCompleto FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido ASC, doc_nombre ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerDocentes: " . $e->getMessage()); 
            return false;
        }
    }
    
    public function obtenerInfoDocente() {
        if (empty($this->cedula_docente)) return false;
        $co = $this->con();
        try {
            $sql = "SELECT
                        d.doc_cedula, CONCAT(d.doc_apellido, ', ', d.doc_nombre) AS nombreCompleto,
                        d.doc_dedicacion, d.doc_condicion, cat.cat_nombre AS categoria,
                        (SELECT GROUP_CONCAT(t.tit_nombre SEPARATOR ', ') FROM titulo_docente td JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre WHERE td.doc_cedula = d.doc_cedula) AS postgrado
                    FROM tbl_docente d
                    LEFT JOIN tbl_categoria cat ON d.cat_nombre = cat.cat_nombre
                    WHERE d.doc_cedula = :cedula_docente";
            $stmt = $co->prepare($sql);
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerInfoDocente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerAsignacionesAcademicas() {
        if (empty($this->cedula_docente)) return [];
        $co = $this->con();
        try {
            $sql = "SELECT
                        u.uc_nombre, u.uc_codigo,
                        GROUP_CONCAT(DISTINCT s.sec_codigo ORDER BY s.sec_codigo SEPARATOR '\n') as secciones,
                        e.eje_nombre, u.uc_periodo,
                        SUM(um.mal_hora_academica) as totalHorasClase
                    FROM uc_docente ud
                    JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo
                    JOIN uc_horario uh ON u.uc_codigo = uh.uc_codigo
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_eje e ON u.eje_nombre = e.eje_nombre
                    JOIN uc_malla um ON u.uc_codigo = um.uc_codigo
                    WHERE ud.doc_cedula = :cedula_docente AND ud.uc_doc_estado = 1
                    GROUP BY u.uc_codigo, u.uc_nombre, e.eje_nombre, u.uc_periodo
                    ORDER BY u.uc_nombre";
            $stmt = $co->prepare($sql);
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerAsignacionesAcademicas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerOtrasActividades() {
        if (empty($this->cedula_docente)) return false;
        $co = $this->con();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_actividad WHERE doc_cedula = :cedula_docente");
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerOtrasActividades: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDatosParrillaHorario() {
        if (empty($this->cedula_docente)) return [];
        $co = $this->con();
        try {
            $sql = "SELECT uh.hor_dia, uh.hor_horainicio, uh.hor_horafin, uh.esp_codigo, uh.sec_codigo, u.uc_nombre
                    FROM uc_docente ud
                    JOIN uc_horario uh ON ud.uc_codigo = uh.uc_codigo
                    JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo
                    WHERE ud.doc_cedula = :cedula_docente AND ud.uc_doc_estado = 1";
            $stmt = $co->prepare($sql);
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerDatosParrillaHorario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerBloquesDeTiempo() {
        if (empty($this->cedula_docente)) return [];
        $co = $this->con();
        try {
            $sql = "SELECT DISTINCT uh.hor_horainicio, uh.hor_horafin 
                    FROM uc_docente ud
                    JOIN uc_horario uh ON ud.uc_codigo = uh.uc_codigo
                    WHERE ud.doc_cedula = :cedula_docente AND ud.uc_doc_estado = 1
                    ORDER BY uh.hor_horainicio";
            $stmt = $co->prepare($sql);
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteHorarioDocente::obtenerBloquesDeTiempo: " . $e->getMessage());
            return [];
        }
    }
}