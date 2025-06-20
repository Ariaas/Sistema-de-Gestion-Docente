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

    private function findOrCreateHorarioIdForGroup($co, $sec_id_clase, $fase_id_clase, $esp_id_clase, $doc_id_clase, $tur_id_clase)
    {
        if (is_null($tur_id_clase)) {
            throw new Exception("Error Crítico: Se intentó crear un registro de horario con tur_id nulo.");
        }

        $sql = "SELECT th.hor_id
                FROM tbl_horario th
                INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                WHERE sh.sec_id = :sec_id
                   AND th.fase_id = :fase_id
                   AND th.esp_id = :esp_id
                   AND dh.doc_id = :doc_id
                   AND th.tur_id = :tur_id
                   AND th.hor_estado = 1 LIMIT 1";

        $params = [
            ':sec_id' => $sec_id_clase,
            ':fase_id' => $fase_id_clase,
            ':esp_id' => $esp_id_clase,
            ':doc_id' => $doc_id_clase,
            ':tur_id' => $tur_id_clase
        ];

        $stmt_find = $co->prepare($sql);
        $stmt_find->execute($params);
        $hor_id_found = $stmt_find->fetchColumn();

        if ($hor_id_found) {
            return $hor_id_found;
        } else {
            $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, tur_id, fase_id, hor_modalidad, hor_estado) VALUES (:esp_id, :tur_id, :fase_id, 'presencial', 1)");
            $stmt_insert_hor->bindParam(':esp_id', $esp_id_clase, PDO::PARAM_INT);
            $stmt_insert_hor->bindParam(':tur_id', $tur_id_clase, PDO::PARAM_INT);
            $stmt_insert_hor->bindParam(':fase_id', $fase_id_clase, PDO::PARAM_INT);
            $stmt_insert_hor->execute();
            $new_hor_id = $co->lastInsertId();

            $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
            $stmt_sh->bindParam(':sec_id', $sec_id_clase);
            $stmt_sh->bindParam(':hor_id', $new_hor_id);
            $stmt_sh->execute();

            $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
            $stmt_dh->bindParam(':doc_id', $doc_id_clase, PDO::PARAM_INT);
            $stmt_dh->bindParam(':hor_id', $new_hor_id);
            $stmt_dh->execute();

            return $new_hor_id;
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
                 WHERE th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                 ORDER BY ts.sec_codigo, tf.fase_numero"
            );
            $r['resultado'] = 'consultar_agrupado';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al listar horarios agrupados: " . $e->getMessage();
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
            $r['mensaje'] = "Error consultando detallado: " . $e->getMessage();
        }
        return $r;
    }

    private function eliminarLogicoPorSeccionFase($co, $sec_id, $fase_id)
    {
        $stmtHorIds = $co->prepare(
            "SELECT th.hor_id FROM tbl_horario th
             INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
             WHERE sh.sec_id = :sec_id AND th.fase_id = :fase_id AND th.hor_estado = 1"
        );
        $stmtHorIds->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
        $stmtHorIds->bindParam(':fase_id', $fase_id, PDO::PARAM_INT);
        $stmtHorIds->execute();
        $horarios_a_eliminar = $stmtHorIds->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($horarios_a_eliminar)) {
            $placeholders = implode(',', array_fill(0, count($horarios_a_eliminar), '?'));
            $stmtUpdate = $co->prepare("UPDATE tbl_horario SET hor_estado = 0 WHERE hor_id IN ($placeholders)");
            $stmtUpdate->execute($horarios_a_eliminar);
        }
    }

    private function verificarConflictosDeEspacio($co, $sec_id_original, $fase_id_original, $nueva_seccion_id, $nueva_fase_id, $items_horario)
    {
        $stmtAnio = $co->prepare(
            "SELECT t.ani_id FROM tbl_seccion s
             JOIN tbl_trayecto t ON s.tra_id = t.tra_id
             WHERE s.sec_id = :sec_id"
        );
        $stmtAnio->execute([':sec_id' => $nueva_seccion_id]);
        $ani_id = $stmtAnio->fetchColumn();

        if (!$ani_id) {
            return "No se pudo determinar el año académico para la validación.";
        }

        $stmtConflicto = $co->prepare(
            "SELECT ts.sec_codigo, te.esp_codigo
             FROM uc_horario AS tuh
             JOIN tbl_horario AS th ON tuh.hor_id = th.hor_id
             JOIN seccion_horario AS tsh ON th.hor_id = tsh.hor_id
             JOIN tbl_seccion AS ts ON tsh.sec_id = ts.sec_id
             JOIN tbl_trayecto AS tt ON ts.tra_id = tt.tra_id
             JOIN tbl_espacio AS te ON th.esp_id = te.esp_id
             WHERE
                th.esp_id = :esp_id AND
                tuh.hor_dia = :dia AND
                tuh.hor_horainicio = :hora_inicio AND
                th.fase_id = :fase_id AND
                tt.ani_id = :ani_id AND
                th.hor_estado = 1 AND
                NOT (tsh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original)
             LIMIT 1"
        );

        foreach ($items_horario as $item) {
            $stmtConflicto->execute([
                ':esp_id' => $item['esp_id'],
                ':dia' => $item['dia'],
                ':hora_inicio' => $item['hora_inicio'],
                ':fase_id' => $nueva_fase_id,
                ':ani_id' => $ani_id,
                ':sec_id_original' => $sec_id_original,
                ':fase_id_original' => $fase_id_original
            ]);

            $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);

            if ($conflicto) {
                return "Conflicto de Horario: El espacio '" . $conflicto['esp_codigo'] .
                    "' ya está ocupado el día " . $item['dia'] . " a las " . date("g:i A", strtotime($item['hora_inicio'])) .
                    " por la sección '" . $conflicto['sec_codigo'] . "' en la misma fase y año académico.";
            }
        }

        return null;
    }

    public function ModificarGrupo($sec_id_original_grupo, $fase_id_original_grupo, $nueva_seccion_id_grupo, $nueva_fase_id_grupo, $items_horario_json)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $items_horario = json_decode($items_horario_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Error decodificando items: ' . json_last_error_msg();
            return $r;
        }

        $conflict_error = $this->verificarConflictosDeEspacio($co, $sec_id_original_grupo, $fase_id_original_grupo, $nueva_seccion_id_grupo, $nueva_fase_id_grupo, $items_horario);
        if ($conflict_error !== null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $conflict_error;
            return $r;
        }

        try {
            $co->beginTransaction();
            $this->eliminarLogicoPorSeccionFase($co, $sec_id_original_grupo, $fase_id_original_grupo);
            $gruposNuevos = [];
            if (is_array($items_horario)) {
                foreach ($items_horario as $item) {
                    if (empty($item['uc_id'])) continue;
                    $doc_id_item = isset($item['doc_id']) && !empty($item['doc_id']) ? $item['doc_id'] : null;
                    $esp_id_item = isset($item['esp_id']) && !empty($item['esp_id']) ? $item['esp_id'] : null;
                    $tur_id_item = isset($item['tur_id']) && !empty($item['tur_id']) ? $item['tur_id'] : null;

                    if (is_null($tur_id_item)) {
                        throw new Exception("Se encontró un item de horario sin tur_id al intentar guardar.");
                    }

                    $key = ($doc_id_item ?? 'null') . "_" . ($esp_id_item ?? 'null') . "_" . ($tur_id_item ?? 'null');

                    if (!isset($gruposNuevos[$key])) {
                        $gruposNuevos[$key] = [
                            'doc_id' => $doc_id_item,
                            'esp_id' => $esp_id_item,
                            'tur_id' => $tur_id_item,
                            'items_uc' => []
                        ];
                    }
                    $gruposNuevos[$key]['items_uc'][] = $item;
                }
            }
            foreach ($gruposNuevos as $grupoData) {
                $hor_id_maestro_grupo = $this->findOrCreateHorarioIdForGroup(
                    $co,
                    $nueva_seccion_id_grupo,
                    $nueva_fase_id_grupo,
                    $grupoData['esp_id'],
                    $grupoData['doc_id'],
                    $grupoData['tur_id']
                );

                $stmtUcHorario = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_horainicio, hor_horafin) VALUES (:ucId, :horId, :dia, :inicio, :fin)");

                foreach ($grupoData['items_uc'] as $item_uc_data) {
                    $stmt_check = $co->prepare("SELECT COUNT(*) FROM uc_horario WHERE hor_id = :hor_id AND uc_id = :uc_id AND hor_dia = :dia AND hor_horainicio = :inicio AND hor_horafin = :fin");
                    $stmt_check->execute([':hor_id' => $hor_id_maestro_grupo, ':uc_id' => $item_uc_data['uc_id'], ':dia' => $item_uc_data['dia'], ':inicio' => $item_uc_data['hora_inicio'], ':fin' => $item_uc_data['hora_fin']]);
                    if ($stmt_check->fetchColumn() == 0) {
                        $stmtUcHorario->bindParam(':ucId', $item_uc_data['uc_id']);
                        $stmtUcHorario->bindParam(':horId', $hor_id_maestro_grupo);
                        $stmtUcHorario->bindParam(':dia', $item_uc_data['dia']);
                        $stmtUcHorario->bindParam(':inicio', $item_uc_data['hora_inicio']);
                        $stmtUcHorario->bindParam(':fin', $item_uc_data['hora_fin']);
                        $stmtUcHorario->execute();
                    }
                }
            }
            $co->commit();
            $r['resultado'] = 'modificar_grupo_ok';
            $r['mensaje'] = 'Grupo de horario modificado correctamente.';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error interno del servidor: " . $e->getMessage();
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
            $this->eliminarLogicoPorSeccionFase($co, $sec_id_grupo, $fase_id_grupo);
            $co->commit();
            $r['resultado'] = 'eliminar_por_seccion_fase_ok';
            $r['mensaje'] = 'Grupo de horario eliminado (desactivado) correctamente.';
        } catch (Exception $e) {
            if ($co && $co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar grupo: " . $e->getMessage();
        }
        return $r;
    }

    public function verificarConflictoEspacioIndividual($esp_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original)
    {
        $co = $this->Con();
        $r = ['conflicto' => false];

        try {
            $stmtAnio = $co->prepare(
                "SELECT t.ani_id FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 WHERE s.sec_id = :sec_id"
            );
            $stmtAnio->execute([':sec_id' => $sec_id_actual]);
            $ani_id = $stmtAnio->fetchColumn();

            if (!$ani_id) {
                return $r;
            }

            $sql = "SELECT ts.sec_codigo, te.esp_codigo
                     FROM uc_horario AS tuh
                     JOIN tbl_horario AS th ON tuh.hor_id = th.hor_id
                     JOIN seccion_horario AS tsh ON th.hor_id = tsh.hor_id
                     JOIN tbl_seccion AS ts ON tsh.sec_id = ts.sec_id
                     JOIN tbl_trayecto AS tt ON ts.tra_id = tt.tra_id
                     JOIN tbl_espacio AS te ON th.esp_id = te.esp_id
                     WHERE
                        th.esp_id = :esp_id AND
                        tuh.hor_dia = :dia AND
                        tuh.hor_horainicio = :hora_inicio AND
                        th.fase_id = :fase_id AND
                        tt.ani_id = :ani_id AND
                        th.hor_estado = 1";

            if (!empty($sec_id_original) && !empty($fase_id_original)) {
                $sql .= " AND NOT (tsh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original)";
            }

            $sql .= " LIMIT 1";

            $stmtConflicto = $co->prepare($sql);

            $params = [
                ':esp_id' => $esp_id,
                ':dia' => $dia,
                ':hora_inicio' => $hora_inicio,
                ':fase_id' => $fase_id_actual,
                ':ani_id' => $ani_id,
            ];

            if (!empty($sec_id_original) && !empty($fase_id_original)) {
                $params[':sec_id_original'] = $sec_id_original;
                $params[':fase_id_original'] = $fase_id_original;
            }

            $stmtConflicto->execute($params);
            $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);

            if ($conflicto) {
                $r['conflicto'] = true;
                $r['mensaje'] = "Conflicto: El espacio '" . htmlspecialchars($conflicto['esp_codigo']) .
                    "' ya está ocupado el " . htmlspecialchars($dia) . " a las " . date("g:i A", strtotime($hora_inicio)) .
                    " por la sección '" . htmlspecialchars($conflicto['sec_codigo']) . "' en la misma fase y año académico.";
            }
        } catch (Exception $e) {
            error_log("Error en verificarConflictoEspacioIndividual: " . $e->getMessage());
        }
        return $r;
    }

    public function verificarConflictoDocenteIndividual($doc_id, $dia, $hora_inicio, $fase_id_actual, $sec_id_actual, $sec_id_original, $fase_id_original)
    {
        $co = $this->Con();
        $r = ['conflicto' => false];

        try {
            $stmtAnio = $co->prepare(
                "SELECT t.ani_id FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 WHERE s.sec_id = :sec_id"
            );
            $stmtAnio->execute([':sec_id' => $sec_id_actual]);
            $ani_id = $stmtAnio->fetchColumn();

            if (!$ani_id || !$doc_id) {
                return $r;
            }

            $sql = "SELECT ts.sec_codigo, d.doc_nombre, d.doc_apellido
                     FROM uc_horario AS tuh
                     JOIN tbl_horario AS th ON tuh.hor_id = th.hor_id
                     JOIN docente_horario AS tdh ON th.hor_id = tdh.hor_id
                     JOIN tbl_docente AS d ON tdh.doc_id = d.doc_id
                     JOIN seccion_horario AS tsh ON th.hor_id = tsh.hor_id
                     JOIN tbl_seccion AS ts ON tsh.sec_id = ts.sec_id
                     JOIN tbl_trayecto AS tt ON ts.tra_id = tt.tra_id
                     WHERE
                        tdh.doc_id = :doc_id AND
                        tuh.hor_dia = :dia AND
                        tuh.hor_horainicio = :hora_inicio AND
                        th.fase_id = :fase_id AND
                        tt.ani_id = :ani_id AND
                        th.hor_estado = 1";

            if (!empty($sec_id_original) && !empty($fase_id_original)) {
                $sql .= " AND NOT (tsh.sec_id = :sec_id_original AND th.fase_id = :fase_id_original)";
            }

            $sql .= " LIMIT 1";

            $stmtConflicto = $co->prepare($sql);

            $params = [
                ':doc_id' => $doc_id,
                ':dia' => $dia,
                ':hora_inicio' => $hora_inicio,
                ':fase_id' => $fase_id_actual,
                ':ani_id' => $ani_id,
            ];

            if (!empty($sec_id_original) && !empty($fase_id_original)) {
                $params[':sec_id_original'] = $sec_id_original;
                $params[':fase_id_original'] = $fase_id_original;
            }

            $stmtConflicto->execute($params);
            $conflicto = $stmtConflicto->fetch(PDO::FETCH_ASSOC);

            if ($conflicto) {
                $r['conflicto'] = true;
                $r['mensaje'] = "Conflicto: El docente " . htmlspecialchars($conflicto['doc_nombre'] . ' ' . $conflicto['doc_apellido']) .
                    " ya tiene una clase asignada el " . htmlspecialchars($dia) . " a las " . date("g:i A", strtotime($hora_inicio)) .
                    " en la sección '" . htmlspecialchars($conflicto['sec_codigo']) . "' (misma fase y año).";
            }
        } catch (Exception $e) {
            error_log("Error en verificarConflictoDocenteIndividual: " . $e->getMessage());
        }
        return $r;
    }

    public function obtenerTurnos()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT tur_id, tur_horainicio, tur_horafin
                 FROM tbl_turno
                 WHERE tur_estado = 1
                 ORDER BY tur_horainicio"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerFases()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT f.fase_id, f.tra_id, f.fase_numero, t.tra_numero, a.ani_anio
                 FROM tbl_fase f
                 JOIN tbl_trayecto t ON f.tra_id = t.tra_id
                 JOIN tbl_anio a ON t.ani_id = a.ani_id
                 WHERE f.fase_estado = 1
                 ORDER BY a.ani_anio DESC, t.tra_numero, f.fase_numero"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerUnidadesCurriculares()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT uc.uc_id, uc.uc_nombre, uc.uc_codigo, uc.tra_id
                 FROM tbl_uc uc
                 WHERE uc.uc_estado = 1
                 ORDER BY uc.uc_nombre"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ===== INICIO DE MODIFICACIÓN =====
    public function obtenerSecciones()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT s.sec_id, s.sec_codigo, s.sec_nombre, s.tra_id, t.tra_numero, a.ani_anio
                 FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 JOIN tbl_anio a ON t.ani_id = a.ani_id
                 WHERE s.sec_estado = 1 AND t.tra_estado = 1
                 ORDER BY s.sec_codigo"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    // ===== FIN DE MODIFICACIÓN =====

    public function obtenerEspacios()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT esp_id, esp_codigo, esp_tipo
                 FROM tbl_espacio
                 WHERE esp_estado = 1
                 ORDER BY esp_codigo, esp_tipo"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function obtenerDocentes()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT doc_id, doc_nombre, doc_apellido, CONCAT(doc_nombre, ' ', doc_apellido) as nombre_completo
                 FROM tbl_docente
                 WHERE doc_estado = 1
                 ORDER BY doc_apellido, doc_nombre"
            );
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
            $sql = "SELECT u.uc_id, u.uc_nombre, u.uc_codigo, u.tra_id
                    FROM tbl_uc u
                    INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id
                    WHERE ud.doc_id = :doc_id AND u.uc_estado = 1";
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
