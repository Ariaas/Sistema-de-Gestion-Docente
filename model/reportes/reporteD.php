<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class Reporte extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerAnioActivo()
    {
        $p = $this->Con()->prepare(
            "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, '|', ani_tipo) as anio_completo 
             FROM tbl_anio 
             WHERE ani_estado = 1 AND ani_activo = 1 
             ORDER BY ani_anio DESC, ani_tipo ASC"
        );
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarDatosDocentesConHoras()
    {
        try {
            
            $anios_activos = $this->obtenerAnioActivo();
            if (empty($anios_activos)) {
                return false; 
            }

            
            $anio = $anios_activos[0]['ani_anio'];
            $tipo = $anios_activos[0]['ani_tipo']; 

            
            if ($tipo == 'intensivo') {
                $periodos_permitidos = ['FASE I', 'Fase I', 'ANUAL', 'anual', '0'];
            } else {
                
                $periodos_permitidos = ['FASE I', 'Fase I', 'FASE II', 'Fase II', 'ANUAL', 'anual', '0'];
            }

            $params = [':anio_anio' => $anio, ':anio_tipo' => $tipo];
            $placeholders = [];
            foreach ($periodos_permitidos as $index => $periodo) {
                $key = ":periodo_" . $index;
                $placeholders[] = $key;
                $params[strval($key)] = $periodo;
            }

            $sql = "SELECT COUNT(DISTINCT uh.doc_cedula) 
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    WHERE s.ani_anio = :anio_anio
                      AND s.ani_tipo = :anio_tipo
                      AND s.sec_estado = 1
                      AND uh.doc_cedula IS NOT NULL
                      AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")";

            $p = $this->Con()->prepare($sql);
            $p->execute($params);
            return $p->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en verificarDatosDocentesConHoras: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDatosReporteHorasDocente($anio, $tipo, $min_horas = 0)
    {

        
        if ($tipo == 'intensivo') {
            $periodos_permitidos = ['FASE I', 'Fase I', 'ANUAL', 'anual', '0'];
        } else {
            
            $periodos_permitidos = ['FASE I', 'Fase I', 'FASE II', 'Fase II', 'ANUAL', 'anual', '0'];
        }

        $params = [
            ':anio_anio' => $anio,
            ':anio_tipo' => $tipo,
            ':min_horas' => $min_horas
        ];
        $placeholders = [];
        foreach ($periodos_permitidos as $index => $periodo) {
            $key = ":periodo_" . $index;
            $placeholders[] = $key;
            $params[strval($key)] = $periodo;
        }

        $sql = "
            SELECT 
                CONCAT(total_horas, ' horas') AS etiqueta, 
                COUNT(doc_cedula) AS cantidad
            FROM (
                
                WITH HorasPorBloqueUnico AS (
                 
                    SELECT DISTINCT
                        uh.doc_cedula,
                        uh.uc_codigo,
                        uh.hor_dia,
                        uh.hor_horainicio,
                        uh.hor_horafin,
                        
                        ROUND(TIMESTAMPDIFF(MINUTE, STR_TO_DATE(uh.hor_horainicio, '%H:%i'), STR_TO_DATE(uh.hor_horafin, '%H:%i')) / 40) AS horas_bloque
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    WHERE
                        s.ani_anio = :anio_anio
                        AND s.ani_tipo = :anio_tipo
                        AND s.sec_estado = 1
                        AND uh.doc_cedula IS NOT NULL
                        AND u.uc_periodo IN (" . implode(', ', $placeholders) . ") 
                )
                
                SELECT
                    hbu.doc_cedula,
                    SUM(hbu.horas_bloque) AS total_horas
                FROM HorasPorBloqueUnico hbu
                WHERE hbu.doc_cedula IS NOT NULL
                GROUP BY hbu.doc_cedula
            ) AS total_horas_docente
            WHERE total_horas > :min_horas 
            GROUP BY total_horas
            ORDER BY total_horas ASC
        ";

        try {
            $p = $this->Con()->prepare($sql);
            $p->execute($params); 
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosReporteHorasDocente: " . $e->getMessage());
            return false;
        }
    }
}
