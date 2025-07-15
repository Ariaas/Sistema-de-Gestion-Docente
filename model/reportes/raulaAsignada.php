<?php
require_once('model/dbconnection.php');

class AsignacionAulasReport extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAulasConAsignaciones()
    {
        $co = $this->con();
        try {
            // Consulta simple para obtener las aulas usadas por día
            $sql = "SELECT DISTINCT
                        e.esp_codigo,
                        uh.hor_dia
                    FROM
                        tbl_espacio e
                    INNER JOIN
                        uc_horario uh ON e.esp_codigo = uh.esp_codigo
                    WHERE
                        e.esp_estado = 1 AND uh.esp_codigo IS NOT NULL
                    ORDER BY
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'),
                        e.esp_codigo ASC";
            
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAulasConAsignaciones: " . $e->getMessage());
            return false;
        }
    }
}