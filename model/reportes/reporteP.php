<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class ReporteP extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function verificarDatosGenerales()
    {
        try {
            $sql = "SELECT COUNT(p.sec_origen) 
                    FROM tbl_prosecusion p
                    WHERE p.pro_cantidad > 0 AND p.pro_estado = 1";
            $p = $this->Con()->prepare($sql);
            $p->execute();
            return $p->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerAniosDeOrigen()
    {
        $p = $this->Con()->prepare(
            "SELECT DISTINCT 
                p.ani_origen,
                p.ani_tipo_origen,
                CONCAT(p.ani_origen, '|', p.ani_tipo_origen) as anio_completo
             FROM tbl_prosecusion p
             WHERE p.pro_estado = 1 AND p.pro_cantidad > 0
             ORDER BY p.ani_origen DESC, p.ani_tipo_origen ASC"
        );
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReporteGeneral($anio, $tipo)
    {
        $sql = "SELECT 'Total Prosecución' as etiqueta, SUM(p.pro_cantidad) as cantidad 
                FROM tbl_prosecusion p
                WHERE p.ani_origen = :anio AND p.ani_tipo_origen = :tipo AND p.pro_estado = 1";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReporteTodasLasSecciones($anio, $tipo)
    {
        $sql = "SELECT p.sec_origen as etiqueta, p.pro_cantidad as cantidad 
                FROM tbl_prosecusion p
                WHERE p.ani_origen = :anio AND p.ani_tipo_origen = :tipo AND p.pro_estado = 1 AND p.pro_cantidad > 0
                ORDER BY p.sec_origen";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReportePorTrayecto($anio, $tipo)
    {
        $sql = "SELECT 
                    CASE
                        WHEN trayecto_num = '0' THEN 'Trayecto Inicial'
                        WHEN trayecto_num = '1' THEN 'Trayecto I'
                        WHEN trayecto_num = '2' THEN 'Trayecto II'
                        WHEN trayecto_num = '3' THEN 'Trayecto III'
                        WHEN trayecto_num = '4' THEN 'Trayecto IV'
                        ELSE 'Indefinido'
                    END AS etiqueta,
                    SUM(pro_cantidad) AS cantidad,
                    trayecto_num
                FROM (
                    SELECT 
                        p.pro_cantidad,

                        SUBSTRING(p.sec_origen, 
                            CASE 
                                WHEN p.sec_origen LIKE 'IIN%' THEN 4  
                                ELSE 3  
                            END, 
                            1
                        ) AS trayecto_num
                    FROM tbl_prosecusion p
                    WHERE p.ani_origen = :anio AND p.ani_tipo_origen = :tipo AND p.pro_estado = 1 AND p.pro_cantidad > 0
                ) AS subquery
                WHERE trayecto_num IN ('0', '1', '2', '3', '4')
                GROUP BY etiqueta, trayecto_num
                ORDER BY trayecto_num";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio, ':tipo' => $tipo]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
