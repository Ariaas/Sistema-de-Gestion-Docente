<?php
require_once('model/dbconnection.php');

class Horario extends Connection
{
    private $hor_id;
    private $esp_id;
    private $fase_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function setId($hor_id)
    {
        $this->hor_id = $hor_id;
    }

    public function setEspacio($esp_id)
    {
        $this->esp_id = $esp_id;
    }

    public function setFaseId($fase_id_param)
    {
        $this->fase_id = $fase_id_param;
    }

    public function getId()
    {
        return $this->hor_id;
    }

    public function getEspacio()
    {
        return $this->esp_id;
    }

    public function getFaseId()
    {
        return $this->fase_id;
    }

    public function verificarHorarioExistente($sec_id, $fase_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $sql = "SELECT COUNT(th.hor_id) as cuenta
                    FROM tbl_horario th
                    INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    WHERE sh.sec_id = :sec_id
                      AND th.fase_id = :fase_id
                      AND th.hor_estado = 1";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
            $stmt->bindParam(':fase_id', $fase_id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($resultado['cuenta'] > 0);
        } catch (Exception $e) {
            error_log("Modelo Horario::verificarHorarioExistente - Error: " . $e->getMessage());
            return true;
        }
    }

    public function ListarAgrupado()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query(
                "SELECT DISTINCT
                    sh.sec_id,
                    ts.sec_codigo,
                    ts.sec_nombre, 
                    tc.coh_numero,
                    tf.fase_id,
                    tf.fase_numero,
                    tt.tra_numero,
                    a.ani_anio
                 FROM tbl_horario th
                 JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                 JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id
                 JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id
                 JOIN tbl_fase tf ON th.fase_id = tf.fase_id
                 JOIN tbl_anio a ON tt.ani_id = a.ani_id
                 JOIN tbl_cohorte tc ON ts.coh_id = tc.coh_id
                 WHERE th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                 ORDER BY ts.sec_codigo, tf.fase_numero"
            );
            $r['resultado'] = 'consultar_agrupado';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "¡ERROR!<br/>Error al listar horarios: " . $e->getMessage();
        }
        return $r;
    }

    public function ConsultarDetallesParaGrupo($sec_id, $fase_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            $sql = "SELECT 
                        uh.uc_id,
                        dh.doc_id,
                        th.esp_id,
                        th.tur_id,
                        uh.hor_dia AS dia,
                        uh.hor_horainicio AS hora_inicio,
                        uh.hor_horafin AS hora_fin,
                        sh.sec_id
                    FROM tbl_horario th
                    INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                    INNER JOIN uc_horario uh ON th.hor_id = uh.hor_id
                    WHERE sh.sec_id = :sec_id 
                      AND th.fase_id = :fase_id 
                      AND th.hor_estado = 1";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
            $stmt->bindParam(':fase_id', $fase_id, PDO::PARAM_INT);
            $stmt->execute();
            $schedule_grid_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['mensaje'] = $schedule_grid_items;
            $r['resultado'] = 'ok';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "¡ERROR!<br/>Error al consultar detalles del horario: " . $e->getMessage();
        }
        return $r;
    }

    private function eliminarFisicoPorSeccionFase($co, $sec_id, $fase_id)
    {
        $stmtHorIds = $co->prepare(
            "SELECT th.hor_id FROM tbl_horario th
             INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
             WHERE sh.sec_id = :sec_id AND th.fase_id = :fase_id"
        );
        $stmtHorIds->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
        $stmtHorIds->bindParam(':fase_id', $fase_id, PDO::PARAM_INT);
        $stmtHorIds->execute();
        $horarios_a_eliminar = $stmtHorIds->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($horarios_a_eliminar)) {
            $placeholders = implode(',', array_fill(0, count($horarios_a_eliminar), '?'));
            $co->prepare("DELETE FROM uc_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
            $co->prepare("DELETE FROM docente_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
            $co->prepare("DELETE FROM seccion_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
            $co->prepare("DELETE FROM tbl_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
        }
    }

    private function verificarConflictosDeEspacio($co, $items_horario, $sec_id_original, $fase_id_original, $nueva_seccion_id, $nueva_fase_id)
    {
        if (empty($items_horario)) return null;

        $stmtAnio = $co->prepare("SELECT t.ani_id FROM tbl_seccion s JOIN tbl_trayecto t ON s.tra_id = t.tra_id WHERE s.sec_id = :sec_id");
        $stmtAnio->execute([':sec_id' => $nueva_seccion_id]);
        $ani_id = $stmtAnio->fetchColumn();
        if (!$ani_id) return "No se pudo determinar el año académico para la validación de espacios.";

        $stmtFase = $co->prepare("SELECT fase_numero FROM tbl_fase WHERE fase_id = :fase_id");
        $stmtFase->execute([':fase_id' => $nueva_fase_id]);
        $current_fase_numero = $stmtFase->fetchColumn();
        if (!$current_fase_numero) return "No se pudo determinar el número de fase para la validación.";

        $stmtConflicto = $co->prepare(
            "SELECT ts.sec_codigo, ts.sec_nombre, tc.coh_numero, te.esp_codigo 
             FROM uc_horario tuh JOIN tbl_horario th ON tuh.hor_id = th.hor_id JOIN seccion_horario tsh ON th.hor_id = tsh.hor_id
             JOIN tbl_seccion ts ON tsh.sec_id = ts.sec_id JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id 
             JOIN tbl_espacio te ON th.esp_id = te.esp_id JOIN tbl_cohorte tc ON ts.coh_id = tc.coh_id
             JOIN tbl_fase tf ON th.fase_id = tf.fase_id
             WHERE th.esp_id = :esp_id AND tuh.hor_dia = :dia AND tuh.hor_horainicio = :hora_inicio AND tt.ani_id = :ani_id AND tf.fase_numero = :current_fase_numero AND th.hor_estado = 1
             AND NOT (tsh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original) LIMIT 1"
        );

        foreach ($items_horario as $item) {
            $stmtConflicto->execute([
                ':esp_id' => $item['esp_id'], 
                ':dia' => $item['dia'], 
                ':hora_inicio' => $item['hora_inicio'], 
                ':ani_id' => $ani_id, 
                ':current_fase_numero' => $current_fase_numero,
                ':sec_id_original' => $sec_id_original, 
                ':fase_id_original' => $fase_id_original
            ]);
            $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);
            if ($conflicto) {
                $nombreSeccionConflicto = $conflicto['sec_codigo'] . '-' . $conflicto['sec_nombre'] . $conflicto['coh_numero'];
                return "Conflicto con BD: El espacio '" . $conflicto['esp_codigo'] . "' ya está ocupado a esa hora y fase por la sección '" . $nombreSeccionConflicto . "'.";
            }
        }
        return null;
    }

    private function verificarConflictosDeDocente($co, $items_horario, $sec_id_original, $fase_id_original, $nueva_seccion_id, $nueva_fase_id)
    {
        if (empty($items_horario)) return null;

        $stmtAnio = $co->prepare("SELECT t.ani_id FROM tbl_seccion s JOIN tbl_trayecto t ON s.tra_id = t.tra_id WHERE s.sec_id = :sec_id");
        $stmtAnio->execute([':sec_id' => $nueva_seccion_id]);
        $current_ani_id = $stmtAnio->fetchColumn();
        if (!$current_ani_id) return "No se pudo determinar el año académico para la validación de docentes.";

        $stmtFase = $co->prepare("SELECT fase_numero FROM tbl_fase WHERE fase_id = :fase_id");
        $stmtFase->execute([':fase_id' => $nueva_fase_id]);
        $current_fase_numero = $stmtFase->fetchColumn();
        if (!$current_fase_numero) return "No se pudo determinar el número de fase para la validación.";

        $stmtConflicto = $co->prepare(
            "SELECT ts.sec_codigo, ts.sec_nombre, tc.coh_numero, CONCAT(td.doc_nombre, ' ', td.doc_apellido) as docente_nombre
             FROM uc_horario tuh JOIN tbl_horario th ON tuh.hor_id = th.hor_id JOIN docente_horario tdh ON th.hor_id = tdh.hor_id
             JOIN tbl_docente td ON tdh.doc_id = td.doc_id JOIN seccion_horario tsh ON th.hor_id = tsh.hor_id
             JOIN tbl_seccion ts ON tsh.sec_id = ts.sec_id JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id 
             JOIN tbl_fase tf ON th.fase_id = tf.fase_id JOIN tbl_cohorte tc ON ts.coh_id = tc.coh_id
             WHERE tdh.doc_id = :doc_id AND tuh.hor_dia = :dia AND tuh.hor_horainicio = :hora_inicio 
               AND tt.ani_id = :current_ani_id AND tf.fase_numero = :current_fase_numero AND th.hor_estado = 1
               AND NOT (tsh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original)
             LIMIT 1"
        );

        foreach ($items_horario as $item) {
            $stmtConflicto->execute([
                ':doc_id' => $item['doc_id'],
                ':dia' => $item['dia'],
                ':hora_inicio' => $item['hora_inicio'],
                ':current_ani_id' => $current_ani_id,
                ':current_fase_numero' => $current_fase_numero,
                ':sec_id_original' => $sec_id_original,
                ':fase_id_original' => $fase_id_original
            ]);
            $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);
            if ($conflicto) {
                $nombreSeccionConflicto = $conflicto['sec_codigo'] . '-' . $conflicto['sec_nombre'] . $conflicto['coh_numero'];
                return "Conflicto con BD: El docente '" . $conflicto['docente_nombre'] . "' ya tiene una clase en la sección '" . $nombreSeccionConflicto . "' a esa misma hora en el mismo año y fase.";
            }
        }
        return null;
    }

    public function ModificarGrupo($sec_id_original_grupo, $fase_id_original_grupo, $nueva_seccion_id_grupo, $nueva_fase_id_grupo, $items_horario_json, $accion_original)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $items_horario = json_decode($items_horario_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['resultado' => 'error', 'mensaje' => 'Error decodificando los datos del horario: ' . json_last_error_msg()];
        }

        $docenteTracker = [];
        $espacioTracker = [];
        $docentesNombres = $this->obtenerDocentes();
        $espaciosNombres = $this->obtenerEspacios();
        $docenteMap = array_column($docentesNombres, 'nombre_completo', 'doc_id');
        $espacioMap = array_column($espaciosNombres, 'esp_codigo', 'esp_id');

        foreach ($items_horario as $item) {
            $hora_inicio_key = $item['hora_inicio'];
            $dia_key = $item['dia'];
            $docente_key = $item['doc_id'] . '-' . $dia_key . '-' . $hora_inicio_key;
            if (isset($docenteTracker[$docente_key])) {
                $nombreDocente = $docenteMap[$item['doc_id']] ?? "ID " . $item['doc_id'];
                return ['resultado' => 'error', 'mensaje' => "Conflicto en los datos enviados: El docente '{$nombreDocente}' está asignado a más de una clase el {$dia_key} a las " . date("g:i A", strtotime($hora_inicio_key)) . "."];
            }
            $docenteTracker[$docente_key] = true;
            $espacio_key = $item['esp_id'] . '-' . $dia_key . '-' . $hora_inicio_key;
            if (isset($espacioTracker[$espacio_key])) {
                $nombreEspacio = $espacioMap[$item['esp_id']] ?? "ID " . $item['esp_id'];
                return ['resultado' => 'error', 'mensaje' => "Conflicto en los datos enviados: El espacio '{$nombreEspacio}' está asignado a más de una clase el {$dia_key} a las " . date("g:i A", strtotime($hora_inicio_key)) . "."];
            }
            $espacioTracker[$espacio_key] = true;
        }

        $error_espacio = $this->verificarConflictosDeEspacio($co, $items_horario, $sec_id_original_grupo, $fase_id_original_grupo, $nueva_seccion_id_grupo, $nueva_fase_id_grupo);
        if ($error_espacio !== null) {
            return ['resultado' => 'error', 'mensaje' => "¡ERROR DE VALIDACIÓN!<br/>" . $error_espacio];
        }
        $error_docente = $this->verificarConflictosDeDocente($co, $items_horario, $sec_id_original_grupo, $fase_id_original_grupo, $nueva_seccion_id_grupo, $nueva_fase_id_grupo);
        if ($error_docente !== null) {
            return ['resultado' => 'error', 'mensaje' => "¡ERROR DE VALIDACIÓN!<br/>" . $error_docente];
        }

        try {
            $co->beginTransaction();
            $this->eliminarFisicoPorSeccionFase($co, $sec_id_original_grupo, $fase_id_original_grupo);
            if (is_array($items_horario) && !empty($items_horario)) {
                $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, tur_id, fase_id, hor_modalidad, hor_estado) VALUES (:esp_id, :tur_id, :fase_id, 'presencial', 1)");
                $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
                $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
                $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_id, :hor_id, :dia, :inicio, :fin)");
                foreach ($items_horario as $item) {
                    if (empty($item['uc_id']) || empty($item['doc_id']) || empty($item['esp_id']) || empty($item['tur_id'])) continue;
                    $stmt_insert_hor->execute([':esp_id' => $item['esp_id'], ':tur_id' => $item['tur_id'], ':fase_id' => $nueva_fase_id_grupo]);
                    $new_hor_id = $co->lastInsertId();
                    $stmt_sh->execute([':sec_id' => $nueva_seccion_id_grupo, ':hor_id' => $new_hor_id]);
                    $stmt_dh->execute([':doc_id' => $item['doc_id'], ':hor_id' => $new_hor_id]);
                    $stmt_uh->execute([':uc_id' => $item['uc_id'], ':hor_id' => $new_hor_id, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
                }
            }
            $co->commit();
            $r['resultado'] = 'modificar_grupo_ok';
            $r['mensaje'] = ($accion_original === 'registrar') ? '¡Registro Incluido!<br/>Se registró el horario correctamente.' : '¡Registro Modificado!<br/>Se modificó el horario correctamente.';
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = "¡ERROR!<br/>Error interno del servidor: " . $e->getMessage();
        }
        return $r;
    }

    public function EliminarPorSeccionFase($sec_id_grupo, $fase_id_grupo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $co->beginTransaction();
            $this->eliminarFisicoPorSeccionFase($co, $sec_id_grupo, $fase_id_grupo);
            $co->commit();
            $r['resultado'] = 'eliminar_por_seccion_fase_ok';
            $r['mensaje'] = '¡Registro Eliminado!<br/>Se eliminó el horario correctamente.';
        } catch (Exception $e) {
            if ($co && $co->inTransaction()) $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = "¡ERROR!<br/>Error al eliminar el horario: " . $e->getMessage();
        }
        return $r;
    }
    
    public function verificarConflictoEspacioIndividual($esp_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original)
    {
        $co = $this->Con();
        try {
            $stmtAnio = $co->prepare("SELECT t.ani_id FROM tbl_seccion s JOIN tbl_trayecto t ON s.tra_id = t.tra_id WHERE s.sec_id = :sec_id");
            $stmtAnio->execute([':sec_id' => $sec_id_actual]);
            $ani_id = $stmtAnio->fetchColumn();
            if (!$ani_id) return ['conflicto' => false];

            $stmtFase = $co->prepare("SELECT fase_numero FROM tbl_fase WHERE fase_id = :fase_id");
            $stmtFase->execute([':fase_id' => $fase_id_actual]);
            $current_fase_numero = $stmtFase->fetchColumn();
            if (!$current_fase_numero) return ['conflicto' => false];

            $sql = "SELECT ts.sec_codigo, ts.sec_nombre, tc.coh_numero, te.esp_codigo, tf.fase_numero FROM uc_horario uh JOIN tbl_horario th ON uh.hor_id = th.hor_id JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id JOIN tbl_espacio te ON th.esp_id = te.esp_id 
                    JOIN tbl_fase tf ON th.fase_id = tf.fase_id JOIN tbl_cohorte tc ON ts.coh_id = tc.coh_id
                    WHERE th.esp_id = :esp_id AND uh.hor_dia = :dia AND uh.hor_horainicio = :hora_inicio AND tt.ani_id = :ani_id AND tf.fase_numero = :current_fase_numero AND th.hor_estado = 1
                    AND NOT (sh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original) LIMIT 1";
            
            $stmt = $co->prepare($sql);
            $stmt->execute([
                ':esp_id' => $esp_id, 
                ':dia' => $dia, 
                ':hora_inicio' => $hora_inicio, 
                ':ani_id' => $ani_id, 
                ':current_fase_numero' => $current_fase_numero,
                ':sec_id_original' => $sec_id_original, 
                ':fase_id_original' => $fase_id_original
            ]);
            $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conflicto) {
                $nombreSeccionConflicto = $conflicto['sec_codigo'] . '-' . $conflicto['sec_nombre'] . $conflicto['coh_numero'];
                return ['conflicto' => true, 'mensaje' => "Conflicto: Espacio '" . $conflicto['esp_codigo'] . "' ya ocupado por la sección '" . $nombreSeccionConflicto . "' en ese bloque para la misma fase."];
            }
            return ['conflicto' => false];
        } catch (Exception $e) {
            error_log("Error en verificarConflictoEspacioIndividual: " . $e->getMessage());
            return ['conflicto' => true, 'mensaje' => 'Error al verificar el espacio.'];
        }
    }

    public function verificarConflictoDocenteIndividual($doc_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original)
    {
        $co = $this->Con();
        try {
            $stmtAnio = $co->prepare("SELECT t.ani_id FROM tbl_seccion s JOIN tbl_trayecto t ON s.tra_id = t.tra_id WHERE s.sec_id = :sec_id");
            $stmtAnio->execute([':sec_id' => $sec_id_actual]);
            $current_ani_id = $stmtAnio->fetchColumn();
            if (!$current_ani_id) return ['conflicto' => false];

            $stmtFase = $co->prepare("SELECT fase_numero FROM tbl_fase WHERE fase_id = :fase_id");
            $stmtFase->execute([':fase_id' => $fase_id_actual]);
            $current_fase_numero = $stmtFase->fetchColumn();
            if (!$current_fase_numero) return ['conflicto' => false];
            
            $sql = "SELECT ts.sec_codigo, ts.sec_nombre, tc.coh_numero, CONCAT(td.doc_nombre, ' ', td.doc_apellido) as docente_nombre
                    FROM uc_horario uh JOIN tbl_horario th ON uh.hor_id = th.hor_id JOIN docente_horario dh ON th.hor_id = dh.hor_id
                    JOIN tbl_docente td ON dh.doc_id = td.doc_id JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id 
                    JOIN tbl_fase tf ON th.fase_id = tf.fase_id JOIN tbl_cohorte tc ON ts.coh_id = tc.coh_id
                    WHERE dh.doc_id = :doc_id AND uh.hor_dia = :dia AND uh.hor_horainicio = :hora_inicio AND tt.ani_id = :current_ani_id AND tf.fase_numero = :current_fase_numero AND th.hor_estado = 1
                    AND NOT (sh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original) LIMIT 1";

            $stmt = $co->prepare($sql);
            $stmt->execute([
                ':doc_id' => $doc_id, ':dia' => $dia, ':hora_inicio' => $hora_inicio, ':current_ani_id' => $current_ani_id,
                ':current_fase_numero' => $current_fase_numero, ':sec_id_original' => $sec_id_original, ':fase_id_original' => $fase_id_original
            ]);
            $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($conflicto) {
                $nombreSeccionConflicto = $conflicto['sec_codigo'] . '-' . $conflicto['sec_nombre'] . $conflicto['coh_numero'];
                return ['conflicto' => true, 'mensaje' => "Conflicto: El docente ya tiene una clase en la sección '" . $nombreSeccionConflicto . "' en ese bloque de tiempo para la misma fase."];
            }
            return ['conflicto' => false];
        } catch (Exception $e) {
            error_log("Error en verificarConflictoDocenteIndividual: " . $e->getMessage());
            return ['conflicto' => true, 'mensaje' => 'Error al verificar el docente.'];
        }
    }

    public function obtenerTurnos()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT tur_id, tur_horainicio, tur_horafin FROM tbl_turno WHERE tur_estado = 1 ORDER BY tur_horainicio");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerFases()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT f.fase_id, f.tra_id, f.fase_numero, t.tra_numero, a.ani_anio FROM tbl_fase f JOIN tbl_trayecto t ON f.tra_id = t.tra_id JOIN tbl_anio a ON t.ani_id = a.ani_id WHERE f.fase_estado = 1 ORDER BY a.ani_anio DESC, t.tra_numero, f.fase_numero");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerUnidadesCurriculares()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT uc.uc_id, uc.uc_nombre, uc.uc_codigo, uc.tra_id FROM tbl_uc uc WHERE uc.uc_estado = 1 ORDER BY uc.uc_nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerSecciones()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT s.sec_id, s.sec_codigo, s.sec_nombre, s.tra_id, t.tra_numero, a.ani_anio, c.coh_numero
                 FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 JOIN tbl_anio a ON t.ani_id = a.ani_id
                 JOIN tbl_cohorte c ON s.coh_id = c.coh_id
                 WHERE s.sec_estado = 1 AND t.tra_estado = 1
                 ORDER BY s.sec_codigo"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerEspacios()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT esp_id, esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo, esp_tipo");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerDocentes()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT doc_id, doc_nombre, doc_apellido, CONCAT(doc_nombre, ' ', doc_apellido) as nombre_completo FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido, doc_nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerUcPorDocenteYTrayecto($doc_id, $tra_id = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $sql = "SELECT u.uc_id, u.uc_nombre, u.uc_codigo, u.tra_id FROM tbl_uc u INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id WHERE ud.doc_id = :doc_id AND u.uc_estado = 1";
            $params = [':doc_id' => $doc_id];
            if ($tra_id !== null && !empty($tra_id)) {
                $sql .= " AND u.tra_id = :tra_id";
                $params[':tra_id'] = $tra_id;
            }
            $sql .= " ORDER BY u.uc_codigo, u.uc_nombre";
            $stmt = $co->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>