<?php



require_once('model/dbconnection.php');
class Transcripcion extends Connection
{
    private $anio;
    private $fase;
 

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio = trim($valor);
    }

    public function set_fase($valor)
    {
        $this->fase = trim($valor);
    }

    public function obtenerTranscripciones()
    {
        $co = $this->con();
        try {
            $sqlBase = "SELECT DISTINCT
                            d.doc_id AS `IDDocente`,
                            d.doc_cedula AS `CedulaDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            u.uc_nombre AS `NombreUnidadCurricular`,
                            s.sec_codigo AS `NombreSeccion`,
                            h.hor_fase AS `FaseHorario` 
                        FROM
                            tbl_docente d
                        INNER JOIN
                            uc_docente ud ON d.doc_id = ud.doc_id
                        INNER JOIN
                            tbl_uc u ON ud.uc_id = u.uc_id
                        INNER JOIN
                            tbl_trayecto tr ON u.tra_id = tr.tra_id
                        INNER JOIN
                            uc_horario uh ON u.uc_id = uh.uc_id
                        INNER JOIN
                            tbl_horario h ON uh.hor_id = h.hor_id
                        INNER JOIN
                            seccion_horario sh ON h.hor_id = sh.hor_id
                        INNER JOIN
                            tbl_seccion s ON sh.sec_id = s.sec_id
                        WHERE 1=1 AND ud.uc_doc_estado  = '1'";

            $params = [];

           
            if (!empty($this->anio)) {
                $sqlBase .= " AND tr.tra_anio = :anio_param"; 
                $params[':anio_param'] = $this->anio;
            }

          
            if (!empty($this->fase)) {
                $sqlBase .= " AND h.hor_fase = :fase_param";
                $params[':fase_param'] = $this->fase;
            }

           
            $sqlBase .= " ORDER BY `NombreCompletoDocente`, h.hor_fase, u.uc_nombre, s.sec_codigo";

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
       
            $p = $co->prepare("SELECT DISTINCT tra_anio FROM tbl_trayecto WHERE tra_anio IS NOT NULL AND tra_anio != '' ORDER BY tra_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerFases()
    {
        $co = $this->con();
        try {
          
            $p = $co->prepare("SELECT DISTINCT hor_fase FROM tbl_horario WHERE hor_fase IS NOT NULL AND hor_fase != '' ORDER BY hor_fase ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerFases: " . $e->getMessage());
            return false;
        }
    }
}
?>