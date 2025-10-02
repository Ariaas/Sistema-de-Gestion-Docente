<?php
require_once('model/dbconnection.php');

class ReporteHorarioDocente extends Connection 
{
    private $cedula_docente, $anio, $fase;

    public function __construct() {
        parent::__construct();
    }

    public function set_cedula_docente($valor) { $this->cedula_docente = trim($valor); }
    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }

    
    public function getAniosActivos() {
        try {
            $sql = "SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
    public function getFases() {
        try {
            $sql = "SELECT DISTINCT fase_numero FROM tbl_fase ORDER BY fase_numero ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
    public function obtenerDocentes() {
        try {
            $p = $this->con()->prepare("SELECT doc_cedula, CONCAT(doc_apellido, ', ', doc_nombre) as nombreCompleto FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido ASC, doc_nombre ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return false; }
    }
    public function obtenerInfoDocente() {
        if (empty($this->cedula_docente)) return false;
        try {
            $sql = "SELECT 
                            d.doc_cedula, 
                            CONCAT(d.doc_apellido, ', ', d.doc_nombre) AS nombreCompleto, 
                            d.doc_dedicacion, 
                            d.doc_condicion, 
                            d.doc_observacion,
                            d.cat_nombre AS categoria,
                            (SELECT GROUP_CONCAT(t.tit_prefijo, ' ', t.tit_nombre SEPARATOR ', ') 
                             FROM titulo_docente td 
                             JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre 
                             WHERE td.doc_cedula = d.doc_cedula AND t.tit_prefijo NOT IN ('Msc.', 'Dr.', 'Esp.')) AS pregrado_titulo,
                            (SELECT GROUP_CONCAT(t.tit_prefijo, ' ', t.tit_nombre SEPARATOR ', ') 
                             FROM titulo_docente td 
                             JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre 
                             WHERE td.doc_cedula = d.doc_cedula AND t.tit_prefijo IN ('Msc.', 'Dr.', 'Esp.')) AS postgrado_titulo
                        FROM tbl_docente d 
                        WHERE d.doc_cedula = :cedula_docente";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return false; }
    }
    public function obtenerOtrasActividades() {
        if (empty($this->cedula_docente)) return false;
        try {
            $stmt = $this->con()->prepare("SELECT * FROM tbl_actividad WHERE doc_cedula = :cedula_docente");
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return false; }
    }

    private function get_allowed_periods() {
        if ($this->fase == 1) {
            return ['Fase I', 'Anual', 'anual', '0'];
        } elseif ($this->fase == 2) {
            return ['Fase II', 'Anual', 'anual'];
        }
        return [];
    }

    public function obtenerAsignacionesAcademicas() {
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->fase)) return [];
        
        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio];
            $allowed_periods = $this->get_allowed_periods();
            
        
            $sql = "SELECT 
                            u.uc_nombre, 
                            u.uc_codigo,
                            
                            GROUP_CONCAT(DISTINCT s.sec_codigo ORDER BY s.sec_codigo SEPARATOR ', ') as secciones,
                            GROUP_CONCAT(DISTINCT 
                                CASE
                                    WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                    WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                    ELSE uh.esp_numero
                                END 
                            ORDER BY uh.esp_edificio, uh.esp_numero SEPARATOR ', ') as ambientes,
                            e.eje_nombre, 
                            u.uc_periodo,
                            (SELECT um.mal_hora_academica FROM uc_malla um JOIN tbl_malla m ON um.mal_codigo = m.mal_codigo WHERE um.uc_codigo = u.uc_codigo AND m.mal_activa = 1 LIMIT 1) as totalHorasClase
                        FROM uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        LEFT JOIN tbl_eje e ON u.eje_nombre = e.eje_nombre
                        WHERE 
                            uh.doc_cedula = :cedula_docente
                            AND s.ani_anio = :anio_param";

            if (!empty($allowed_periods)) {
                $placeholders = [];
                foreach ($allowed_periods as $i => $period) {
                    $key = ":period" . $i;
                    $placeholders[] = $key;
                    $params[$key] = $period;
                }
                $sql .= " AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")";
            }
            
          
            $sql .= " GROUP BY u.uc_codigo, u.uc_nombre, e.eje_nombre, u.uc_periodo ORDER BY u.uc_nombre";
            
            $stmt = $this->con()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en obtenerAsignacionesAcademicas: " . $e->getMessage());
            return []; 
        }
    }

    public function obtenerDatosParrillaHorario() {
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->fase)) return [];
        
        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio];
            $allowed_periods = $this->get_allowed_periods();
            
       
            $sql = "SELECT 
                            uh.hor_dia, 
                            uh.hor_horainicio, 
                            uh.hor_horafin, 
                            uh.sec_codigo,
                            uh.subgrupo, 
                            u.uc_nombre,
                            CASE
                                WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                ELSE uh.esp_numero
                            END AS esp_codigo_formatted
                        FROM uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        WHERE 
                            uh.doc_cedula = :cedula_docente
                            AND s.ani_anio = :anio_param";
            
            if (!empty($allowed_periods)) {
                $placeholders = [];
                foreach ($allowed_periods as $i => $period) {
                    $key = ":period" . $i;
                    $placeholders[] = $key;
                    $params[$key] = $period;
                }
                $sql .= " AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")";
            }

            $stmt = $this->con()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en obtenerDatosParrillaHorario: " . $e->getMessage());
            return []; 
        }
    }

    public function getTurnos() {
        try {
            $sql = "SELECT tur_nombre, tur_horaInicio, tur_horaFin FROM tbl_turno WHERE tur_estado = 1";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
}