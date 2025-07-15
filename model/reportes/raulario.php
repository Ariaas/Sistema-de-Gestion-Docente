<?php
require_once('model/dbconnection.php');

class AularioReport extends Connection
{
    private $espacio_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_espacio_id($valor)
    {
        $this->espacio_id = trim($valor);
    }

    public function getEspacios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getEspacios: " . $e->getMessage());
            return false;
        }
    }

    public function getDistinctTimeSlotsForEspacio()
    {
        if (empty($this->espacio_id)) {
            return [];
        }
        $co = $this->con();
        try {
            // MODIFICACIÓN: Se elimina el JOIN a tbl_horario.
            $sql = "SELECT DISTINCT
                        uh.hor_horainicio AS hor_inicio,
                        uh.hor_horafin AS hor_fin
                    FROM
                        uc_horario uh
                    WHERE
                        uh.esp_codigo = :espacio_codigo_param
                    ORDER BY
                        uh.hor_horainicio ASC";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':espacio_codigo_param', $this->espacio_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getDistinctTimeSlotsForEspacio: " . $e->getMessage());
            return [];
        }
    }

    public function getHorarioDataByEspacio()
    {
        if (empty($this->espacio_id)) {
            return [];
        }
        $co = $this->con();
        try {
            // MODIFICACIÓN: Se elimina el JOIN a tbl_horario y el WHERE filtra directamente en uc_horario.
            $sql = "SELECT
                        uh.hor_dia,
                        uh.hor_horainicio AS hor_inicio,
                        uh.hor_horafin AS hor_fin,
                        u.uc_nombre AS UnidadDisplay,
                        u.uc_nombre AS NombreCompletoUC,
                        s.sec_codigo AS NombreSeccion,
                        CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente
                    FROM
                        uc_horario uh
                    JOIN
                        tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    JOIN
                        tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    LEFT JOIN
                        uc_docente ud ON u.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                    LEFT JOIN
                        tbl_docente d ON ud.doc_cedula = d.doc_cedula
                    WHERE
                        uh.esp_codigo = :espacio_codigo_param
                    ORDER BY
                        uh.hor_horainicio ASC, u.uc_codigo ASC, s.sec_codigo ASC";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':espacio_codigo_param', $this->espacio_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AularioReport::getHorarioDataByEspacio: " . $e->getMessage());
            return false;
        }
    }

     public function getEspacioCodigoByCodigo($codigo)
    {
        if (empty($codigo)) return null;
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT esp_codigo FROM tbl_espacio WHERE esp_codigo = :codigo_param");
            $p->bindParam(':codigo_param', $codigo, PDO::PARAM_STR);
            $p->execute();
            $result = $p->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['esp_codigo'] : null;
        } catch (PDOException $e) {
            error_log("Error en AularioReport::getEspacioCodigoByCodigo: " . $e->getMessage());
            return null;
        }
    }
}
?>