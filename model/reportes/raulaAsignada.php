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
                        e.esp_id,
                        e.esp_codigo,
                        e.esp_tipo,
                        uh.hor_dia,
                        uh.hor_inicio,
                        uh.hor_fin
                    FROM
                        tbl_espacio e
                    INNER JOIN
                        tbl_horario h ON e.esp_id = h.esp_id 
                    INNER JOIN
                        uc_horario uh ON h.hor_id = uh.hor_id 
                    WHERE
                       
                     AND ud.uc_doc_estado  = '1'
                    ORDER BY
                        e.esp_codigo ASC,
              
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'),
                        uh.hor_inicio ASC";
            
            $stmt = $co->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

           
            $aulasAgrupadas = [];
            foreach ($results as $row) {
                $espacioKey = $row['esp_id'];
                if (!isset($aulasAgrupadas[$espacioKey])) {
                    $aulasAgrupadas[$espacioKey] = [
                        'esp_codigo' => $row['esp_codigo'],
                        'esp_tipo' => $row['esp_tipo'],
                        'horarios_por_dia' => []
                    ];
                }
                $diaKey = $row['hor_dia'];
                if (!isset($aulasAgrupadas[$espacioKey]['horarios_por_dia'][$diaKey])) {
                    $aulasAgrupadas[$espacioKey]['horarios_por_dia'][$diaKey] = [];
                }
                $aulasAgrupadas[$espacioKey]['horarios_por_dia'][$diaKey][] = [
                    'inicio' => $row['hor_inicio'],
                    'fin' => $row['hor_fin']
                ];
            }
            return $aulasAgrupadas;

        } catch (PDOException $e) {
            error_log("Error en AsignacionAulasReport::getAulasConAsignaciones: " . $e->getMessage());
            return false;
        }
    }
}
?>