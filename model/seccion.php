<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

public function obtenerTodosLosHorarios() {
    try {
        // Consulta corregida para obtener el docente directamente de uc_horario
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

    private function _obtenerHorasAcademicasActuales($doc_cedula, $co, $seccion_a_excluir = null)
    {
        $sql = "
            SELECT SUM(um.mal_hora_academica)
            FROM uc_horario uh
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            JOIN uc_malla um ON uh.uc_codigo = um.uc_codigo AND s.sec_estado = 1
            JOIN tbl_malla m ON um.mal_codigo = m.mal_codigo AND m.mal_activa = 1
            WHERE uh.doc_cedula = :doc_cedula
        ";
        $params = [':doc_cedula' => $doc_cedula];

        if ($seccion_a_excluir !== null) {
            $sql .= " AND s.sec_codigo != :sec_codigo_excluir";
            $params[':sec_codigo_excluir'] = $seccion_a_excluir;
        }

        $stmt = $co->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        return $total ? (int)$total : 0;
    }

    public function CrearHorarioAleatorio($sec_codigo, $trayecto)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dias_todos = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $isWeekendSchedule = (substr($sec_codigo, 1, 1) === '4');
        $saturdayClassNeeded = $isWeekendSchedule;
        
        $bloques_todos = $this->obtenerTurnos();
        
        $turno_seccion_char = substr($sec_codigo, 1, 1);
        $turno_seccion = 'mañana'; 
        if ($turno_seccion_char === '2') {
            $turno_seccion = 'tarde';
        } elseif ($turno_seccion_char === '3') {
            $turno_seccion = 'noche';
        } elseif ($turno_seccion_char === '4') {
            $turno_seccion = 'mañana';
        }

        $bloques_40min = array_filter($bloques_todos, function($bloque) use ($turno_seccion) {
            $hora_inicio = (int)substr($bloque['tur_horainicio'], 0, 2);
            if ($turno_seccion === 'mañana') return $hora_inicio < 13;
            if ($turno_seccion === 'tarde') return $hora_inicio >= 13 && $hora_inicio < 18;
            if ($turno_seccion === 'noche') return $hora_inicio >= 18;
            return false;
        });
        
        $bloques_80min_slots = [];
        $bloques_array = array_values($bloques_40min);
        $num_bloques = count($bloques_array);
        for ($i = 0; $i < $num_bloques - 1; $i += 2) {
            $bloque1 = $bloques_array[$i];
            $bloque2 = $bloques_array[$i + 1];
    
            if (isset($bloque2) && $bloque1['tur_horafin'] === $bloque2['tur_horainicio']) {
                $bloques_80min_slots[] = [
                    'tur_horainicio' => $bloque1['tur_horainicio'],
                    'tur_horafin' => $bloque2['tur_horafin']
                ];
            }
        }

        $fase_actual = $this->determinarFaseActual();
        $active_malla = $co->query("SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1")->fetchColumn();

        $sql_ucs = "SELECT u.uc_codigo, um.mal_hora_academica FROM tbl_uc u JOIN uc_malla um ON u.uc_codigo = um.uc_codigo WHERE u.uc_trayecto = :trayecto AND um.mal_codigo = :mal_codigo AND u.uc_estado = 1";

        if ($trayecto == '0') {
            $sql_ucs .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
        }

        if ($fase_actual === 'fase1') $sql_ucs .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
        elseif ($fase_actual === 'fase2') $sql_ucs .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
        
        $stmt_ucs = $co->prepare($sql_ucs);
        $stmt_ucs->execute([':trayecto' => $trayecto, ':mal_codigo' => $active_malla]);
        $ucs_por_asignar_map = [];
        foreach ($stmt_ucs->fetchAll(PDO::FETCH_ASSOC) as $uc) {
            $ucs_por_asignar_map[$uc['uc_codigo']] = $uc;
        }

        $espacios = $this->obtenerEspacios();
        if (empty($ucs_por_asignar_map) || empty($espacios) || empty($bloques_80min_slots)) {
            return ['resultado' => 'ok', 'mensaje' => 'No hay suficientes datos (UCs, espacios, bloques de turno, etc.) para generar un horario.', 'horario' => []];
        }
        
        $ocupacion_global = [];
        foreach ($dias_todos as $dia) {
            $ocupacion_global[$dia] = [];
        }
        
        $sql_existentes = "
            SELECT uh.hor_dia, uh.hor_horainicio, uh.hor_horafin, uh.doc_cedula, uh.esp_numero, uh.esp_tipo, uh.esp_edificio 
            FROM uc_horario uh 
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            WHERE s.sec_estado = 1";
        
        $horarios_existentes = $co->query($sql_existentes)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($horarios_existentes as $h) {
            $dia_key = trim(strtolower($h['hor_dia']));
            if (!isset($ocupacion_global[$dia_key])) continue;

            $ocupacion_global[$dia_key][] = [
                'tipo' => 'docente',
                'id' => $h['doc_cedula'],
                'inicio' => $h['hor_horainicio'],
                'fin' => $h['hor_horafin']
            ];
            $ocupacion_global[$dia_key][] = [
                'tipo' => 'espacio',
                'id' => $h['esp_numero'] . '|' . $h['esp_tipo'] . '|' . $h['esp_edificio'],
                'inicio' => $h['hor_horainicio'],
                'fin' => $h['hor_horafin']
            ];
        }

        $docentes_info = [];
        $stmt_doc_info = $co->query("SELECT doc_cedula FROM tbl_docente WHERE doc_estado = 1");
        while ($doc = $stmt_doc_info->fetch(PDO::FETCH_ASSOC)) {
            $cedula = $doc['doc_cedula'];
            $max_horas_stmt = $co->prepare("SELECT act_academicas FROM tbl_actividad WHERE doc_cedula = ?");
            $max_horas_stmt->execute([$cedula]);
            $max_horas = $max_horas_stmt->fetchColumn();
            $preferencias_stmt = $co->prepare("SELECT dia_semana, hora_inicio, hora_fin FROM tbl_docente_preferencia WHERE doc_cedula = ?");
            $preferencias_stmt->execute([$cedula]);
            $docentes_info[$cedula] = [
                'max_horas' => ($max_horas === false || is_null($max_horas)) ? 0 : (int)$max_horas,
                'horas_asignadas' => $this->_obtenerHorasAcademicasActuales($cedula, $co, $sec_codigo),
                'preferencias' => $preferencias_stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }

        $stmt_docentes_por_uc = $co->prepare("SELECT doc_cedula FROM uc_docente WHERE uc_codigo = ?");
        $horario_generado = [];
        
        $uc_codes = array_keys($ucs_por_asignar_map);
        shuffle($uc_codes);

        foreach ($uc_codes as $uc_code) {
            if (!isset($ucs_por_asignar_map[$uc_code])) continue;
            $uc = $ucs_por_asignar_map[$uc_code];
            $stmt_docentes_por_uc->execute([$uc['uc_codigo']]);
            $posibles_docentes = $stmt_docentes_por_uc->fetchAll(PDO::FETCH_COLUMN, 0);
            if (empty($posibles_docentes)) continue;

            $costo_uc = (int)$uc['mal_hora_academica'];
            $opciones_validas = [];
            
            foreach ($posibles_docentes as $docente_id) {
                if (!isset($docentes_info[$docente_id])) continue;
                if (($docentes_info[$docente_id]['horas_asignadas'] + $costo_uc) > $docentes_info[$docente_id]['max_horas']) continue;

                foreach ($dias_todos as $dia) {
                    foreach ($bloques_80min_slots as $bloque) { 
                        $opciones_validas[] = ['dia' => $dia, 'bloque' => $bloque, 'docente' => $docente_id];
                    }
                }
            }
            
            if ($saturdayClassNeeded) {
                usort($opciones_validas, function($a, $b) {
                    if ($a['dia'] === 'sábado' && $b['dia'] !== 'sábado') return -1;
                    if ($a['dia'] !== 'sábado' && $b['dia'] === 'sábado') return 1;
                    return 0;
                });
            } else {
                shuffle($opciones_validas);
            }
            
            foreach ($opciones_validas as $opcion) {
                $docente_id = $opcion['docente'];
                $dia = $opcion['dia'];
                $bloque = $opcion['bloque'];
                $espacio = $espacios[array_rand($espacios)];
                $hora_inicio = $bloque['tur_horainicio'];
                $hora_fin = $bloque['tur_horafin'];
                $espacio_key = $espacio['numero'] . '|' . $espacio['tipo'] . '|' . $espacio['edificio'];

                $conflicto = false;
                if (isset($ocupacion_global[$dia])) {
                    foreach ($ocupacion_global[$dia] as $slot) {
                        if ($slot['tipo'] === 'docente' && $slot['id'] === $docente_id && $hora_inicio < $slot['fin'] && $hora_fin > $slot['inicio']) {
                            $conflicto = true; break;
                        }
                        if ($slot['tipo'] === 'espacio' && $slot['id'] === $espacio_key && $hora_inicio < $slot['fin'] && $hora_fin > $slot['inicio']) {
                            $conflicto = true; break;
                        }
                    }
                }
                if ($conflicto) continue;

                $horario_generado[] = [
                    'uc_codigo'   => $uc['uc_codigo'], 
                    'doc_cedula'  => $docente_id, 
                    'espacio'     => $espacio, 
                    'dia'         => $dia, 
                    'hora_inicio' => substr($hora_inicio, 0, 5), 
                    'hora_fin'    => substr($hora_fin, 0, 5)
                ];
                
                $ocupacion_global[$dia][] = ['tipo' => 'docente', 'id' => $docente_id, 'inicio' => $hora_inicio, 'fin' => $hora_fin];
                $ocupacion_global[$dia][] = ['tipo' => 'espacio', 'id' => $espacio_key, 'inicio' => $hora_inicio, 'fin' => $hora_fin];
                $docentes_info[$docente_id]['horas_asignadas'] += $costo_uc;
                
                if ($saturdayClassNeeded && $dia === 'sábado') {
                    $saturdayClassNeeded = false;
                }
                
                unset($ucs_por_asignar_map[$uc_code]);
                goto next_uc;
            }
            next_uc:
        }
        
        return ['resultado' => 'ok', 'mensaje' => 'Plantilla de horario generada.', 'horario' => $horario_generado];
    }
    
    public function __construct()
    {
        parent::__construct();
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
            return $this->Con()->query("SELECT DISTINCT mal_cohorte FROM tbl_malla WHERE mal_estado = 1 AND mal_activa = 1")->fetchAll(PDO::FETCH_COLUMN, 0);
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
                    $horario_actual_result = $this->ConsultarDetalles($seccion['sec_codigo']);
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

        $active_malla = $co->query("SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1")->fetchColumn();
        
        foreach ($todas_uc_f2 as $uc_f2_codigo) {
            $stmt_docentes_uc = $co->prepare("SELECT doc_cedula FROM uc_docente WHERE uc_codigo = ?");
            $stmt_docentes_uc->execute([$uc_f2_codigo]);
            $posibles_docentes = $stmt_docentes_uc->fetchAll(PDO::FETCH_COLUMN);
            shuffle($posibles_docentes);
            
            $stmt_costo = $co->prepare("SELECT mal_hora_academica FROM uc_malla WHERE uc_codigo = :uc_codigo AND mal_codigo = :mal_codigo");
            $stmt_costo->execute([':uc_codigo' => $uc_f2_codigo, ':mal_codigo' => $active_malla]);
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
            $clases_origen_result = $this->ConsultarDetalles($sec_codigo_origen);
            $clases_origen = $clases_origen_result['mensaje'] ?? [];
            $codigos_destinos = array_filter($sec_codigos_a_unir, function ($codigo) use ($sec_codigo_origen) {
                return $codigo != $sec_codigo_origen;
            });
            foreach ($codigos_destinos as $codigo_destino) {
                $this->EliminarDependenciasDeSeccion($codigo_destino, $co);
                
                if (!empty($clases_origen)) {
                    $hora_principal_para_turno = $clases_origen[0]['hora_inicio'] ?? '08:00:00';
                    $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
                    $stmt_hor->execute([
                        ':sec_codigo' => $codigo_destino,
                        ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
                    ]);

                   $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, sec_codigo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :doc_cedula, :sec_codigo, :esp_numero, :esp_tipo, :esp_edificio, :dia, :inicio, :fin)");
                    $stmt_doc = $co->prepare("INSERT INTO docente_horario (doc_cedula, sec_codigo) VALUES (:doc_cedula, :sec_codigo) ON DUPLICATE KEY UPDATE sec_codigo=sec_codigo");
                    $docentes_procesados = [];
                    foreach ($clases_origen as $item) {
                        $espacio = $item['espacio'] ?? ['numero' => null, 'tipo' => null, 'edificio' => null];
                        $stmt_uh->execute([
                            ':uc_codigo' => $item['uc_codigo'],
                            ':doc_cedula' => $item['doc_cedula'], 
                            ':sec_codigo' => $codigo_destino,
                            ':esp_numero' => $espacio['numero'],
                            ':esp_tipo' => $espacio['tipo'],
                            ':esp_edificio' => $espacio['edificio'],
                            ':dia' => $item['dia'],
                            ':inicio' => $item['hora_inicio'],
                            ':fin' => $item['hora_fin']
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
    
    public function RegistrarSeccion($codigoSeccion, $cantidadSeccion, $anio_anio, $anio_tipo)
{
    if (empty($codigoSeccion) || !isset($cantidadSeccion) || $cantidadSeccion === '' || empty($anio_anio) || empty($anio_tipo)) {
        return ['resultado' => 'error', 'mensaje' => 'Todos los campos de la sección son obligatorios.'];
    }

   
    $cohorteSeccion = substr($codigoSeccion, -1);
    if (!is_numeric($cohorteSeccion)) {
        return ['resultado' => 'error', 'mensaje' => 'El código de la sección debe terminar en un número de cohorte válido.'];
    }

    $cohortesValidas = $this->obtenerCohortesMalla();
    if (!in_array($cohorteSeccion, $cohortesValidas)) {
        $mensaje = '¡Cohorte no válido! El cohorte ' . htmlspecialchars($cohorteSeccion) . ' no existe en la malla curricular activa. Cohortes permitidos: ' . implode(', ', $cohortesValidas);
        return ['resultado' => 'error', 'mensaje' => $mensaje];
    }
 

    $cantidadInt = filter_var($cantidadSeccion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
    if ($cantidadInt === false) {
        return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número entero entre 0 y 99.'];
    }

    $co = $this->Con();
    try {
        $co->beginTransaction();
        
        $stmt_check = $co->prepare("SELECT sec_estado FROM tbl_seccion WHERE sec_codigo = :codigo");
        $stmt_check->execute([':codigo' => $codigoSeccion]);
        $seccion_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($seccion_existente) {
            if ($seccion_existente['sec_estado'] == 1) {
                $co->rollBack();
                return ['resultado' => 'error', 'mensaje' => '¡ERROR! La sección con ese código ya existe y está activa.'];
            } else {
                $this->EliminarDependenciasDeSeccion($codigoSeccion, $co);
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
            'mensaje' => '¡Se registró la sección correctamente!',
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

    public function ExisteSeccion($codigoSeccion, $anio_anio, $anio_tipo)
    {
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = :codigo AND ani_anio = :anio AND ani_tipo = :tipo AND sec_estado = 1");
            $stmt->execute([':codigo' => $codigoSeccion, ':anio' => $anio_anio, ':tipo' => $anio_tipo]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en ExisteSeccion: " . $e->getMessage());
            return true;
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

    // --- El bloque de verificación de carga académica ha sido ELIMINADO de esta función ---

    // --- Verificación de cruce de horario del docente ---
    if (!empty($doc_cedula)) {
        $sql_doc = "SELECT uh.sec_codigo, d.doc_nombre, d.doc_apellido
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                    WHERE s.sec_estado = 1 AND uh.doc_cedula = :doc_cedula AND uh.sec_codigo != :sec_codigo
                    AND uh.hor_dia = :dia AND uh.hor_horainicio < :hora_fin AND uh.hor_horafin > :hora_inicio";
        
        $stmt_doc = $co->prepare($sql_doc);
        $stmt_doc->execute([':doc_cedula' => $doc_cedula, ':sec_codigo' => $sec_codigo, ':dia' => $dia, ':hora_inicio' => $hora_inicio, ':hora_fin' => $hora_fin]);
        
        $conflictos_docente = $stmt_doc->fetchAll(PDO::FETCH_ASSOC);

        foreach($conflictos_docente as $conflicto_docente) {
            $prefijo = substr($conflicto_docente['sec_codigo'], 0, 1) === '3' || substr($conflicto_docente['sec_codigo'], 0, 1) === '4' ? 'IIN' : 'IN';
            $mensaje = "<b>Conflicto de Horario:</b> El docente <b>{$conflicto_docente['doc_nombre']} {$conflicto_docente['doc_apellido']}</b> ya tiene una clase en la sección <b>{$prefijo}{$conflicto_docente['sec_codigo']}</b> en este horario.";
            $conflictos[] = ['tipo' => 'docente', 'mensaje' => $mensaje];
        }
    }

    // --- Verificación de ocupación de espacio ---
    if (!empty($espacio) && !empty($espacio['numero'])) {
        $sql_esp = "SELECT uh.sec_codigo, d.doc_nombre, d.doc_apellido
                    FROM uc_horario uh
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    LEFT JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                    WHERE s.sec_estado = 1 AND uh.esp_numero = :esp_numero AND uh.esp_tipo = :esp_tipo AND uh.esp_edificio = :esp_edificio
                    AND uh.sec_codigo != :sec_codigo AND uh.hor_dia = :dia AND uh.hor_horainicio < :hora_fin AND uh.hor_horafin > :hora_inicio";

        $stmt_esp = $co->prepare($sql_esp);
        $stmt_esp->execute([':esp_numero' => $espacio['numero'], ':esp_tipo' => $espacio['tipo'], ':esp_edificio' => $espacio['edificio'], ':sec_codigo' => $sec_codigo, ':dia' => $dia, ':hora_inicio' => $hora_inicio, ':hora_fin' => $hora_fin]);
        $conflicto_espacio = $stmt_esp->fetch(PDO::FETCH_ASSOC);

        if ($conflicto_espacio) {
            $prefijo = substr($conflicto_espacio['sec_codigo'], 0, 1) === '3' || substr($conflicto_espacio['sec_codigo'], 0, 1) === '4' ? 'IIN' : 'IN';
            $docente_ocupante = ($conflicto_espacio['doc_nombre']) ? "(Docente: {$conflicto_espacio['doc_nombre']} {$conflicto_espacio['doc_apellido']})" : "";
            $mensaje = "<b>Conflicto de Espacio:</b> El espacio <b>{$espacio['numero']} ({$espacio['tipo']})</b> ya está ocupado por la sección <b>{$prefijo}{$conflicto_espacio['sec_codigo']}</b> {$docente_ocupante} en este horario.";
            $conflictos[] = ['tipo' => 'espacio', 'mensaje' => $mensaje];
        }
    }

    if (!empty($conflictos)) {
        return ['conflicto' => true, 'mensajes' => $conflictos];
    }

    return ['conflicto' => false];
}


   public function Modificar($sec_codigo, $items_horario_json, $cantidadSeccion, $forzar = false)
{
    if (empty($sec_codigo) || !isset($cantidadSeccion)) {
        return ['resultado' => 'error', 'mensaje' => 'Faltan datos para modificar la sección.'];
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
        $active_malla = $co->query("SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1")->fetchColumn();
        if ($active_malla) {
            foreach ($items_horario as $item) {
                if (!empty($item['doc_cedula']) && !empty($item['uc_codigo'])) {
                    if (!isset($horas_por_docente[$item['doc_cedula']])) {
                        $horas_por_docente[$item['doc_cedula']] = 0;
                    }
                    $stmt_horas_uc = $co->prepare("SELECT mal_hora_academica FROM uc_malla WHERE uc_codigo = :uc_codigo AND mal_codigo = :mal_codigo");
                    $stmt_horas_uc->execute([':uc_codigo' => $item['uc_codigo'], ':mal_codigo' => $active_malla]);
                    $horas_uc = $stmt_horas_uc->fetchColumn();
                    if ($horas_uc) {
                        $horas_por_docente[$item['doc_cedula']] += (int)$horas_uc;
                    }
                }
            }
        }

        foreach ($horas_por_docente as $doc_cedula => $horas_asignadas) {
            $stmt_max_horas = $co->prepare("SELECT act_academicas FROM tbl_actividad WHERE doc_cedula = ?");
            $stmt_max_horas->execute([$doc_cedula]);
            $max_horas = $stmt_max_horas->fetchColumn();
            $horas_actuales = $this->_obtenerHorasAcademicasActuales($doc_cedula, $co, $sec_codigo);
            
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
                $sec_codigo
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

        $stmt_update_seccion = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = :cantidad WHERE sec_codigo = :codigo");
        $stmt_update_seccion->execute([':cantidad' => $cantidadInt, ':codigo' => $sec_codigo]);
        
        $this->EliminarDependenciasDeSeccion($sec_codigo, $co);

        if (!empty($items_horario)) {
            
            $hora_principal_para_turno = $items_horario[0]['hora_inicio'] ?? '08:00:00';
            $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
            $stmt_hor->execute([
                ':sec_codigo' => $sec_codigo,
                ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
            ]);

            $stmt_uh = $co->prepare(
                "INSERT INTO uc_horario (uc_codigo, doc_cedula, subgrupo, sec_codigo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) 
                 VALUES (:uc_codigo, :doc_cedula, :subgrupo, :sec_codigo, :esp_numero, :esp_tipo, :esp_edificio, :dia, :inicio, :fin)"
            );
            
            foreach ($items_horario as $item) {
                $espacio = $item['espacio'] ?? ['numero' => null, 'tipo' => null, 'edificio' => null];
                
                $doc_cedula = !empty($item['doc_cedula']) ? $item['doc_cedula'] : null;
                $uc_codigo = !empty($item['uc_codigo']) ? $item['uc_codigo'] : null;
                $subgrupo = !empty($item['subgrupo']) ? trim($item['subgrupo']) : null;
                if ($subgrupo === '') $subgrupo = null;

                $stmt_uh->execute([
                    ':uc_codigo' => $uc_codigo,
                    ':doc_cedula' => $doc_cedula,
                    ':subgrupo' => $subgrupo,
                    ':sec_codigo' => $sec_codigo,
                    ':esp_numero' => $espacio['numero'],
                    ':esp_tipo' => $espacio['tipo'],
                    // CORRECCIÓN: El nombre del parámetro ahora es el correcto
                    ':esp_edificio' => $espacio['edificio'],
                    ':dia' => $item['dia'],
                    ':inicio' => $item['hora_inicio'],
                    ':fin' => $item['hora_fin']
                ]);
            }
        }

        $co->commit();
        return ['resultado' => 'modificar_ok', 'mensaje' => '¡Horario actualizado correctamente!'];

    } catch (Exception $e) {
        if ($co->inTransaction()) {
            $co->rollBack();
        }
        error_log("Error en Modificar: " . $e->getMessage());
        return ['resultado' => 'error', 'mensaje' => 'Error del servidor al modificar el horario: ' . $e->getMessage()];
    }
}
    
    public function EliminarSeccionYHorario($sec_codigo)
    {
        if (empty($sec_codigo)) return ['resultado' => 'error', 'mensaje' => 'Código de sección no proporcionado.'];
        
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            $co->beginTransaction();
            $this->EliminarDependenciasDeSeccion($sec_codigo, $co);
            $stmt = $co->prepare("DELETE FROM tbl_seccion WHERE sec_codigo = :sec_codigo");
            $stmt->bindParam(':sec_codigo', $sec_codigo, PDO::PARAM_STR);
            $stmt->execute();
            
            $co->commit();
            return ['resultado' => 'eliminar_seccion_y_horario_ok', 'mensaje' => '¡Sección y todos sus datos asociados han sido eliminados permanentemente!'];
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => '¡ERROR!<br/>' . $e->getMessage()];
        }
    }

    public function EliminarDependenciasDeSeccion($sec_codigo, $co_externo = null)
    {
        $co = $co_externo ?? $this->Con();
        $es_transaccion_interna = ($co_externo === null);
        
        try {
            if ($es_transaccion_interna) $co->beginTransaction();
            
            $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM tbl_aprobados WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM per_aprobados WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM tbl_prosecusion WHERE sec_origen = :sec_codigo OR sec_promocion = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);

            if ($es_transaccion_interna) $co->commit();
        } catch (Exception $e) {
            if ($es_transaccion_interna && $co->inTransaction()) $co->rollBack();
            throw $e;
        }
    }

    public function ConsultarDetalles($sec_codigo)
    {
        if (!$sec_codigo) return ['resultado' => 'error', 'mensaje' => 'Falta el código de la sección.'];

        try {
            $sql = "SELECT 
                    uh.uc_codigo, 
                    uh.doc_cedula,
                    uh.subgrupo,
                    uh.esp_numero,
                    uh.esp_tipo,
                    uh.esp_edificio,
                    uh.hor_dia as dia, 
                    uh.hor_horainicio as hora_inicio, 
                    uh.hor_horafin as hora_fin
                FROM uc_horario uh
                WHERE uh.sec_codigo = :sec_codigo";

            $stmt = $this->Con()->prepare($sql);
            $stmt->execute([':sec_codigo' => $sec_codigo]);
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
        if (empty($doc_cedula)) {
            return ['data' => [], 'mensaje' => 'Cédula de docente no proporcionada.'];
        }
        $fase_actual = $this->determinarFaseActual();

        if ($fase_actual === 'ninguna') {
            return ['data' => [], 'mensaje' => 'Fuera de período de asignación de UCs.'];
        }

        try {
            $sql = "SELECT u.uc_codigo, u.uc_nombre, u.uc_trayecto, u.uc_periodo FROM tbl_uc u INNER JOIN uc_docente ud ON u.uc_codigo = ud.uc_codigo WHERE ud.doc_cedula = :doc_cedula AND u.uc_estado = 1";
            $params = [':doc_cedula' => $doc_cedula];

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
                return ['data' => [], 'mensaje' => 'Docente sin UCs disponibles' . $mensaje_extra . " en {$fase_texto}."];
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
    
    private function getTurnoEnum($hora_inicio)
    {
        $hour = intval(substr($hora_inicio, 0, 2));
        if ($hour >= 18) return 'noche';
        if ($hour >= 13) return 'tarde';
        return 'mañana';
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
            return (int) $this->Con()->query("SELECT COUNT(*) FROM tbl_malla WHERE mal_estado = 1 AND mal_activa = 1")->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al contar mallas activas: " . $e->getMessage());
            return 0;
        }
    }
}
?>