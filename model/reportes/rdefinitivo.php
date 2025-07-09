<?php
require_once('model/dbconnection.php');

class DefinitivoEmit extends Connection
{
    private $docente_id;
    private $seccion_id;
    private $fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_docente($valor)
    {
        $this->docente_id = trim($valor);
    }

    public function set_seccion($valor)
    {
        $this->seccion_id = trim($valor);
    }

    public function set_fase($valor)
    {
        $this->fase = trim($valor);
    }

    public function obtenerDatosDefinitivoEmit()
    {
        $co = $this->con();
        try {
            // --- INICIO: LÓGICA DE FILTROS CORREGIDA ---

            // 1. Se define la consulta base sin filtros.
            $sqlBase = "SELECT
                            d.doc_cedula AS IDDocente,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente,
                            d.doc_cedula AS CedulaDocente,
                            u.uc_nombre AS NombreUnidadCurricular,
                            s.sec_codigo AS NombreSeccion
                        FROM
                            tbl_docente d
                        INNER JOIN
                            uc_docente ud ON d.doc_cedula = ud.doc_cedula
                        INNER JOIN
                            tbl_uc u ON ud.uc_codigo = u.uc_codigo
                        INNER JOIN
                            uc_horario uh ON u.uc_codigo = uh.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        WHERE
                            d.doc_estado = 1 AND ud.uc_doc_estado = 1";

            $params = [];

            // 2. Se añaden los filtros a la consulta SOLO si tienen un valor.
            if (!empty($this->docente_id)) {
                $sqlBase .= " AND d.doc_cedula = :doc_cedula";
                $params[':doc_cedula'] = $this->docente_id;
            }

            if (!empty($this->seccion_id)) {
                $sqlBase .= " AND s.sec_codigo = :sec_codigo";
                $params[':sec_codigo'] = $this->seccion_id;
            }

            if ($this->fase !== '' && $this->fase !== null) {
                $sqlBase .= " AND SUBSTRING_INDEX(s.ani_tipo, '-', -1) = :fase";
                $params[':fase'] = $this->fase;
            }

            $sqlBase .= " ORDER BY NombreCompletoDocente, NombreSeccion";
            
            // --- FIN: LÓGICA DE FILTROS CORREGIDA ---

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
            $p = $co->prepare("SELECT doc_cedula, CONCAT(doc_nombre, ' ', doc_apellido) as NombreCompleto FROM tbl_docente WHERE doc_estado = 1 ORDER BY NombreCompleto");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerDocentes: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerSecciones()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE sec_estado = 1 ORDER BY sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}
?>