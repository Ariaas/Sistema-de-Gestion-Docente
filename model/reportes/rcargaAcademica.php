<?php

require_once('model/dbconnection.php');

class Carga extends Connection
{
    private $anio, $ani_tipo, $fase;
    private $trayecto;

    public function __construct()
    {
        parent::__construct();
    }

    public function setAnio($valor) { $this->anio = trim($valor); }
    public function setAniTipo($valor) { $this->ani_tipo = trim($valor); }
    public function setFase($valor) { $this->fase = trim($valor); }

    public function set_trayecto($valor)
    {
        $this->trayecto = $valor;
    }

    public function obtenerUnidadesCurriculares()
    {
        // Validar campos requeridos
        if (empty($this->anio) || empty($this->ani_tipo)) return [];
        
        // Si es intensivo, no se requiere fase
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        
        if (!$esIntensivo && empty($this->fase)) return [];

        $allowed_periods = [];
        
        // Si es intensivo, incluir todos los periodos
        if ($esIntensivo) {
            $allowed_periods = ['Fase I', 'Fase II', 'Anual', 'anual', '0'];
        } else {
            // Lógica normal para años regulares
            if ($this->fase == 1) {
                $allowed_periods = ['Fase I', 'Anual', 'anual', '0'];
            } elseif ($this->fase == 2) {
                $allowed_periods = ['Fase II', 'Anual', 'anual'];
            }
        }

        if (empty($allowed_periods)) return [];
        
        try {
            $params = [':anio_param' => $this->anio, ':ani_tipo_param' => $this->ani_tipo];
            
            error_log("CargaAcademica - Año: " . $this->anio . ", Tipo: " . $this->ani_tipo . ", Fase: " . $this->fase);
            
            $sqlBase = "SELECT
                            u.uc_trayecto AS 'Número de Trayecto',
                            u.uc_nombre AS 'Nombre de la Unidad Curricular',
                            s.sec_codigo AS 'Código de Sección',
                            (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM uc_horario uh2
                                LEFT JOIN tbl_docente d ON uh2.doc_cedula = d.doc_cedula
                                WHERE uh2.sec_codigo = uh.sec_codigo 
                                    AND uh2.uc_codigo = uh.uc_codigo
                                    AND uh2.ani_anio = uh.ani_anio
                                LIMIT 1
                            ) AS 'Nombre Completo del Docente'
                        FROM
                            uc_horario uh
                        INNER JOIN
                            tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio
                        WHERE
                            s.ani_anio = :anio_param
                            AND s.ani_tipo = :ani_tipo_param
                            AND u.uc_estado = 1
                            AND s.sec_estado = 1";
            
            // Agregar filtro de periodos
            $period_placeholders = [];
            $i = 0;
            foreach ($allowed_periods as $period) {
                $key = ":period" . $i++;
                $period_placeholders[] = $key;
                $params[$key] = $period;
            }
            $in_clause = implode(', ', $period_placeholders);
            $sqlBase .= " AND u.uc_periodo IN ({$in_clause})";
            
            if (isset($this->trayecto) && $this->trayecto !== '') {
                $sqlBase .= " AND u.uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }
            
            $sqlBase .= " GROUP BY u.uc_trayecto, u.uc_nombre, s.sec_codigo
                         ORDER BY u.uc_trayecto, s.sec_codigo, u.uc_nombre";

            error_log("CargaAcademica SQL: " . $sqlBase);
            error_log("CargaAcademica Params: " . print_r($params, true));

            $resultado = $this->con()->prepare($sqlBase);
            $resultado->execute($params);
            $result = $resultado->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("CargaAcademica - Registros encontrados: " . count($result));
            
            return $result;

        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerUnidadesCurriculares: " . $e->getMessage());
            return false;
        }
    }

    
    public function getAniosActivos()
    {
        try {
            $sql = "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::getAniosActivos: " . $e->getMessage());
            return [];
        }
    }

    public function getFases()
    {
        try {
            $sql = "SELECT DISTINCT fase_numero FROM tbl_fase ORDER BY fase_numero ASC";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::getFases: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerTrayectos()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT DISTINCT 
                                    uc_trayecto AS tra_id, 
                                    CASE 
                                        WHEN uc_trayecto = 0 THEN 'Trayecto Inicial' 
                                        ELSE CONCAT('Trayecto ', uc_trayecto) 
                                    END AS tra_numero 
                                FROM tbl_uc ORDER BY uc_trayecto");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }

}