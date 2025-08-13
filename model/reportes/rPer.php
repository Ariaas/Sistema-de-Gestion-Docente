<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{

    public function verificarDatosDePer()
    {
        $sql = "SELECT COUNT(*) as total FROM per_aprobados WHERE pa_estado = 1";
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
        $sql = "SELECT COALESCE(SUM(per_cantidad), 0) as total_en_per
                FROM per_aprobados
                WHERE ani_anio = :anio AND ani_tipo = :tipo AND pa_estado = 1";

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

        $sql = "SELECT uc.uc_nombre, SUM(pa.per_cantidad) as per_cantidad
                FROM per_aprobados pa
                JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                WHERE pa.sec_codigo IN ($placeholders)
                  AND pa.ani_anio = ?
                  AND pa.ani_tipo = ?
                  AND pa.pa_estado = 1
                GROUP BY uc.uc_codigo, uc.uc_nombre
                ORDER BY uc.uc_nombre";

        try {
            $p = $this->Con()->prepare($sql);
            $params = array_merge($seccion_codigos, [$anio, $tipo]);
            $p->execute($params);
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }


    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql_individual = "SELECT pa.sec_codigo, SUM(pa.per_cantidad) as per_cantidad
                           FROM per_aprobados pa
                           WHERE pa.uc_codigo = :uc_codigo
                             AND pa.ani_anio = :anio
                             AND pa.ani_tipo = :tipo
                             AND pa.pa_estado = 1
                           GROUP BY pa.sec_codigo
                           ORDER BY pa.sec_codigo";

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
                    'per_cantidad' => 0
                ];
            } else {
                $grupos[$signature]['sec_codigo'] .= ',' . $registro['sec_codigo'];
            }
            $grupos[$signature]['per_cantidad'] += (int)$registro['per_cantidad'];
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
                          JOIN per_aprobados p ON s.sec_codigo = p.sec_codigo
                          WHERE s.ani_anio = :anio 
                            AND s.ani_tipo = :tipo 
                            AND s.sec_estado = 1 
                            AND p.pa_estado = 1
                            AND p.ani_anio = :anio
                            AND p.ani_tipo = :tipo";
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
                JOIN per_aprobados pa ON uc.uc_codigo = pa.uc_codigo
                WHERE pa.ani_anio = :anio 
                  AND pa.ani_tipo = :tipo 
                  AND uc.uc_estado = 1 
                  AND pa.pa_estado = 1
                ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
