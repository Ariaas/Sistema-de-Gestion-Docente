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
            // CORREGIDO: Se cambia INNER JOIN por LEFT JOIN para hacer la consulta más flexible
            // y se mueven algunas condiciones al 'ON' del JOIN.
            $sqlBase = "SELECT
                            d.doc_id AS `IDDocente`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `NombreCompletoDocente`,
                            d.doc_cedula AS `CedulaDocente`,
                            u.uc_nombre AS `NombreUnidadCurricular`,
                            s.sec_codigo AS `NombreSeccion`,
                            s.sec_id AS `IDSeccion`,
                            u.uc_periodo AS `Periodo`,
                            CASE
                                WHEN u.uc_periodo = '1' THEN 'FASE I'
                                WHEN u.uc_periodo = '2' THEN 'FASE II'
                                ELSE 'ANUAL'
                            END AS `FaseNombre`
                        FROM
                            tbl_docente d
                        LEFT JOIN uc_docente ud ON d.doc_id = ud.doc_id AND ud.uc_doc_estado = '1'
                        LEFT JOIN tbl_uc u ON ud.uc_id = u.uc_id
                        LEFT JOIN uc_horario uh ON u.uc_id = uh.uc_id
                        LEFT JOIN tbl_horario h ON uh.hor_id = h.hor_id
                        LEFT JOIN seccion_horario sh ON h.hor_id = sh.hor_id
                        LEFT JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                        WHERE u.uc_id IS NOT NULL"; // Nos aseguramos de traer solo docentes con UC asignadas

            $params = [];

            if (!empty($this->docente_id)) {
                $sqlBase .= " AND d.doc_id = :docente_id";
                $params[':docente_id'] = $this->docente_id;
            }

            if (!empty($this->seccion_id)) {
                $sqlBase .= " AND s.sec_id = :seccion_id";
                $params[':seccion_id'] = $this->seccion_id;
            }

            if ($this->fase !== '') {
                $sqlBase .= " AND u.uc_periodo = :fase";
                $params[':fase'] = $this->fase;
            }

            $sqlBase .= " ORDER BY `NombreCompletoDocente`, `Periodo`, u.uc_nombre, s.sec_codigo";

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

    public function obtenerSecciones()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT sec_id, sec_codigo FROM tbl_seccion WHERE sec_estado = 1 ORDER BY sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}