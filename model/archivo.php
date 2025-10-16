<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $sec_codigo;
    private $uc_codigo;
    private $ani_anio;
    private $ani_tipo;
    private $uc_nombre;
    private $doc_cedula;
    private $archivosDir;

    public function __construct()
    {
        parent::__construct();
        $this->archivosDir = realpath(__DIR__ . '/../archivos_subidos/') . DIRECTORY_SEPARATOR;

        if (!file_exists($this->archivosDir)) {
            mkdir($this->archivosDir, 0777, true);
        }
    }

    public function setSecCodigo($sec_codigo)
    {
        $this->sec_codigo = $sec_codigo;
    }
    public function getSecCodigo()
    {
        return $this->sec_codigo;
    }
    public function setUcCodigo($uc_codigo)
    {
        $this->uc_codigo = $uc_codigo;
    }
    public function getUcCodigo()
    {
        return $this->uc_codigo;
    }
    public function setAnioAnio($ani_anio)
    {
        $this->ani_anio = $ani_anio;
    }
    public function getAnioAnio()
    {
        return $this->ani_anio;
    }
    public function setAnioTipo($ani_tipo)
    {
        $this->ani_tipo = $ani_tipo;
    }
    public function getAnioTipo()
    {
        return $this->ani_tipo;
    }
    public function setUcNombre($uc_nombre)
    {
        $this->uc_nombre = $uc_nombre;
    }
    public function getUcNombre()
    {
        return $this->uc_nombre;
    }
    public function setDocCedula($doc_cedula)
    {
        $this->doc_cedula = $doc_cedula;
    }
    public function getDocCedula()
    {
        return $this->doc_cedula;
    }

    private function generarIdentificadorUnico()
    {
        $ucCodigo = $this->getUcCodigo();
        $secCodigo = str_replace(',', '_', $this->getSecCodigo());
        $anioAnio = $this->getAnioAnio();
        $anioTipo = $this->getAnioTipo();
        return $ucCodigo . "_" . $secCodigo . "_" . $anioAnio . "_" . $anioTipo;
    }

    public function guardarRegistroInicial($archivo)
    {
        $secciones_a_registrar = explode(',', $this->getSecCodigo());

        $this->Con()->beginTransaction();
        try {
            if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'mensaje' => 'El archivo del acta de notas es obligatorio.'];
            }

            $identificador = $this->generarIdentificadorUnico();
            $nombreArchivo = "DEF_{$identificador}." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($archivo['tmp_name'], $this->archivosDir . $nombreArchivo)) {
                throw new Exception('Error al guardar el archivo.');
            }

            $sql_apro = "INSERT INTO tbl_aprobados (ani_anio, ani_tipo, uc_codigo, sec_codigo, doc_cedula, apro_estado) VALUES (?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE doc_cedula = VALUES(doc_cedula), apro_estado = 1";
            $p_apro = $this->Con()->prepare($sql_apro);

            foreach ($secciones_a_registrar as $seccion) {
                $p_apro->execute([$this->getAnioAnio(), $this->getAnioTipo(), $this->getUcCodigo(), $seccion, $this->getDocCedula()]);
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Acta guardada con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    public function listarRegistros()
    {
        $sql = "SELECT 
                    ap.sec_codigo, 
                    ap.ani_anio, ap.ani_tipo, 
                    uc.uc_codigo, uc.uc_nombre, 
                    ap.doc_cedula, d.doc_nombre, d.doc_apellido
                FROM tbl_aprobados ap
                LEFT JOIN tbl_seccion s ON ap.sec_codigo = s.sec_codigo 
                LEFT JOIN tbl_uc uc ON ap.uc_codigo = uc.uc_codigo
                LEFT JOIN tbl_docente d ON ap.doc_cedula = d.doc_cedula
                WHERE ap.apro_estado = 1
                ORDER BY ap.ani_anio DESC, d.doc_nombre ASC, uc.uc_nombre ASC, s.sec_codigo ASC";

        $p = $this->Con()->prepare($sql);
        $p->execute();
        $registros_individuales = $p->fetchAll(PDO::FETCH_ASSOC);

        $sql_all_sigs = "SELECT s.ani_anio, s.ani_tipo, uh.uc_codigo, uh.sec_codigo, GROUP_CONCAT(CONCAT_WS('-', uh.hor_dia, uh.hor_horainicio) ORDER BY uh.hor_dia, uh.hor_horainicio SEPARATOR ';') as signature FROM uc_horario uh JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo GROUP BY s.ani_anio, s.ani_tipo, uh.uc_codigo, uh.sec_codigo";
        $p_all_sigs = $this->Con()->prepare($sql_all_sigs);
        $p_all_sigs->execute();
        $signatures_map = [];
        foreach ($p_all_sigs->fetchAll(PDO::FETCH_ASSOC) as $sig) {
            $signatures_map["{$sig['ani_anio']}-{$sig['ani_tipo']}-{$sig['uc_codigo']}-{$sig['sec_codigo']}"] = $sig['signature'];
        }

        $registros_agrupados = [];
        foreach ($registros_individuales as $registro) {
            $key = "{$registro['ani_anio']}-{$registro['ani_tipo']}-{$registro['uc_codigo']}-{$registro['sec_codigo']}";
            $signature = $signatures_map[$key] ?? 'sin-horario';
            $group_key = "{$registro['ani_anio']}-{$registro['ani_tipo']}-{$registro['uc_codigo']}-$signature-{$registro['doc_cedula']}";

            if (!isset($registros_agrupados[$group_key])) {
                $registros_agrupados[$group_key] = $registro;
                $registros_agrupados[$group_key]['secciones_del_grupo'] = [$registro['sec_codigo']];
            } else {
                $registros_agrupados[$group_key]['secciones_del_grupo'][] = $registro['sec_codigo'];
            }
        }

        foreach ($registros_agrupados as &$grupo) {
            sort($grupo['secciones_del_grupo']);
            $grupo['sec_codigo'] = implode(',', $grupo['secciones_del_grupo']);

            $base_identificador = $grupo['uc_codigo'] . "_" . str_replace(',', '_', $grupo['sec_codigo']) . "_" . $grupo['ani_anio'] . "_" . $grupo['ani_tipo'];

            $extension_probable = "pdf";
            $grupo['archivo_definitivo'] = "DEF_{$base_identificador}.{$extension_probable}";
        }

        return array_values($registros_agrupados);
    }

    public function eliminarRegistroCompleto()
    {
        $secciones_a_eliminar = explode(',', $this->getSecCodigo());
        $this->Con()->beginTransaction();
        try {
            $sql_apro = "UPDATE tbl_aprobados SET apro_estado = 0 WHERE ani_anio = ? AND ani_tipo = ? AND uc_codigo = ? AND sec_codigo = ?";
            $p_del_apro = $this->Con()->prepare($sql_apro);

            foreach ($secciones_a_eliminar as $sec) {
                $params = [$this->getAnioAnio(), $this->getAnioTipo(), $this->getUcCodigo(), $sec];
                $p_del_apro->execute($params);
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro de grupo eliminado exitosamente.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al desactivar el registro: ' . $e->getMessage()];
        }
    }

    
    public function verificarExistencia($ani_anio, $ani_tipo, $uc_codigo, $sec_codigo)
    {
        
        $sql = "SELECT COUNT(*) FROM tbl_aprobados WHERE ani_anio = ? AND ani_tipo = ? AND uc_codigo = ? AND sec_codigo = ? AND apro_estado = 1";
        $stmt = $this->Con()->prepare($sql);
        $stmt->execute([$ani_anio, $ani_tipo, $uc_codigo, $sec_codigo]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function obtenerDocentes()
    {
        $p = $this->Con()->prepare("SELECT doc_cedula, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_nombre, doc_apellido");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
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
            SELECT sec_codigo, uc_codigo,
                GROUP_CONCAT(
                    DISTINCT CONCAT_WS('-', hor_dia, hor_horainicio) ORDER BY hor_dia, hor_horainicio SEPARATOR ';'
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

            foreach ($grupo as $sec) {
                $secciones_procesadas[] = $sec;
            }
            sort($grupo);
            $resultado_final[] = ['sec_codigo' => implode(',', $grupo), 'sec_codigo_label' => implode('-', $grupo)];
        }

        $secciones_restantes = array_diff(array_keys($secciones_docente), $secciones_procesadas);
        foreach ($secciones_restantes as $sec) {
            $resultado_final[] = ['sec_codigo' => $sec, 'sec_codigo_label' => $sec];
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
}
