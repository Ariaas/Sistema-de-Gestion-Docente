<?php
require_once('model/dbconnection.php');

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
                p.ani_origen
             FROM tbl_prosecusion p
             WHERE p.pro_estado = 1 AND p.pro_cantidad > 0
             ORDER BY p.ani_origen DESC"
        );
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReporteGeneral($anio)
    {
        $sql = "SELECT 'Total Prosecución' as etiqueta, SUM(p.pro_cantidad) as cantidad 
                FROM tbl_prosecusion p
                WHERE p.ani_origen = :anio AND p.pro_estado = 1";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReporteTodasLasSecciones($anio)
    {
        $sql = "SELECT p.sec_origen as etiqueta, p.pro_cantidad as cantidad 
                FROM tbl_prosecusion p
                WHERE p.ani_origen = :anio AND p.pro_estado = 1 AND p.pro_cantidad > 0
                ORDER BY p.sec_origen";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosReportePorTrayecto($anio)
    {
        $sql = "SELECT 
                    CASE
                        WHEN SUBSTR(p.sec_origen, 1, 1) = '0' THEN 'Trayecto Inicial'
                        WHEN SUBSTR(p.sec_origen, 1, 1) = '1' THEN 'Trayecto I'
                        WHEN SUBSTR(p.sec_origen, 1, 1) = '2' THEN 'Trayecto II'
                        WHEN SUBSTR(p.sec_origen, 1, 1) = '3' THEN 'Trayecto III'
                        WHEN SUBSTR(p.sec_origen, 1, 1) = '4' THEN 'Trayecto IV'
                        ELSE 'Indefinido'
                    END AS etiqueta,
                    SUM(p.pro_cantidad) AS cantidad,
                    SUBSTR(p.sec_origen, 1, 1) as trayecto_num
                FROM tbl_prosecusion p
                WHERE p.ani_origen = :anio AND p.pro_estado = 1 AND p.pro_cantidad > 0
                GROUP BY etiqueta, trayecto_num
                ORDER BY trayecto_num";
        $p = $this->Con()->prepare($sql);
        $p->execute([':anio' => $anio]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
