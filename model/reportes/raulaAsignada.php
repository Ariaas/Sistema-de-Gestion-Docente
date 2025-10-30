<?php
require_once('model/dbconnection.php');

class AsignacionAulasReport extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    
    public function getAnios()
    {
        $co = $this->con();
        try {
            $sql = "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC";
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAnios: " . $e->getMessage());
            return [];
        }
    }

    public function getFases()
    {
        $co = $this->con();
        try {
            $sql = "SELECT DISTINCT fase_numero FROM tbl_fase ORDER BY fase_numero ASC";
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getFases: " . $e->getMessage());
            return [];
        }
    }

    
    public function getAulasConAsignaciones($anio, $ani_tipo, $fase = null)
    {
        $co = $this->con();
        try 
        {
            $sql = "SELECT DISTINCT
                        CONCAT(e.esp_tipo, ' ', e.esp_numero, ' (', e.esp_edificio, ')') AS aula_completa,
                        uh.hor_dia
                    FROM
                        uc_horario uh
                    INNER JOIN tbl_espacio e ON uh.esp_numero = e.esp_numero AND uh.esp_tipo = e.esp_tipo AND uh.esp_edificio = e.esp_edificio
                    INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio
                    INNER JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    WHERE
                        s.ani_anio = :anio 
                        AND s.ani_tipo = :ani_tipo
                        AND e.esp_estado = 1";
            
            
            $esIntensivo = strtolower($ani_tipo) === 'intensivo';
            if (!$esIntensivo && !empty($fase)) {
                
                $allowed_periods = [];
                if ($fase == 1) {
                    $allowed_periods = ['Fase I', 'Anual'];
                } elseif ($fase == 2) {
                    $allowed_periods = ['Fase II', 'Anual'];
                }
                
                if (!empty($allowed_periods)) {
                    $placeholders = implode(',', array_fill(0, count($allowed_periods), '?'));
                    $sql .= " AND u.uc_periodo IN ($placeholders)";
                }
            }
            
            $sql .= " ORDER BY
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                        e.esp_edificio, e.esp_tipo, e.esp_numero ASC";
            
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->bindParam(':ani_tipo', $ani_tipo, PDO::PARAM_STR);
            
            
            if (!$esIntensivo && !empty($allowed_periods)) {
                $paramIndex = 1;
                foreach ($allowed_periods as $period) {
                    $stmt->bindValue($paramIndex++, $period, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAulasConAsignaciones: " . $e->getMessage());
            return false;
        }
    }
}
?>