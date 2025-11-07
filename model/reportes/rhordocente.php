<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class ReporteHorarioDocente extends Connection 
{
    private $cedula_docente, $anio, $ani_tipo, $fase;

    public function __construct() {
        parent::__construct();
    }

    public function set_cedula_docente($valor) { $this->cedula_docente = trim($valor); }
    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setAniTipo($valor) { $this->ani_tipo = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }

    
    public function getAniosActivos() {
        try {
            $sql = "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC";
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
            $p = $this->con()->prepare("SELECT doc_cedula, CONCAT(doc_apellido, ' ', doc_nombre) as nombreCompleto FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido ASC, doc_nombre ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return false; }
    }
    
    public function obtenerDocentesPorAnio($anio, $ani_tipo) {
        if (empty($anio) || empty($ani_tipo)) return $this->obtenerDocentes();
        
        try {
            $sql = $sql = "SELECT DISTINCT d.doc_cedula, CONCAT(d.doc_apellido, ' ', d.doc_nombre) as nombreCompleto 
                    FROM tbl_docente d
                    INNER JOIN uc_horario uh ON d.doc_cedula = uh.doc_cedula

                    INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                        AND uh.ani_anio = s.ani_anio 
                        AND uh.ani_tipo = s.ani_tipo

                    WHERE d.doc_estado = 1 
                        AND s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param
                    ORDER BY d.doc_apellido ASC, d.doc_nombre ASC";
            
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([':anio_param' => $anio, ':ani_tipo_param' => $ani_tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en obtenerDocentesPorAnio: " . $e->getMessage());
            return $this->obtenerDocentes(); 
        }
    }
    public function obtenerInfoDocente() {
        if (empty($this->cedula_docente)) return false;
        try {
            $sql = "SELECT 
                            d.doc_cedula, 
                            CONCAT(d.doc_apellido, ' ', d.doc_nombre) AS nombreCompleto, 
                            d.doc_dedicacion, 
                            d.doc_condicion, 
                            d.doc_observacion,
                            d.cat_nombre AS categoria,
                            (SELECT GROUP_CONCAT(DISTINCT CONCAT(td.tit_prefijo, ' ', td.tit_nombre) ORDER BY td.tit_prefijo, td.tit_nombre SEPARATOR ', ') 
                             FROM titulo_docente td 
                             WHERE td.doc_cedula = d.doc_cedula 
                             AND td.tit_prefijo IN ('Ing.', 'Lic.', 'Prof.', 'Tec.', 'TSU', 'T.S.U.', 'Abg.', 'Arq.')) AS pregrado_titulo,
                            (SELECT GROUP_CONCAT(DISTINCT CONCAT(td.tit_prefijo, ' ', td.tit_nombre) ORDER BY td.tit_prefijo, td.tit_nombre SEPARATOR ', ') 
                             FROM titulo_docente td 
                             WHERE td.doc_cedula = d.doc_cedula 
                             AND td.tit_prefijo IN ('Msc.', 'MSc.', 'M.Sc.', 'Dr.', 'PhD', 'PhD.', 'Ph.D.', 'Ph.D', 'Esp.', 'Dra.')) AS postgrado_titulo
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
            $stmt = $this->con()->prepare("SELECT * FROM tbl_actividad WHERE doc_cedula = :cedula_docente AND act_estado = 1");
            $stmt->execute([':cedula_docente' => $this->cedula_docente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en obtenerOtrasActividades: " . $e->getMessage());
            return false; 
        }
    }

    private function get_allowed_periods() {
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        
        if ($esIntensivo) {
            return ['Fase I', 'Fase II', 'Anual'];
        }
        
        
        if ($this->fase == 1) {
            return ['Fase I', 'Anual'];
        } elseif ($this->fase == 2) {
            return ['Fase II', 'Anual'];
        }
        return [];
    }

    
    public function obtenerAsignacionesAcademicas() {
        
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->ani_tipo)) return [];
        
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];
        
        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            $allowed_periods = $this->get_allowed_periods();
            
        
            $sql = "SELECT 
                            u.uc_nombre, 
                            u.uc_codigo,
                            
                            GROUP_CONCAT(DISTINCT s.sec_codigo ORDER BY s.sec_codigo SEPARATOR ', ') as secciones,
                            COALESCE(
                                 GROUP_CONCAT(DISTINCT 
                                  CASE
                                    WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                    WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                    WHEN uh.esp_numero IS NOT NULL THEN uh.esp_numero
                                    ELSE NULL
                                  END 
                                 ORDER BY uh.esp_edificio, uh.esp_numero SEPARATOR ', '),
                             '(Sin espacio)') as ambientes,
                            e.eje_nombre, 
                            u.uc_periodo,
                            (SELECT um.mal_hora_academica FROM uc_malla um JOIN tbl_malla m ON um.mal_codigo = m.mal_codigo WHERE um.uc_codigo = u.uc_codigo AND m.mal_activa = 1 LIMIT 1) as totalHorasClase
                        FROM uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                            AND uh.ani_anio = s.ani_anio
                            AND uh.ani_tipo = s.ani_tipo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        LEFT JOIN tbl_eje e ON u.eje_nombre = e.eje_nombre
                        WHERE 
                            uh.doc_cedula = :cedula_docente
                            AND uh.doc_cedula IS NOT NULL
                            AND s.ani_anio = :anio_param
                            AND s.ani_tipo = :ani_tipo_param";

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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) { 
            error_log("Error en obtenerAsignacionesAcademicas: " . $e->getMessage());
            return []; 
        }
    }

    public function obtenerDatosParrillaHorario() {
        
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->ani_tipo)) return [];
        
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];
        
        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            $allowed_periods = $this->get_allowed_periods();
            
       
            $sql = "SELECT 
                            uh.hor_dia, 
                            uh.hor_horainicio, 
                            uh.hor_horafin, 
                            uh.sec_codigo,
                            uh.subgrupo, 
                            u.uc_nombre,
                            u.uc_codigo,
                            CASE
                                WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                WHEN uh.esp_numero IS NOT NULL THEN uh.esp_numero
                                ELSE '(Sin espacio)'
                  END AS esp_codigo_formatted
                        FROM uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                            AND uh.ani_anio = s.ani_anio
                          	AND uh.ani_tipo = s.ani_tipo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        WHERE 
                          	uh.doc_cedula = :cedula_docente
                          	AND uh.doc_cedula IS NOT NULL
                          	AND s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param";
            
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

    
    public function getBloquesPersonalizados() {
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->ani_tipo)) return [];
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];

        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            $allowed_periods = $this->get_allowed_periods();
            
            $sql = "SELECT DISTINCT bp.tur_horainicio, bp.tur_horafin, bp.bloque_sintetico
                    FROM tbl_bloque_personalizado bp
JOIN tbl_seccion s ON bp.sec_codigo = s.sec_codigo AND bp.ani_anio = s.ani_anio AND bp.ani_tipo = s.ani_tipo
JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo AND s.ani_anio = uh.ani_anio AND s.ani_tipo = uh.ani_tipo
JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    WHERE uh.doc_cedula = :cedula_docente
                        AND uh.doc_cedula IS NOT NULL
                        AND s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param";
            
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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            
            foreach ($result as &$bloque) {
                if (strlen($bloque['tur_horainicio']) === 5) {
                    $bloque['tur_horainicio'] .= ':00';
                }
                if (strlen($bloque['tur_horafin']) === 5) {
                    $bloque['tur_horafin'] .= ':00';
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en getBloquesPersonalizados: " . $e->getMessage());
            return [];
        }
    }

    
    public function getBloquesEliminados() {
        if (empty($this->cedula_docente) || empty($this->anio) || empty($this->ani_tipo)) return [];
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];

        try {
            $params = [':cedula_docente' => $this->cedula_docente, ':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            $allowed_periods = $this->get_allowed_periods();
            
            $sql = "SELECT DISTINCT be.tur_horainicio
                    FROM tbl_bloque_eliminado be
JOIN tbl_seccion s ON be.sec_codigo = s.sec_codigo AND be.ani_anio = s.ani_anio AND be.ani_tipo = s.ani_tipo
JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo AND s.ani_anio = uh.ani_anio AND s.ani_tipo = uh.ani_tipo
JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    WHERE uh.doc_cedula = :cedula_docente
                        AND uh.doc_cedula IS NOT NULL
                        AND s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param";
            
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
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            
            foreach ($result as &$hora) {
                if (strlen($hora) === 5) {
                    $hora .= ':00';
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en getBloquesEliminados: " . $e->getMessage());
            return [];
        }
    }
}