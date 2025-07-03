<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{

    // Obtiene los datos agregados para el reporte general de un año.
    public function obtenerDatosEstadisticosPorAnio($anio_id)
    {
        $sql = "SELECT
                    COALESCE(SUM(s.sec_cantidad), 0) as total_estudiantes,
                    COALESCE(SUM(r.rem_cantidad), 0) as total_en_per,
                    COALESCE(SUM(ra.per_aprobados), 0) as total_aprobados_per
                FROM tbl_anio a
                LEFT JOIN tbl_seccion s ON a.ani_id = s.ani_id
                LEFT JOIN tbl_remedial r ON s.sec_id = r.sec_id AND r.rem_estado = 1
                LEFT JOIN remedial_anio ra ON r.rem_id = ra.rem_id
                WHERE a.ani_id = :anio_id";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio_id', $anio_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene los datos para una sección específica.
    public function obtenerDatosEstadisticosPorSeccion($seccion_id)
    {
        $sql = "SELECT
                    s.sec_cantidad as total_estudiantes,
                    COALESCE(SUM(r.rem_cantidad), 0) as total_en_per,
                    COALESCE(SUM(ra.per_aprobados), 0) as total_aprobados_per
                FROM tbl_seccion s
                LEFT JOIN tbl_remedial r ON s.sec_id = r.sec_id AND r.rem_estado = 1
                LEFT JOIN remedial_anio ra ON r.rem_id = ra.rem_id
                WHERE s.sec_id = :seccion_id
                GROUP BY s.sec_id";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':seccion_id', $seccion_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene los datos para una unidad curricular específica en un año.
    public function obtenerDatosEstadisticosPorUC($uc_id, $anio_id)
    {
        $sql = "SELECT
                    COALESCE(SUM(s.sec_cantidad), 0) as total_estudiantes,
                    COALESCE(SUM(r.rem_cantidad), 0) as total_en_per,
                    COALESCE(SUM(ra.per_aprobados), 0) as total_aprobados_per
                FROM tbl_remedial r
                JOIN tbl_seccion s ON r.sec_id = s.sec_id
                JOIN remedial_anio ra ON r.rem_id = ra.rem_id
                WHERE r.uc_id = :uc_id AND s.ani_id = :anio_id AND r.rem_estado = 1";

        $p = $this->Con()->prepare($sql);
        $p->bindParam(':uc_id', $uc_id, PDO::PARAM_INT);
        $p->bindParam(':anio_id', $anio_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

    // --- Funciones para poblar los selects de los filtros ---

    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesPorAnio($anio_id)
    {
        $p = $this->Con()->prepare("SELECT sec_id, sec_codigo FROM tbl_seccion WHERE ani_id = :anio_id AND sec_estado = 1 ORDER BY sec_codigo");
        $p->bindParam(':anio_id', $anio_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUCPorAnio($anio_id)
    {
        $sql = "SELECT DISTINCT uc.uc_id, uc.uc_nombre
                FROM tbl_uc uc
                JOIN tbl_remedial r ON uc.uc_id = r.uc_id
                JOIN tbl_seccion s ON r.sec_id = s.sec_id
                WHERE s.ani_id = :anio_id AND uc.uc_estado = 1
                ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio_id', $anio_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
 