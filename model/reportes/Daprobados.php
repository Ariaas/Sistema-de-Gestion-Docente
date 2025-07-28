<?php

require_once('model/dbconnection.php');

class Reporte extends Connection
{
   
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT COALESCE(SUM(ta.apro_cantidad), 0) as total_aprobados_directo
                FROM tbl_aprobados ta
                JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo
                WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

   
    public function obtenerDatosEstadisticosPorSeccion($seccion_codigo)
    {
        $sql = "SELECT uc.uc_nombre, ta.apro_cantidad
                FROM tbl_aprobados ta
                JOIN tbl_uc uc ON ta.uc_codigo = uc.uc_codigo
                WHERE ta.sec_codigo = :seccion_codigo
                ORDER BY uc.uc_nombre";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':seccion_codigo', $seccion_codigo, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

  
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT
                apro.sec_codigo,
                SUM(apro.apro_cantidad) as apro_cantidad
            FROM tbl_aprobados AS apro
            WHERE apro.uc_codigo = :uc_codigo
              AND apro.ani_anio = :anio
              AND apro.ani_tipo = :tipo
              AND apro.apro_estado = 1
            GROUP BY apro.sec_codigo
            ORDER BY apro.sec_codigo";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':uc_codigo', $uc_codigo, PDO::PARAM_STR);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

  

    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo, CONCAT(ani_anio, '|', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo DESC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesPorAnio($anio, $tipo)
    {
        $p = $this->Con()->prepare("SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1 ORDER BY sec_codigo");
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUCPorAnio($anio, $tipo)
    {
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre
                FROM tbl_uc uc
                JOIN tbl_aprobados ta ON uc.uc_codigo = ta.uc_codigo
                JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo
                WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo AND uc.uc_estado = 1
                ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
