<?php


require_once('model/dbconnection.php');

class CuentaCupos extends Connection
{
    private $anio_id;
    private $ani_tipo;
    private $fase_numero;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
    }

    public function set_ani_tipo($valor)
    {
        $this->ani_tipo = $valor;
    }

    public function set_fase($valor)
    {
        $this->fase_numero = $valor;
    }

    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en CuentaCupos::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerFases()
    {
        return [
            ['fase_numero' => 1],
            ['fase_numero' => 2]
        ];
    }


    public function obtenerCuentaCupos()
    {
        if (empty($this->anio_id) || empty($this->ani_tipo)) {
            return [];
        }

        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        
        
        if (!$esIntensivo && empty($this->fase_numero)) {
            return [];
        }

        $co = $this->con();
        try {
            
            $sql = "
                SELECT
                    s.sec_codigo,
                    s.sec_cantidad,
                    uh.uc_codigo,
                    uh.hor_dia,
                    uh.hor_horainicio,
                    u.uc_periodo
                FROM
                    tbl_seccion s
                LEFT JOIN
                    uc_horario uh ON s.sec_codigo = uh.sec_codigo 
                        AND s.ani_anio = uh.ani_anio 
                        AND s.ani_tipo = uh.ani_tipo
                LEFT JOIN
                    tbl_uc u ON uh.uc_codigo = u.uc_codigo
                WHERE
                    s.ani_anio = :anio_id 
                    AND s.ani_tipo = :ani_tipo
                    AND s.sec_estado = 1";

            
            if (!$esIntensivo) {
                $periodos_permitidos = [];
                if ($this->fase_numero == 1) {
                    $periodos_permitidos = ['Fase I', 'Anual'];
                } else {
                    $periodos_permitidos = ['Fase II', 'Anual'];
                }
                
                $placeholders = [];
                foreach ($periodos_permitidos as $index => $periodo) {
                    $placeholders[] = ":periodo_" . $index;
                }
                
                $sql .= " AND (u.uc_periodo IN (" . implode(', ', $placeholders) . ") OR u.uc_periodo IS NULL)";
            }

            $sql .= " ORDER BY s.sec_codigo";

            $resultado = $co->prepare($sql);
            
            $params = [
                ':anio_id' => $this->anio_id,
                ':ani_tipo' => $this->ani_tipo
            ];

            
            if (!$esIntensivo) {
                foreach ($periodos_permitidos as $index => $periodo) {
                    $params[':periodo_' . $index] = $periodo;
                }
            }

            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en CuentaCupos::obtenerCuentaCupos: " . $e->getMessage());
            return false;
        }
    }
}
?>