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
            $sqlBase = "SELECT
                            d.doc_cedula AS IDDocente,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente,
                            d.doc_cedula AS CedulaDocente,
                            u.uc_nombre AS NombreUnidadCurricular,
                            s.sec_codigo AS NombreSeccion
                        FROM
                            uc_docente ud
                        INNER JOIN
                            tbl_docente d ON d.doc_cedula = ud.doc_cedula
                        INNER JOIN
                            tbl_uc u ON ud.uc_codigo = u.uc_codigo
                        INNER JOIN
                            uc_horario uh ON u.uc_codigo = uh.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        WHERE
                            d.doc_estado = 1 AND ud.uc_doc_estado = 1";

            $params = [];

            if (!empty($this->docente_id)) {
                $sqlBase .= " AND d.doc_cedula = :doc_cedula";
                $params[':doc_cedula'] = $this->docente_id;
            }

            if (!empty($this->seccion_id)) {
                $sqlBase .= " AND s.sec_codigo = :sec_codigo";
                $params[':sec_codigo'] = $this->seccion_id;
            }

            // --- ▼▼▼ LÓGICA DE FILTRO POR FASE CORREGIDA ▼▼▼ ---
            if (!empty($this->fase)) {
                if ($this->fase == '1') {
                    // Si se selecciona Fase I, se incluyen también las anuales
                    $sqlBase .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual')";
                } elseif ($this->fase == '2') {
                    // Si se selecciona Fase II, se incluyen también las anuales
                    $sqlBase .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
                } elseif ($this->fase == 'anual') {
                    // Si se selecciona Anual, se muestran solo las anuales
                    $sqlBase .= " AND u.uc_periodo = :fase_periodo";
                    $params[':fase_periodo'] = 'Anual';
                }
            }
            
            $sqlBase .= " GROUP BY d.doc_cedula, u.uc_codigo, s.sec_codigo";
            $sqlBase .= " ORDER BY NombreCompletoDocente, NombreSeccion";
            
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
            $p = $co->prepare("SELECT sec_codigo FROM tbl_seccion s
                               JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                               WHERE s.sec_estado = 1 AND a.ani_activo = 1 
                               ORDER BY sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}