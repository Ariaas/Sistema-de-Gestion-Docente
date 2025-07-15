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
            
            $sqlBase = "SELECT
                            d.doc_cedula AS `IDDocente`,
                            d.doc_cedula AS `CedulaDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            u.uc_nombre AS `NombreUnidadCurricular`,
                            -- ▼▼▼ CAMBIO PARA FORMATEAR LA SECCIÓN ▼▼▼
                            CASE
                                WHEN LEFT(s.sec_codigo, 1) IN ('1', '2') THEN CONCAT('IN', s.sec_codigo)
                                WHEN LEFT(s.sec_codigo, 1) IN ('3', '4') THEN CONCAT('IIN', s.sec_codigo)
                                ELSE CAST(s.sec_codigo AS CHAR)
                            END AS `NombreSeccion`
                        FROM
                            uc_docente ud
                        INNER JOIN
                            tbl_docente d ON ud.doc_cedula = d.doc_cedula
                        INNER JOIN
                            tbl_uc u ON ud.uc_codigo = u.uc_codigo
                        INNER JOIN
                            uc_horario uh ON u.uc_codigo = uh.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        ";
            
            $conditions = ["ud.uc_doc_estado = 1"];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            if ($this->fase !== '') {
                $conditions[] = "u.uc_periodo = :fase";
                $params[':fase'] = $this->fase;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sqlBase .= " ORDER BY `NombreCompletoDocente`, u.uc_nombre, s.sec_codigo";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerTranscripciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtiene las U.C. que tienen horario pero no docente.
     */
    public function obtenerCursosSinDocente() {
        $co = $this->con();
        try {
            $sqlBase = "SELECT DISTINCT
                            u.uc_nombre AS `NombreUnidadCurricular`
                        FROM
                            uc_horario uh
                        INNER JOIN
                            tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        LEFT JOIN
                            uc_docente ud ON uh.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                        ";

            $conditions = ["ud.doc_cedula IS NULL"]; // La clave es buscar donde el docente es NULO
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            if ($this->fase !== '') {
                $conditions[] = "u.uc_periodo = :fase";
                $params[':fase'] = $this->fase;
            }

            $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            $sqlBase .= " ORDER BY u.uc_nombre";
            
            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            error_log("Error en Transcripcion::obtenerCursosSinDocente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT * FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}