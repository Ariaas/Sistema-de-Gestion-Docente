<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    // Obtiene los 3 componentes desde la BD para el reporte general.
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT
                    (SELECT COALESCE(SUM(ta.apro_cantidad), 0) FROM tbl_aprobados ta WHERE ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1) as total_aprobados_directo,
                    (SELECT COALESCE(SUM(pa.per_cantidad), 0) FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1) as total_en_per,
                    (SELECT COALESCE(SUM(pa.per_aprobados), 0) FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1) as total_aprobados_per";
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

    // Devuelve los componentes para cada UC de una sección.
    public function obtenerDatosEstadisticosPorSeccion($seccion_codigo, $anio, $tipo)
    {
        $sql = "SELECT
                    uc.uc_nombre,
                    SUM(COALESCE(T.apro_cantidad, 0)) AS aprobados_directo,
                    SUM(COALESCE(T.per_cantidad, 0)) AS per_cantidad,
                    SUM(COALESCE(T.per_aprobados, 0)) AS per_aprobados
                FROM (
                    SELECT uc_codigo, apro_cantidad, 0 AS per_cantidad, 0 AS per_aprobados
                    FROM tbl_aprobados
                    WHERE sec_codigo = :seccion_codigo AND ani_anio = :anio AND ani_tipo = :tipo AND apro_estado = 1
                    UNION ALL
                    SELECT uc_codigo, 0 AS apro_cantidad, per_cantidad, per_aprobados
                    FROM per_aprobados
                    WHERE sec_codigo = :seccion_codigo AND ani_anio = :anio AND ani_tipo = :tipo AND pa_estado = 1
                ) AS T
                JOIN tbl_uc uc ON T.uc_codigo = uc.uc_codigo
                GROUP BY uc.uc_codigo, uc.uc_nombre
                ORDER BY uc.uc_nombre";

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

    // Devuelve los componentes para cada sección de una UC.
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT
                    T.sec_codigo,
                    SUM(COALESCE(T.apro_cantidad, 0)) AS aprobados_directo,
                    SUM(COALESCE(T.per_cantidad, 0)) AS per_cantidad,
                    SUM(COALESCE(T.per_aprobados, 0)) AS per_aprobados
                FROM (
                    SELECT sec_codigo, apro_cantidad, 0 AS per_cantidad, 0 AS per_aprobados
                    FROM tbl_aprobados
                    WHERE uc_codigo = :uc_codigo AND ani_anio = :anio AND ani_tipo = :tipo AND apro_estado = 1
                    UNION ALL
                    SELECT sec_codigo, 0 AS apro_cantidad, per_cantidad, per_aprobados
                    FROM per_aprobados
                    WHERE uc_codigo = :uc_codigo AND ani_anio = :anio AND ani_tipo = :tipo AND pa_estado = 1
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
                    SELECT uc_codigo FROM tbl_aprobados ta WHERE ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1
                    UNION
                    SELECT uc_codigo FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1
                ) AND uc.uc_estado = 1 ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
