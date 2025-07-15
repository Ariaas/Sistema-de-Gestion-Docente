<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    // Obtiene los 3 componentes desde la BD para el reporte general.
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT
                    (SELECT COALESCE(SUM(ta.apro_cantidad), 0) FROM tbl_aprobados ta JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo) as total_aprobados_directo,
                    COALESCE(SUM(pa.per_cantidad), 0) as total_en_per,
                    COALESCE(SUM(pa.per_aprobados), 0) as total_aprobados_per
                FROM per_aprobados pa
                WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

    // Devuelve los componentes para cada UC de una sección.
    public function obtenerDatosEstadisticosPorSeccion($seccion_codigo)
    {
        $sql = "SELECT
                    uc.uc_nombre,
                    COALESCE(ta.apro_cantidad, 0) AS aprobados_directo,
                    COALESCE(pa.per_cantidad, 0) AS per_cantidad,
                    COALESCE(pa.per_aprobados, 0) AS per_aprobados
                FROM uc_horario uh
                JOIN tbl_uc uc ON uh.uc_codigo = uc.uc_codigo
                LEFT JOIN tbl_aprobados ta ON uh.uc_codigo = ta.uc_codigo AND uh.sec_codigo = ta.sec_codigo
                LEFT JOIN per_aprobados pa ON uh.uc_codigo = pa.uc_codigo AND uh.sec_codigo = pa.sec_codigo
                WHERE uh.sec_codigo = :seccion_codigo
                GROUP BY uc.uc_codigo
                ORDER BY uc.uc_nombre";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':seccion_codigo', $seccion_codigo, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve los componentes para cada sección de una UC.
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT
                    s.sec_codigo,
                    COALESCE(ta.apro_cantidad, 0) AS aprobados_directo,
                    COALESCE(pa.per_cantidad, 0) AS per_cantidad,
                    COALESCE(pa.per_aprobados, 0) AS per_aprobados
                FROM tbl_seccion s
                LEFT JOIN tbl_aprobados ta ON s.sec_codigo = ta.sec_codigo AND ta.uc_codigo = :uc_codigo
                LEFT JOIN per_aprobados pa ON s.sec_codigo = pa.sec_codigo AND pa.uc_codigo = :uc_codigo
                WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo
                AND (ta.uc_codigo = :uc_codigo OR pa.uc_codigo = :uc_codigo)
                GROUP BY s.sec_codigo
                ORDER BY s.sec_codigo";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':uc_codigo', $uc_codigo, PDO::PARAM_STR);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- El resto de funciones no necesitan cambios ---
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
                    SELECT uc_codigo FROM tbl_aprobados ta JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo
                    UNION
                    SELECT uc_codigo FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo
                ) AND uc.uc_estado = 1 ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
