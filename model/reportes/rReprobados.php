<?php
require_once('model/dbconnection.php');

class Reporte extends Connection
{
    private $directorioArchivosPer;

    public function __construct()
    {
        parent::__construct();
        $this->directorioArchivosPer = $_SERVER['DOCUMENT_ROOT'] . '/Sistema-de-Gestion-Docente/archivos_per/';
    }

    public function verificarDatosReprobados()
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

    private function agruparYCalcularReprobados($sql_base, $parametros = [])
    {
        $p = $this->Con()->prepare($sql_base);
        $p->execute($parametros);
        $registros = $p->fetchAll(PDO::FETCH_ASSOC);
        if (empty($registros)) return [];

        
        $anio = $parametros[':anio'] ?? 0;
        $tipo = $parametros[':tipo'] ?? '';

        $sql_firmas = "SELECT sec_codigo, uc_codigo, GROUP_CONCAT(DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio) ORDER BY hor_dia, hor_horainicio SEPARATOR ';') as signature 
                       FROM uc_horario 
                       WHERE sec_codigo IN (SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?)
                       GROUP BY sec_codigo, uc_codigo";
        $p_firmas = $this->Con()->prepare($sql_firmas);
        $p_firmas->execute([$anio, $tipo]);
        $mapa_firmas_bruto = $p_firmas->fetchAll(PDO::FETCH_ASSOC);

        $mapa_firmas = [];
        foreach ($mapa_firmas_bruto as $firma) {
            $mapa_firmas[$firma['sec_codigo']][$firma['uc_codigo']] = $firma['signature'];
        }

        $grupos = [];
        foreach ($registros as $registro) {
            $uc_codigo = $registro['uc_codigo'];
            $firma_horario = $mapa_firmas[$registro['sec_codigo']][$uc_codigo] ?? 'sin-horario-' . $registro['sec_codigo'];
            $clave_grupo = $uc_codigo . '::' . $firma_horario;

            if (!isset($grupos[$clave_grupo])) {
                $grupos[$clave_grupo] = [
                    'sec_codigo' => $registro['sec_codigo'],
                    'uc_codigo' => $uc_codigo,
                    'uc_nombre' => $registro['uc_nombre'],
                    'fase_numero' => $registro['fase_numero'],
                    'per_cantidad' => 0,
                    'per_aprobados' => 0,
                ];
            } else {
                $grupos[$clave_grupo]['sec_codigo'] .= ',' . $registro['sec_codigo'];
            }
            $grupos[$clave_grupo]['per_cantidad'] += (int)$registro['per_cantidad'];
            $grupos[$clave_grupo]['per_aprobados'] += (int)$registro['per_aprobados'];
        }

        foreach ($grupos as &$grupo) {
            $identificador_actual = $grupo['uc_codigo'] . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $anio . "_" . $tipo . "_fase" . $grupo['fase_numero'];
            $identificador_antiguo = preg_replace('/[^a-zA-Z0-9_]/', '', $grupo['uc_nombre']) . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $anio . "_" . $tipo . "_fase" . $grupo['fase_numero'];

            $archivosEncontrados = glob($this->directorioArchivosPer . "PER_{$identificador_actual}.*");
            if (empty($archivosEncontrados)) {
                $archivosEncontrados = glob($this->directorioArchivosPer . "PER_{$identificador_antiguo}.*");
            }

            if (!empty($archivosEncontrados)) {
                $grupo['reprobados_per'] = $grupo['per_cantidad'] - $grupo['per_aprobados'];
            } else {
                $grupo['reprobados_per'] = 0;
            }
        }

        return array_values($grupos);
    }
    
    public function obtenerDatosEstadisticosPorAnio($anio, $tipo)
    {
        $sql = "SELECT pa.*, uc.uc_nombre 
                FROM per_aprobados pa JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo 
                WHERE pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1 AND uc.uc_estado = 1";

        $grupos = $this->agruparYCalcularReprobados($sql, [':anio' => $anio, ':tipo' => $tipo]);
        $total_reprobados = array_sum(array_column($grupos, 'reprobados_per'));

        return ['total_reprobados_per' => $total_reprobados];
    }

    public function obtenerDatosEstadisticosPorSeccion($seccion_codigos_str, $anio, $tipo)
    {
        $seccion_codigos = explode(',', $seccion_codigos_str);
        if (empty($seccion_codigos)) return [];
        $placeholders = implode(',', array_fill(0, count($seccion_codigos), '?'));

        $sql = "SELECT pa.*, uc.uc_nombre 
                FROM per_aprobados pa JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo 
                WHERE pa.sec_codigo IN ($placeholders) AND pa.ani_anio = ? AND pa.ani_tipo = ? AND pa.pa_estado = 1 AND uc.uc_estado = 1";

        $grupos_procesados = $this->agruparYCalcularReprobados($sql, array_merge($seccion_codigos, [$anio, $tipo]));

        $resultado_final = [];
        foreach($grupos_procesados as $grupo){
            $uc_nombre = $grupo['uc_nombre'];
            if(!isset($resultado_final[$uc_nombre])){
                $resultado_final[$uc_nombre] = ['uc_nombre' => $uc_nombre, 'reprobados_per' => 0];
            }
            $resultado_final[$uc_nombre]['reprobados_per'] += $grupo['reprobados_per'];
        }
        return array_values($resultado_final);
    }
    
    public function obtenerDatosEstadisticosPorUC($uc_codigo, $anio, $tipo)
    {
        $sql = "SELECT pa.*, uc.uc_nombre 
                FROM per_aprobados pa JOIN tbl_uc uc ON pa.uc_codigo = uc.uc_codigo 
                WHERE pa.uc_codigo = :uc_codigo AND pa.ani_anio = :anio AND pa.ani_tipo = :tipo AND pa.pa_estado = 1";

        return $this->agruparYCalcularReprobados($sql, [':uc_codigo' => $uc_codigo, ':anio' => $anio, ':tipo' => $tipo]);
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

        try {
            $p = $this->Con()->prepare($sql);
            $p->bindParam(':anio', $anio, PDO::PARAM_INT);
            $p->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>