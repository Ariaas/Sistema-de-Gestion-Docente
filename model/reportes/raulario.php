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
            $sql = "SELECT CONCAT(esp_tipo, ' ', esp_numero, ' (', esp_edificio, ')') as esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    

    public function getAulariosFiltrados()
    {
        if (empty($this->anio) || empty($this->fase)) return [];

      
        $allowed_periods = ($this->fase == 1) ? ['Fase I', 'anual'] : ['Fase II', 'anual'];
        
        try {
            $params = [':anio_param' => $this->anio];
            
           
            $sql_base = "SELECT
                            CONCAT(uh.esp_tipo, ' ', uh.esp_numero, ' (', uh.esp_edificio, ')') AS esp_codigo,
                            uh.hor_dia,
                            CONCAT(uh.hor_horainicio, ':00') as hor_horainicio,
                            CONCAT(uh.hor_horafin, ':00') as hor_horafin,
                            u.uc_nombre,
                            uh.sec_codigo,
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

            if (isset($this->espacio) && $this->espacio !== '') {
                $sql_base .= " AND CONCAT(uh.esp_tipo, ' ', uh.esp_numero, ' (', uh.esp_edificio, ')') = :espacio_param";
                $params[':espacio_param'] = $this->espacio;
            }
            
            $sql_base .= " GROUP BY esp_codigo, uh.hor_dia, uh.hor_horafin, uh.hor_horainicio, u.uc_nombre, uh.sec_codigo
                           ORDER BY esp_codigo ASC, uh.hor_horainicio ASC, uh.hor_dia ASC";
            
            $stmt = $this->con()->prepare($sql_base);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en AularioReport::getAulariosFiltrados: " . $e->getMessage());
            return false;
        }
    }
}