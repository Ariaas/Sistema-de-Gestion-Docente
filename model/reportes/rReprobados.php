<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    // Calcula el total de estudiantes que reprobaron el PER en un año.
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT (COALESCE(SUM(per_cantidad), 0) - COALESCE(SUM(per_aprobados), 0)) as total_reprobados_per
                FROM per_aprobados
                WHERE ani_anio = :anio AND ani_tipo = :tipo AND pa_estado = 1";

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

    // Devuelve una lista de UCs con sus reprobados del PER para una sección.
    public function obtenerDatosEstadisticosPorSeccion($seccion_codigo, $anio, $tipo)
    {
        $sql = "SELECT uc.uc_nombre, (SUM(pa.per_cantidad) - SUM(pa.per_aprobados)) as reprobados_per
                FROM per_aprobados pa
                JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                WHERE pa.sec_codigo = :seccion_codigo
                  AND pa.ani_anio = :anio
                  AND pa.ani_tipo = :tipo
                  AND pa.pa_estado = 1
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

    // Devuelve una lista de secciones con sus reprobados del PER para una UC.
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT pa.sec_codigo, (SUM(pa.per_cantidad) - SUM(pa.per_aprobados)) as reprobados_per
                FROM per_aprobados pa
                WHERE pa.uc_codigo = :uc_codigo
                  AND pa.ani_anio = :anio
                  AND pa.ani_tipo = :tipo
                  AND pa.pa_estado = 1
                GROUP BY pa.sec_codigo
                ORDER BY pa.sec_codigo";

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
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre
                FROM tbl_uc uc
                JOIN per_aprobados pa ON uc.uc_codigo = pa.uc_codigo
                WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND uc.uc_estado = 1 AND pa.pa_estado = 1
                ORDER BY uc.uc_nombre";

        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
