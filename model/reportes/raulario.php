<?php
require_once('model/dbconnection.php');


class AularioReport extends Connection
{
    private $espacio_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_espacio_id($valor)
    {
        $this->espacio_id = trim($valor);
    }

    public function getEspacios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT esp_id, esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getEspacios: " . $e->getMessage());
            return false;
        }
    }

    public function getDistinctTimeSlotsForEspacio()
    {
        if (empty($this->espacio_id)) {
            return [];
        }
        $co = $this->con();
        try {
            $sql = "SELECT DISTINCT
                        uh.hor_inicio,
                        uh.hor_fin
                    FROM
                        uc_horario uh
                    JOIN
                        tbl_horario h_link ON uh.hor_id = h_link.hor_id
                    WHERE
                        h_link.esp_id = :espacio_id_param
                    ORDER BY
                        uh.hor_inicio ASC";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':espacio_id_param', $this->espacio_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getDistinctTimeSlotsForEspacio: " . $e->getMessage());
            return [];
        }
    }

    public function getHorarioDataByEspacio()
    {
        if (empty($this->espacio_id)) {
            return [];
        }
        $co = $this->con();
        try {
            $sql = "SELECT
                        uh.hor_dia,
                        uh.hor_inicio,
                        uh.hor_fin,
                        COALESCE(u.uc_codigo, u.uc_nombre) AS UnidadDisplay,
                        u.uc_nombre AS NombreCompletoUC,
                        s.sec_codigo AS NombreSeccion,
                        CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente
                    FROM
                        uc_horario uh
                    JOIN
                        tbl_uc u ON uh.uc_id = u.uc_id
                    JOIN
                        tbl_horario h_link ON uh.hor_id = h_link.hor_id 
                    LEFT JOIN 
                        seccion_horario sh ON h_link.hor_id = sh.hor_id 
                    LEFT JOIN
                        tbl_seccion s ON sh.sec_id = s.sec_id
                    LEFT JOIN
                        uc_docente ud ON u.uc_id = ud.uc_id
                    LEFT JOIN
                        tbl_docente d ON ud.doc_id = d.doc_id
                    WHERE
                        h_link.esp_id = :espacio_id_param AND ud.uc_doc_estado  = '1'
                    ORDER BY
                        uh.hor_inicio ASC, u.uc_codigo ASC, s.sec_codigo ASC";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':espacio_id_param', $this->espacio_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AularioReport::getHorarioDataByEspacio: " . $e->getMessage());
            return false;
        }
    }

     public function getEspacioCodigoById($id)
    {
        if (empty($id)) return null;
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT esp_codigo FROM tbl_espacio WHERE esp_id = :id_param");
            $p->bindParam(':id_param', $id, PDO::PARAM_INT);
            $p->execute();
            $result = $p->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['esp_codigo'] : null;
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getEspacioCodigoById: " . $e->getMessage());
            return null;
        }
    }
}
?>