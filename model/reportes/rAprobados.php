<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT
                    (SELECT COALESCE(SUM(ta.apro_cantidad), 0)
                     FROM tbl_aprobados ta
                     WHERE ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1)
                    +
                    (SELECT COALESCE(SUM(pa.per_aprobados), 0)
                     FROM per_aprobados pa
                     WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1)
                AS total_aprobados";

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

        $sql = "SELECT T.uc_nombre, SUM(T.total_aprobados) as total_aprobados
                FROM (
                    SELECT uc.uc_nombre, ta.apro_cantidad as total_aprobados
                    FROM tbl_aprobados ta
                    JOIN tbl_uc uc ON ta.uc_codigo = uc.uc_codigo
                    WHERE ta.sec_codigo IN ($placeholders) AND ta.ani_anio = ? AND ta.ani_tipo = ? AND ta.apro_estado = 1
                    UNION ALL
                    SELECT uc.uc_nombre, pa.per_aprobados as total_aprobados
                    FROM per_aprobados pa
                    JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                    WHERE pa.sec_codigo IN ($placeholders) AND pa.ani_anio = ? AND pa.ani_tipo = ? AND pa.pa_estado = 1
                ) AS T
                GROUP BY T.uc_nombre
                ORDER BY T.uc_nombre";
        try {
            $p = $this->Con()->prepare($sql);
            $params = array_merge($seccion_codigos, [$anio, $tipo], $seccion_codigos, [$anio, $tipo]);
            $p->execute($params);
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql_individual = "SELECT T.sec_codigo, SUM(T.total_aprobados) as total_aprobados
                           FROM (
                               SELECT ta.sec_codigo, ta.apro_cantidad as total_aprobados
                               FROM tbl_aprobados ta
                               WHERE ta.uc_codigo = :uc_codigo AND ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1
                               UNION ALL
                               SELECT pa.sec_codigo, pa.per_aprobados as total_aprobados
                               FROM per_aprobados pa
                               WHERE pa.uc_codigo = :uc_codigo AND pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1
                           ) AS T
                           GROUP BY T.sec_codigo
                           ORDER BY T.sec_codigo";
        
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
                    'total_aprobados' => 0
                ];
            } else {
                $grupos[$signature]['sec_codigo'] .= ',' . $registro['sec_codigo'];
            }
            $grupos[$signature]['total_aprobados'] += (int)$registro['total_aprobados'];
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
        $sql_secciones = "SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1";
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
        $sql = "SELECT DISTINCT uc.uc_codigo, uc.uc_nombre FROM tbl_uc uc WHERE uc.uc_codigo IN (
                    SELECT ta.uc_codigo FROM tbl_aprobados ta JOIN tbl_seccion s ON ta.sec_codigo = s.sec_codigo WHERE s.ani_anio = :anio AND s.ani_tipo = :tipo
                    UNION
                    SELECT pa.uc_codigo FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo
                ) AND uc.uc_estado = 1 ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>