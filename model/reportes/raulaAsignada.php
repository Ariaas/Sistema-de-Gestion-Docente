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
            $sql = "SELECT
                        e.esp_codigo,
                        uh.hor_dia
                    FROM
                        tbl_espacio e
                    INNER JOIN
                        tbl_horario h ON e.esp_id = h.esp_id
                    INNER JOIN
                        uc_horario uh ON h.hor_id = uh.hor_id
                    WHERE
                        e.esp_estado = 1
                    ORDER BY
                        FIELD(uh.hor_dia, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'),
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
?>