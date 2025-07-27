<?php
require_once('model/dbconnection.php');

class SeccionReport extends Connection
{
    private $anio, $fase, $trayecto;

    public function __construct() { parent::__construct(); }

   
    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }
    public function setTrayecto($valor) { $this->trayecto = trim($valor); }
    

    public function getAniosActivos() {
        try {
            $sql = "SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC";
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

    public function getTrayectos() {
        try {
            $sql = "SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_trayecto IS NOT NULL ORDER BY uc_trayecto ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getHorariosFiltrados()
    {
        if (empty($this->anio) || empty($this->fase)) return [];

        $allowed_periods = [];
        if ($this->fase == 1) {
            $allowed_periods = ['Fase I', 'Anual'];
        } elseif ($this->fase == 2) {
            $allowed_periods = ['Fase II', 'Anual'];
        }

        if (empty($allowed_periods)) return [];
        
        try {
            $params = [':anio_param' => $this->anio];
            
            $sql_base = "SELECT
                        uh.sec_codigo,
                        u.uc_trayecto,
                        uh.hor_dia,
                        CONCAT(uh.hor_horainicio, ':00') as hor_horainicio,
                        CONCAT(uh.hor_horafin, ':00') as hor_horafin,
                        u.uc_nombre,
                        CONCAT(uh.esp_tipo, ' ', uh.esp_numero, ' (', uh.esp_edificio, ')') AS esp_codigo,
                        GROUP_CONCAT(DISTINCT CONCAT(d.doc_nombre, ' ', d.doc_apellido) SEPARATOR '\n') AS NombreCompletoDocente
                    FROM
                        uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    LEFT JOIN docente_horario dh ON uh.sec_codigo = dh.sec_codigo
                    LEFT JOIN tbl_docente d ON dh.doc_cedula = d.doc_cedula AND d.doc_estado = 1
                    WHERE
                        s.ani_anio = :anio_param
                        AND u.uc_estado = 1";
            
            $period_placeholders = [];
            $i = 0;
            foreach ($allowed_periods as $period) {
                $key = ":period" . $i++;
                $period_placeholders[] = $key;
                $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sql_base .= " AND u.uc_periodo IN ({$in_clause})";

            if (isset($this->trayecto) && $this->trayecto !== '') {
                $sql_base .= " AND u.uc_trayecto = :trayecto_param";
                $params[':trayecto_param'] = $this->trayecto;
            }
            
            $sql_base .= " GROUP BY uh.sec_codigo, u.uc_trayecto, uh.hor_dia, uh.hor_horafin, uh.hor_horainicio, u.uc_nombre, esp_codigo
                         ORDER BY u.uc_trayecto ASC, uh.sec_codigo ASC, uh.hor_horainicio ASC";
            
            $stmt = $this->con()->prepare($sql_base);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getHorariosFiltrados: " . $e->getMessage());
            return false;
        }
    }
}
?>