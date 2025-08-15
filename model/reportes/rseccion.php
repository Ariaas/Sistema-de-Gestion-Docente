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
            $allowed_periods = ['Fase I', 'Anual', 'anual', '0'];
        } elseif ($this->fase == 2) {
            $allowed_periods = ['Fase II', 'Anual', 'anual'];
        }

        if (empty($allowed_periods)) return [];
        
        try {
            $params = [':anio_param' => $this->anio];
            
            $sql_base = "SELECT
                            uh.sec_codigo,
                            u.uc_trayecto,
                            uh.hor_dia,
                            uh.hor_horainicio,
                            uh.hor_horafin,
                            u.uc_nombre,
                            uh.esp_tipo,
                            CASE
                                WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('L-', uh.esp_numero)
                                WHEN uh.esp_tipo = 'Aula' THEN CONCAT(LEFT(uh.esp_edificio, 1), '-', uh.esp_numero)
                                ELSE uh.esp_numero
                            END AS esp_codigo,
                            (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM uc_docente ud
                                INNER JOIN docente_horario dh ON ud.doc_cedula = dh.doc_cedula
                                INNER JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE ud.uc_codigo = uh.uc_codigo
                                  AND dh.sec_codigo = uh.sec_codigo
                                LIMIT 1
                            ) as NombreCompletoDocente
                        FROM
                            uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        WHERE
                            s.ani_anio = :anio_param
                            AND u.uc_estado = 1
                            AND s.sec_estado = 1";
            
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
            
            $sql_base .= " ORDER BY u.uc_trayecto ASC, uh.sec_codigo ASC, uh.hor_horainicio ASC";
            
            $stmt = $this->con()->prepare($sql_base);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en SeccionReport::getHorariosFiltrados: " . $e->getMessage());
            return false;
        }
    }

    public function getTurnosCompletos() {
		try {
			$sql = "SELECT tur_nombre, tur_horaInicio, tur_horaFin FROM tbl_turno WHERE tur_estado = 1 ORDER BY tur_horaInicio ASC";
			$stmt = $this->con()->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) { return []; }
	}
}