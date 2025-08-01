<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    private $archivosPerDir;

    public function __construct()
    {
        parent::__construct();
        $this->archivosPerDir = realpath(__DIR__ . '/../../archivos_per/') . DIRECTORY_SEPARATOR;
    }

    private function groupAndCalculateStats($base_sql, $params = [])
    {
        $p = $this->Con()->prepare($base_sql);
        $p->execute($params);
        $registros = $p->fetchAll(PDO::FETCH_ASSOC);
        if (empty($registros)) return [];

        $anio = $params[':anio'] ?? ($params[count($params)-2] ?? 0);
        $tipo = $params[':tipo'] ?? ($params[count($params)-1] ?? '');

        $sql_signatures = "SELECT sec_codigo, uc_codigo, GROUP_CONCAT(DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio) ORDER BY hor_dia, hor_horainicio SEPARATOR ';') as signature 
                           FROM uc_horario 
                           WHERE sec_codigo IN (SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?)
                           GROUP BY sec_codigo, uc_codigo";
        $p_sigs = $this->Con()->prepare($sql_signatures);
        $p_sigs->execute([$anio, $tipo]);
        $signatures_map_raw = $p_sigs->fetchAll(PDO::FETCH_ASSOC);
        $signatures_map = [];
        foreach($signatures_map_raw as $sig) {
            $signatures_map[$sig['sec_codigo']][$sig['uc_codigo']] = $sig['signature'];
        }

        $grupos = [];
        foreach ($registros as $registro) {
            $uc_codigo = $registro['uc_codigo'];
            $signature = $signatures_map[$registro['sec_codigo']][$uc_codigo] ?? 'sin-horario-' . $registro['sec_codigo'];
            $group_key = $uc_codigo . '::' . $signature;
            
            if (!isset($grupos[$group_key])) {
                $grupos[$group_key] = [
                    'sec_codigo' => $registro['sec_codigo'],
                    'uc_codigo' => $uc_codigo,
                    'uc_nombre' => $registro['uc_nombre'],
                    'fase_numero' => $registro['fase_numero'],
                    'aprobados_directo' => 0,
                    'per_cantidad' => 0,
                    'per_aprobados' => 0,
                ];
            } else {
                $grupos[$group_key]['sec_codigo'] .= ',' . $registro['sec_codigo'];
            }
            $grupos[$group_key]['aprobados_directo'] += (int)$registro['aprobados_directo'];
            $grupos[$group_key]['per_cantidad'] += (int)$registro['per_cantidad'];
            $grupos[$group_key]['per_aprobados'] += (int)$registro['per_aprobados'];
        }

        foreach ($grupos as &$grupo) {
            $identificador_nuevo = $grupo['uc_codigo'] . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $anio . "_" . $tipo . "_fase" . $grupo['fase_numero'];
            $identificador_viejo = preg_replace('/[^a-zA-Z0-9_]/', '', $grupo['uc_nombre']) . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $anio . "_" . $tipo . "_fase" . $grupo['fase_numero'];
            
            $foundFiles = glob($this->archivosPerDir . "PER_{$identificador_nuevo}.*");
            if(empty($foundFiles)) {
                $foundFiles = glob($this->archivosPerDir . "PER_{$identificador_viejo}.*");
            }
            
            if (!empty($foundFiles)) {
                $grupo['reprobados_per'] = $grupo['per_cantidad'] - $grupo['per_aprobados'];
            } else {
                $grupo['reprobados_per'] = 0;
            }
        }

        return array_values($grupos);
    }
    
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT 
                    pa.sec_codigo, pa.uc_codigo, pa.fase_numero, uc.uc_nombre, 
                    COALESCE(ta.apro_cantidad, 0) as aprobados_directo, 
                    pa.per_cantidad, pa.per_aprobados 
                FROM per_aprobados pa
                LEFT JOIN tbl_aprobados ta ON pa.uc_codigo = ta.uc_codigo AND pa.sec_codigo = ta.sec_codigo AND pa.ani_anio = ta.ani_anio AND pa.ani_tipo = ta.ani_tipo AND pa.fase_numero = ta.fase_numero
                JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1";
        
        $grupos = $this->groupAndCalculateStats($sql, [':anio' => $anio, ':tipo' => $tipo]);
        
        $totales = [
            'total_aprobados_directo' => array_sum(array_column($grupos, 'aprobados_directo')),
            'total_aprobados_per' => array_sum(array_column($grupos, 'per_aprobados')),
            'total_reprobados_per' => array_sum(array_column($grupos, 'reprobados_per'))
        ];
        
        return $totales;
    }
    
    public function obtenerDatosEstadisticosPorSeccion($seccion_codigos_str, $anio, $tipo)
    {
        $seccion_codigos = explode(',', $seccion_codigos_str);
        if (empty($seccion_codigos)) return [];
        $placeholders = implode(',', array_fill(0, count($seccion_codigos), '?'));

        $sql = "SELECT 
                    pa.sec_codigo, pa.uc_codigo, pa.fase_numero, uc.uc_nombre, 
                    COALESCE(ta.apro_cantidad, 0) as aprobados_directo, 
                    pa.per_cantidad, pa.per_aprobados 
                FROM per_aprobados pa
                LEFT JOIN tbl_aprobados ta ON pa.uc_codigo = ta.uc_codigo AND pa.sec_codigo = ta.sec_codigo AND pa.ani_anio = ta.ani_anio AND pa.ani_tipo = ta.ani_tipo AND pa.fase_numero = ta.fase_numero
                JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                WHERE pa.sec_codigo IN ($placeholders) AND pa.ani_anio = ? AND pa.ani_tipo = ? AND pa.pa_estado = 1";
        
        $grupos_procesados = $this->groupAndCalculateStats($sql, array_merge($seccion_codigos, [$anio, $tipo]));

        $resultado_final = [];
        foreach($grupos_procesados as $grupo){
            $uc_nombre = $grupo['uc_nombre'];
            if(!isset($resultado_final[$uc_nombre])){
                $resultado_final[$uc_nombre] = [
                    'uc_nombre' => $uc_nombre, 
                    'aprobados_directo' => 0,
                    'per_aprobados' => 0,
                    'reprobados_per' => 0
                ];
            }
            $resultado_final[$uc_nombre]['aprobados_directo'] += $grupo['aprobados_directo'];
            $resultado_final[$uc_nombre]['per_aprobados'] += $grupo['per_aprobados'];
            $resultado_final[$uc_nombre]['reprobados_per'] += $grupo['reprobados_per'];
        }
        return array_values($resultado_final);
    }
    
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT 
                    pa.sec_codigo, pa.uc_codigo, pa.fase_numero, uc.uc_nombre, 
                    COALESCE(ta.apro_cantidad, 0) as aprobados_directo, 
                    pa.per_cantidad, pa.per_aprobados 
                FROM per_aprobados pa
                LEFT JOIN tbl_aprobados ta ON pa.uc_codigo = ta.uc_codigo AND pa.sec_codigo = ta.sec_codigo AND pa.ani_anio = ta.ani_anio AND pa.ani_tipo = ta.ani_tipo AND pa.fase_numero = ta.fase_numero
                JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo
                WHERE pa.uc_codigo = :uc_codigo AND pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1";
        
        return $this->groupAndCalculateStats($sql, [':uc_codigo' => $uc_codigo, ':anio' => $anio, ':tipo' => $tipo]);
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
                    SELECT uc_codigo FROM tbl_aprobados ta WHERE ta.ani_anio = :anio AND ta.ani_tipo = :tipo AND ta.apro_estado = 1
                    UNION
                    SELECT uc_codigo FROM per_aprobados pa WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1
                ) AND uc.uc_estado = 1 ORDER BY uc.uc_nombre";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':anio', $anio, PDO::PARAM_INT);
        $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>