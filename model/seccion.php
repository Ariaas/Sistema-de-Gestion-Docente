
<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

    /**
     * Crea una plantilla de horario aleatorio para una sección al momento de crearla.
     * No toma en cuenta prioridad de docente, solo distribuye bloques y días aleatoriamente.
     */
    public function CrearHorarioAleatorio($sec_codigo, $trayecto)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        $bloques = $this->obtenerTurnos();
        $docentes_raw = $this->obtenerDocentes();
        $docentes = [];
        $fase_actual = $this->determinarFaseActual();
        // Filtrar docentes completos y que tengan al menos una UC asignada en uc_docente, del trayecto y fase
        foreach ($docentes_raw as $d) {
            if (!empty($d['doc_cedula']) && !empty($d['doc_nombre']) && !empty($d['doc_apellido'])) {
                $sql = "SELECT COUNT(*) FROM uc_docente ud INNER JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo WHERE ud.doc_cedula = :doc_cedula AND ud.uc_doc_estado = 1 AND u.uc_trayecto = :trayecto AND u.uc_estado = 1";
                if ($fase_actual === 'fase1') {
                    $sql .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
                } elseif ($fase_actual === 'fase2') {
                    $sql .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
                }
                $stmt_ucd = $co->prepare($sql);
                $stmt_ucd->execute([':doc_cedula' => $d['doc_cedula'], ':trayecto' => $trayecto]);
                if ($stmt_ucd->fetchColumn() > 0) {
                    $docentes[] = $d;
                }
            }
        }
        // Filtrar UCs del trayecto y fase que tengan al menos un docente asignado en uc_docente
        $ucs_raw = $this->obtenerUnidadesCurriculares();
        $ucs = [];
        foreach ($ucs_raw as $uc) {
            $sql = "SELECT COUNT(*) FROM uc_docente ud INNER JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo WHERE ud.uc_codigo = :uc_codigo AND ud.uc_doc_estado = 1 AND u.uc_estado = 1";
            if ($fase_actual === 'fase1') {
                $sql .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
            } elseif ($fase_actual === 'fase2') {
                $sql .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
            }
            $stmt_ucd = $co->prepare($sql);
            $stmt_ucd->execute([':uc_codigo' => $uc['uc_codigo']]);
            if ($stmt_ucd->fetchColumn() > 0 && $uc['uc_trayecto'] == $trayecto) {
                $ucs[] = $uc;
            }
        }
        // Si no hay docentes o UCs, crear horario vacío y permitir edición manual
        if (empty($docentes) || empty($ucs)) {
            $co->beginTransaction();
            $this->EliminarPorSeccion($sec_codigo, $co);
            $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
            $stmt_hor->execute([
                ':sec_codigo' => $sec_codigo,
                ':tur_nombre' => $this->getTurnoEnum('08:00:00')
            ]);
            $co->commit();
            return ['resultado' => 'ok', 'mensaje' => 'No hay docentes ni UCs disponibles. Horario vacío creado para edición manual.', 'horario' => []];
        }
        $espacios = $this->obtenerEspacios();
        $espacios_count = count($espacios);
        $espacio_idx = 0;
        $horario = [];
        $ocupados_set = [];
        $uc_asignadas = [];
        // Obtener todas las horas de inicio válidas del sistema
        $horas_validas = array_map(function ($b) {
            return $b['tur_horainicio'];
        }, $bloques);
        // Helper para encontrar la hora válida más cercana
        $findClosestHora = function ($hora_pref) use ($horas_validas) {
            $hora_pref_ts = strtotime($hora_pref);
            $closest = $horas_validas[0];
            $min_diff = abs(strtotime($closest) - $hora_pref_ts);
            foreach ($horas_validas as $h) {
                $diff = abs(strtotime($h) - $hora_pref_ts);
                if ($diff < $min_diff) {
                    $min_diff = $diff;
                    $closest = $h;
                }
            }
            return $closest;
        };
        // Asignación aleatoria de UCs y docentes
        foreach ($ucs as $uc) {
            $asignado = false;
            $intentos = 0;
            while (!$asignado && $intentos < 100) {
                $dia = $dias[array_rand($dias)];
                $bloque = $bloques[array_rand($bloques)];
                $hora = $bloque['tur_horainicio'];
                if (!isset($ocupados_set[$dia . '_' . $hora]) && $espacios_count > 0) {
                    $sql = "SELECT d.doc_cedula FROM uc_docente ud INNER JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula INNER JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo WHERE ud.uc_codigo = :uc_codigo AND ud.uc_doc_estado = 1 AND d.doc_estado = 1 AND u.uc_estado = 1";
                    if ($fase_actual === 'fase1') {
                        $sql .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
                    } elseif ($fase_actual === 'fase2') {
                        $sql .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
                    }
                    $stmt_docentes_uc = $co->prepare($sql);
                    $stmt_docentes_uc->execute([':uc_codigo' => $uc['uc_codigo']]);
                    $docentes_uc = $stmt_docentes_uc->fetchAll(PDO::FETCH_COLUMN, 0);
                    if (!empty($docentes_uc)) {
                        $docente_cedula = $docentes_uc[array_rand($docentes_uc)];
                        $espacio = $espacios[$espacio_idx % $espacios_count];
                        $horario[] = [
                            'uc_codigo' => $uc['uc_codigo'],
                            'doc_cedula' => $docente_cedula,
                            'esp_codigo' => $espacio['esp_codigo'],
                            'dia' => $dia,
                            'hora_inicio' => $bloque['tur_horainicio'],
                            'hora_fin' => $bloque['tur_horafin'],
                        ];
                        $ocupados_set[$dia . '_' . $hora] = true;
                        $uc_asignadas[] = $uc['uc_codigo'];
                        $espacio_idx++;
                        $asignado = true;
                    }
                }
                $intentos++;
            }
        }
        // Asignar UCs restantes de forma aleatoria si no se asignaron por preferencia
        foreach ($ucs as $uc) {
            if (!in_array($uc['uc_codigo'], $uc_asignadas)) {
                $dia = $dias[array_rand($dias)];
                $bloque = $bloques[array_rand($bloques)];
                if (!isset($ocupados_set[$dia . '_' . $bloque['tur_horainicio']]) && $espacios_count > 0) {
                    $docentes_uc = [];
                    $sql = "SELECT d.doc_cedula FROM uc_docente ud INNER JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula INNER JOIN tbl_uc u ON ud.uc_codigo = u.uc_codigo WHERE ud.uc_codigo = :uc_codigo AND ud.uc_doc_estado = 1 AND d.doc_estado = 1 AND u.uc_estado = 1";
                    if ($fase_actual === 'fase1') {
                        $sql .= " AND (u.uc_periodo = 'Fase I' OR u.uc_periodo = 'Anual' OR u.uc_periodo = '0')";
                    } elseif ($fase_actual === 'fase2') {
                        $sql .= " AND (u.uc_periodo = 'Fase II' OR u.uc_periodo = 'Anual')";
                    }
                    $stmt_docentes_uc = $co->prepare($sql);
                    $stmt_docentes_uc->execute([':uc_codigo' => $uc['uc_codigo']]);
                    $docentes_uc = $stmt_docentes_uc->fetchAll(PDO::FETCH_COLUMN, 0);
                    if (!empty($docentes_uc)) {
                        $docente_cedula = $docentes_uc[array_rand($docentes_uc)];
                        $espacio = $espacios[$espacio_idx % $espacios_count];
                        $horario[] = [
                            'uc_codigo' => $uc['uc_codigo'],
                            'doc_cedula' => $docente_cedula,
                            'esp_codigo' => $espacio['esp_codigo'],
                            'dia' => $dia,
                            'hora_inicio' => $bloque['tur_horainicio'],
                            'hora_fin' => $bloque['tur_horafin'],
                        ];
                        $ocupados_set[$dia . '_' . $bloque['tur_horainicio']] = true;
                        $uc_asignadas[] = $uc['uc_codigo'];
                        $espacio_idx++;
                    }
                }
            }
        }
        // Validar conflictos antes de guardar
        $error_conflicto = $this->validarConflictos($horario, $sec_codigo, $co);
        if ($error_conflicto) {
            return ['resultado' => 'error', 'mensaje' => $error_conflicto];
        }
        // Guardar horario generado
        $co->beginTransaction();
        $this->EliminarPorSeccion($sec_codigo, $co);
        // Crear registro padre en tbl_horario para cumplir la FK
        $hora_principal_para_turno = $horario[0]['hora_inicio'] ?? '08:00:00';
        $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
        $stmt_hor->execute([
            ':sec_codigo' => $sec_codigo,
            ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
        ]);
        $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, sec_codigo, esp_codigo, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :sec_codigo, :esp_codigo, :dia, :inicio, :fin)");
        foreach ($horario as $item) {
            $stmt_uh->execute([
                ':uc_codigo' => $item['uc_codigo'],
                ':sec_codigo' => $sec_codigo,
                ':esp_codigo' => $item['esp_codigo'],
                ':dia' => $item['dia'],
                ':inicio' => $item['hora_inicio'],
                ':fin' => $item['hora_fin'],
            ]);
        }
        $co->commit();
        return ['resultado' => 'ok', 'mensaje' => 'Horario aleatorio generado correctamente.', 'horario' => $horario];
    }

    // Helper para obtener hora fin
    private function getHoraFin($hora_inicio, $bloques)
    {
        foreach ($bloques as $b) {
            if ($b['tur_horainicio'] == $hora_inicio) return $b['tur_horafin'];
        }
        return date('H:i:s', strtotime($hora_inicio) + 40 * 60); // fallback 40 min
    }
    public function __construct()
    {
        parent::__construct();
    }

    private function determinarFaseActual()
    {
        try {
            // Consulta para obtener el año activo
            $stmt_anio = $this->Con()->prepare("SELECT ani_anio, ani_tipo FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt_anio->execute();
            $anio_activo = $stmt_anio->fetch(PDO::FETCH_ASSOC);

            if ($anio_activo) {
                // Consulta para obtener las fases del año activo
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
                        // Devuelve 'fase1' para fase_numero 1, 'fase2' para fase_numero 2, etc.
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

            $stmt_docente = $co->prepare("

            SELECT s.sec_codigo, d.doc_nombre, d.doc_apellido
            FROM uc_horario uh
            JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo
            JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
            JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
            WHERE uh.hor_dia = :dia 
              AND uh.hor_horainicio = :inicio 
              AND ud.doc_cedula = :doc_cedula 
              AND uh.sec_codigo != :sec_codigo
              AND s.sec_estado = 1
              AND ud.uc_doc_estado = 1
        ");
        foreach ($items_horario as $item) {
            $dia_normalizado = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $item['dia']));
            $stmt_docente->execute([
                ':dia' => $dia_normalizado,
                ':inicio' => $item['hora_inicio'],
                ':doc_cedula' => $item['doc_cedula'],
                ':sec_codigo' => $sec_codigo
            ]);
            $conflicto_docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);
            if ($conflicto_docente) {
                return "Conflicto: El docente <strong>" . htmlspecialchars($conflicto_docente['doc_nombre'] . ' ' . $conflicto_docente['doc_apellido']) . "</strong> ya tiene una clase a las <strong>" . date("g:i a", strtotime($item['hora_inicio'])) . "</strong> en la sección <strong>IN" . htmlspecialchars($conflicto_docente['sec_codigo']) . "</strong>.";
            }
        }
        return null;
    }

    public function ValidarClaseEnVivo($doc_cedula, $esp_codigo, $dia, $hora_inicio, $sec_codigo)
    {

        if (empty($dia) || empty($hora_inicio) || empty($sec_codigo) || (empty($doc_cedula) && empty($esp_codigo))) {
            return ['conflicto' => false];
        }
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dia_normalizado = strtolower(str_replace(['é', 'á', 'í', 'ó', 'ú'], ['e', 'a', 'i', 'o', 'u'], $dia));
            if (!empty($doc_cedula)) {
                $stmt_docente = $co->prepare("
                    SELECT s.sec_codigo, d.doc_nombre, d.doc_apellido
                    FROM uc_horario uh
                    JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo
                    JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                    JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    WHERE uh.hor_dia = :dia AND uh.hor_horainicio = :inicio AND ud.doc_cedula = :doc_cedula AND uh.sec_codigo != :sec_codigo AND s.sec_estado = 1 AND ud.uc_doc_estado = 1
                    LIMIT 1
                ");
                $stmt_docente->execute([':dia' => $dia_normalizado, ':inicio' => $hora_inicio, ':doc_cedula' => $doc_cedula, ':sec_codigo' => $sec_codigo]);
                $conflicto = $stmt_docente->fetch(PDO::FETCH_ASSOC);
                if ($conflicto) {
                    return ['conflicto' => true, 'tipo' => 'docente', 'mensaje' => "Conflicto: Docente ya asignado en sección <strong>IN" . htmlspecialchars($conflicto['sec_codigo']) . "</strong> a esta hora."];
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
            // Primero, se eliminan los registros hijos existentes
            $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);
            $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);

            // Ahora nos aseguramos de que el registro padre en tbl_horario exista (ya no maneja el aula)
            $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = :sec_codigo")->execute([':sec_codigo' => $sec_codigo]);

            if (!empty($items_horario)) {
                $hora_principal_para_turno = $items_horario[0]['hora_inicio'] ?? '08:00:00';

                // Se inserta el registro padre sin información del aula
                $stmt_hor = $co->prepare("INSERT INTO tbl_horario (sec_codigo, tur_nombre, hor_estado) VALUES (:sec_codigo, :tur_nombre, 1)");
                $stmt_hor->execute([
                    ':sec_codigo' => $sec_codigo,
                    ':tur_nombre' => $this->getTurnoEnum($hora_principal_para_turno)
                ]);

                // Se prepara la inserción en la tabla hija, AHORA CON EL AULA INDIVIDUAL
                $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_codigo, sec_codigo, esp_codigo, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_codigo, :sec_codigo, :esp_codigo, :dia, :inicio, :fin)");
                $stmt_doc = $co->prepare("INSERT INTO docente_horario (doc_cedula, sec_codigo) VALUES (:doc_cedula, :sec_codigo) ON DUPLICATE KEY UPDATE sec_codigo=sec_codigo");

                $docentes_en_seccion = [];

                foreach ($items_horario as $item) {
                    if (!empty($item['uc_codigo']) && !empty($item['doc_cedula'])) {
                        // Ahora se guarda el esp_codigo con CADA clase
                        $stmt_uh->execute([
                            ':uc_codigo' => $item['uc_codigo'],
                            ':sec_codigo' => $sec_codigo,
                            ':esp_codigo' => $item['esp_codigo'] ?? null, // Se usa el aula de este item
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
                    uh.esp_codigo, 
                    uh.hor_dia as dia, 
                    uh.hor_horainicio as hora_inicio, 
                    uh.hor_horafin as hora_fin 
                FROM uc_horario uh 
                LEFT JOIN uc_docente ud ON uh.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1 
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
            $sql = "SELECT u.uc_codigo, u.uc_nombre, u.uc_trayecto, u.uc_periodo FROM tbl_uc u INNER JOIN uc_docente ud ON u.uc_codigo = ud.uc_codigo WHERE ud.doc_cedula = :doc_cedula AND u.uc_estado = 1 AND ud.uc_doc_estado = 1";
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
            return $this->Con()->query("SELECT esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1")->fetchAll(PDO::FETCH_ASSOC);
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