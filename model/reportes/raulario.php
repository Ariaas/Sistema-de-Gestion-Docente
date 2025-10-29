<?php
require_once('model/dbconnection.php');

class AularioReport extends Connection
{
    private $anio, $ani_tipo, $fase, $espacio;

    public function __construct() { parent::__construct(); }

    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setAniTipo($valor) { $this->ani_tipo = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }
    public function setEspacio($valor) { $this->espacio = trim($valor); }
    
    public function getAniosActivos() {
        try {
            $sql = "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC";
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
    
    public function getEspacios() {
        try {
            $sql = "SELECT DISTINCT
                        CASE
                            WHEN esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', esp_numero)
                            ELSE CONCAT(LEFT(esp_edificio, 1), '-', esp_numero)
                        END as esp_codigo,
                        esp_tipo
                    FROM tbl_espacio
                    WHERE esp_estado = 1 ORDER BY esp_tipo ASC, esp_numero ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

   public function getAulariosFiltrados()
    {
        // Validar campos requeridos
        if (empty($this->anio) || empty($this->ani_tipo)) return [];
        
        // Si es intensivo, no se requiere fase
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        
        if (!$esIntensivo && empty($this->fase)) return [];

        $allowed_periods = [];
        
        // Si es intensivo, incluir todos los periodos
        if ($esIntensivo) {
            $allowed_periods = ['Fase I', 'Fase II', 'Anual', 'anual', '0'];
        } else {
            // Lógica normal para años regulares
            $allowed_periods = ($this->fase == 1) ? ['Fase I', 'anual', 'Anual', '0'] : ['Fase II', 'anual', 'Anual'];
        }
        
        try {
            $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            
            $sql_base = "SELECT
                            CASE
                                WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                ELSE CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                            END AS esp_codigo,
                            uh.hor_dia,
                            uh.hor_horainicio,
                            uh.hor_horafin,
                            u.uc_codigo,
                            u.uc_nombre,
                            uh.subgrupo,
                            uh.sec_codigo,
                            uh.sec_codigo AS sec_codigo_formatted,
                            uh.doc_cedula,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) as NombreCompletoDocente
                        FROM
                            uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                            AND uh.ani_anio = s.ani_anio
                            AND s.ani_tipo = :ani_tipo_param
                            AND (uh.ani_tipo = s.ani_tipo OR uh.ani_tipo IS NULL)
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        LEFT JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                        WHERE
                            s.ani_anio = :anio_param
                            AND u.uc_estado = 1
                            AND s.sec_estado = 1
                            AND uh.esp_numero IS NOT NULL
                            AND uh.esp_tipo IS NOT NULL";
            
            $period_placeholders = [];
            $i = 0;
            foreach ($allowed_periods as $period) {
                $key = ":period" . $i++;
                $period_placeholders[] = $key;
                $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sql_base .= " AND u.uc_periodo IN ({$in_clause})";

            if (isset($this->espacio) && $this->espacio !== '') {
                $sql_base .= " AND CASE
                                    WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                                    ELSE CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                END = :espacio_param";
                $params[':espacio_param'] = $this->espacio;
            }
            
            $sql_base .= " ORDER BY esp_codigo ASC, uh.hor_horainicio ASC, uh.hor_dia ASC";
            
            error_log("AularioReport SQL: " . $sql_base);
            error_log("AularioReport Params: " . print_r($params, true));
            error_log("AularioReport Allowed Periods: " . print_r($allowed_periods, true));
            
            $stmt = $this->con()->prepare($sql_base);
            $stmt->execute($params);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("AularioReport - Registros encontrados: " . count($result));
            
            // Log de las primeras 5 filas para debug
            foreach (array_slice($result, 0, 5) as $idx => $row) {
                error_log("AularioReport Row $idx: " . json_encode($row));
            }
            
            return $result;

        } catch (PDOException $e) {
            error_log("Error en AularioReport::getAulariosFiltrados: " . $e->getMessage());
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
    
    public function getEspaciosPorAnio($anio, $ani_tipo) {
        if (empty($anio) || empty($ani_tipo)) return $this->getEspacios();
        
        try {
            $sql = "SELECT DISTINCT
                        CASE
                            WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                            ELSE CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                        END as esp_codigo,
                        uh.esp_tipo
                    FROM uc_horario uh
                    INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                        AND uh.ani_anio = s.ani_anio
                    WHERE s.ani_anio = :anio_param
                        AND s.ani_tipo = :ani_tipo_param
                        AND uh.esp_numero IS NOT NULL
                    ORDER BY uh.esp_tipo ASC, uh.esp_numero ASC";
            
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([':anio_param' => $anio, ':ani_tipo_param' => $ani_tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en getEspaciosPorAnio: " . $e->getMessage());
            return $this->getEspacios(); 
        }
    }
}