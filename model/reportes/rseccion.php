<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class SeccionReport extends Connection
{
    private $anio, $ani_tipo, $fase, $trayecto;

    public function __construct() { parent::__construct(); }

    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setAniTipo($valor) { $this->ani_tipo = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }
    public function setTrayecto($valor) { $this->trayecto = trim($valor); }
    
    
    /**
     * CORRECCIÓN 1: Helper para la lógica de Intensivo y Fases.
     * Si es intensivo, retorna un array vacío para NO filtrar por período.
     */
    private function getAllowedPeriods($esIntensivo)
    {
        if ($esIntensivo) {
            return []; 
        }

        // Si es regular, filtramos según la fase
        if ($this->fase == 1) {
            return ['Fase I', 'anual', 'Anual', '0'];
        } elseif ($this->fase == 2) {
            return ['Fase II', 'anual', 'Anual'];
        }
        
        return []; 
    }

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

    public function getTrayectos() {
        try {
            $sql = "SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_trayecto IS NOT NULL ORDER BY uc_trayecto ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }


  
public function getHorariosFiltrados()
{
    if (empty($this->anio) || empty($this->ani_tipo)) return [];
    
    $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
    
    if (!$esIntensivo && empty($this->fase)) return [];

    // CORRECCIÓN 1: Usar el helper.
    $allowed_periods = $this->getAllowedPeriods($esIntensivo);
    
    // NO usamos "if (empty($allowed_periods)) return [];"
    // porque para Intensivo, $allowed_periods DEBE estar vacío.
    
    try {
        $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
        
        error_log("SeccionReport - Año: " . $this->anio . ", Tipo: " . $this->ani_tipo . ", Fase: " . $this->fase);
        
        $sql_base = "SELECT
                        uh.sec_codigo,
                        h.tur_nombre,
                        s.grupo_union_id,
                        uh.subgrupo,
                        u.uc_trayecto,
                        uh.hor_dia,
                        uh.hor_horainicio,
                        uh.hor_horafin,
                        u.uc_nombre,
                        uh.esp_tipo,
                        uh.doc_cedula,
                        CONCAT(d.doc_nombre, ' ', d.doc_apellido) as NombreCompletoDocente,
                        CASE
                            WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                            WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                            ELSE uh.esp_numero
                        END AS esp_codigo
                   FROM
                        uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                        AND uh.ani_anio = s.ani_anio
                        AND uh.ani_tipo = s.ani_tipo
                    JOIN tbl_horario h ON s.sec_codigo = h.sec_codigo
                        AND s.ani_anio = h.ani_anio
                        AND s.ani_tipo = h.ani_tipo
                    
                    LEFT JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo AND u.uc_estado = 1
                    
                    LEFT JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                    WHERE
                        s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param
                        AND s.sec_estado = 1";
        
        // CORRECCIÓN 1 y 2:
        // Solo aplicar el filtro si $allowed_periods NO está vacío (es decir, NO es intensivo)
        // Y TAMBIÉN permitir filas donde u.uc_periodo es NULL (las "Sin UC")
        if (!empty($allowed_periods)) {
            $period_placeholders = [];
            $i = 0;
            foreach ($allowed_periods as $period) {
                $key = ":period" . $i++;
                $period_placeholders[] = $key;
                $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sql_base .= " AND (u.uc_periodo IN ({$in_clause}) OR u.uc_periodo IS NULL)";
        }

        if (isset($this->trayecto) && $this->trayecto !== '') {
            // CORRECCIÓN 3: El filtro de trayecto también debe permitir NULLs
            $sql_base .= " AND (u.uc_trayecto = :trayecto_param OR u.uc_trayecto IS NULL)";
            $params[':trayecto_param'] = $this->trayecto;
        }
        
        $sql_base .= " ORDER BY u.uc_trayecto ASC, uh.sec_codigo ASC, uh.hor_horainicio ASC, uh.subgrupo ASC";
        
        error_log("SeccionReport SQL: " . $sql_base);
        error_log("SeccionReport Params: " . print_r($params, true));
        
        $stmt = $this->con()->prepare($sql_base);
        $stmt->execute($params);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("SeccionReport - Registros encontrados: " . count($result));
        
        return $result;

    } catch (PDOException $e) {
        error_log("Error en SeccionReport::getHorariosFiltrados: " . $e->getMessage());
        return false;
    }
}

    public function getTurnosCompletos() {
        try {
            $sql = "SELECT tur_nombre, tur_horaInicio, tur_horaFin FROM tbl_turno WHERE tur_estado = 1 ORDER BY tur_horaInicio ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getBloquesPersonalizados() {
        if (empty($this->anio) || empty($this->ani_tipo)) return [];
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];

        // CORRECCIÓN 1: Usar el helper
        $allowed_periods = $this->getAllowedPeriods($esIntensivo);

        try {
            $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            
            $sql = "SELECT DISTINCT bp.tur_horainicio, bp.tur_horafin, bp.bloque_sintetico
                    FROM tbl_bloque_personalizado bp
                    WHERE bp.ani_anio = :anio_param
                        AND bp.ani_tipo = :ani_tipo_param
                        AND bp.sec_codigo IN (
                            SELECT DISTINCT s.sec_codigo
                            FROM tbl_seccion s
                            JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo 
                                AND s.ani_anio = uh.ani_anio 
                                AND s.ani_tipo = uh.ani_tipo
                            LEFT JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo AND u.uc_estado = 1
                            WHERE s.ani_anio = :anio_param
                                AND s.ani_tipo = :ani_tipo_param
                                AND s.sec_estado = 1";
            
            // CORRECCIÓN 1 y 2:
            if (!empty($allowed_periods)) {
                $period_placeholders = [];
                $i = 0;
            foreach ($allowed_periods as $period) {
               $key = ":period" . $i++;
               $period_placeholders[] = $key;
               $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sql .= " AND (u.uc_periodo IN ({$in_clause}) OR u.uc_periodo IS NULL)";
         }

         if (isset($this->trayecto) && $this->trayecto !== '') {
            // CORRECCIÓN 3:
            $sql .= " AND (u.uc_trayecto = :trayecto_param OR u.uc_trayecto IS NULL)";
            $params[':trayecto_param'] = $this->trayecto;
         }
         
         $sql .= " )";
         
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
      if (empty($this->anio) || empty($this->ani_tipo)) return [];
      
      $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
      if (!$esIntensivo && empty($this->fase)) return [];

      // CORRECCIÓN 1: Usar el helper
      $allowed_periods = $this->getAllowedPeriods($esIntensivo);

      try {
         $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];

         $sql = "SELECT DISTINCT be.tur_horainicio
               FROM tbl_bloque_eliminado be
               WHERE be.ani_anio = :anio_param
                  AND be.ani_tipo = :ani_tipo_param
               AND be.sec_codigo IN (
                     SELECT DISTINCT s.sec_codigo
                     FROM tbl_seccion s
                     JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo 
                        AND s.ani_anio = uh.ani_anio 
                        AND s.ani_tipo = uh.ani_tipo
                  LEFT JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo AND u.uc_estado = 1
                  WHERE s.ani_anio = :anio_param
               AND s.ani_tipo = :ani_tipo_param
                        AND s.sec_estado = 1";
         
         // CORRECCIÓN 1 y 2:
         if (!empty($allowed_periods)) {
            $period_placeholders = [];
            $i = 0;
            foreach ($allowed_periods as $period) {
               $key = ":period" . $i++;
               $period_placeholders[] = $key;
               $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sql .= " AND (u.uc_periodo IN ({$in_clause}) OR u.uc_periodo IS NULL)";
         }

         if (isset($this->trayecto) && $this->trayecto !== '') {
            // CORRECCIÓN 3:
            $sql .= " AND (u.uc_trayecto = :trayecto_param OR u.uc_trayecto IS NULL)";
            $params[':trayecto_param'] = $this->trayecto;
         }
         
         $sql .= " )";
         
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
?>