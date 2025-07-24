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
            // --- CONSULTA SQL CORREGIDA ---
            // Se ajusta el SELECT y el JOIN para usar las columnas correctas.
            $sql = "SELECT DISTINCT
                        CONCAT(e.esp_tipo, ' ', e.esp_numero, ' (', e.esp_edificio, ')') AS aula_completa,
                        uh.hor_dia
                    FROM
                        tbl_espacio e
                    INNER JOIN
                        uc_horario uh ON e.esp_numero = uh.esp_numero 
                                     AND e.esp_tipo = uh.esp_tipo 
                                     AND e.esp_edificio = uh.esp_edificio
                    WHERE
                        e.esp_estado = 1
                    ORDER BY
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'),
                        e.esp_edificio, e.esp_numero ASC";
            
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAulasConAsignaciones: " . $e->getMessage());
            return false;
        }
    }
}