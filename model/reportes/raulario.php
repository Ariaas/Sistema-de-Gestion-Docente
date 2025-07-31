<?php
require_once('model/dbconnection.php');

class AularioReport extends Connection
{
    private $anio, $fase, $espacio;

    public function __construct() { parent::__construct(); }

    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }
    public function setEspacio($valor) { $this->espacio = trim($valor); }
    

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
    
    public function getEspacios() {
        try {
           $sql = "SELECT
            CASE
                WHEN esp_tipo = 'Laboratorio' THEN CONCAT('Lab. ', esp_numero, ' - ', esp_edificio)
                ELSE CONCAT(esp_tipo, ' ', esp_numero, ' (', esp_edificio, ')')
            END as esp_codigo,
            esp_tipo
        FROM tbl_espacio
        WHERE esp_estado = 1 ORDER BY esp_codigo ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function getAulariosFiltrados()
    {
        if (empty($this->anio) || empty($this->fase)) return [];

        $allowed_periods = ($this->fase == 1) ? ['Fase I', 'anual', 'Anual', '0'] : ['Fase II', 'anual', 'Anual'];
        
        try {
            $params = [':anio_param' => $this->anio];
            
            // --- CONSULTA CORREGIDA APLICANDO LA "RECETA" ---
            $sql_base = "SELECT
                           CASE
    WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('Lab. ', uh.esp_numero, ' - ', uh.esp_edificio)
    ELSE CONCAT(uh.esp_tipo, ' ', uh.esp_numero, ' (', uh.esp_edificio, ')')
END AS esp_codigo,
                            uh.hor_dia,
                            CONCAT(uh.hor_horainicio, ':00') as hor_horainicio,
                            CONCAT(uh.hor_horafin, ':00') as hor_horafin,
                            u.uc_nombre,
                            uh.sec_codigo,
                            (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM docente_horario dh
                                JOIN uc_docente ud ON dh.doc_cedula = ud.doc_cedula
                                JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE dh.sec_codigo = uh.sec_codigo AND ud.uc_codigo = uh.uc_codigo
                                LIMIT 1
                            ) AS NombreCompletoDocente
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

          if (isset($this->espacio) && $this->espacio !== '') {
    $sql_base .= " AND CASE
                        WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('Lab. ', uh.esp_numero, ' - ', uh.esp_edificio)
                        ELSE CONCAT(uh.esp_tipo, ' ', uh.esp_numero, ' (', uh.esp_edificio, ')')
                      END = :espacio_param";
    $params[':espacio_param'] = $this->espacio;
}
            
            $sql_base .= " ORDER BY esp_codigo ASC, uh.hor_horainicio ASC, uh.hor_dia ASC";
            
            $stmt = $this->con()->prepare($sql_base);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AularioReport::getAulariosFiltrados: " . $e->getMessage());
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