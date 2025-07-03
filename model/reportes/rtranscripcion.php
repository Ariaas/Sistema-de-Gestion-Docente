<?php
require_once('model/dbconnection.php');

class Transcripcion extends Connection
{
    private $anio_id;
    private $fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor) {
        $this->anio_id = trim($valor);
    }

    public function set_fase($valor) {
        $this->fase = trim($valor);
    }

    public function obtenerTranscripciones()
    {
        $co = $this->con();
        try {
            
            // --- CORRECCIÓN DEFINITIVA DE LA CONSULTA ---
            // Se construye la consulta dinámicamente para aplicar los filtros
            // en la cláusula ON de los LEFT JOIN, lo que asegura que no se pierdan datos.

            $params = [];
            $sqlSelect = "SELECT
                            d.doc_id AS `IDDocente`,
                            d.doc_cedula AS `CedulaDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            u.uc_nombre AS `NombreUnidadCurricular`,
                            s.sec_codigo AS `NombreSeccion`";

            $sqlJoins = " FROM tbl_docente d
                        LEFT JOIN uc_docente ud ON d.doc_id = ud.doc_id AND ud.uc_doc_estado = '1'
                        LEFT JOIN tbl_uc u ON ud.uc_id = u.uc_id";

            // Se añade el filtro de FASE directamente al JOIN de tbl_uc
            if ($this->fase !== '') {
                $sqlJoins .= " AND u.uc_periodo = :fase";
                $params[':fase'] = $this->fase;
            }

            $sqlJoins .= " LEFT JOIN uc_horario uh ON u.uc_id = uh.uc_id
                         LEFT JOIN tbl_horario h ON uh.hor_id = h.hor_id
                         LEFT JOIN seccion_horario sh ON h.hor_id = sh.hor_id
                         LEFT JOIN tbl_seccion s ON sh.sec_id = s.sec_id";

            // Se añade el filtro de AÑO directamente al JOIN de tbl_seccion
            if (!empty($this->anio_id)) {
                $sqlJoins .= " AND s.ani_id = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            $sqlWhere = " WHERE u.uc_id IS NOT NULL";
            
            $sqlOrder = " ORDER BY `NombreCompletoDocente`, u.uc_nombre, s.sec_codigo";

            // Se arma la consulta final
            $sqlBase = $sqlSelect . $sqlJoins . $sqlWhere . $sqlOrder;


            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerTranscripciones: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}
?>