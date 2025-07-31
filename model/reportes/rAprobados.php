<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT
                    (SELECT COALESCE(SUM(ta.apro_cantidad), 0)
                     FROM tbl_aprobados ta
                     WHERE ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1)
                    +
                    (SELECT COALESCE(SUM(pa.per_aprobados), 0)
                     FROM per_aprobados pa
                     WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1)
                AS total_aprobados";

        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerDatosEstadisticosPorSeccion($seccion_codigo, $anio, $tipo)
    {
        $sql = "SELECT T.uc_nombre, SUM(T.total_aprobados) as total_aprobados
                FROM (
                    SELECT uc.uc_nombre, ta.apro_cantidad as total_aprobados
                    FROM tbl_aprobados ta
                    JOIN tbl_uc uc ON ta.uc_codigo = uc.uc_codigo
                    WHERE ta.sec_codigo = :seccion_codigo AND ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1
                    UNION ALL
                    SELECT uc.uc_nombre, pa.per_aprobados as total_aprobados
                    FROM per_aprobados pa
                    JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                    WHERE pa.sec_codigo = :seccion_codigo AND pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1
                ) AS T
                GROUP BY T.uc_nombre
                ORDER BY T.uc_nombre";
        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':seccion_codigo', $seccion_codigo, PDO::PARAM_STR);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT T.sec_codigo, SUM(T.total_aprobados) as total_aprobados
                FROM (
                    SELECT ta.sec_codigo, ta.apro_cantidad as total_aprobados
                    FROM tbl_aprobados ta
                    WHERE ta.uc_codigo = :uc_codigo AND ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1
                    UNION ALL
                    SELECT pa.sec_codigo, pa.per_aprobados as total_aprobados
                    FROM per_aprobados pa
                    WHERE pa.uc_codigo = :uc_codigo AND pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1
                ) AS T
                GROUP BY T.sec_codigo
                ORDER BY T.sec_codigo";
        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':uc_codigo', $uc_codigo, PDO::PARAM_STR);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
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
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre FROM tbl_uc uc WHERE uc.uc_codigo IN (
                    SELECT ta.uc_codigo FROM tbl_aprobados ta JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo
                    UNION
                    SELECT pa.uc_codigo FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo
                ) AND uc.uc_estado = 1 ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
