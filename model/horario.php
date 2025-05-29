<?php
require_once('model/dbconnection.php'); 

class Horario extends Connection
{
    private $hor_id;
    private $esp_id;
    private $hor_fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function setId($hor_id) { $this->hor_id = $hor_id; }
    public function setEspacio($esp_id) { $this->esp_id = $esp_id; }
    public function setFase($hor_fase_param) { $this->hor_fase = $hor_fase_param; }

    public function getId() { return $this->hor_id; }
    public function getEspacio() { return $this->esp_id; }
    public function getFase() { return $this->hor_fase; }

    public function verificarHorarioExistente($sec_id, $hor_fase) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $sql = "SELECT COUNT(th.hor_id) as cuenta
                    FROM tbl_horario th
                    INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    WHERE sh.sec_id = :sec_id
                      AND th.hor_fase = :hor_fase
                      AND th.hor_estado = 1"; 
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
            $stmt->bindParam(':hor_fase', $hor_fase);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($resultado['cuenta'] > 0);
        } catch (Exception $e) {
            error_log("Modelo Horario::verificarHorarioExistente - Error: " . $e->getMessage());
            return true; 
    }
    }
    private function findOrCreateHorarioIdForGroup($co, $sec_id_clase, $hor_fase_clase, $esp_id_clase, $doc_id_clase) {
        $sql = "SELECT th.hor_id
                FROM tbl_horario th
                INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                WHERE sh.sec_id = :sec_id
                   AND th.hor_fase = :hor_fase
                   AND th.esp_id = :esp_id
                   AND dh.doc_id = :doc_id
                   AND th.hor_estado = 1 LIMIT 1"; 

        $params = [
            ':sec_id' => $sec_id_clase,
            ':hor_fase' => $hor_fase_clase,
            ':esp_id' => $esp_id_clase, 
            ':doc_id' => $doc_id_clase  
        ];

        $stmt_find = $co->prepare($sql);
        $stmt_find->execute($params);
        $hor_id_found = $stmt_find->fetchColumn();

        if ($hor_id_found) {
            error_log("findOrCreateHorarioIdForGroup: Encontrado hor_id existente: $hor_id_found para sec $sec_id_clase, fase $hor_fase_clase, esp $esp_id_clase, doc $doc_id_clase");
            return $hor_id_found;
        } else {
            error_log("findOrCreateHorarioIdForGroup: No se encontró hor_id activo. Creando uno nuevo para sec $sec_id_clase, fase $hor_fase_clase, esp $esp_id_clase, doc $doc_id_clase");
            $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_fase, hor_estado) VALUES (:esp_id, :hor_fase, 1)");
            $stmt_insert_hor->bindParam(':esp_id', $esp_id_clase, PDO::PARAM_INT); 
            $stmt_insert_hor->bindParam(':hor_fase', $hor_fase_clase);
            $stmt_insert_hor->execute();
            $new_hor_id = $co->lastInsertId();
            error_log("findOrCreateHorarioIdForGroup: Nuevo hor_id creado: $new_hor_id");

            $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
            $stmt_sh->bindParam(':sec_id', $sec_id_clase);
            $stmt_sh->bindParam(':hor_id', $new_hor_id);
            $stmt_sh->execute();
            error_log("findOrCreateHorarioIdForGroup: Insertado en seccion_horario para hor_id $new_hor_id, sec_id $sec_id_clase");
            $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
            $stmt_dh->bindParam(':doc_id', $doc_id_clase, PDO::PARAM_INT); 
            $stmt_dh->bindParam(':hor_id', $new_hor_id);
            $stmt_dh->execute();
            error_log("findOrCreateHorarioIdForGroup: Insertado en docente_horario para hor_id $new_hor_id, doc_id $doc_id_clase");

            return $new_hor_id;
        }
    }

    public function RegistrarClaseIndividual($esp_id_clase, $hor_fase_clase, $dia_clase, $hora_inicio_clase, $hora_fin_clase, $sec_id_clase, $doc_id_clase, $uc_id_clase)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        try {
            if (empty($esp_id_clase)) {
                throw new Exception("El Espacio (esp_id) es requerido para registrar la clase.");
            }
            if (empty($doc_id_clase)) {
                throw new Exception("El Docente (doc_id) es requerido para registrar la clase.");
            }
            if (empty($hor_fase_clase) || empty($dia_clase) || empty($hora_inicio_clase) || empty($hora_fin_clase) || empty($sec_id_clase) || empty($uc_id_clase)) {
                throw new Exception("Fase, día, horas, sección y UC son requeridos para registrar la clase.");
            }

            $hor_id_grupo = $this->findOrCreateHorarioIdForGroup($co, $sec_id_clase, $hor_fase_clase, $esp_id_clase, $doc_id_clase);

            $stmt_check_uc = $co->prepare("SELECT COUNT(*) FROM uc_horario
                                          WHERE uc_id = :uc_id AND hor_id = :hor_id
                                          AND hor_dia = :hor_dia AND hor_inicio = :hor_inicio AND hor_fin = :hor_fin");
            $stmt_check_uc->bindParam(':uc_id', $uc_id_clase);
            $stmt_check_uc->bindParam(':hor_id', $hor_id_grupo);
            $stmt_check_uc->bindParam(':hor_dia', $dia_clase);
            $stmt_check_uc->bindParam(':hor_inicio', $hora_inicio_clase);
            $stmt_check_uc->bindParam(':hor_fin', $hora_fin_clase);
            $stmt_check_uc->execute();

            if ($stmt_check_uc->fetchColumn() > 0) {
                $r['resultado'] = 'registrar_clase_ok_existente';
                $r['mensaje'] = 'La clase ya existía (misma UC, día, hora para mismo Docente/Espacio/Sección/Fase) y no fue re-registrada.';
                return $r;
            }

            $stmt_insert_uc_hor = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_inicio, hor_fin) VALUES (:uc_id, :hor_id, :hor_dia, :hor_inicio, :hor_fin)");
            $stmt_insert_uc_hor->bindParam(':uc_id', $uc_id_clase);
            $stmt_insert_uc_hor->bindParam(':hor_id', $hor_id_grupo);
            $stmt_insert_uc_hor->bindParam(':hor_dia', $dia_clase);
            $stmt_insert_uc_hor->bindParam(':hor_inicio', $hora_inicio_clase);
            $stmt_insert_uc_hor->bindParam(':hor_fin', $hora_fin_clase);
            $stmt_insert_uc_hor->execute();

            $r['resultado'] = 'registrar_clase_ok';
            $r['mensaje'] = 'Clase registrada correctamente.';

        } catch (Exception $e) {
            error_log("Modelo Horario::RegistrarClaseIndividual - Error: " . $e->getMessage());
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error registrando clase: " . $e->getMessage();
        }
        return $r;
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
                    th.hor_fase,
                    tt.tra_numero,
                    tt.tra_anio
                 FROM tbl_horario th
                 JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                 JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id
                 JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id
                 WHERE th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                 ORDER BY ts.sec_codigo, th.hor_fase"
            );
            $r['resultado'] = 'consultar_agrupado';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Modelo Horario::ListarAgrupado - Error: " . $e->getMessage());
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al listar horarios agrupados: " . $e->getMessage();
        }
        return $r;
    }

    public function ConsultarDetallesParaGrupo($sec_id, $hor_fase) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $schedule_grid_items = [];

        try {
            $stmtHorariosGrupo = $co->prepare(
                "SELECT th.hor_id, th.esp_id AS hor_esp_id, dh.doc_id AS hor_doc_id
                 FROM tbl_horario th
                 INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                 INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                 WHERE sh.sec_id = :sec_id AND th.hor_fase = :hor_fase AND th.hor_estado = 1"
            );
            $stmtHorariosGrupo->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
            $stmtHorariosGrupo->bindParam(':hor_fase', $hor_fase);
            $stmtHorariosGrupo->execute();
            $gruposDeHorario = $stmtHorariosGrupo->fetchAll(PDO::FETCH_ASSOC);

            if (empty($gruposDeHorario)) {
                $r['mensaje'] = [];
                $r['resultado'] = 'ok';
                return $r;
            }

            $stmtUcItems = $co->prepare(
                "SELECT uc_id, hor_dia, hor_inicio, hor_fin
                 FROM uc_horario
                 WHERE hor_id = :id_hor_grupo"
            );

            foreach ($gruposDeHorario as $grupo) {
                $stmtUcItems->bindParam(':id_hor_grupo', $grupo['hor_id'], PDO::PARAM_INT);
                $stmtUcItems->execute();
                $ucItems = $stmtUcItems->fetchAll(PDO::FETCH_ASSOC);

                foreach ($ucItems as $uc) {
                    $schedule_grid_items[] = [
                        'uc_id' => $uc['uc_id'],
                        'doc_id' => $grupo['hor_doc_id'],
                        'esp_id' => $grupo['hor_esp_id'],
                        'dia' => $uc['hor_dia'],
                        'hora_inicio' => $uc['hor_inicio'],
                        'hora_fin' => $uc['hor_fin'],
                        'sec_id' => intval($sec_id)
                    ];
                }
            }

            $r['mensaje'] = $schedule_grid_items;
            $r['resultado'] = 'ok';

        } catch (Exception $e) {
            error_log("Modelo Horario::ConsultarDetallesParaGrupo - Error: " . $e->getMessage());
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error consultando detallado: " . $e->getMessage();
        }
        return $r;
    }

    private function eliminarLogicoPorSeccionFase($co, $sec_id, $hor_fase) {
        $stmtHorIds = $co->prepare(
            "SELECT th.hor_id FROM tbl_horario th
             INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
             WHERE sh.sec_id = :sec_id AND th.hor_fase = :hor_fase AND th.hor_estado = 1"
        );
        $stmtHorIds->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
        $stmtHorIds->bindParam(':hor_fase', $hor_fase);
        $stmtHorIds->execute();
        $horarios_a_eliminar = $stmtHorIds->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($horarios_a_eliminar)) {
            $placeholders = implode(',', array_fill(0, count($horarios_a_eliminar), '?'));
            $stmtUpdate = $co->prepare("UPDATE tbl_horario SET hor_estado = 0 WHERE hor_id IN ($placeholders)");
            $stmtUpdate->execute($horarios_a_eliminar);
            error_log("eliminarLogicoPorSeccionFase: Desactivados hor_id: " . implode(', ', $horarios_a_eliminar));
        } else {
            error_log("eliminarLogicoPorSeccionFase: No se encontraron horarios activos para desactivar para sec_id $sec_id, hor_fase $hor_fase.");
        }
    }

    public function ModificarGrupo($sec_id_original_grupo, $hor_fase_original_grupo, $nueva_seccion_id_grupo, $nueva_hor_fase_grupo, $items_horario_json) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        $items_horario = json_decode($items_horario_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $r['resultado'] = 'error'; $r['mensaje'] = 'Error decodificando items: ' . json_last_error_msg(); return $r;
        }

        try {
            $co->beginTransaction();

            $this->eliminarLogicoPorSeccionFase($co, $sec_id_original_grupo, $hor_fase_original_grupo);
            
            $gruposNuevos = [];
            if (is_array($items_horario)) {
                foreach ($items_horario as $item) {
                    if (empty($item['uc_id'])) continue; 
                    
                    $doc_id_item = isset($item['doc_id']) && !empty($item['doc_id']) ? $item['doc_id'] : null;
                    $esp_id_item = isset($item['esp_id']) && !empty($item['esp_id']) ? $item['esp_id'] : null;

                    $key = ($doc_id_item ?? 'null') . "_" . ($esp_id_item ?? 'null');
                    
                    if (!isset($gruposNuevos[$key])) {
                        $gruposNuevos[$key] = [
                            'doc_id' => $doc_id_item,
                            'esp_id' => $esp_id_item,
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
                    $nueva_hor_fase_grupo, 
                    $grupoData['esp_id'], 
                    $grupoData['doc_id']
                );

                $stmtUcHorario = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_inicio, hor_fin) VALUES (:ucId, :horId, :dia, :inicio, :fin)");
                foreach ($grupoData['items_uc'] as $item_uc_data) {
                    $stmt_check = $co->prepare("SELECT COUNT(*) FROM uc_horario WHERE hor_id = :hor_id AND uc_id = :uc_id AND hor_dia = :dia AND hor_inicio = :inicio AND hor_fin = :fin");
                    $stmt_check->execute([
                        ':hor_id' => $hor_id_maestro_grupo,
                        ':uc_id' => $item_uc_data['uc_id'],
                        ':dia' => $item_uc_data['dia'],
                        ':inicio' => $item_uc_data['hora_inicio'],
                        ':fin' => $item_uc_data['hora_fin']
                    ]);
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
            if ($co->inTransaction()) { $co->rollBack(); }
            error_log("Modelo Horario::ModificarGrupo - Error: " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine());
            $r['resultado'] = 'error'; $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function EliminarPorSeccionFase($sec_id_grupo, $hor_fase_grupo) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $co->beginTransaction();
            $this->eliminarLogicoPorSeccionFase($co, $sec_id_grupo, $hor_fase_grupo);
            $co->commit();
            $r['resultado'] = 'eliminar_por_seccion_fase_ok';
            $r['mensaje'] = 'Grupo de horario eliminado (desactivado) correctamente.';
        } catch (Exception $e) {
            if ($co && $co->inTransaction()) { $co->rollBack(); }
            error_log("Modelo Horario::EliminarPorSeccionFase - Error: " . $e->getMessage());
            $r['resultado'] = 'error'; $r['mensaje'] = "Error al eliminar grupo: " . $e->getMessage();
        }
        return $r;
    }

    public function ModificarClaseIndividual($hor_id_clase_original, $esp_id_clase, $hor_fase_clase, $dia_clase, $hora_inicio_clase, $hora_fin_clase, $sec_id_clase, $doc_id_clase, $uc_id_clase)
    {
        error_log("ModificarClaseIndividual fue llamada, pero se recomienda usar ModificarGrupo.");
        $r['resultado'] = 'error'; $r['mensaje'] = 'La modificación de clase individual ha sido integrada en la modificación de grupo. Utilice esa función.';
        return $r;
    }

    public function EliminarClaseIndividual($hor_id_clase_param_dummy)
    {
        error_log("EliminarClaseIndividual fue llamada, pero se recomienda usar EliminarPorSeccionFase o ModificarGrupo (para vaciarlo).");
        $r['resultado'] = 'error'; $r['mensaje'] = 'La eliminación de clase individual ha sido integrada en la modificación/eliminación de grupo.';
        return $r;
    }

    public function VerHorarioClaseIndividual($hor_id_param_dummy)
    {
        $r['resultado'] = 'error'; $r['mensaje'] = 'Ver clase individual no es directamente aplicable con la nueva lógica de grupo. Use "Ver Horario" del grupo.';
        return $r;
    }

    public function obtenerUnidadesCurriculares(){
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
            error_log("Error obtenerUnidadesCurriculares: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerSecciones(){
        try {
            $co = $this->Con();
            $stmt = $co->query(
                "SELECT s.sec_id, s.sec_codigo, s.tra_id, t.tra_numero, t.tra_anio
                 FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 WHERE s.sec_estado = 1 AND t.tra_estado = 1
                 ORDER BY s.sec_codigo"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerSecciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEspacios(){
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
            error_log("Error obtenerEspacios: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDocentes() {
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
            error_log("Error obtenerDocentes: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerUcPorDocenteYTrayecto($doc_id, $tra_id = null) {
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
            error_log("Modelo Horario::obtenerUcPorDocenteYTrayecto - Error: " . $e->getMessage());
            return [];
        }
    }
}
?>