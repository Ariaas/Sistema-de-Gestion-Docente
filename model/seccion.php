<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

      public function __construct()
    {
        parent::__construct();
    }


public function obtenerTodosLosHorarios() {
    try {
       
        $sql = "SELECT 
                uh.sec_codigo, 
                uh.doc_cedula,
                uh.esp_numero,
                uh.esp_tipo,
                uh.esp_edificio,
                uh.hor_dia as dia, 
                uh.hor_horainicio as hora_inicio, 
                uh.hor_horafin as hora_fin,
                s.sec_estado
            FROM uc_horario uh 
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            WHERE s.sec_estado = 1";
            
        $stmt = $this->Con()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as &$row) {
            if (isset($row['hora_inicio']) && strlen($row['hora_inicio']) === 5) {
                $row['hora_inicio'] .= ':00';
            }
            if (isset($row['hora_fin']) && strlen($row['hora_fin']) === 5) {
                $row['hora_fin'] .= ':00';
            }
        }
        return $results;
    } catch (Exception $e) {
        error_log("Error en obtenerTodosLosHorarios: " . $e->getMessage());
        return [];
    }
}

    private function _obtenerHorasAcademicasActuales($doc_cedula, $co, $seccion_a_excluir = null, $ani_anio_excluir = null)
    {
        $sql = "
            SELECT SUM(u.uc_creditos)
            FROM uc_horario uh
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio
            JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
            WHERE s.sec_estado = 1 AND uh.doc_cedula = :doc_cedula
        ";
        $params = [':doc_cedula' => $doc_cedula];

        if ($seccion_a_excluir !== null) {
            $sql .= " AND s.sec_codigo != :sec_codigo_excluir";
            $params[':sec_codigo_excluir'] = $seccion_a_excluir;
        }
        
        if ($ani_anio_excluir !== null) {
            $sql .= " AND s.ani_anio != :ani_anio_excluir";
            $params[':ani_anio_excluir'] = $ani_anio_excluir;
        }

        $stmt = $co->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        return $total ? (int)$total : 0;
    }

  
    private function determinarFaseActual()
    {
        try {
            $stmt_anio = $this->Con()->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt_anio->execute();
            $anio_activo = $stmt_anio->fetch(PDO::FETCH_ASSOC);

            if ($anio_activo) {
                $stmt_fases = $this->Con()->prepare(
                    "SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase WHERE ani_anio = :ani_anio AND ani_tipo = :ani_tipo"
                );
                $stmt_fases->execute([':ani_anio' => $anio_activo['ani_anio'], ':ani_tipo' => $anio_activo['ani_tipo']]);
                $fases = $stmt_fases->fetchAll(PDO::FETCH_ASSOC);
                $hoy = new DateTime();

                foreach ($fases as $fase) {
                    $apertura = new DateTime($fase['fase_apertura']);
                    $cierre = new DateTime($fase['fase_cierre']);
                    $cierre->setTime(23, 59, 59);
                    if ($hoy >= $apertura && $hoy <= $cierre) {
                        return 'fase' . $fase['fase_numero'];
                    }
                }
            }
            return 'ninguna';
        } catch (Exception $e) {
            error_log("Error en determinarFaseActual: " . $e->getMessage());
            return 'ninguna';
        }
    }

    public function obtenerCohortesMalla()
    {
        try {
            return $this->Con()->query("SELECT DISTINCT mal_cohorte FROM tbl_malla WHERE mal_activa = 1")->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            error_log("Error al obtener cohortes: " . $e->getMessage());
            return [];
        }
    }

    public function ActualizarSeccionesParaFase2()
    {
        if ($this->determinarFaseActual() !== 'fase2' || isset($_SESSION['promocion_f2_ejecutada_session'])) {
            return null;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $reporte = ['exitos' => 0, 'fallos' => [], 'observaciones' => []];

        try {
            $stmt_anio = $co->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt_anio->execute();
            $anio_activo = $stmt_anio->fetch(PDO::FETCH_ASSOC);

            if (!$anio_activo) {
                $_SESSION['promocion_f2_ejecutada_session'] = true;
                return ['observaciones' => ['No hay un año académico activo para procesar.']];
            }

            $stmt_secciones = $co->prepare("SELECT sec_codigo, sec_cantidad FROM tbl_seccion WHERE ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1");
            $stmt_secciones->execute([':anio' => $anio_activo['ani_anio'], ':tipo' => $anio_activo['ani_tipo']]);
            $secciones_a_procesar = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);

            if (empty($secciones_a_procesar)) {
                $_SESSION['promocion_f2_ejecutada_session'] = true;
                return null;
            }

            $ucs_info = $co->query("SELECT uc_codigo, uc_nombre, uc_periodo, uc_trayecto FROM tbl_uc")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

            foreach ($secciones_a_procesar as $seccion) {
                $co->beginTransaction();
                try {
                    $horario_actual_result = $this->ConsultarDetalles($seccion['sec_codigo'], $anio_activo['ani_anio']);
                    $horario_actual = $horario_actual_result['mensaje'] ?? [];
                    $nuevo_horario_seccion = [];
                    $clases_procesadas = 0;

                    foreach ($horario_actual as $clase) {
                        $uc_periodo = $ucs_info[$clase['uc_codigo']][0]['uc_periodo'] ?? 'Anual';
                        $uc_trayecto = $ucs_info[$clase['uc_codigo']][0]['uc_trayecto'] ?? null;
                        
                        if ($uc_periodo === 'Anual' || $uc_periodo === 'Fase II') {
                            $nuevo_horario_seccion[] = $clase;
                            continue;
                        }

                        if ($uc_periodo === 'Fase I' || $uc_periodo === '0') {
                            $clases_procesadas++;
                            $reemplazo = $this->encontrarReemplazoFase2($clase, $uc_trayecto, $nuevo_horario_seccion, $co, $ucs_info);
                            
                            if ($reemplazo) {
                                $nuevo_horario_seccion[] = $reemplazo;
                                $reporte['observaciones'][] = "En sección <strong>{$seccion['sec_codigo']}</strong>: UC '{$ucs_info[$clase['uc_codigo']][0]['uc_nombre']}' reemplazada por '{$ucs_info[$reemplazo['uc_codigo']][0]['uc_nombre']}'.";
                            } else {
                                $reporte['observaciones'][] = "En sección <strong>{$seccion['sec_codigo']}</strong>: No se encontró reemplazo para la clase de '{$ucs_info[$clase['uc_codigo']][0]['uc_nombre']}' del docente {$clase['doc_cedula']}. El bloque ha sido vaciado.";
                            }
                        }
                    }

                    if ($clases_procesadas > 0) {
                        $this->Modificar($seccion['sec_codigo'], json_encode($nuevo_horario_seccion), $seccion['sec_cantidad']);
                    }

                    $co->commit();
                    $reporte['exitos']++;

                } catch (Exception $e) {
                    $co->rollBack();
                    $reporte['fallos'][] = "Error al procesar sección {$seccion['sec_codigo']}: " . $e->getMessage();
                }
            }

            $_SESSION['promocion_f2_ejecutada_session'] = true;
            return $reporte;

        } catch (Exception $e) {
            error_log("Error Crítico en ActualizarSeccionesParaFase2: " . $e->getMessage());
            return ['exitos' => 0, 'fallos' => ['Ocurrió un error general: ' . $e->getMessage()], 'observaciones' => []];
        }
    }

    private function encontrarReemplazoFase2($clase_original, $trayecto, $horario_propuesto_actual, $co, $ucs_info_global) {
        $doc_cedula_original = $clase_original['doc_cedula'];
        $nombre_uc_original = $ucs_info_global[$clase_original['uc_codigo']][0]['uc_nombre'];
        $posible_nombre_f2 = str_ireplace([' I', ' 1'], [' II', ' 2'], $nombre_uc_original);

        $sql_reemplazo = "SELECT u.uc_codigo FROM tbl_uc u JOIN uc_docente ud ON u.uc_codigo = ud.uc_codigo WHERE ud.doc_cedula = :doc_cedula AND u.uc_nombre = :nombre_f2 AND u.uc_trayecto = :trayecto AND u.uc_periodo = 'Fase II' AND u.uc_estado = 1 LIMIT 1";
        $stmt_reemplazo = $co->prepare($sql_reemplazo);
        $stmt_reemplazo->execute([':doc_cedula' => $doc_cedula_original, ':nombre_f2' => $posible_nombre_f2, ':trayecto' => $trayecto]);
        $uc_reemplazo_directo = $stmt_reemplazo->fetchColumn();

        if ($uc_reemplazo_directo) {
            $clase_original['uc_codigo'] = $uc_reemplazo_directo;
            return $clase_original;
        }

        $sql_otras_uc = "SELECT u.uc_codigo FROM tbl_uc u JOIN uc_docente ud ON u.uc_codigo = ud.uc_codigo WHERE ud.doc_cedula = :doc_cedula AND u.uc_trayecto = :trayecto AND u.uc_periodo = 'Fase II' AND u.uc_estado = 1";
        $stmt_otras_uc = $co->prepare($sql_otras_uc);
        $stmt_otras_uc->execute([':doc_cedula' => $doc_cedula_original, ':trayecto' => $trayecto]);
        $ucs_f2_docente = $stmt_otras_uc->fetchAll(PDO::FETCH_COLUMN);

        if (count($ucs_f2_docente) === 1) {
            $clase_original['uc_codigo'] = $ucs_f2_docente[0];
            return $clase_original;
        }
        
        $sql_all_uc_f2 = "SELECT uc_codigo FROM tbl_uc WHERE uc_trayecto = :trayecto AND uc_periodo = 'Fase II' AND uc_estado = 1";
        $stmt_all_uc_f2 = $co->prepare($sql_all_uc_f2);
        $stmt_all_uc_f2->execute([':trayecto' => $trayecto]);
        $todas_uc_f2 = $stmt_all_uc_f2->fetchAll(PDO::FETCH_COLUMN);
        
        if(empty($todas_uc_f2)) return null;

        shuffle($todas_uc_f2);

        foreach ($todas_uc_f2 as $uc_f2_codigo) {
            $stmt_docentes_uc = $co->prepare("SELECT doc_cedula FROM uc_docente WHERE uc_codigo = ?");
            $stmt_docentes_uc->execute([$uc_f2_codigo]);
            $posibles_docentes = $stmt_docentes_uc->fetchAll(PDO::FETCH_COLUMN);
            shuffle($posibles_docentes);
            
            $stmt_costo = $co->prepare("SELECT uc_creditos FROM tbl_uc WHERE uc_codigo = :uc_codigo");
            $stmt_costo->execute([':uc_codigo' => $uc_f2_codigo]);
            $costo_uc = (int)$stmt_costo->fetchColumn();

            foreach ($posibles_docentes as $docente_id) {
                $val_vivo = $this->ValidarClaseEnVivo($docente_id, $clase_original['espacio']['numero'], $clase_original['espacio']['tipo'], $clase_original['espacio']['edificio'], $clase_original['dia'], $clase_original['hora_inicio'], $clase_original['hora_fin'], 'temp_check_code');
                if ($val_vivo['conflicto'] === true) continue;
                
                foreach($horario_propuesto_actual as $clase_propuesta) {
                    if($clase_propuesta['dia'] == $clase_original['dia'] && $clase_propuesta['hora_inicio'] == $clase_original['hora_inicio'] && $clase_propuesta['doc_cedula'] == $docente_id) {
                         continue 2;
                    }
                }
                
                return [
                    'uc_codigo'   => $uc_f2_codigo,
                    'doc_cedula'  => $docente_id,
                    'espacio'     => $clase_original['espacio'],
                    'dia'         => $clase_original['dia'],
                    'hora_inicio' => $clase_original['hora_inicio'],
                    'hora_fin'    => $clase_original['hora_fin']
                ];
            }
        }
        return null;
    }

   public function UnirHorarios($sec_codigo_origen, $sec_codigos_a_unir)
{
    if (empty($sec_codigo_origen) || empty($sec_codigos_a_unir) || count($sec_codigos_a_unir) < 2) {
        return ['resultado' => 'error', 'mensaje' => 'Debe seleccionar al menos 2 secciones y una de origen.'];
    }
    try {
        $co_val = $this->Con();
        $placeholders = implode(',', array_fill(0, count($sec_codigos_a_unir), '?'));
        $stmt = $co_val->prepare("SELECT sec_codigo, ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo IN ($placeholders)");
        $stmt->execute(array_values($sec_codigos_a_unir));
        $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($secciones) !== count($sec_codigos_a_unir)) {
            return ['resultado' => 'error', 'mensaje' => 'Una o más secciones seleccionadas no son válidas.'];
        }
        $seccion_origen_data = null;
        foreach ($secciones as $s) {
            if ($s['sec_codigo'] == $sec_codigo_origen) {
                $seccion_origen_data = $s;
                break;
            }
        }
        if (!$seccion_origen_data) {
            return ['resultado' => 'error', 'mensaje' => 'La sección de origen seleccionada no es válida o no está entre las secciones a unir.'];
        }
        $primer_anio = $seccion_origen_data['ani_anio'];
        $primer_tipo = $seccion_origen_data['ani_tipo'];
        $primer_trayecto = substr($seccion_origen_data['sec_codigo'], 0, 1);
        $primer_turno = substr($seccion_origen_data['sec_codigo'], 1, 1);

        foreach ($secciones as $seccion) {
            $codigo_actual_str = (string)$seccion['sec_codigo'];
            if (
                $seccion['ani_anio'] !== $primer_anio ||
                $seccion['ani_tipo'] !== $primer_tipo ||
                substr($codigo_actual_str, 0, 1) !== $primer_trayecto ||
                substr($codigo_actual_str, 1, 1) !== $primer_turno
            ) {
                return ['resultado' => 'error', 'mensaje' => 'Acción no permitida: Solo se pueden unir horarios de secciones del mismo año, tipo, trayecto y turno.'];
            }
        }
    } catch (Exception $e) {
        return ['resultado' => 'error', 'mensaje' => 'Error al validar las secciones: ' . $e->getMessage()];
    }
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try {
        $co->beginTransaction();

       
        $grupo_id = uniqid('grupo_', true);

       
        $placeholders_secciones = implode(',', array_fill(0, count($sec_codigos_a_unir), '?'));
        $stmt_update_grupo = $co->prepare("UPDATE tbl_seccion SET grupo_union_id = ? WHERE sec_codigo IN ($placeholders_secciones)");
        $params = array_merge([$grupo_id], $sec_codigos_a_unir);
        $stmt_update_grupo->execute($params);


       
$clases_origen_result = $this->ConsultarDetalles($sec_codigo_origen, $seccion_origen_data['ani_anio']);
        $clases_origen = $clases_origen_result['mensaje'] ?? [];
        $codigos_destinos = array_filter($sec_codigos_a_unir, function ($codigo) use ($sec_codigo_origen) {
            return $codigo != $sec_codigo_origen;
        });
        foreach ($codigos_destinos as $codigo_destino) {
            $anio_academico = $seccion_origen_data['ani_anio'];
$this->EliminarDependenciasDeSeccion($codigo_destino, $anio_academico, $co);
            
            if (!empty($clases_origen)) {
                $hora_principal_para_turno = $clases_origen[0]['hora_inicio'] ?? '08:00:00';
                $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, ani_anio, ani_tipo, tur_nombre, hor_estado) VALUES (:sec_codigo, :ani_anio, :ani_tipo, :tur_nombre, 1)");
                $stmt_hor->execute([
                    ':sec_codigo' => $codigo_destino,
                    ':ani_anio' => $anio_academico,
                    ':ani_tipo' => $seccion_origen_data['ani_tipo'],
                    ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
                ]);

                $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, ani_anio, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :doc_cedula, :sec_codigo, :ani_anio, :subgrupo, :esp_numero, :esp_tipo, :esp_edificio, :dia, :inicio, :fin)");
                $stmt_doc = $co->prepare("INSERT INTO docente_horario (doc_cedula, sec_codigo) VALUES (:doc_cedula, :sec_codigo) ON DUPLICATE KEY UPDATE sec_codigo=sec_codigo");
                $docentes_procesados = [];
                foreach ($clases_origen as $item) {
                    $espacio = $item['espacio'] ?? ['numero' => null, 'tipo' => null, 'edificio' => null];
                    $stmt_uh->execute([
        ':uc_codigo'    => $item['uc_codigo'],
        ':doc_cedula'   => $item['doc_cedula'],
        ':sec_codigo'   => $codigo_destino,
        ':ani_anio'     => $anio_academico, 
        ':subgrupo'     => $item['subgrupo'],   
        ':esp_numero'   => $espacio['numero'],
        ':esp_tipo'     => $espacio['tipo'],
        ':esp_edificio' => $espacio['edificio'],
        ':dia'          => $item['dia'],
        ':inicio'       => $item['hora_inicio'],
        ':fin'          => $item['hora_fin']
    ]);

                    if (!in_array($item['doc_cedula'], $docentes_procesados)) {
                        $stmt_doc->execute([':doc_cedula' => $item['doc_cedula'], ':sec_codigo' => $codigo_destino]);
                        $docentes_procesados[] = $item['doc_cedula'];
                    }
                }
            }
        }
        $co->commit();
        return ['resultado' => 'unir_horarios_ok', 'mensaje' => '¡Horarios unidos y actualizados correctamente!'];
    } catch (Exception $e) {
        if ($co->inTransaction()) $co->rollBack();
        return ['resultado' => 'error', 'mensaje' => 'Error al unir los horarios: ' . $e->getMessage()];
    }
}
    
public function RegistrarSeccion($codigoSeccion, $cantidadSeccion, $anio_anio, $anio_tipo, $forzar_cohorte = false)
{
    if (empty($codigoSeccion) || !isset($cantidadSeccion) || $cantidadSeccion === '' || empty($anio_anio) || empty($anio_tipo)) {
        return ['resultado' => 'error', 'mensaje' => 'Todos los campos de la sección son obligatorios.'];
    }

    if (!preg_match('/^[a-zA-Z]{2,3}\d{4}$/', $codigoSeccion)) {
        return ['resultado' => 'error', 'mensaje' => 'Formato de código inválido. Debe tener 2-3 letras seguido de exactamente 4 dígitos (ej: IN1101, IIN3104).'];
    }
    
    $prefix = strtoupper(substr($codigoSeccion, 0, preg_match('/^\D+/', $codigoSeccion, $matches) ? strlen($matches[0]) : 0));
    $numericPart = preg_replace('/^\D+/', '', $codigoSeccion);
    $codigoSeccion = $prefix . $numericPart;
    
    // Validar que exista la cohorte correspondiente al último dígito
    if (!$forzar_cohorte) {
        $numeroCohorte = (int)substr($numericPart, -1);
        $co = $this->Con();
        $stmt_malla = $co->prepare("SELECT mal_codigo, mal_nombre FROM tbl_malla WHERE mal_cohorte = :cohorte AND mal_activa = 1");
        $stmt_malla->execute([':cohorte' => $numeroCohorte]);
        $malla = $stmt_malla->fetch(PDO::FETCH_ASSOC);
        
        if (!$malla) {
            return [
                'resultado' => 'confirmar_cohorte',
                'mensaje' => "<b>Advertencia:</b> La Cohorte {$numeroCohorte} no está creada o no está activa en la malla.<br/><br/>¿Desea registrar la sección de todas formas?"
            ];
        }
    }

    $cantidadInt = filter_var($cantidadSeccion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
    if ($cantidadInt === false) {
        return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número entero entre 0 y 99.'];
    }

    $co = $this->Con();
    try {
        $co->beginTransaction();
        
        $stmt_check = $co->prepare("SELECT sec_estado FROM tbl_seccion WHERE sec_codigo = :codigo AND ani_anio = :anio AND ani_tipo = :tipo");
        $stmt_check->execute([':codigo' => $codigoSeccion, ':anio' => $anio_anio, ':tipo' => $anio_tipo]);
        $seccion_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($seccion_existente) {
            if ($seccion_existente['sec_estado'] == 1) {
                $co->rollBack();
                $tipoTexto = $anio_tipo === 'regular' ? 'Regular' : 'Intensivo';
                return ['resultado' => 'error', 'mensaje' => "¡ERROR! La sección {$codigoSeccion} ya existe para el año {$anio_anio} ({$tipoTexto}).<br/>Puede registrar esta misma sección en un período diferente (Regular/Intensivo)."];
            } else {
                $this->EliminarDependenciasDeSeccion($codigoSeccion, $anio_anio, $co);
                $stmtSeccion = $co->prepare(
                    "UPDATE tbl_seccion SET sec_cantidad = :cantidad, ani_anio = :anio, ani_tipo = :tipo, sec_estado = 1 WHERE sec_codigo = :codigo"
                );
            }
        } else {
            $stmtSeccion = $co->prepare(
                "INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_anio, ani_tipo, sec_estado) VALUES (:codigo, :cantidad, :anio, :tipo, 1)"
            );
        }
        
        $stmtSeccion->bindParam(':codigo', $codigoSeccion, PDO::PARAM_STR);
        $stmtSeccion->bindParam(':cantidad', $cantidadInt, PDO::PARAM_INT);
        $stmtSeccion->bindParam(':anio', $anio_anio, PDO::PARAM_INT);
        $stmtSeccion->bindParam(':tipo', $anio_tipo, PDO::PARAM_STR);
        $stmtSeccion->execute();

        $co->commit();

        return [
            'resultado' => 'registrar_seccion_ok',
            'mensaje' => 'Registro Incluido!<br/>Se registró la sección correctamente!',
            'nuevo_codigo' => $codigoSeccion,
            'nueva_cantidad' => $cantidadInt
        ];
    } catch (Exception $e) {
        if ($co->inTransaction()) {
            $co->rollBack();
        }
        return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
    }
}

    public function VerificarCodigoSeccion($codigoSeccion, $anio_anio, $anio_tipo)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = :codigo AND ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1");
            $stmt->execute([':codigo' => $codigoSeccion, ':anio' => $anio_anio, ':tipo' => $anio_tipo]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en VerificarCodigoSeccion: " . $e->getMessage());
            return true;
        }
    }

    public function VerificarMallaExiste($numeroMalla)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT mal_codigo, mal_nombre FROM tbl_malla WHERE mal_cohorte = :cohorte AND mal_activa = 1");
            $stmt->execute([':cohorte' => (int)$numeroMalla]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Exception $e) {
            error_log("Error en VerificarMallaExiste: " . $e->getMessage());
            return false;
        }
    }

    public function ListarAgrupado()
    {
        try {
            $stmt = $this->Con()->query("SELECT ts.sec_codigo, ts.sec_cantidad, ts.ani_anio, ts.ani_tipo FROM tbl_seccion ts WHERE ts.sec_estado = 1 ORDER BY ts.ani_anio DESC, ts.sec_codigo");
            return ['resultado' => 'consultar_agrupado', 'mensaje' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => "¡ERROR!<br/>Error al listar horarios: " . $e->getMessage()];
        }
    }
    
 public function ValidarClaseEnVivo($doc_cedula, $uc_codigo, $espacio, $dia, $hora_inicio, $hora_fin, $sec_codigo)
{
    $co = $this->Con();
    $conflictos = [];

   
    $stmt_grupo = $co->prepare("SELECT grupo_union_id FROM tbl_seccion WHERE sec_codigo = ?");
    $stmt_grupo->execute([$sec_codigo]);
    $grupo_id = $stmt_grupo->fetchColumn();

    $secciones_a_excluir = [$sec_codigo]; 

   
    if ($grupo_id) {
        $stmt_secciones_grupo = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id = ?");
        $stmt_secciones_grupo->execute([$grupo_id]);
        $secciones_hermanas = $stmt_secciones_grupo->fetchAll(PDO::FETCH_COLUMN);
        $secciones_a_excluir = array_unique(array_merge($secciones_a_excluir, $secciones_hermanas));
    }
    

    $placeholders_exclusion = implode(',', array_fill(0, count($secciones_a_excluir), '?'));

    if (!empty($doc_cedula)) {
        
        $sql_doc = "SELECT uh.sec_codigo, d.doc_nombre, d.doc_apellido
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                    WHERE s.sec_estado = 1 AND uh.doc_cedula = ? AND uh.sec_codigo NOT IN ($placeholders_exclusion)
                    AND uh.hor_dia = ? AND uh.hor_horainicio < ? AND uh.hor_horafin > ?";
        
        $stmt_doc = $co->prepare($sql_doc);
       
        $params_doc = array_merge([$doc_cedula], $secciones_a_excluir, [$dia, $hora_fin, $hora_inicio]);
        $stmt_doc->execute($params_doc);
        
        $conflictos_docente = $stmt_doc->fetchAll(PDO::FETCH_ASSOC);

        foreach($conflictos_docente as $conflicto_docente) {
            $mensaje = "<b>Conflicto de Horario:</b> El docente <b>{$conflicto_docente['doc_nombre']} {$conflicto_docente['doc_apellido']}</b> ya tiene una clase en la sección <b>{$conflicto_docente['sec_codigo']}</b> en este horario.";
            $conflictos[] = ['tipo' => 'docente', 'mensaje' => $mensaje];
        }
    }

    
    if (!empty($espacio) && !empty($espacio['numero'])) {
        
        $sql_esp = "SELECT uh.sec_codigo, d.doc_nombre, d.doc_apellido
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    LEFT JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                    WHERE s.sec_estado = 1 AND uh.esp_numero = ? AND uh.esp_tipo = ? AND uh.esp_edificio = ?
                    AND uh.sec_codigo NOT IN ($placeholders_exclusion) AND uh.hor_dia = ? AND uh.hor_horainicio < ? AND uh.hor_horafin > ?";

        $stmt_esp = $co->prepare($sql_esp);
        
        $params_esp = array_merge([$espacio['numero'], $espacio['tipo'], $espacio['edificio']], $secciones_a_excluir, [$dia, $hora_fin, $hora_inicio]);
        $stmt_esp->execute($params_esp);
        $conflicto_espacio = $stmt_esp->fetch(PDO::FETCH_ASSOC);

        if ($conflicto_espacio) {
            $docente_ocupante = ($conflicto_espacio['doc_nombre']) ? "(Docente: {$conflicto_espacio['doc_nombre']} {$conflicto_espacio['doc_apellido']})" : "";
            $mensaje = "<b>Conflicto de Espacio:</b> El espacio <b>{$espacio['numero']} ({$espacio['tipo']})</b> ya está ocupado por la sección <b>{$conflicto_espacio['sec_codigo']}</b> {$docente_ocupante} en este horario.";
            $conflictos[] = ['tipo' => 'espacio', 'mensaje' => $mensaje];
        }
    }

    if (!empty($conflictos)) {
        return ['conflicto' => true, 'mensajes' => $conflictos];
    }

    return ['conflicto' => false];
}

  public function Modificar($sec_codigo, $ani_anio, $items_horario_json, $cantidadSeccion, $forzar = false, $modo_operacion = 'modificar')
{
    if (empty($sec_codigo) || empty($ani_anio) || !isset($cantidadSeccion)) {
        return ['resultado' => 'error', 'mensaje' => 'Faltan datos clave (código, año o cantidad) para modificar la sección.'];
    }
    
    $cantidadInt = filter_var($cantidadSeccion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
    if ($cantidadInt === false) {
        return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número entero entre 0 y 99.'];
    }

    $items_horario = json_decode($items_horario_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['resultado' => 'error', 'mensaje' => 'El formato del horario es incorrecto. Error: ' . json_last_error_msg()];
    }

    if (!$forzar) {
        $co = $this->Con();
        $conflictos_encontrados = [];
        
        $uc_codigos = array_map(function($item) { return $item['uc_codigo'] ?? null; }, $items_horario);
        $uc_counts = array_count_values(array_filter($uc_codigos));

        foreach ($uc_counts as $uc => $count) {
            if ($count > 1) {
                $stmt_uc_name = $co->prepare("SELECT uc_nombre FROM tbl_uc WHERE uc_codigo = ?");
                $stmt_uc_name->execute([$uc]);
                $uc_nombre = $stmt_uc_name->fetchColumn();
                $conflictos_encontrados[] = "La UC <b>'{$uc_nombre}'</b> está asignada {$count} veces en esta sección.";
            }
        }

        $horas_por_docente = [];
        foreach ($items_horario as $item) {
            if (!empty($item['doc_cedula']) && !empty($item['uc_codigo'])) {
                if (!isset($horas_por_docente[$item['doc_cedula']])) {
                    $horas_por_docente[$item['doc_cedula']] = 0;
                }
                $stmt_horas_uc = $co->prepare("SELECT uc_creditos FROM tbl_uc WHERE uc_codigo = :uc_codigo");
                $stmt_horas_uc->execute([':uc_codigo' => $item['uc_codigo']]);
                $horas_uc = $stmt_horas_uc->fetchColumn();
                if ($horas_uc) {
                    $horas_por_docente[$item['doc_cedula']] += (int)$horas_uc;
                }
            }
        }

        foreach ($horas_por_docente as $doc_cedula => $horas_asignadas) {
            $stmt_max_horas = $co->prepare("SELECT act_academicas FROM tbl_actividad WHERE doc_cedula = ?");
            $stmt_max_horas->execute([$doc_cedula]);
            $max_horas = $stmt_max_horas->fetchColumn();
            $horas_actuales = $this->_obtenerHorasAcademicasActuales($doc_cedula, $co, $sec_codigo, $ani_anio);
            
            if ($max_horas !== false && ($horas_actuales + $horas_asignadas) > (int)$max_horas) {
                 $stmt_doc = $co->prepare("SELECT doc_nombre, doc_apellido FROM tbl_docente WHERE doc_cedula = ?");
                 $stmt_doc->execute([$doc_cedula]);
                 $doc = $stmt_doc->fetch(PDO::FETCH_ASSOC);
                 $conflictos_encontrados[] = "El docente <b>{$doc['doc_nombre']} {$doc['doc_apellido']}</b> excedería sus horas académicas (Asignadas: " . ($horas_actuales + $horas_asignadas) . ", Máximo: {$max_horas}).";
            }
        }

        foreach ($items_horario as $item) {
            $validacion = $this->ValidarClaseEnVivo(
                $item['doc_cedula'] ?? null,
                $item['uc_codigo'] ?? null,
                $item['espacio'] ?? null, 
                $item['dia'],
                $item['hora_inicio'], 
                $item['hora_fin'], 
                $sec_codigo,
                $ani_anio
            );
            if ($validacion['conflicto']) {
                $mensajes = array_column($validacion['mensajes'], 'mensaje');
                $conflictos_encontrados = array_merge($conflictos_encontrados, $mensajes);
            }
        }

        if (!empty($conflictos_encontrados)) {
            $mensaje_html = "Se encontraron los siguientes conflictos:<ul class='text-start mt-2'>";
            foreach (array_unique($conflictos_encontrados) as $msg) {
                $mensaje_html .= "<li>{$msg}</li>";
            }
            $mensaje_html .= "</ul>";
            return ['resultado' => 'confirmar_conflicto', 'mensaje' => $mensaje_html];
        }
    }

    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $co->beginTransaction();

        $stmt_update_seccion = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = :cantidad WHERE sec_codigo = :codigo AND ani_anio = :anio");
        $stmt_update_seccion->execute([':cantidad' => $cantidadInt, ':codigo' => $sec_codigo, ':anio' => $ani_anio]);
        
        $this->EliminarDependenciasDeSeccion($sec_codigo, $ani_anio, $co);

        if (!empty($items_horario)) {
            $hora_principal = $items_horario[0]['hora_inicio'] ?? '08:00:00';
            $turno_nombre = $this->getTurnoEnum($hora_principal);
            
            $stmt_horario = $co->prepare("INSERT INTO tbl_horario (sec_codigo, ani_anio, ani_tipo, tur_nombre, hor_estado) VALUES (:sec_codigo, :ani_anio, :ani_tipo, :tur_nombre, 1) ON DUPLICATE KEY UPDATE hor_estado = 1, tur_nombre = :tur_nombre");
            
            $stmt_tipo = $co->prepare("SELECT ani_tipo FROM tbl_seccion WHERE sec_codigo = :sec_codigo AND ani_anio = :ani_anio");
            $stmt_tipo->execute([':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio]);
            $ani_tipo = $stmt_tipo->fetchColumn() ?: 'regular';
            
            $stmt_horario->execute([':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio, ':ani_tipo' => $ani_tipo, ':tur_nombre' => $turno_nombre]);
            
            $stmt_uh = $co->prepare(
                "INSERT INTO uc_horario (sec_codigo, ani_anio, uc_codigo, doc_cedula, subgrupo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) 
                 VALUES (:sec_codigo, :ani_anio, :uc_codigo, :doc_cedula, :subgrupo, :esp_numero, :esp_tipo, :esp_edificio, :dia, :inicio, :fin)"
            );
            
            foreach ($items_horario as $item) {
                $espacio = $item['espacio'] ?? ['numero' => null, 'tipo' => null, 'edificio' => null];
                $doc_cedula = !empty($item['doc_cedula']) ? $item['doc_cedula'] : null;
                $uc_codigo = !empty($item['uc_codigo']) ? $item['uc_codigo'] : null;
                $subgrupo = !empty($item['subgrupo']) ? trim($item['subgrupo']) : null;
                if ($subgrupo === '') $subgrupo = null;

                if ($uc_codigo === null) {
                    continue;
                }

                $stmt_uh->execute([
                    ':sec_codigo'   => $sec_codigo,
                    ':ani_anio'     => $ani_anio,
                    ':uc_codigo'    => $uc_codigo,
                    ':doc_cedula'   => $doc_cedula,
                    ':subgrupo'     => $subgrupo,
                    ':esp_numero'   => $espacio['numero'],
                    ':esp_tipo'     => $espacio['tipo'],
                    ':esp_edificio' => $espacio['edificio'],
                    ':dia'          => $item['dia'],
                    ':inicio'       => $item['hora_inicio'],
                    ':fin'          => $item['hora_fin']
                ]);
            }
        }

        $co->commit();
        
        if ($modo_operacion === 'registrar') {
            return ['resultado' => 'modificar_ok', 'mensaje' => 'Registro Incluido!<br/>Se registró el horario correctamente!'];
        } else {
            return ['resultado' => 'modificar_ok', 'mensaje' => 'Registro Modificado!<br/>Se modificó el horario correctamente!'];
        }

    } catch (Exception $e) {
        if ($co->inTransaction()) {
            $co->rollBack();
        }
        error_log("Error en Modificar: " . $e->getMessage());
        return ['resultado' => 'error', 'mensaje' => 'Error del servidor al modificar el horario: ' . $e->getMessage()];
    }
}
   public function EliminarSeccionYHorario($sec_codigo, $ani_anio)
{
    if (empty($sec_codigo) || empty($ani_anio)) {
        return ['resultado' => 'error', 'mensaje' => 'Faltan datos (código o año) para eliminar la sección.'];
    }
    
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $co->beginTransaction();
        $this->EliminarDependenciasDeSeccion($sec_codigo, $ani_anio, $co);
        
        $stmt = $co->prepare("DELETE FROM tbl_seccion WHERE sec_codigo = :sec_codigo AND ani_anio = :ani_anio");
        $stmt->execute([':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio]);
        
        $co->commit();
        return ['resultado' => 'eliminar_ok', 'mensaje' => 'Registro Eliminado!<br/>Se eliminó la sección correctamente!'];
    } catch (Exception $e) {
        if ($co->inTransaction()) $co->rollBack();
        return ['resultado' => 'error', 'mensaje' => '¡ERROR!<br/>' . $e->getMessage()];
    }
}

 

public function EliminarDependenciasDeSeccion($sec_codigo, $ani_anio, $co_externo = null)
{
    $co = $co_externo ?? $this->Con();
    $es_transaccion_interna = ($co_externo === null);
    
    try {
        if ($es_transaccion_interna) $co->beginTransaction();
        
       
        $params_con_anio = [':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio];
        
       
        $params_sin_anio = [':sec_codigo' => $sec_codigo];

   
        
        $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = :sec_codigo AND ani_anio = :ani_anio")->execute($params_con_anio);
        
       
        $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = :sec_codigo")->execute($params_sin_anio);
        
        
        $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = :sec_codigo")->execute($params_sin_anio);

        if ($es_transaccion_interna) $co->commit();
    } catch (Exception $e) {
        if ($es_transaccion_interna && $co->inTransaction()) $co->rollBack();
        throw $e;
    }
}

   public function ConsultarDetalles($sec_codigo, $ani_anio){
    if (!$sec_codigo || !$ani_anio) {
        return ['resultado' => 'error', 'mensaje' => 'Falta el código o el año de la sección.'];
    }

    try {
        $sql = "SELECT 
                uh.uc_codigo, uh.doc_cedula, uh.subgrupo, uh.esp_numero, uh.esp_tipo, uh.esp_edificio,
                uh.hor_dia as dia, uh.hor_horainicio as hora_inicio, uh.hor_horafin as hora_fin
            FROM uc_horario uh
            WHERE uh.sec_codigo = :sec_codigo AND uh.ani_anio = :ani_anio";

        $stmt = $this->Con()->prepare($sql);
        $stmt->execute([':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio]);
        $schedule_grid_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($schedule_grid_items as &$item) {
            if (strlen($item['hora_inicio']) === 5) {
                $item['hora_inicio'] .= ':00';
            }
            if (strlen($item['hora_fin']) === 5) {
                $item['hora_fin'] .= ':00';
            }

            $item['espacio'] = [
                'numero' => $item['esp_numero'],
                'tipo' => $item['esp_tipo'],
                'edificio' => $item['esp_edificio']
            ];
            unset($item['esp_numero'], $item['esp_tipo'], $item['esp_edificio']);
        }

        return ['resultado' => 'ok', 'mensaje' => $schedule_grid_items];
    } catch (Exception $e) {
        error_log("Error en ConsultarDetalles: " . $e->getMessage());
        return ['resultado' => 'error', 'mensaje' => "¡ERROR!<br/>Error al consultar detalles: " . $e->getMessage()];
    }
}
    
    public function obtenerAnios()
    {
        try {
            return $this->Con()->query("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerAnios: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTurnos()
    {
        $bloques_horario = [];
        try {
            $stmt = $this->Con()->prepare("SELECT tur_horaInicio, tur_horaFin FROM tbl_turno WHERE tur_estado = 1 ORDER BY tur_horaInicio");
            $stmt->execute();
            $turnos_principales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($turnos_principales)) {
                $bloque_id_counter = 1;
                $intervalo = new DateInterval('PT40M');
                foreach ($turnos_principales as $turno) {
                    $hora_actual = new DateTime($turno['tur_horaInicio']);
                    $hora_fin_turno = new DateTime($turno['tur_horaFin']);
                    while ($hora_actual < $hora_fin_turno) {
                        $bloque_inicio = clone $hora_actual;
                        $hora_actual->add($intervalo);
                        $bloque_fin = (clone $hora_actual > $hora_fin_turno) ? $hora_fin_turno : clone $hora_actual;
                        $bloques_horario[] = ['tur_id' => $bloque_id_counter++, 'tur_horainicio' => $bloque_inicio->format('H:i:s'), 'tur_horafin' => $bloque_fin->format('H:i:s')];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error en obtenerTurnos (generador de bloques): " . $e->getMessage());
            $bloques_horario = [];
        }
        if (empty($bloques_horario)) {
            
            $bloques_horario = [
                ['tur_id' => 1, 'tur_horainicio' => '08:00:00', 'tur_horafin' => '08:40:00'],
                ['tur_id' => 2, 'tur_horainicio' => '08:40:00', 'tur_horafin' => '09:20:00'],
                ['tur_id' => 3, 'tur_horainicio' => '09:20:00', 'tur_horafin' => '10:00:00'],
                ['tur_id' => 4, 'tur_horainicio' => '10:00:00', 'tur_horafin' => '10:40:00'],
                ['tur_id' => 5, 'tur_horainicio' => '10:40:00', 'tur_horafin' => '11:20:00'],
                ['tur_id' => 6, 'tur_horainicio' => '11:20:00', 'tur_horafin' => '12:00:00'],
                ['tur_id' => 7, 'tur_horainicio' => '13:00:00', 'tur_horafin' => '13:40:00'],
                ['tur_id' => 8, 'tur_horainicio' => '13:40:00', 'tur_horafin' => '14:20:00'],
                ['tur_id' => 9, 'tur_horainicio' => '14:20:00', 'tur_horafin' => '15:00:00'],
                ['tur_id' => 10, 'tur_horainicio' => '15:00:00', 'tur_horafin' => '15:40:00'],
                ['tur_id' => 11, 'tur_horainicio' => '15:40:00', 'tur_horafin' => '16:20:00'],
                ['tur_id' => 12, 'tur_horainicio' => '16:20:00', 'tur_horafin' => '17:00:00'],
                ['tur_id' => 13, 'tur_horainicio' => '18:00:00', 'tur_horafin' => '18:40:00'],
                ['tur_id' => 14, 'tur_horainicio' => '18:40:00', 'tur_horafin' => '19:20:00'],
                ['tur_id' => 15, 'tur_horainicio' => '19:20:00', 'tur_horafin' => '20:00:00'],
                ['tur_id' => 16, 'tur_horainicio' => '20:00:00', 'tur_horafin' => '20:40:00'],
            ];
        }
        return $bloques_horario;
    }

    public function obtenerUcPorDocente($doc_cedula, $trayecto_seccion = null)
{
  

    $fase_actual = $this->determinarFaseActual();

    if ($fase_actual === 'ninguna') {
        return ['data' => [], 'mensaje' => 'Fuera de período de asignación de UCs.'];
    }

    try {
        
        $sql = "SELECT u.uc_codigo, u.uc_nombre, u.uc_trayecto, u.uc_periodo FROM tbl_uc u WHERE u.uc_estado = 1";
        $params = []; 

        if ($fase_actual === 'fase1') {
            $sql .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
        } elseif ($fase_actual === 'fase2') {
            $sql .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
        }

        if ($trayecto_seccion !== null && is_numeric($trayecto_seccion)) {
            $sql .= " AND u.uc_trayecto = :trayecto_seccion";
            $params[':trayecto_seccion'] = (int)$trayecto_seccion;
        }

        $sql .= " ORDER BY u.uc_nombre";
        $stmt = $this->Con()->prepare($sql);
        $stmt->execute($params);
        $ucs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ucs)) {
            $fase_texto = str_replace(['fase1', 'fase2'], ['Fase 1', 'Fase 2'], $fase_actual);
            $mensaje_extra = ($trayecto_seccion !== null) ? " para el trayecto {$trayecto_seccion}" : "";
          
            return ['data' => [], 'mensaje' => 'No hay UCs disponibles' . $mensaje_extra . " en {$fase_texto}."];
        }
        return ['data' => $ucs, 'mensaje' => 'ok'];
    } catch (Exception $e) {
        error_log("Error en obtenerUcPorDocente: " . $e->getMessage());
        return ['data' => [], 'mensaje' => 'Error al consultar las UCs.'];
    }
}

    public function obtenerUnidadesCurriculares()
    {
        try {
            return $this->Con()->query("SELECT uc_codigo, uc_nombre, uc_trayecto FROM tbl_uc WHERE uc_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEspacios()
    {
        try {
            return $this->Con()->query("SELECT esp_numero AS numero, esp_tipo AS tipo, esp_edificio AS edificio FROM tbl_espacio WHERE esp_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDocentes()
    {
        try {
            return $this->Con()->query("SELECT doc_cedula, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTurnoEnum($hora_inicio)
    {
        $hour = intval(substr($hora_inicio, 0, 2));
        if ($hour >= 18) return 'Noche';
        if ($hour >= 13) return 'Tarde';
        return 'Mañana';
    }

    public function contarDocentes()
    {
        try {
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_docente WHERE doc_estado = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar docentes: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarEspacios()
    {
        try {
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_espacio WHERE esp_estado = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar espacios: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarTurnos()
    {
        try {
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_turno WHERE tur_estado = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar turnos: " . $e->getMessage());
            return 0;
        }
    }

    public function contarAniosActivos()
    {
        try {
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar años activos: " . $e->getMessage());
            return 0;
        }
    }

    public function contarMallasActivas()
    {
        try {
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_malla WHERE mal_activa = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar mallas activas: " . $e->getMessage());
            return 0;
        }
 
 
   }
public function obtenerAnioActivo() {
    try {
        $stmt = $this->Con()->prepare("SELECT ani_anio FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error en obtenerAnioActivo: " . $e->getMessage());
        return null;
    }
}
public function existenSeccionesParaAnio($anio) {
    if (!$anio) {
        return false;
    }
    try {
        $stmt = $this->Con()->prepare("SELECT 1 FROM tbl_seccion WHERE ani_anio = ? LIMIT 1");
        $stmt->execute([$anio]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error en existenSeccionesParaAnio: " . $e->getMessage());
        return false;
    }
}
public function duplicarSeccionesAnioAnterior() {
    $co = $this->Con();
    $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $anio_actual = $this->obtenerAnioActivo();
        if (!$anio_actual) {
            return ['resultado' => 'error', 'mensaje' => 'No se encontró un año académico activo.'];
        }
        $anio_anterior = $anio_actual - 1;

        $co->beginTransaction();

        $stmt_secciones_viejas = $co->prepare("SELECT * FROM tbl_seccion WHERE ani_anio = ? AND sec_estado = 1");
        $stmt_secciones_viejas->execute([$anio_anterior]);
        $secciones_a_duplicar = $stmt_secciones_viejas->fetchAll(PDO::FETCH_ASSOC);

        if (empty($secciones_a_duplicar)) {
            $co->rollBack();
            return ['resultado' => 'info_ok', 'mensaje' => 'No se encontraron secciones en el año anterior para duplicar.'];
        }
        
        $codigos_secciones = array_column($secciones_a_duplicar, 'sec_codigo');
        $placeholders = implode(',', array_fill(0, count($codigos_secciones), '?'));

        
        $stmt_delete_sec = $co->prepare("DELETE FROM tbl_seccion WHERE ani_anio = ? AND sec_codigo IN ($placeholders)");
        $stmt_delete_sec->execute(array_merge([$anio_actual], $codigos_secciones));
        
        
        $stmt_delete_hor = $co->prepare("DELETE FROM uc_horario WHERE ani_anio = ? AND sec_codigo IN ($placeholders)");
        $stmt_delete_hor->execute(array_merge([$anio_actual], $codigos_secciones));

        
        $stmt_insert_sec = $co->prepare(
            "INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, sec_estado, ani_anio, ani_tipo) VALUES (?, 0, 1, ?, ?)"
        );
        foreach ($secciones_a_duplicar as $seccion) {
            $stmt_insert_sec->execute([$seccion['sec_codigo'], $anio_actual, $seccion['ani_tipo']]);
        }

       
        $stmt_horarios = $co->prepare(
            "SELECT DISTINCT uh.sec_codigo, uh.uc_codigo, uh.hor_dia, uh.hor_horainicio, uh.hor_horafin, uh.subgrupo
             FROM uc_horario uh
             JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio
             WHERE s.ani_anio = ? AND uh.sec_codigo IN ($placeholders)"
        );
        $stmt_horarios->execute(array_merge([$anio_anterior], $codigos_secciones));
        $horarios_a_duplicar = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);

        $stmt_insert_hor = $co->prepare(
            "INSERT INTO uc_horario (sec_codigo, ani_anio, uc_codigo, hor_dia, hor_horainicio, hor_horafin, subgrupo, doc_cedula, esp_numero, esp_tipo, esp_edificio) 
             VALUES (:sec_codigo, :ani_anio, :uc_codigo, :dia, :inicio, :fin, :subgrupo, NULL, NULL, NULL, NULL)"
        );

        foreach ($horarios_a_duplicar as $horario) {
            $stmt_insert_hor->execute([
                ':sec_codigo' => $horario['sec_codigo'],
                ':ani_anio'   => $anio_actual,
                ':uc_codigo'  => $horario['uc_codigo'],
                ':dia'        => $horario['hor_dia'],
                ':inicio'     => $horario['hor_horainicio'],
                ':fin'        => $horario['hor_horafin'],
                ':subgrupo'   => $horario['subgrupo']
            ]);
        }
        
        $co->commit();
        return ['resultado' => 'duplicar_anio_anterior_ok', 'mensaje' => '¡Éxito! Se han duplicado ' . count($secciones_a_duplicar) . ' secciones del año anterior. Ahora puede asignarles docentes y espacios.'];

    } catch (Exception $e) {
        if ($co->inTransaction()) {
            $co->rollBack();
        }
        error_log("Error en duplicarSeccionesAnioAnterior: " . $e->getMessage());
        return ['resultado' => 'error', 'mensaje' => 'Ocurrió un error en el servidor al intentar duplicar las secciones: ' . $e->getMessage()];
    }

}

public function obtenerDatosCompletosHorarioParaReporte($sec_codigo, $ani_anio)
{
    if (!$sec_codigo || !$ani_anio) {
        return ['resultado' => 'error', 'mensaje' => 'Faltan datos de la sección.'];
    }

    try {
        $co = $this->Con();

       
        $stmt_grupo = $co->prepare("SELECT grupo_union_id FROM tbl_seccion WHERE sec_codigo = :sec_codigo AND ani_anio = :ani_anio");
        $stmt_grupo->execute([':sec_codigo' => $sec_codigo, ':ani_anio' => $ani_anio]);
        $grupo_id = $stmt_grupo->fetchColumn();

   
        if ($grupo_id) {
            $stmt_secciones_grupo = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id = :grupo_id AND ani_anio = :ani_anio");
            $stmt_secciones_grupo->execute([':grupo_id' => $grupo_id, ':ani_anio' => $ani_anio]);
            $secciones_a_incluir = $stmt_secciones_grupo->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $secciones_a_incluir = [$sec_codigo];
        }
        
        $placeholders = implode(',', array_fill(0, count($secciones_a_incluir), '?'));

        $sql = "SELECT 
                    uh.sec_codigo,
                    s.grupo_union_id,
                    uh.subgrupo,
                    u.uc_trayecto,
                    uh.hor_dia,
                    uh.hor_horainicio,
                    uh.hor_horafin,
                    u.uc_nombre,
                    CONCAT(d.doc_nombre, ' ', d.doc_apellido) as docente_nombre,
                    CASE
                        WHEN uh.esp_tipo = 'Laboratorio' THEN CONCAT('LAB ', uh.esp_numero)
                        ELSE CONCAT(uh.esp_edificio, ' - ', uh.esp_tipo, ' ', uh.esp_numero)
                    END AS espacio_nombre
                FROM uc_horario uh
                JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo AND uh.ani_anio = s.ani_anio
                LEFT JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                LEFT JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                WHERE uh.ani_anio = ? AND uh.sec_codigo IN ($placeholders)
                ORDER BY uh.hor_horainicio, uh.hor_dia";

        $params = array_merge([$ani_anio], $secciones_a_incluir);
        $stmt_horario = $co->prepare($sql);
        $stmt_horario->execute($params);
        $horario_items = $stmt_horario->fetchAll(PDO::FETCH_ASSOC);

        
        $secciones_data = [];
        foreach($horario_items as $item){
            $secciones_data[$item['sec_codigo']] = true;
        }


        foreach ($horario_items as &$item) {
        
        if (strlen($item['hor_horainicio']) === 5) {
            $item['hor_horainicio'] .= ':00';
        }
        
        if (strlen($item['hor_horafin']) === 5) {
            $item['hor_horafin'] .= ':00';
        }
    }

        return [
            'resultado' => 'ok', 
            'secciones' => array_keys($secciones_data), 
            'horario'   => $horario_items,
            'anio'      => $ani_anio,
            'turnos'    => $this->obtenerTurnos()
        ];

    } catch (Exception $e) {
        error_log("Error en obtenerDatosCompletosHorarioParaReporte: " . $e->getMessage());
        return ['resultado' => 'error', 'mensaje' => "Error al consultar los datos del reporte."];
    }
}
}
?>