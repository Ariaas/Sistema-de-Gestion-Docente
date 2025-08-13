<?php

require_once('model/dbconnection.php');

class Reporte extends Connection
{
    public function verificarDatosDeAprobadosD()
    {
        $sql = "SELECT COUNT(*) as total FROM tbl_aprobados WHERE apro_estado = 1";
        try {
            $p = $this->Con()->prepare($sql);
            $p->execute();
            $resultado = $p->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT
                    COALESCE(SUM(apro.apro_cantidad), 0) as total_aprobados_directo
                FROM tbl_aprobados AS apro
                WHERE apro.ani_anio = :anio
                  AND apro.ani_tipo = :tipo
                  AND apro.apro_estado = 1";

        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerDatosEstadisticosPorSeccion($seccion_codigos_str, $anio, $tipo)
    {
        $seccion_codigos = explode(',', $seccion_codigos_str);
        if (empty($seccion_codigos)) return [];
        
        $placeholders = implode(',', array_fill(0, count($seccion_codigos), '?'));

        $sql = "SELECT
                    uc.uc_nombre,
                    uc.uc_codigo,
                    SUM(apro.apro_cantidad) as apro_cantidad
                FROM tbl_aprobados AS apro
                JOIN tbl_uc AS uc ON apro.uc_codigo = uc.uc_codigo
                WHERE apro.sec_codigo IN ($placeholders)
                  AND apro.ani_anio = ?
                  AND apro.ani_tipo = ?
                  AND apro.apro_estado = 1
                GROUP BY uc.uc_codigo, uc.uc_nombre
                ORDER BY uc.uc_nombre";

        $p = $this->Con()->prepare($sql);
        $params = array_merge($seccion_codigos, [$anio, $tipo]);
        $p->execute($params);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql_individual = "SELECT
                                apro.sec_codigo,
                                SUM(apro.apro_cantidad) as apro_cantidad
                           FROM tbl_aprobados AS apro
                           WHERE apro.uc_codigo = :uc_codigo
                             AND apro.ani_anio = :anio
                             AND apro.ani_tipo = :tipo
                             AND apro.apro_estado = 1
                           GROUP BY apro.sec_codigo
                           ORDER BY apro.sec_codigo";

        $p = $this->Con()->prepare($sql_individual);
        $p->execute([':uc_codigo' => $uc_codigo, ':anio' => $anio, ':tipo' => $tipo]);
        $registros_individuales = $p->fetchAll(PDO::FETCH_ASSOC);

        if (empty($registros_individuales)) return [];

        $sql_signatures = "SELECT sec_codigo, GROUP_CONCAT(DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio) ORDER BY hor_dia, hor_horainicio SEPARATOR ';') as signature 
                           FROM uc_horario 
                           WHERE uc_codigo = :uc_codigo AND sec_codigo IN (SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = :anio AND ani_tipo = :tipo)
                           GROUP BY sec_codigo";
        $p_sigs = $this->Con()->prepare($sql_signatures);
        $p_sigs->execute([':uc_codigo' => $uc_codigo, ':anio' => $anio, ':tipo' => $tipo]);
        $signatures_map = $p_sigs->fetchAll(PDO::FETCH_KEY_PAIR);

        $grupos = [];
        foreach ($registros_individuales as $registro) {
            $signature = $signatures_map[$registro['sec_codigo']] ?? 'sin-horario-' . $registro['sec_codigo'];
            if (!isset($grupos[$signature])) {
                $grupos[$signature] = [
                    'sec_codigo' => $registro['sec_codigo'],
                    'apro_cantidad' => 0
                ];
            } else {
                $grupos[$signature]['sec_codigo'] .= ',' . $registro['sec_codigo'];
            }
            $grupos[$signature]['apro_cantidad'] += (int)$registro['apro_cantidad'];
        }

        return array_values($grupos);
    }

    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo, CONCAT(ani_anio, '|', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo DESC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesAgrupadasPorAnio($anio, $tipo)
    {
        $sql_secciones = "SELECT DISTINCT s.sec_codigo 
                          FROM tbl_seccion s
                          JOIN tbl_aprobados a ON s.sec_codigo = a.sec_codigo
                          WHERE s.ani_anio = :anio 
                            AND s.ani_tipo = :tipo 
                            AND s.sec_estado = 1 
                            AND a.apro_estado = 1
                            AND a.ani_anio = :anio
                            AND a.ani_tipo = :tipo";
        $p_secciones = $this->Con()->prepare($sql_secciones);
        $p_secciones->execute([':anio' => $anio, ':tipo' => $tipo]);
        $secciones = $p_secciones->fetchAll(PDO::FETCH_COLUMN);

        if (empty($secciones)) return [];

        $placeholders = implode(',', array_fill(0, count($secciones), '?'));
        $sql_horarios = "
            SELECT sec_codigo, uc_codigo, GROUP_CONCAT(DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio, hor_horafin) ORDER BY hor_dia, hor_horainicio SEPARATOR ';') as horario_signature
            FROM uc_horario WHERE sec_codigo IN ($placeholders) GROUP BY sec_codigo, uc_codigo";
        $p_horarios = $this->Con()->prepare($sql_horarios);
        $p_horarios->execute($secciones);
        $horarios = $p_horarios->fetchAll(PDO::FETCH_ASSOC);

        $grupos_por_uc_firma = [];
        foreach ($horarios as $horario) {
            $uc_firma_key = $horario['uc_codigo'] . '::' . $horario['horario_signature'];
            $grupos_por_uc_firma[$uc_firma_key][] = $horario['sec_codigo'];
        }

        $resultado_final = [];
        $secciones_procesadas = [];
        foreach ($grupos_por_uc_firma as $grupo) {
            if (count(array_intersect($grupo, $secciones_procesadas)) > 0) continue;
            
            foreach ($grupo as $sec) $secciones_procesadas[] = $sec;
            sort($grupo);
            $resultado_final[] = [
                'sec_codigo' => implode(',', $grupo),
                'sec_codigo_label' => implode('-', $grupo)
            ];
        }

        $secciones_restantes = array_diff($secciones, $secciones_procesadas);
        foreach ($secciones_restantes as $sec) {
            $resultado_final[] = ['sec_codigo' => $sec, 'sec_codigo_label' => $sec];
        }
        
        usort($resultado_final, fn($a, $b) => strcmp($a['sec_codigo_label'], $b['sec_codigo_label']));
        return $resultado_final;
    }

    public function obtenerUCPorAnio($anio, $tipo)
    {
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre
                FROM tbl_uc uc
                JOIN tbl_aprobados ta ON uc.uc_codigo = ta.uc_codigo
                WHERE ta.ani_anio = :anio 
                  AND ta.ani_tipo = :tipo 
                  AND uc.uc_estado = 1 
                  AND ta.apro_estado = 1
                ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>