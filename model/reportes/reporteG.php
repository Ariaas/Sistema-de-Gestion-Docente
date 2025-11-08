<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class ReporteG extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function verificarDatosGenerales()
    {
        try {
            $anios_activos = $this->obtenerAnioActivo();
            $anio = $anios_activos[0]['ani_anio'];
            $tipo = $anios_activos[0]['ani_tipo'];

           
            if ($tipo == 'intensivo') {
                $periodos_permitidos = ['FASE I', 'Fase I', 'ANUAL', 'anual', '0'];
            } else {
                
                $periodos_permitidos = ['FASE I', 'Fase I', 'FASE II', 'Fase II', 'ANUAL', 'anual', '0'];
            }

            $sql = "SELECT COUNT(s.sec_codigo) 
                    FROM tbl_seccion s
                    JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                    WHERE a.ani_activo = 1 AND s.sec_cantidad > 0 AND a.ani_anio = :anio AND a.ani_tipo = :tipo";
            $p = $this->Con()->prepare($sql);
            $p->execute([':anio' => $anio, ':tipo' => $tipo]);
            return $p->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
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

    public function obtenerDatosReporteGeneral($anio, $tipo)
    {
       
        if ($tipo == 'intensivo') {
            $periodos_permitidos = ['FASE I', 'Fase I', 'ANUAL', 'anual', '0'];
        } else {
            
            $periodos_permitidos = ['FASE I', 'Fase I', 'FASE II', 'Fase II', 'ANUAL', 'anual', '0'];
        }

        $sql = "SELECT 'Total Estudiantes' as etiqueta, SUM(sec_cantidad) as cantidad 
                FROM tbl_seccion 
                WHERE ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReporteTodasLasSecciones($anio, $tipo)
    {
        $sql = "SELECT sec_codigo as etiqueta, sec_cantidad as cantidad 
                FROM tbl_seccion 
                WHERE ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1 AND sec_cantidad > 0
                ORDER BY sec_codigo";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReportePorTrayecto($anio, $tipo)
    {
               $sql = "SELECT 
                    trayecto_label AS etiqueta,
                    SUM(sec_cantidad) AS cantidad
                FROM (
                    SELECT DISTINCT
                        s.sec_codigo,
                        s.sec_cantidad,
                        CASE
                            WHEN uc.uc_trayecto = '0' THEN 'Trayecto Inicial'
                            WHEN uc.uc_trayecto = '1' THEN 'Trayecto I'
                            WHEN uc.uc_trayecto = '2' THEN 'Trayecto II'
                            WHEN uc.uc_trayecto = '3' THEN 'Trayecto III'
                            WHEN uc.uc_trayecto = '4' THEN 'Trayecto IV'
                            ELSE 'Indefinido'
                        END AS trayecto_label,
                        uc.uc_trayecto
                    FROM tbl_seccion s
                    INNER JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo
                    INNER JOIN tbl_uc uc ON uh.uc_codigo = uc.uc_codigo
                    WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo AND s.sec_estado = 1
                ) AS subconsulta
                GROUP BY trayecto_label, uc_trayecto
                ORDER BY uc_trayecto";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
