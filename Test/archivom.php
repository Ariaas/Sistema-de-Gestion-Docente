<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $sec_codigo;
    private $uc_codigo;
    private $ani_anio;
    private $ani_tipo;
    private $apro_cantidad;
    private $per_cantidad;
    private $per_aprobados;
    private $uc_nombre;
    private $doc_cedula;
    private $fase_numero;
    private $archivosDir;
    private $archivosPerDir;

    public function __construct()
    {
        parent::__construct();
        $this->archivosDir = realpath(__DIR__ . '/../archivos_subidos/') . DIRECTORY_SEPARATOR;
        $this->archivosPerDir = realpath(__DIR__ . '/../archivos_per/') . DIRECTORY_SEPARATOR;

        if (!file_exists($this->archivosDir)) {
            mkdir($this->archivosDir, 0777, true);
        }
        if (!file_exists($this->archivosPerDir)) {
            mkdir($this->archivosPerDir, 0777, true);
        }
    }

    public function setSecCodigo($sec_codigo){ $this->sec_codigo = $sec_codigo; }
    public function getSecCodigo(){ return $this->sec_codigo; }
    public function setUcCodigo($uc_codigo){ $this->uc_codigo = $uc_codigo; }
    public function getUcCodigo(){ return $this->uc_codigo; }
    public function setAnioAnio($ani_anio){ $this->ani_anio = $ani_anio; }
    public function getAnioAnio(){ return $this->ani_anio; }
    public function setAnioTipo($ani_tipo){ $this->ani_tipo = $ani_tipo; }
    public function getAnioTipo(){ return $this->ani_tipo; }
    public function setAproCantidad($apro_cantidad){ $this->apro_cantidad = $apro_cantidad; }
    public function getAproCantidad(){ return $this->apro_cantidad; }
    public function setPerCantidad($per_cantidad){ $this->per_cantidad = $per_cantidad; }
    public function getPerCantidad(){ return $this->per_cantidad; }
    public function setPerAprobados($per_aprobados){ $this->per_aprobados = $per_aprobados; }
    public function getPerAprobados(){ return $this->per_aprobados; }
    public function setUcNombre($uc_nombre){ $this->uc_nombre = $uc_nombre; }
    public function getUcNombre(){ return $this->uc_nombre; }
    public function setDocCedula($doc_cedula){ $this->doc_cedula = $doc_cedula; }
    public function setFaseNumero($fase_numero){ $this->fase_numero = $fase_numero; }
    public function getDocCedula(){ return $this->doc_cedula; }

    private function generarIdentificadorUnico()
    {
        $ucCodigo = $this->getUcCodigo(); 
        $secCodigo = str_replace(',', '_', $this->getSecCodigo());
        $anioAnio = $this->getAnioAnio();
        $anioTipo = $this->getAnioTipo();
        $faseNumero = $this->fase_numero;
        return $ucCodigo . "_" . $secCodigo . "_" . $anioAnio . "_" . $anioTipo . "_fase" . $faseNumero;
    }

    public function guardarRegistroInicial($archivo)
    {
        $secciones_a_registrar = explode(',', $this->getSecCodigo());
        $master_section = $secciones_a_registrar[0];

        $this->Con()->beginTransaction();
        try {
            if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'mensaje' => 'El archivo del acta de notas es obligatorio.'];
            }

            $identificador = $this->generarIdentificadorUnico();
            $nombreArchivo = "DEF_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($archivo['tmp_name'], $this->archivosDir . $nombreArchivo)) {
                throw new Exception('Error al guardar el archivo definitivo.');
            }

            $sql_apro = "INSERT INTO tbl_aprobados (ani_anio, ani_tipo, fase_numero, uc_codigo, sec_codigo, doc_cedula, apro_cantidad, apro_estado) VALUES (?, ?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE apro_cantidad = VALUES(apro_cantidad), doc_cedula = VALUES(doc_cedula), apro_estado = 1";
            $p_apro = $this->Con()->prepare($sql_apro);

            $sql_per = "INSERT INTO per_aprobados (ani_anio, ani_tipo, fase_numero, uc_codigo, sec_codigo, per_cantidad, per_aprobados, pa_estado) VALUES (?, ?, ?, ?, ?, ?, 0, 1) ON DUPLICATE KEY UPDATE per_cantidad = VALUES(per_cantidad), pa_estado = 1";
            $p_per = $this->Con()->prepare($sql_per);
            
            $p_apro->execute([$this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $master_section, $this->getDocCedula(), $this->getAproCantidad()]);
            $p_per->execute([$this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $master_section, $this->getPerCantidad()]);

            for ($i = 1; $i < count($secciones_a_registrar); $i++) {
                $slave_section = $secciones_a_registrar[$i];
                $p_apro->execute([$this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $slave_section, $this->getDocCedula(), 0]);
                $p_per->execute([$this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $slave_section, 0]);
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro de grupo guardado con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    public function registrarAprobadosPer($archivo)
    {
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'mensaje' => 'El archivo del PER es obligatorio.'];
        }
        $secciones_del_grupo = explode(',', $this->getSecCodigo());
        $master_section = $secciones_del_grupo[0];

        $this->Con()->beginTransaction();
        try {
            $identificador = $this->generarIdentificadorUnico();
            $nombreArchivo = "PER_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($archivo['tmp_name'], $this->archivosPerDir . $nombreArchivo)) {
                throw new Exception('Error al guardar el archivo del PER.');
            }
            
            $sql_update = "UPDATE per_aprobados SET per_aprobados = ?, pa_estado = 1 WHERE ani_anio = ? AND ani_tipo = ? AND fase_numero = ? AND uc_codigo = ? AND sec_codigo = ?";
            $stmt = $this->Con()->prepare($sql_update);
            
            $params_master = [$this->getPerAprobados(), $this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $master_section];
            $stmt->execute($params_master);

            for ($i = 1; $i < count($secciones_del_grupo); $i++) {
                 $slave_section = $secciones_del_grupo[$i];
                 $params_slave = [0, $this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $slave_section];
                 $stmt->execute($params_slave);
            }
            
            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Aprobados del PER registrados con éxito.'];

        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al registrar aprobados: ' . $e->getMessage()];
        }
    }

    public function listarRegistros($doc_cedula, $rol, $filtrar_propios = false)
    {
        $sql = "SELECT s.sec_codigo, s.sec_cantidad, p.ani_anio, p.ani_tipo, p.fase_numero, uc.uc_codigo, uc.uc_nombre, uc.uc_periodo, ap.apro_cantidad, ap.doc_cedula, p.per_cantidad, p.per_aprobados FROM per_aprobados p LEFT JOIN tbl_aprobados ap ON p.ani_anio = ap.ani_anio AND p.ani_tipo = ap.ani_tipo AND p.uc_codigo = ap.uc_codigo AND p.sec_codigo = ap.sec_codigo AND p.fase_numero = ap.fase_numero LEFT JOIN tbl_seccion s ON p.sec_codigo = s.sec_codigo LEFT JOIN tbl_uc uc ON p.uc_codigo = uc.uc_codigo WHERE p.pa_estado = 1 ";
        if ($rol == 'Docente' || ($rol == 'Administrador' && $filtrar_propios)) {
            $sql .= " AND ap.doc_cedula = :doc_cedula ";
        }
        $sql .= " ORDER BY p.ani_anio DESC, p.fase_numero ASC, uc.uc_nombre ASC, s.sec_codigo ASC";
        
        $p = $this->Con()->prepare($sql);
        if ($rol == 'Docente' || ($rol == 'Administrador' && $filtrar_propios)) {
            $p->bindParam(':doc_cedula', $doc_cedula, PDO::PARAM_INT);
        }
        $p->execute();
        $registros_individuales = $p->fetchAll(PDO::FETCH_ASSOC);

        $sql_all_sigs = "SELECT s.ani_anio, s.ani_tipo, uh.uc_codigo, uh.sec_codigo, GROUP_CONCAT(CONCAT_WS('-', uh.hor_dia, uh.hor_horainicio) ORDER BY uh.hor_dia, uh.hor_horainicio SEPARATOR ';') as signature FROM uc_horario uh JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo GROUP BY s.ani_anio, s.ani_tipo, uh.uc_codigo, uh.sec_codigo";
        $p_all_sigs = $this->Con()->prepare($sql_all_sigs);
        $p_all_sigs->execute();
        $signatures_map = [];
        foreach($p_all_sigs->fetchAll(PDO::FETCH_ASSOC) as $sig) {
            $signatures_map["{$sig['ani_anio']}-{$sig['ani_tipo']}-{$sig['uc_codigo']}-{$sig['sec_codigo']}"] = $sig['signature'];
        }

        $registros_agrupados = [];
        foreach ($registros_individuales as $registro) {
            $key = "{$registro['ani_anio']}-{$registro['ani_tipo']}-{$registro['uc_codigo']}-{$registro['sec_codigo']}";
            $signature = $signatures_map[$key] ?? 'sin-horario';
            $group_key = "{$registro['ani_anio']}-{$registro['ani_tipo']}-{$registro['fase_numero']}-{$registro['uc_codigo']}-$signature";

            if (!isset($registros_agrupados[$group_key])) {
                $registros_agrupados[$group_key] = $registro;
                $registros_agrupados[$group_key]['secciones_del_grupo'] = [$registro['sec_codigo']];
            } else {
                $registros_agrupados[$group_key]['sec_cantidad'] = (int)$registros_agrupados[$group_key]['sec_cantidad'] + (int)$registro['sec_cantidad'];
                $registros_agrupados[$group_key]['apro_cantidad'] = (int)$registros_agrupados[$group_key]['apro_cantidad'] + (int)$registro['apro_cantidad'];
                $registros_agrupados[$group_key]['per_cantidad'] = (int)$registros_agrupados[$group_key]['per_cantidad'] + (int)$registro['per_cantidad'];
                $registros_agrupados[$group_key]['per_aprobados'] = (int)$registros_agrupados[$group_key]['per_aprobados'] + (int)$registro['per_aprobados'];
                $registros_agrupados[$group_key]['secciones_del_grupo'][] = $registro['sec_codigo'];
            }
        }
        
        foreach($registros_agrupados as &$grupo) {
            sort($grupo['secciones_del_grupo']);
            $grupo['sec_codigo'] = implode(',', $grupo['secciones_del_grupo']);
            $grupo['per_abierto'] = $this->esPeriodoPerAbierto($grupo['ani_anio'], $grupo['fase_numero']);
            
            $base_identificador_nuevo = $grupo['uc_codigo'] . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $grupo['ani_anio'] . "_" . $grupo['ani_tipo'] . "_fase" . $grupo['fase_numero'];
            $base_identificador_viejo = preg_replace('/[^a-zA-Z0-9_]/', '', $grupo['uc_nombre']) . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $grupo['ani_anio'] . "_" . $grupo['ani_tipo'] . "_fase" . $grupo['fase_numero'];
            
            $foundFilesDef = glob($this->archivosDir . "DEF_{$base_identificador_nuevo}.*");
            if(empty($foundFilesDef)) $foundFilesDef = glob($this->archivosDir . "DEF_{$base_identificador_viejo}.*");

            $foundFilesPer = glob($this->archivosPerDir . "PER_{$base_identificador_nuevo}.*");
            if(empty($foundFilesPer)) $foundFilesPer = glob($this->archivosPerDir . "PER_{$base_identificador_viejo}.*");

            $grupo['archivo_definitivo'] = !empty($foundFilesDef) ? basename($foundFilesDef[0]) : null;
            $grupo['archivo_per'] = !empty($foundFilesPer) ? basename($foundFilesPer[0]) : null;

            if (!empty($foundFilesPer)) {
                $grupo['reprobados_finales'] = (int)$grupo['per_cantidad'] - (int)$grupo['per_aprobados'];
            } else {
                $grupo['reprobados_finales'] = 0;
            }
        }

        return array_values($registros_agrupados);
    }
    
    public function eliminarRegistroCompleto()
    {
        $secciones_a_eliminar = explode(',', $this->getSecCodigo());
        $this->Con()->beginTransaction();
        try {
            $sql_per = "UPDATE per_aprobados SET pa_estado = 0 WHERE ani_anio = ? AND ani_tipo = ? AND fase_numero = ? AND uc_codigo = ? AND sec_codigo = ?";
            $p_del_per = $this->Con()->prepare($sql_per);

            $sql_apro = "UPDATE tbl_aprobados SET apro_estado = 0 WHERE ani_anio = ? AND ani_tipo = ? AND fase_numero = ? AND uc_codigo = ? AND sec_codigo = ?";
            $p_del_apro = $this->Con()->prepare($sql_apro);

            foreach ($secciones_a_eliminar as $sec) {
                $params = [$this->getAnioAnio(), $this->getAnioTipo(), $this->fase_numero, $this->getUcCodigo(), $sec];
                $p_del_per->execute($params);
                $p_del_apro->execute($params);
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro de grupo eliminado exitosamente.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al desactivar el registro: ' . $e->getMessage()];
        }
    }
    
    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerFaseActual()
    {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo, fase_numero FROM tbl_fase WHERE CURRENT_DATE BETWEEN fase_apertura AND fase_cierre LIMIT 1");
        $p->execute();
        return $p->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerSeccionesAgrupadasPorAnio($anio_anio, $ani_tipo, $doc_cedula)
    {
        $sql_secciones_docente = "
            SELECT DISTINCT s.sec_codigo, s.sec_cantidad 
            FROM tbl_seccion s 
            JOIN docente_horario dh ON s.sec_codigo = dh.sec_codigo
            WHERE s.ani_anio = :anio_anio AND s.ani_tipo = :ani_tipo AND dh.doc_cedula = :doc_cedula AND s.sec_estado = 1
        ";
        $p_secciones = $this->Con()->prepare($sql_secciones_docente);
        $p_secciones->execute([':anio_anio' => $anio_anio, ':ani_tipo' => $ani_tipo, ':doc_cedula' => $doc_cedula]);
        $secciones_docente = $p_secciones->fetchAll(PDO::FETCH_KEY_PAIR);

        if (empty($secciones_docente)) return [];

        $placeholders = implode(',', array_fill(0, count($secciones_docente), '?'));
        $sql_horarios = "
            SELECT 
                sec_codigo, uc_codigo,
                GROUP_CONCAT(
                    DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio, hor_horafin, esp_numero, esp_edificio, esp_tipo) 
                    ORDER BY hor_dia, hor_horainicio SEPARATOR ';'
                ) as horario_signature
            FROM uc_horario WHERE sec_codigo IN ($placeholders) GROUP BY sec_codigo, uc_codigo
        ";
        $p_horarios = $this->Con()->prepare($sql_horarios);
        $p_horarios->execute(array_keys($secciones_docente));
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
            
            $total_cantidad = 0;
            foreach ($grupo as $sec) {
                $total_cantidad += $secciones_docente[$sec] ?? 0;
                $secciones_procesadas[] = $sec;
            }
            sort($grupo);
            $resultado_final[] = [
                'sec_codigo' => implode(',', $grupo),
                'sec_codigo_label' => implode('-', $grupo),
                'sec_cantidad' => $total_cantidad
            ];
        }

        $secciones_restantes = array_diff(array_keys($secciones_docente), $secciones_procesadas);
        foreach ($secciones_restantes as $sec) {
            $resultado_final[] = ['sec_codigo' => $sec, 'sec_codigo_label' => $sec, 'sec_cantidad' => $secciones_docente[$sec]];
        }
        
        usort($resultado_final, fn($a, $b) => strcmp($a['sec_codigo_label'], $b['sec_codigo_label']));
        return $resultado_final;
    }

    public function obtenerUnidadesPorSeccion($doc_cedula, $sec_codigos_str)
    {
        $sec_codigos = explode(',', $sec_codigos_str);
        if (empty($sec_codigos)) return [];
        
        $placeholders = implode(',', array_fill(0, count($sec_codigos), '?'));

        $sql = "
            SELECT DISTINCT uc.uc_codigo, uc.uc_nombre FROM tbl_uc uc
            JOIN uc_docente ud ON uc.uc_codigo = ud.uc_codigo
            JOIN uc_horario uh ON uc.uc_codigo = uh.uc_codigo
            WHERE ud.doc_cedula = ? AND uh.sec_codigo IN ($placeholders) AND uc.uc_estado = 1
            ORDER BY uc.uc_nombre ASC
        ";
        $p = $this->Con()->prepare($sql);
        $p->execute(array_merge([$doc_cedula], $sec_codigos));
        $unidades = $p->fetchAll(PDO::FETCH_ASSOC);

        if (count($sec_codigos) == 1) return $unidades;

        $unidades_comunes = [];
        foreach ($unidades as $uc) {
            $sql_sig = "
                SELECT GROUP_CONCAT(DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio) ORDER BY hor_dia, hor_horainicio SEPARATOR ';') 
                FROM uc_horario WHERE uc_codigo = ? AND sec_codigo = ?
            ";
            $p_sig = $this->Con()->prepare($sql_sig);
            $p_sig->execute([$uc['uc_codigo'], $sec_codigos[0]]);
            $first_signature = $p_sig->fetchColumn();
            
            $es_comun = true;
            for ($i = 1; $i < count($sec_codigos); $i++) {
                $p_sig->execute([$uc['uc_codigo'], $sec_codigos[$i]]);
                if ($p_sig->fetchColumn() !== $first_signature) {
                    $es_comun = false;
                    break;
                }
            }
            if ($es_comun) $unidades_comunes[] = $uc;
        }
        return $unidades_comunes;
    }

    private function esPeriodoPerAbierto($anio_del_registro, $fase_del_registro)
    {
        $fase_actual = $this->obtenerFaseActual();
        if (!$fase_actual) {
            return false;
        }

        if ($fase_del_registro == 1) {
            return ($fase_actual['fase_numero'] == 2 && $fase_actual['ani_anio'] == $anio_del_registro);
        }

        if ($fase_del_registro == 2) {
            $siguiente_anio = $anio_del_registro + 1;
            return ($fase_actual['fase_numero'] == 1 && $fase_actual['ani_anio'] == $siguiente_anio);
        }

        return false;
    }
}
?>