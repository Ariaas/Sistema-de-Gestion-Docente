<?php
require_once('model/dbconnection.php');

class Reporthorariodocente extends Connection 
{
    private $docente_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_docente_id($valor)
    {
        $this->docente_id = trim($valor);
    }

    public function getDocentes()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT doc_id, doc_cedula, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido ASC, doc_nombre ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Reporthorariodocente::getDocentes: " . $e->getMessage()); 
            return false;
        }
    }

    public function getDocenteNameById($id)
    {
        if (empty($id)) return null;
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT CONCAT(doc_nombre, ' ', doc_apellido) AS NombreCompleto FROM tbl_docente WHERE doc_id = :id_param");
            $p->bindParam(':id_param', $id, PDO::PARAM_INT);
            $p->execute();
            $result = $p->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['NombreCompleto'] : null;
        } catch (PDOException $e) {
            error_log("Error en Reporthorariodocente::getDocenteNameById: " . $e->getMessage());
            return null;
        }
    }

    public function getDistinctTimeSlotsForDocente()
    {
        if (empty($this->docente_id)) {
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
                        uc_docente ud ON uh.uc_id = ud.uc_id
                    WHERE
                        ud.doc_id = :docente_id_param
                    ORDER BY
                        uh.hor_inicio ASC";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':docente_id_param', $this->docente_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Reporthorariodocente::getDistinctTimeSlotsForDocente: " . $e->getMessage()); 
            return [];
        }
    }

    public function getHorarioDataByDocente()
    {
        if (empty($this->docente_id)) {
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
                        e.esp_codigo AS NombreEspacio
                    FROM
                        uc_docente ud
                    JOIN
                        tbl_uc u ON ud.uc_id = u.uc_id
                    JOIN
                        uc_horario uh ON u.uc_id = uh.uc_id
                    JOIN
                        tbl_horario h_link ON uh.hor_id = h_link.hor_id
                    JOIN
                        tbl_espacio e ON h_link.esp_id = e.esp_id
                    LEFT JOIN 
                        seccion_horario sh ON h_link.hor_id = sh.hor_id
                    LEFT JOIN
                        tbl_seccion s ON sh.sec_id = s.sec_id
                    WHERE
                        ud.doc_id = :docente_id_param
                        AND ud.uc_doc_estado  = '1'
                    ORDER BY
                        uh.hor_inicio ASC, uh.hor_dia ASC, u.uc_codigo ASC, s.sec_codigo ASC, e.esp_codigo ASC";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':docente_id_param', $this->docente_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Reporthorariodocente::getHorarioDataByDocente: " . $e->getMessage()); // Nombre de clase cambiado
            return false;
        }
    }
}
?>