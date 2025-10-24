<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerAnioActivo()
    {
        $p = $this->Con()->prepare(
            "SELECT ani_anio, ani_tipo, CONCAT(ani_anio, '|', ani_tipo) as anio_completo 
             FROM tbl_anio 
             WHERE ani_estado = 1 AND ani_activo = 1 
             LIMIT 1"
        );
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }

    public function verificarDatosAulasAsignadas()
    {
        try {
            $anio_activo = $this->obtenerAnioActivo();
            if (!$anio_activo) {
                return false;
            }

            $anio = $anio_activo['ani_anio'];
            $tipo = $anio_activo['ani_tipo'];

            $periodos_permitidos = ($tipo == '1') ? ['FASE I', 'ANUAL', 'anual', '0'] : ['FASE II', 'ANUAL', 'anual'];

            $params = [':anio_anio' => $anio];
            $placeholders = [];
            foreach ($periodos_permitidos as $index => $periodo) {
                $key = ":periodo_" . $index;
                $placeholders[] = $key;
                $params[strval($key)] = $periodo;
            }

            $sql = "SELECT 1 
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                    JOIN tbl_espacio e ON uh.esp_numero = e.esp_numero AND uh.esp_tipo = e.esp_tipo AND uh.esp_edificio = e.esp_edificio
                    WHERE s.ani_anio = :anio_anio
                      AND s.sec_estado = 1
                      AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")
                      AND e.esp_estado = 1 
                    LIMIT 1";

            $p = $this->Con()->prepare($sql);
            $p->execute($params);
            return $p->fetchColumn() == 1;
        } catch (Exception $e) {
            error_log("Error en verificarDatosAulasAsignadas: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDatosReporteAulas($anio, $tipo, $limite_key = 'all')
    {
        $periodos_permitidos = ($tipo == '1') ? ['FASE I', 'ANUAL', 'anual', '0'] : ['FASE II', 'ANUAL', 'anual'];

        $params = [':anio_anio' => $anio];
        $placeholders = [];
        foreach ($periodos_permitidos as $index => $periodo) {
            $key = ":periodo_" . $index;
            $placeholders[] = $key;
            $params[strval($key)] = $periodo;
        }

        $limit_sql = "";
        if ($limite_key === 'top10') {
            $limit_sql = "LIMIT 10";
        } elseif ($limite_key === 'top5') {
            $limit_sql = "LIMIT 5";
        } elseif ($limite_key === 'top1') {
            $limit_sql = "LIMIT 1";
        }

        $sql = "
            SELECT 
                CONCAT(e.esp_tipo, ' ', e.esp_numero, ' (', e.esp_edificio, ')') AS etiqueta,
                COUNT(*) AS cantidad
            FROM uc_horario uh
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
            JOIN tbl_espacio e ON uh.esp_numero = e.esp_numero AND uh.esp_tipo = e.esp_tipo AND uh.esp_edificio = e.esp_edificio
            WHERE
                s.ani_anio = :anio_anio
                AND s.sec_estado = 1
                AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")
                AND e.esp_estado = 1
            GROUP BY e.esp_edificio, e.esp_tipo, e.esp_numero
            ORDER BY cantidad DESC
            $limit_sql
        ";

        try {
            $p = $this->Con()->prepare($sql);
            $p->execute($params);
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosReporteAulas: " . $e->getMessage());
            return false;
        }
    }
}
