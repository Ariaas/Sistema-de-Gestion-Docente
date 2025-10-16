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
            $sql = "SELECT ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC";
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAnios: " . $e->getMessage());
            return [];
        }
    }

    
    public function getAulasConAsignaciones($anio)
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
                    INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    WHERE
                        s.ani_anio = :anio AND e.esp_estado = 1
                    ORDER BY
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                        e.esp_edificio, e.esp_tipo, e.esp_numero ASC";
            
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAulasConAsignaciones: " . $e->getMessage());
            return false;
        }
    }
}
?>