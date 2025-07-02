<?php
require_once('model/dbconnection.php');

class DefinitivoEmit extends Connection
{
    private $docente_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_docente($valor)
    {
        $this->docente_id = trim($valor);
    }

    public function obtenerDatosDefinitivoEmit()
    {
        $co = $this->con();
        try {
            $sqlBase = "SELECT
                            d.doc_id AS `IDDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            d.doc_cedula AS `CedulaDocente`,
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
                        WHERE ud.uc_doc_estado = '1'";

            $params = [];

            if (!empty($this->docente_id)) {
                $sqlBase .= " AND d.doc_id = :docente_id";
                $params[':docente_id'] = $this->docente_id;
            }

            $sqlBase .= " GROUP BY d.doc_id, u.uc_id, s.sec_id";
            $sqlBase .= " ORDER BY `NombreCompletoDocente`, u.uc_nombre, s.sec_codigo";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerDatosDefinitivoEmit: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDocentes()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT doc_id, CONCAT(doc_nombre, ' ', doc_apellido) as NombreCompleto FROM tbl_docente WHERE doc_estado = 1 ORDER BY NombreCompleto");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerDocentes: " . $e->getMessage());
            return false;
        }
    }
}
