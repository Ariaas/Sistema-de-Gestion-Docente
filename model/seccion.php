<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

  
    private function _obtenerHorasAcademicasActuales($doc_cedula, $co, $seccion_a_excluir = null) {
        
        $sql = "
            SELECT SUM(um.mal_hora_academica)
            FROM uc_horario uh
            JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            JOIN uc_malla um ON uh.uc_codigo = um.uc_codigo
            JOIN tbl_malla m ON um.mal_codigo = m.mal_codigo
            WHERE ud.doc_cedula = :doc_cedula
              AND s.sec_estado = 1
              AND m.mal_activa = 1
             
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


        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        $bloques = $this->obtenerTurnos();
        $fase_actual = $this->determinarFaseActual();
        $active_malla = $co->query("SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1")->fetchColumn();
        
        $sql_ucs = "SELECT u.uc_codigo, um.mal_hora_academica FROM tbl_uc u JOIN uc_malla um ON u.uc_codigo = um.uc_codigo WHERE u.uc_trayecto = :trayecto AND um.mal_codigo = :mal_codigo AND u.uc_estado = 1";
        if ($fase_actual === 'fase1') $sql_ucs .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
        elseif ($fase_actual === 'fase2') $sql_ucs .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
        $stmt_ucs = $co->prepare($sql_ucs);
        $stmt_ucs->execute([':trayecto' => $trayecto, ':mal_codigo' => $active_malla]);
        $ucs_por_asignar = $stmt_ucs->fetchAll(PDO::FETCH_ASSOC);

        $espacios = $this->obtenerEspacios();
        if (empty($ucs_por_asignar) || empty($espacios) || empty($bloques)) {
            return ['resultado' => 'ok', 'mensaje' => 'No hay suficientes datos (UCs, espacios, etc.) para generar un horario.', 'horario' => []];
        }


        $ocupacion_global = [];
        $horarios_existentes = $co->query("SELECT uh.hor_dia, uh.hor_horainicio, ud.doc_cedula, uh.esp_numero, uh.esp_tipo, uh.esp_edificio FROM uc_horario uh JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo WHERE s.sec_estado = 1 ")->fetchAll(PDO::FETCH_ASSOC);
        foreach($horarios_existentes as $h) {
            $key = trim($h['hor_dia']) . '_' . trim($h['hor_horainicio']);
            if(!isset($ocupacion_global[$key])) $ocupacion_global[$key] = ['docentes' => [], 'espacios' => []];
            $ocupacion_global[$key]['docentes'][$h['doc_cedula']] = true;
            $espacio_key = "{$h['esp_numero']}-{$h['esp_tipo']}-{$h['esp_edificio']}";
            $ocupacion_global[$key]['espacios'][$espacio_key] = true;
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
                'horas_asignadas' => $this->_obtenerHorasAcademicasActuales($cedula, $co),
                'preferencias' => $preferencias_stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        }
        
        $stmt_docentes_por_uc = $co->prepare("SELECT doc_cedula FROM uc_docente WHERE uc_codigo = ? ");
        $horario_generado = [];
        shuffle($ucs_por_asignar);

   
        foreach ($ucs_por_asignar as $uc) {
            $stmt_docentes_por_uc->execute([$uc['uc_codigo']]);
            $posibles_docentes = $stmt_docentes_por_uc->fetchAll(PDO::FETCH_COLUMN, 0);
            if (empty($posibles_docentes)) continue;

            $costo_uc = (int)$uc['mal_hora_academica'];
            
            $opciones_validas = [];
            foreach ($posibles_docentes as $docente_id) {
                if (!isset($docentes_info[$docente_id])) continue;
                $info_docente = $docentes_info[$docente_id];

                if (($info_docente['horas_asignadas'] + $costo_uc) > $info_docente['max_horas']) continue;

                $tiene_preferencias = !empty($info_docente['preferencias']);

                if ($tiene_preferencias) {
                    foreach ($info_docente['preferencias'] as $pref) {
                        $dia_pref = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $pref['dia_semana']));
                        foreach ($bloques as $bloque) {
                            if ($bloque['tur_horainicio'] >= $pref['hora_inicio'] && $bloque['tur_horafin'] <= $pref['hora_fin']) {
                                $opciones_validas[] = ['dia' => $dia_pref, 'bloque' => $bloque, 'docente' => $docente_id];
                            }
                        }
                    }
                } else {
                    foreach ($dias as $dia) {
                        foreach ($bloques as $bloque) {
                            $opciones_validas[] = ['dia' => $dia, 'bloque' => $bloque, 'docente' => $docente_id];
                        }
                    }
                }
            }
            
            shuffle($opciones_validas);

            foreach ($opciones_validas as $opcion) {
                $docente_id = $opcion['docente'];
                $dia = $opcion['dia'];
                $bloque = $opcion['bloque'];
                $espacio = $espacios[array_rand($espacios)];
                $hora_corta = substr($bloque['tur_horainicio'], 0, 5);
                $key_slot = $dia . '_' . $hora_corta;
                $espacio_key = "{$espacio['esp_numero']}-{$espacio['esp_tipo']}-{$espacio['esp_edificio']}";
                if (($docentes_info[$docente_id]['horas_asignadas'] + $costo_uc) > $docentes_info[$docente_id]['max_horas']) continue;
                if (isset($ocupacion_global[$key_slot]['docentes'][$docente_id])) continue;
                if (isset($ocupacion_global[$key_slot]['espacios'][$espacio_key])) continue;

                // Guardar los 3 campos del espacio en el horario generado
$horario_generado[] = [ 
    'uc_codigo' => $uc['uc_codigo'], 
    'doc_cedula' => $docente_id, 
    'esp_numero' => $espacio['esp_numero'], // Guardar individualmente
    'esp_tipo' => $espacio['esp_tipo'],
    'esp_edificio' => $espacio['esp_edificio'],
    'dia' => $dia, 
    'hora_inicio' => $hora_corta, 
    'hora_fin' => substr($bloque['tur_horafin'], 0, 5)
];
                if(!isset($ocupacion_global[$key_slot])) $ocupacion_global[$key_slot] = ['docentes' => [], 'espacios' => []];
                $ocupacion_global[$key_slot]['docentes'][$docente_id] = true;
                $ocupacion_global[$key_slot]['espacios'][$espacio_key] = true;
                $docentes_info[$docente_id]['horas_asignadas'] += $costo_uc;
                
                goto siguiente_uc;
            }
            siguiente_uc:;
        }

        return ['resultado' => 'ok', 'mensaje' => 'Plantilla de horario generada.', 'horario' => $horario_generado];
    }
    
  
    private function getHoraFin($hora_inicio, $bloques)
    {
        foreach ($bloques as $b) {
            if ($b['tur_horainicio'] == $hora_inicio) return $b['tur_horafin'];
        }
        return date('H:i:s', strtotime($hora_inicio) + 40 * 60); 
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
    public function EjecutarPromocionAutomatica()
    {

        if ($this->determinarFaseActual() !== 'fase2' || isset($_SESSION['promocion_f2_ejecutada_session'])) {
            return null;
        }
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt_anio = $co->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt_anio->execute();
            $anio_activo = $stmt_anio->fetch(PDO::FETCH_ASSOC);
            if (!$anio_activo) {
                $_SESSION['promocion_f2_ejecutada_session'] = true;
                return null;
            }
            $ani_anio = $anio_activo['ani_anio'];
            $ani_tipo = $anio_activo['ani_tipo'];
            $stmt_secciones_f1 = $co->prepare("SELECT sec_codigo, sec_cantidad FROM tbl_seccion WHERE ani_anio = :ani_anio AND ani_tipo = :ani_tipo AND sec_estado = 1 AND sec_codigo LIKE '_1%'");
            $stmt_secciones_f1->execute([':ani_anio' => $ani_anio, ':ani_tipo' => $ani_tipo]);
            $secciones_origen = $stmt_secciones_f1->fetchAll(PDO::FETCH_ASSOC);
            if (empty($secciones_origen)) {
                $_SESSION['promocion_f2_ejecutada_session'] = true;
                return null;
            }
            $reporte = ['exitos' => 0, 'fallos' => [], 'observaciones' => []];
            foreach ($secciones_origen as $origen) {
                $codigo_origen = $origen['sec_codigo'];
                $codigo_destino = substr_replace($codigo_origen, '2', 1, 1);
                $stmt_seccion_f2 = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = :ani_anio AND ani_tipo = :ani_tipo AND sec_estado = 1 AND sec_codigo = :codigo_destino");
                $stmt_seccion_f2->execute([':ani_anio' => $ani_anio, ':ani_tipo' => $ani_tipo, ':codigo_destino' => $codigo_destino]);
                $seccion_destino = $stmt_seccion_f2->fetch(PDO::FETCH_ASSOC);
                if (!$seccion_destino) {
                    $reporte['fallos'][] = "No se encontró la sección de destino '{$codigo_destino}' para la sección de origen '{$codigo_origen}'.";
                    continue;
                }
                $co->beginTransaction();
                try {
                    $sec_codigo_destino = $seccion_destino['sec_codigo'];
                    $clases_origen_result = $this->ConsultarDetalles($origen['sec_codigo']);
                    $clases_origen = $clases_origen_result['mensaje'] ?? [];
                    $this->EliminarPorSeccion($sec_codigo_destino, $co);
                    $trayecto_destino = substr($codigo_destino, 0, 1);
                    $docentes = $this->obtenerDocentes();
                    $nuevas_clases = [];
                    foreach ($clases_origen as $clase) {
                        $resultado_uc_docente = $this->obtenerUcPorDocente($clase['doc_cedula'], $trayecto_destino);
                        $ucs_posibles = $resultado_uc_docente['data'];
                        if (count($ucs_posibles) === 1) {
                            $nuevas_clases[] = ['uc_codigo' => $ucs_posibles[0]['uc_codigo'], 'doc_cedula' => $clase['doc_cedula'], 'esp_codigo' => $clase['esp_codigo'], 'dia' => $clase['dia'], 'hora_inicio' => $clase['hora_inicio'], 'hora_fin' => $clase['hora_fin']];
                        } else {
                            $docente_info = array_values(array_filter($docentes, function ($d) use ($clase) {
                                return $d['doc_cedula'] == $clase['doc_cedula'];
                            }))[0] ?? null;
                            $nombre_docente = $docente_info ? $docente_info['doc_nombre'] . ' ' . $docente_info['doc_apellido'] : 'Cédula ' . $clase['doc_cedula'];
                            if (empty($ucs_posibles)) {
                                $reporte['observaciones'][] = "Docente <strong>{$nombre_docente}</strong> no tiene UC de Fase 2 para el trayecto {$trayecto_destino}.";
                            } else {
                                $reporte['observaciones'][] = "Docente <strong>{$nombre_docente}</strong> tiene múltiples UCs de Fase 2 válidas para el trayecto {$trayecto_destino}, no se asignó automáticamente para la clase original de {$clase['dia']} a {$clase['hora_inicio']}.";
                            }
                        }
                    }
                    $error_conflicto = $this->validarConflictos($nuevas_clases, $sec_codigo_destino, $co);
                    if ($error_conflicto) throw new Exception($error_conflicto);
                    if (!empty($nuevas_clases)) {
                        $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, sec_codigo, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :sec_codigo, :dia, :inicio, :fin)");
                        foreach ($nuevas_clases as $item) {
                            $stmt_uh->execute([':uc_codigo' => $item['uc_codigo'], ':sec_codigo' => $sec_codigo_destino, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
                        }
                    }
                    $co->commit();
                    $reporte['exitos']++;
                } catch (Exception $e) {
                    $co->rollBack();
                    $reporte['fallos'][] = "Error al promover sección {$codigo_origen}: " . $e->getMessage();
                }
            }
            $_SESSION['promocion_f2_ejecutada_session'] = true;
            return $reporte;
        } catch (Exception $e) {
            error_log("Error en Promoción Automática: " . $e->getMessage());
            return ['exitos' => 0, 'fallos' => ['Ocurrió un error crítico durante el proceso: ' . $e->getMessage()], 'observaciones' => []];
        }
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

            foreach ($secciones as $seccion) {
                if ($seccion['ani_anio'] !== $primer_anio || $seccion['ani_tipo'] !== $primer_tipo || substr($seccion['sec_codigo'], 0, 1) !== $primer_trayecto) {
                    return ['resultado' => 'error', 'mensaje' => 'Acción no permitida: Solo se pueden unir horarios de secciones del mismo año, tipo y trayecto.'];
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
                $this->EliminarPorSeccion($codigo_destino, $co);
                if (!empty($clases_origen)) {
                    $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, sec_codigo, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :sec_codigo, :dia, :inicio, :fin)");
                    foreach ($clases_origen as $item) {
                        $stmt_uh->execute([':uc_codigo' => $item['uc_codigo'], ':sec_codigo' => $codigo_destino, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
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
        $cantidadInt = filter_var($cantidadSeccion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
        if ($cantidadInt === false) {
            return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número entero entre 0 y 99.'];
        }

        $co = $this->Con();
        try {

            $stmt_check = $co->prepare("SELECT sec_estado FROM tbl_seccion WHERE sec_codigo = :codigo");
            $stmt_check->execute([':codigo' => $codigoSeccion]);
            $seccion_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($seccion_existente) {

                if ($seccion_existente['sec_estado'] == 1) {

                    return ['resultado' => 'error', 'mensaje' => '¡ERROR! La sección con ese código ya existe y está activa.'];
                } else {

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


            return [
                'resultado' => 'registrar_seccion_ok',
                'mensaje' => '¡Se registró la sección correctamente!',
                'nuevo_codigo' => $codigoSeccion,
                'nueva_cantidad' => $cantidadInt
            ];
        } catch (Exception $e) {
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

    private function validarConflictos($items_horario, $sec_codigo, $co)
    {
    
        $stmt_docente = $co->prepare("SELECT s.sec_codigo FROM uc_horario uh JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo WHERE ud.doc_cedula = :doc_cedula AND uh.hor_dia = :dia AND uh.hor_horainicio = :inicio AND uh.sec_codigo != :sec_codigo AND s.sec_estado = 1 LIMIT 1");
       $stmt_espacio = $co->prepare("SELECT s.sec_codigo FROM uc_horario uh JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo WHERE uh.esp_numero = :esp_numero AND uh.esp_tipo = :esp_tipo AND uh.esp_edificio = :esp_edificio AND uh.hor_dia = :dia AND uh.hor_horainicio = :inicio AND uh.sec_codigo != :sec_codigo AND s.sec_estado = 1 LIMIT 1");

        foreach ($items_horario as $item) {
            $dia_normalizado = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $item['dia']));
            
            $stmt_docente->execute([':doc_cedula' => $item['doc_cedula'], ':dia' => $dia_normalizado, ':inicio' => $item['hora_inicio'], ':sec_codigo' => $sec_codigo]);
            if ($conflicto = $stmt_docente->fetch(PDO::FETCH_ASSOC)) {
                return "Conflicto: El docente ya tiene una clase a esta hora en la sección IN" . htmlspecialchars($conflicto['sec_codigo']) . ".";
            }

             if (!empty($item['esp_numero']) && !empty($item['esp_tipo']) && !empty($item['esp_edificio'])) {
        $stmt_espacio->execute([
            ':esp_numero' => $item['esp_numero'], 
            ':esp_tipo' => $item['esp_tipo'], 
            ':esp_edificio' => $item['esp_edificio'], 
            ':dia' => $dia_normalizado, 
            ':inicio' => $item['hora_inicio'], 
            ':sec_codigo' => $sec_codigo
        ]);
        if ($conflicto = $stmt_espacio->fetch(PDO::FETCH_ASSOC)) {
            // El mensaje de error debe ser más descriptivo
            return "Conflicto: El espacio " . htmlspecialchars($item['esp_tipo'] . ' ' . $item['esp_numero']) . " ya está ocupado a esta hora en la sección IN" . htmlspecialchars($conflicto['sec_codigo']) . ".";
        }
            }
        }

     
        $horas_propuestas_por_docente = [];
        $codigos_uc = array_column($items_horario, 'uc_codigo');
        if (empty($codigos_uc)) return null; 


        $placeholders = implode(',', array_fill(0, count($codigos_uc), '?'));
        $stmt_costos = $co->prepare("SELECT uc_codigo, mal_hora_academica FROM uc_malla WHERE uc_codigo IN ($placeholders) AND mal_codigo = (SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1)");
        $stmt_costos->execute($codigos_uc);
        $costos_uc = $stmt_costos->fetchAll(PDO::FETCH_KEY_PAIR);

    
        foreach ($items_horario as $item) {
            $costo = $costos_uc[$item['uc_codigo']] ?? 0;
            if (!isset($horas_propuestas_por_docente[$item['doc_cedula']])) {
                $horas_propuestas_por_docente[$item['doc_cedula']] = 0;
            }
            $horas_propuestas_por_docente[$item['doc_cedula']] += $costo;
        }

   
        foreach ($horas_propuestas_por_docente as $doc_cedula => $horas_propuestas) {
            $horas_actuales = $this->_obtenerHorasAcademicasActuales($doc_cedula, $co, $sec_codigo);
            
            $stmt_max = $co->prepare("SELECT doc_nombre, doc_apellido, act_academicas FROM tbl_actividad JOIN tbl_docente USING(doc_cedula) WHERE doc_cedula = :doc_cedula");
            $stmt_max->execute([':doc_cedula' => $doc_cedula]);
            $doc_info = $stmt_max->fetch(PDO::FETCH_ASSOC);
            $max_horas = $doc_info['act_academicas'] ?? 0;

            if (($horas_actuales + $horas_propuestas) > $max_horas) {
                $nombre_docente = htmlspecialchars($doc_info['doc_nombre'] . ' ' . $doc_info['doc_apellido']);
                return "Error de carga horaria: El docente <strong>{$nombre_docente}</strong> excedería su límite de horas de clase. Límite: {$max_horas}h. Asignadas: {$horas_actuales}h. Propuestas: {$horas_propuestas}h.";
            }
        }

        return null; 
    }

   public function ValidarClaseEnVivo($doc_cedula, $esp_numero, $esp_tipo, $esp_edificio, $dia, $hora_inicio, $sec_codigo, $uc_codigo = null)
    {
        if (empty($dia) || empty($hora_inicio) || empty($sec_codigo)) {
            return ['conflicto' => false];
        }
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dia_normalizado = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $dia));
            
      
            if (!empty($doc_cedula)) {
                $stmt_docente = $co->prepare("SELECT s.sec_codigo FROM uc_horario uh JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo WHERE ud.doc_cedula = :doc_cedula AND uh.hor_dia = :dia AND uh.hor_horainicio = :inicio AND uh.sec_codigo != :sec_codigo AND s.sec_estado = 1 LIMIT 1");
                $stmt_docente->execute([':dia' => $dia_normalizado, ':inicio' => $hora_inicio, ':doc_cedula' => $doc_cedula, ':sec_codigo' => $sec_codigo]);
                if ($conflicto = $stmt_docente->fetch(PDO::FETCH_ASSOC)) {
                    $prefijo = (substr($conflicto['sec_codigo'], 0, 1) === '3' || substr($conflicto['sec_codigo'], 0, 1) === '4') ? 'IIN' : 'IN';
                    return ['conflicto' => true, 'tipo' => 'docente', 'mensaje' => "Conflicto: Docente ya asignado en sección <strong>".$prefijo . htmlspecialchars($conflicto['sec_codigo']) . "</strong> a esta hora."];
                }
            }
           if (!empty($esp_numero) && !empty($esp_tipo) && !empty($esp_edificio)) {
    $stmt_espacio = $co->prepare("SELECT s.sec_codigo FROM uc_horario uh JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo WHERE uh.esp_numero = :esp_numero AND uh.esp_tipo = :esp_tipo AND uh.esp_edificio = :esp_edificio AND uh.hor_dia = :dia AND uh.hor_horainicio = :inicio AND uh.sec_codigo != :sec_codigo AND s.sec_estado = 1 LIMIT 1");
    $stmt_espacio->execute([
        ':esp_numero' => $esp_numero,
        ':esp_tipo' => $esp_tipo,
        ':esp_edificio' => $esp_edificio,
        ':dia' => $dia_normalizado, 
        ':inicio' => $hora_inicio, 
        ':sec_codigo' => $sec_codigo
    ]);
    if ($conflicto = $stmt_espacio->fetch(PDO::FETCH_ASSOC)) { 
                    $prefijo = (substr($conflicto['sec_codigo'], 0, 1) === '3' || substr($conflicto['sec_codigo'], 0, 1) === '4') ? 'IIN' : 'IN';
                    return ['conflicto' => true, 'tipo' => 'espacio', 'mensaje' => "Conflicto: Espacio ya ocupado en sección <strong>".$prefijo . htmlspecialchars($conflicto['sec_codigo']) . "</strong> a esta hora."];
                }
            }

            if (!empty($doc_cedula) && !empty($uc_codigo)) {
                $stmt_max = $co->prepare("SELECT act_academicas FROM tbl_actividad WHERE doc_cedula = :doc_cedula");
                $stmt_max->execute([':doc_cedula' => $doc_cedula]);
                $max_horas = $stmt_max->fetchColumn();

                if ($max_horas === false || is_null($max_horas)) {
                    return ['conflicto' => true, 'tipo' => 'docente', 'mensaje' => "<strong>Inválido:</strong> El docente no tiene un plan de actividades registrado."];
                }
                $max_horas = (int)$max_horas;
                if ($max_horas <= 0) {
                     return ['conflicto' => true, 'tipo' => 'docente', 'mensaje' => "<strong>Inválido:</strong> El docente no tiene horas de clase permitidas en su plan de actividades."];
                }

                $active_malla = $co->query("SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1 LIMIT 1")->fetchColumn();
                $stmt_costo = $co->prepare("SELECT mal_hora_academica FROM uc_malla WHERE uc_codigo = :uc_codigo AND mal_codigo = :mal_codigo");
                $stmt_costo->execute([':uc_codigo' => $uc_codigo, ':mal_codigo' => $active_malla]);
                $costo_uc = (int)$stmt_costo->fetchColumn();

                $current_horas = $this->_obtenerHorasAcademicasActuales($doc_cedula, $co);
                
                if (($current_horas + $costo_uc) > $max_horas) {
                    return ['conflicto' => true, 'tipo' => 'docente', 'mensaje' => "<strong>Límite Excedido:</strong> Asignar esta clase de {$costo_uc}h superaría el máximo de <strong>{$max_horas}h</strong> del docente (ya tiene {$current_horas}h asignadas)."];
                }
            }
            
            return ['conflicto' => false];
        } catch (Exception $e) {
            error_log("Error en ValidarClaseEnVivo: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public function Modificar($sec_codigo, $items_horario_json)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $items_horario = json_decode($items_horario_json, true);
            foreach ($items_horario as &$item) {
                $item['dia'] = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $item['dia']));
            }
            unset($item);

            $error_conflicto = $this->validarConflictos($items_horario, $sec_codigo, $co);
            if ($error_conflicto) {
                return ['resultado' => 'error', 'mensaje' => $error_conflicto];
            }

            $co->beginTransaction();
           
            $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);

     
            $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);

            if (!empty($items_horario)) {
                $hora_principal_para_turno = $items_horario[0]['hora_inicio'] ?? '08:00:00';

               
                $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
                $stmt_hor->execute([
                    ':sec_codigo' => $sec_codigo,
                    ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
                ]);

                // Se prepara la inserción en la tabla hija, AHORA CON EL AULA INDIVIDUAL
                $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, sec_codigo, esp_numero, esp_tipo, esp_edificio, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :sec_codigo, :esp_numero, :esp_tipo, :esp_edificio, :dia, :inicio, :fin)");
                $stmt_doc = $co->prepare("INSERT INTO docente_horario (doc_cedula, sec_codigo) VALUES (:doc_cedula, :sec_codigo) ON DUPLICATE KEY UPDATE sec_codigo=sec_codigo");

                $docentes_en_seccion = [];

                foreach ($items_horario as $item) {
                    if (!empty($item['uc_codigo']) && !empty($item['doc_cedula'])) {
                        
                        $stmt_uh->execute([
    ':uc_codigo' => $item['uc_codigo'],
    ':sec_codigo' => $sec_codigo,
    ':esp_numero' => $item['esp_numero'] ?? null,
    ':esp_tipo' => $item['esp_tipo'] ?? null,
    ':esp_edificio' => $item['esp_edificio'] ?? null,
    ':dia' => $item['dia'],
    ':inicio' => $item['hora_inicio'],
    ':fin' => $item['hora_fin']
]);

                        if (!in_array($item['doc_cedula'], $docentes_en_seccion)) {
                            $stmt_doc->execute([':doc_cedula' => $item['doc_cedula'], ':sec_codigo' => $sec_codigo]);
                            $docentes_en_seccion[] = $item['doc_cedula'];
                        }
                    }
                }
            }
            $co->commit();
            return ['resultado' => 'modificar_ok', 'mensaje' => '¡Horario guardado correctamente!'];
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => "¡ERROR DE BASE DE DATOS!<br/>" . $e->getMessage()];
        }
    }



    public function EliminarSeccionYHorario($sec_codigo)
    {

        if (empty($sec_codigo)) return ['resultado' => 'error', 'mensaje' => 'Código de sección no proporcionado.'];
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $this->EliminarPorSeccion($sec_codigo, $co);
            $stmt = $co->prepare("UPDATE tbl_seccion SET sec_estado = 0 WHERE sec_codigo = :sec_codigo");
            $stmt->bindParam(':sec_codigo', $sec_codigo, PDO::PARAM_INT);
            $stmt->execute();
            $co->commit();
            return ['resultado' => 'eliminar_seccion_y_horario_ok', 'mensaje' => '¡Sección y horario eliminados correctamente!'];
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => '¡ERROR!<br/>' . $e->getMessage()];
        }
    }

    public function EliminarPorSeccion($sec_codigo, $co_externo = null)
    {

        $co = $co_externo ?? $this->Con();
        $es_transaccion_interna = ($co_externo === null);
        try {
            if ($es_transaccion_interna) $co->beginTransaction();
            $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
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
        ud.doc_cedula, 
        uh.esp_numero,      -- CAMBIADO
        uh.esp_tipo,        -- AÑADIDO
        uh.esp_edificio,    -- AÑADIDO
        uh.hor_dia as dia, 
        uh.hor_horainicio as hora_inicio, 
        uh.hor_horafin as hora_fin 
    FROM uc_horario uh 
    LEFT JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo 
    WHERE uh.sec_codigo = :sec_codigo";


            $stmt = $this->Con()->prepare($sql);
            $stmt->execute([':sec_codigo' => $sec_codigo]);
            $schedule_grid_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            return $this->Con()->query("SELECT esp_numero, esp_tipo, esp_edificio FROM tbl_espacio WHERE esp_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
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