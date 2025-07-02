<?php
require_once('model/dbconnection.php');

class Transcripcion extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerTranscripciones()
    {
        $co = $this->con();
        try {
            
            $sqlBase = "SELECT
                            d.doc_id AS `IDDocente`,
                            d.doc_cedula AS `CedulaDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            u.uc_nombre AS `NombreUnidadCurricular`,
                            s.sec_codigo AS `NombreSeccion`
                        FROM
                            tbl_docente d
                        INNER JOIN uc_docente ud ON d.doc_id = ud.doc_id
                        INNER JOIN tbl_uc u ON ud.uc_id = u.uc_id
                        INNER JOIN uc_horario uh ON u.uc_id = uh.uc_id
                        INNER JOIN tbl_horario h ON uh.hor_id = h.hor_id
                        INNER JOIN seccion_horario sh ON h.hor_id = sh.hor_id
                        INNER JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                        WHERE ud.uc_doc_estado = '1'
                        GROUP BY d.doc_id, u.uc_id, s.sec_id
                        ORDER BY `NombreCompletoDocente`, u.uc_nombre, s.sec_codigo";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerTranscripciones: " . $e->getMessage());
            return false;
        }
    }
}
?>