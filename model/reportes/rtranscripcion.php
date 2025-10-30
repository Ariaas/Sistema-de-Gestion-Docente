<?php
require_once('model/dbconnection.php');

class Transcripcion extends Connection
{
    private $anio, $ani_tipo, $fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function setAnio($valor) {
        $this->anio = trim($valor);
    }

    public function setAniTipo($valor) {
        $this->ani_tipo = trim($valor);
    }

    public function setFase($valor) {
        $this->fase = trim($valor);
    }

    public function obtenerTranscripciones()
    {
        
        if (empty($this->anio) || empty($this->ani_tipo)) return [];
        
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        if (!$esIntensivo && empty($this->fase)) return [];

        $co = $this->con();
        try {
            $sqlBase = "SELECT
                d.doc_cedula AS CedulaDocente,
                CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente,
                u.uc_nombre AS `NombreUnidadCurricular`,
                GROUP_CONCAT(
                    DISTINCT s.sec_codigo ORDER BY s.sec_codigo SEPARATOR ','
                ) AS `NombreSeccion`
            FROM
                uc_horario uh
            INNER JOIN
                tbl_docente d ON uh.doc_cedula = d.doc_cedula
            INNER JOIN
                tbl_uc u ON uh.uc_codigo = u.uc_codigo
            INNER JOIN
                tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    AND uh.ani_anio = s.ani_anio
                    AND s.ani_tipo = :ani_tipo_param
                    AND (uh.ani_tipo = s.ani_tipo OR uh.ani_tipo IS NULL)
            ";
            
            $conditions = [ "s.sec_estado = 1", "d.doc_estado = 1", "s.ani_anio = :anio_param", "uh.doc_cedula IS NOT NULL" ];
            $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];

            
            $allowed_periods = [];
            if ($esIntensivo) {
                $allowed_periods = ['Fase I', 'Fase II', 'Anual', 'anual', '0'];
            } else {
                if ($this->fase == 1) {
                    $allowed_periods = ['Fase I', 'Anual', 'anual', '0'];
                } elseif ($this->fase == 2) {
                    $allowed_periods = ['Fase II', 'Anual', 'anual'];
                }
            }

            if (!empty($allowed_periods)) {
                $period_placeholders = [];
                $i = 0;
                foreach ($allowed_periods as $period) {
                    $key = ":period" . $i++;
                    $period_placeholders[] = $key;
                    $params[$key] = $period;
                }
                $in_clause = implode(', ', $period_placeholders);
                $conditions[] = "u.uc_periodo IN ({$in_clause})";
            }
            
            $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            
            $sqlBase .= " GROUP BY d.doc_cedula, u.uc_codigo";
            $sqlBase .= " ORDER BY NombreCompletoDocente, u.uc_nombre";

            error_log("Transcripcion SQL: " . $sqlBase);
            error_log("Transcripcion Params: " . print_r($params, true));
            error_log("Transcripcion Allowed Periods: " . print_r($allowed_periods, true));

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            
            $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
            error_log("Transcripcion - Registros encontrados: " . count($data));
            
            foreach ($data as &$row) {
                $row['IDDocente'] = $row['CedulaDocente'];
            }

            return $data;

        } catch (PDOException $e) {
            error_log("Error en Transcripcion::obtenerTranscripciones: " . $e->getMessage());
            return false;
        }
    }

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
                            AND uh.ani_anio = s.ani_anio
                            AND s.ani_tipo = :ani_tipo_param
                            AND (uh.ani_tipo = s.ani_tipo OR uh.ani_tipo IS NULL)
                        ";

            $where_clauses = ["NOT EXISTS (SELECT 1 FROM docente_horario dh WHERE dh.sec_codigo = s.sec_codigo)", "s.ani_anio = :anio_param"];
            $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];

            
            $allowed_periods = [];
            if (strtolower($this->ani_tipo) === 'intensivo') {
                $allowed_periods = ['Fase I', 'Fase II', 'Anual', 'anual', '0'];
            } else {
                if ($this->fase == 1) {
                    $allowed_periods = ['Fase I', 'Anual', 'anual', '0'];
                } elseif ($this->fase == 2) {
                    $allowed_periods = ['Fase II', 'Anual', 'anual'];
                }
            }

            if (!empty($allowed_periods)) {
                $period_placeholders = [];
                $i = 0;
                foreach ($allowed_periods as $period) {
                    $key = ":period" . $i++;
                    $period_placeholders[] = $key;
                    $params[$key] = $period;
                }
                $in_clause = implode(', ', $period_placeholders);
                $where_clauses[] = "u.uc_periodo IN ({$in_clause})";
            }
            
            if (!empty($where_clauses)) {
                $sqlBase .= " WHERE " . implode(" AND ", $where_clauses);
            }

            $sqlBase .= " ORDER BY u.uc_nombre";
            
            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            error_log("Error en Transcripcion::obtenerCursosSinDocente: " . $e->getMessage());
            return false;
        }
    }

    public function getAniosActivos() {
        try {
            $sql = "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getFases() {
        try {
            $sql = "SELECT DISTINCT fase_numero FROM tbl_fase ORDER BY fase_numero ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
}