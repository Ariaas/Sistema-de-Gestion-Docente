<?php
require_once('model/dbconnection.php');

class SeccionReport extends Connection
{
    private $seccion_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_seccion_id($valor)
    {
        $this->seccion_id = trim($valor);
    }

    public function getSecciones()
    {
        $co = $this->con();
        try {
            $sql = "SELECT s.sec_codigo FROM tbl_seccion s 
                    JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo 
                    WHERE a.ani_activo = 1 AND s.sec_estado = 1 
                    ORDER BY s.sec_codigo ASC";
            $p = $co->prepare($sql);
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getSecciones: " . $e->getMessage());
            return false;
        }
    }

    public function getDistinctTimeSlotsForSeccion()
    {
        if (empty($this->seccion_id)) return [];
        $co = $this->con();
        try {
            $sql = "SELECT DISTINCT uh.hor_horainicio, uh.hor_horafin FROM uc_horario uh 
                    WHERE uh.sec_codigo = :seccion_id_param ORDER BY uh.hor_horainicio ASC";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':seccion_id_param', $this->seccion_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getDistinctTimeSlotsForSeccion: " . $e->getMessage());
            return [];
        }
    }

    public function getHorarioDataBySeccion()
    {
        if (empty($this->seccion_id)) return [];
        $co = $this->con();
        try {
            // MODIFICACIÓN: Se elimina el JOIN a tbl_horario. El esp_codigo se obtiene de uc_horario.
            $sql = "SELECT
                    uh.hor_dia,
                    uh.hor_horainicio,
                    uh.hor_horafin,
                    u.uc_nombre,
                    COALESCE(uh.esp_codigo, 'N/A') AS esp_codigo,
                    GROUP_CONCAT(DISTINCT CONCAT(d.doc_nombre, ' ', d.doc_apellido) SEPARATOR '\n') AS NombreCompletoDocente
                FROM
                    uc_horario uh
                JOIN
                    tbl_uc u ON uh.uc_codigo = u.uc_codigo AND u.uc_estado = 1
                LEFT JOIN
                    uc_docente ud ON uh.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                LEFT JOIN
                    tbl_docente d ON ud.doc_cedula = d.doc_cedula AND d.doc_estado = 1
                WHERE
                    uh.sec_codigo = :seccion_id_param
                GROUP BY
                    uh.hor_dia, uh.hor_horafin, uh.hor_horainicio, u.uc_nombre, uh.esp_codigo
                ORDER BY
                    uh.hor_horainicio ASC, uh.hor_dia ASC";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':seccion_id_param', $this->seccion_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getHorarioDataBySeccion: " . $e->getMessage());
            return false;
        }
    }

     public function getSeccionCodigoById($id)
    {
        if (empty($id)) return null;
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE sec_codigo = :id_param");
            $p->bindParam(':id_param', $id, PDO::PARAM_STR);
            $p->execute();
            $result = $p->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['sec_codigo'] : null;
        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getSeccionCodigoById: " . $e->getMessage());
            return null;
        }
    }
}
?>