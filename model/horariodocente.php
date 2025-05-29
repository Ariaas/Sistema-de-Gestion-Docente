<?php
require_once('model/dbconnection.php'); 
class Horariodocente extends Connection{
    private $hdo_id;
    private $hdo_lapso; 
    private $hdo_tipoactividad;
    private $hdo_descripcion;
    private $hdo_dependencia;
    private $hdo_observacion;
    private $hdo_hora;

    public function __construct(){ parent::__construct(); }

    public function getHdoId() { return $this->hdo_id; }
    public function setHdoId($hdo_id) { $this->hdo_id = $this->cleanData($hdo_id); }
    public function getHdoLapso() { return $this->hdo_lapso; }
    public function setHdoLapso($hdo_lapso) { $this->hdo_lapso = $this->cleanData($hdo_lapso); }
    public function getHdoTipoactividad() { return $this->hdo_tipoactividad; }
    public function setHdoTipoactividad($hdo_tipoactividad) { $this->hdo_tipoactividad = $this->cleanData($hdo_tipoactividad); }
    public function getHdoDescripcion() { return $this->hdo_descripcion; }
    public function setHdoDescripcion($hdo_descripcion) { $this->hdo_descripcion = $this->cleanData($hdo_descripcion); }
    public function getHdoDependencia() { return $this->hdo_dependencia; }
    public function setHdoDependencia($hdo_dependencia) { $this->hdo_dependencia = $this->cleanData($hdo_dependencia); }
    public function getHdoObservacion() { return $this->hdo_observacion; }
    public function setHdoObservacion($hdo_observacion) { $this->hdo_observacion = $this->cleanData($hdo_observacion); }
    public function getHdoHora() { return $this->hdo_hora; }
    public function setHdoHora($hdo_hora) { $this->hdo_hora = $this->cleanData($hdo_hora); }

    private function cleanData($data) {
        if ($data === null) return null;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public function obtenerLapsosParaDocente($doc_id) {
        try {
            if (empty($doc_id)) {
                return ['success' => false, 'lapsos' => [], 'message' => 'ID de docente no proporcionado.'];
            }
            $co = $this->Con();
            $sql = "SELECT DISTINCT th.hor_fase, tt.tra_anio
                    FROM tbl_horario th
                    INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                    INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                    INNER JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id
                    INNER JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id
                    WHERE dh.doc_id = :doc_id AND th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                    ORDER BY tt.tra_anio DESC, th.hor_fase ASC";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->execute();
            $lapsos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'lapsos' => $lapsos];
        } catch (PDOException $e) {
            return ['success' => false, 'lapsos' => [], 'message' => $e->getMessage()];
        }
    }


    public function Registrar($doc_id_seleccionado = null, $lapso_compuesto = null){
        $co = null; 
        try {
            if (empty($lapso_compuesto) || empty($this->hdo_tipoactividad) || empty($this->hdo_descripcion) || empty($this->hdo_dependencia) || empty($this->hdo_hora)) {
                return ['resultado' => 'error', 'mensaje' => 'Error al registrar: Faltan datos obligatorios para la actividad.'];
            }
            if ($doc_id_seleccionado === null || $doc_id_seleccionado === '') {
                 return ['resultado' => 'error', 'mensaje' => 'Error al registrar: Debe seleccionar un docente.'];
            }

            list($fase_seleccionada, $anio_seleccionado) = explode('-', $lapso_compuesto);
            if (empty($fase_seleccionada) || empty($anio_seleccionado)) {
                return ['resultado' => 'error', 'mensaje' => 'Error al registrar: Formato de lapso incorrecto.'];
            }
            $this->setHdoLapso($fase_seleccionada); 

            $co = $this->Con();
            $co->beginTransaction();

            $stmt_hdo = $co->prepare("INSERT INTO tbl_horariodocente(hdo_lapso, hdo_tipoactividad, hdo_descripcion, hdo_dependencia, hdo_observacion, hdo_hora, hdo_estado) VALUES (:hdo_lapso, :hdo_tipoactividad, :hdo_descripcion, :hdo_dependencia, :hdo_observacion, :hdo_hora, 1)");
            $stmt_hdo->bindParam(':hdo_lapso', $this->hdo_lapso); 
            $stmt_hdo->bindParam(':hdo_tipoactividad', $this->hdo_tipoactividad);
            $stmt_hdo->bindParam(':hdo_descripcion', $this->hdo_descripcion);
            $stmt_hdo->bindParam(':hdo_dependencia', $this->hdo_dependencia);
            $stmt_hdo->bindParam(':hdo_observacion', $this->hdo_observacion);
            $stmt_hdo->bindParam(':hdo_hora', $this->hdo_hora, PDO::PARAM_INT);
            $stmt_hdo->execute();
            $nuevo_hdo_id = $co->lastInsertId();

            $hor_id_encontrado = null;

            if ($doc_id_seleccionado && $nuevo_hdo_id) {
                $stmt_find_hor = $co->prepare(
                    "SELECT th.hor_id 
                     FROM tbl_horario th
                     INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                     INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                     INNER JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id
                     INNER JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id
                     WHERE dh.doc_id = :doc_id 
                       AND th.hor_fase = :fase_seleccionada 
                       AND tt.tra_anio = :anio_seleccionado
                       AND th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                     LIMIT 1" 
                );
                $stmt_find_hor->bindParam(':doc_id', $doc_id_seleccionado, PDO::PARAM_INT);
                $stmt_find_hor->bindParam(':fase_seleccionada', $fase_seleccionada); 
                $stmt_find_hor->bindParam(':anio_seleccionado', $anio_seleccionado, PDO::PARAM_INT); 
                $stmt_find_hor->execute();
                $result_hor = $stmt_find_hor->fetch(PDO::FETCH_ASSOC);

                if ($result_hor && isset($result_hor['hor_id'])) {
                    $hor_id_encontrado = $result_hor['hor_id'];
                } else {
                    $co->rollBack();
                    return ['resultado' => 'error', 'mensaje' => "Error al registrar: No se encontró un horario base (tbl_horario) para el docente en la Fase $fase_seleccionada - Año $anio_seleccionado. Verifique la configuración del horario del docente."];
                }

                if ($hor_id_encontrado) {
                    $stmt_chd = $co->prepare("INSERT INTO crear_horariodocente (hor_id, hdo_id) VALUES (:hor_id, :hdo_id)");
                    $stmt_chd->bindParam(':hor_id', $hor_id_encontrado, PDO::PARAM_INT);
                    $stmt_chd->bindParam(':hdo_id', $nuevo_hdo_id, PDO::PARAM_INT);
                    $stmt_chd->execute();
                }
            }
            
            $co->commit();
            return ['resultado' => 'registrar', 'mensaje' => 'Registro Incluido Correctamente!'];
        } catch (PDOException $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error PDO Horariodocente::Registrar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error de base de datos al registrar: ".$e->getMessage()];
        } 
        catch (Exception $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error General Horariodocente::Registrar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error general al registrar: ".$e->getMessage()];
        }
    }       

    public function Consultar(){
        try {
            $co = $this->Con();
            $sql = "SELECT DISTINCT
                        thd.hdo_id,
                        thd.hdo_lapso,      
                        tt.tra_anio,        
                        thd.hdo_tipoactividad,
                        thd.hdo_descripcion,
                        thd.hdo_dependencia,
                        thd.hdo_observacion,
                        thd.hdo_hora,
                        td.doc_id,
                        CONCAT(IFNULL(td.doc_prefijo,''), IF(td.doc_prefijo IS NOT NULL AND td.doc_prefijo != '', '. ', ''), td.doc_nombre, ' ', td.doc_apellido) AS doc_nombre_completo
                    FROM
                        tbl_horariodocente thd
                    LEFT JOIN
                        crear_horariodocente chd ON thd.hdo_id = chd.hdo_id
                    LEFT JOIN
                        tbl_horario th_linked ON chd.hor_id = th_linked.hor_id 
                    LEFT JOIN 
                        seccion_horario sh_linked ON th_linked.hor_id = sh_linked.hor_id
                    LEFT JOIN 
                        tbl_seccion ts_linked ON sh_linked.sec_id = ts_linked.sec_id
                    LEFT JOIN 
                        tbl_trayecto tt ON ts_linked.tra_id = tt.tra_id
                    LEFT JOIN
                        docente_horario dh ON chd.hor_id = dh.hor_id 
                    LEFT JOIN
                        tbl_docente td ON dh.doc_id = td.doc_id
                    WHERE
                        thd.hdo_estado = 1 
                    ORDER BY doc_nombre_completo, tt.tra_anio DESC, thd.hdo_lapso ASC, thd.hdo_tipoactividad";

            $stmt = $co->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['resultado' => 'consultar', 'mensaje' => $data];
        } catch (Exception $e) {
            error_log("Error Horariodocente::Consultar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error al consultar: ".$e->getMessage()];
        }
    }

    public function Modificar($doc_id_seleccionado = null, $lapso_compuesto = null){
        $co = null; 
        try {
            if (empty($this->hdo_id) || empty($lapso_compuesto) || empty($this->hdo_tipoactividad) || empty($this->hdo_descripcion) || empty($this->hdo_dependencia) || empty($this->hdo_hora)) {
                return ['resultado' => 'error', 'mensaje' => 'Error al modificar: Faltan datos obligatorios.'];
            }
             if ($doc_id_seleccionado === null || $doc_id_seleccionado === '') {
                 return ['resultado' => 'error', 'mensaje' => 'Error al modificar: Debe seleccionar un docente.'];
            }

            list($fase_seleccionada, $anio_seleccionado) = explode('-', $lapso_compuesto);
            if (empty($fase_seleccionada) || empty($anio_seleccionado)) {
                return ['resultado' => 'error', 'mensaje' => 'Error al modificar: Formato de lapso incorrecto.'];
            }
            $this->setHdoLapso($fase_seleccionada); 

            $co = $this->Con();
            $co->beginTransaction();

            $stmt_update_hdo = $co->prepare("UPDATE tbl_horariodocente SET hdo_lapso = :hdo_lapso, hdo_tipoactividad = :hdo_tipoactividad, hdo_descripcion = :hdo_descripcion, hdo_dependencia = :hdo_dependencia, hdo_observacion = :hdo_observacion, hdo_hora = :hdo_hora WHERE hdo_id = :hdo_id");
            $stmt_update_hdo->bindParam(':hdo_lapso', $this->hdo_lapso);
            $stmt_update_hdo->bindParam(':hdo_tipoactividad', $this->hdo_tipoactividad);
            $stmt_update_hdo->bindParam(':hdo_descripcion', $this->hdo_descripcion);
            $stmt_update_hdo->bindParam(':hdo_dependencia', $this->hdo_dependencia);
            $stmt_update_hdo->bindParam(':hdo_observacion', $this->hdo_observacion);
            $stmt_update_hdo->bindParam(':hdo_hora', $this->hdo_hora, PDO::PARAM_INT);
            $stmt_update_hdo->bindParam(':hdo_id', $this->hdo_id, PDO::PARAM_INT);
            $stmt_update_hdo->execute();
            
            
            $stmt_find_new_hor = $co->prepare(
                "SELECT th.hor_id 
                 FROM tbl_horario th
                 INNER JOIN docente_horario dh ON th.hor_id = dh.hor_id
                 INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id
                 INNER JOIN tbl_seccion ts ON sh.sec_id = ts.sec_id
                 INNER JOIN tbl_trayecto tt ON ts.tra_id = tt.tra_id
                 WHERE dh.doc_id = :doc_id 
                   AND th.hor_fase = :fase_seleccionada 
                   AND tt.tra_anio = :anio_seleccionado
                   AND th.hor_estado = 1 AND ts.sec_estado = 1 AND tt.tra_estado = 1
                 LIMIT 1"
            );
            $stmt_find_new_hor->bindParam(':doc_id', $doc_id_seleccionado, PDO::PARAM_INT);
            $stmt_find_new_hor->bindParam(':fase_seleccionada', $fase_seleccionada);
            $stmt_find_new_hor->bindParam(':anio_seleccionado', $anio_seleccionado, PDO::PARAM_INT);
            $stmt_find_new_hor->execute();
            $new_hor_result = $stmt_find_new_hor->fetch(PDO::FETCH_ASSOC);
            $new_hor_id = $new_hor_result ? $new_hor_result['hor_id'] : null;

            if (!$new_hor_id) {
                $co->rollBack();
                return ['resultado' => 'error', 'mensaje' => "Error al modificar: No se encontró un horario base para el docente en la Fase $fase_seleccionada - Año $anio_seleccionado."];
            }

            
            $stmt_upsert_chd = $co->prepare(
                "INSERT INTO crear_horariodocente (hdo_id, hor_id) VALUES (:hdo_id, :hor_id)
                 ON DUPLICATE KEY UPDATE hor_id = :hor_id_update" 
                                                               
            );
            

            $stmt_delete_old_link = $co->prepare("DELETE FROM crear_horariodocente WHERE hdo_id = :hdo_id");
            $stmt_delete_old_link->bindParam(':hdo_id', $this->hdo_id, PDO::PARAM_INT);
            $stmt_delete_old_link->execute();
            
            $stmt_insert_new_link = $co->prepare("INSERT INTO crear_horariodocente (hdo_id, hor_id) VALUES (:hdo_id, :hor_id)");
            $stmt_insert_new_link->bindParam(':hdo_id', $this->hdo_id, PDO::PARAM_INT);
            $stmt_insert_new_link->bindParam(':hor_id', $new_hor_id, PDO::PARAM_INT);
            $stmt_insert_new_link->execute();


            $co->commit();
            return ['resultado' => 'modificar', 'mensaje' => 'Registro Modificado Correctamente!'];
        } catch (PDOException $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error PDO Horariodocente::Modificar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error de base de datos al modificar: ".$e->getMessage()];
        }
        catch (Exception $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error General Horariodocente::Modificar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error general al modificar: ".$e->getMessage()];
        }
    }

    public function Eliminar(){
        $co = null; 
        try {
            if (empty($this->hdo_id)) {
                return ['resultado' => 'error', 'mensaje' => 'Error al eliminar: ID no proporcionado.'];
            }
            $co = $this->Con();
            $co->beginTransaction();
            
            $stmt = $co->prepare("UPDATE tbl_horariodocente SET hdo_estado = 0 WHERE hdo_id = :hdo_id");
            $stmt->bindParam(':hdo_id', $this->hdo_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $co->commit();
            return ['resultado' => 'eliminar', 'mensaje' => 'Registro Eliminado Correctamente!'];
        } catch (PDOException $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error PDO Horariodocente::Eliminar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error de base de datos al eliminar: ".$e->getMessage()];
        }
        catch (Exception $e) { 
            if ($co && $co->inTransaction()) { 
                $co->rollBack();
            }
            error_log("Error General Horariodocente::Eliminar: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "Error general al eliminar: ".$e->getMessage()];
        }
    }

    public function obtenerDocentes() {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT doc_id, doc_prefijo, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido, doc_nombre");
            $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'teachers' => $teachers];
        } catch (Exception $e) {
            error_log("Error Horariodocente::obtenerDocentes: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cargar docentes: ' . $e->getMessage()];
        }
    }
    
    public function obtenerUnidadesCurriculares() {
        try { $co = $this->Con(); $stmt = $co->query("SELECT uc_id, uc_nombre, uc_codigo FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_codigo"); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (Exception $e) { error_log("Error obtenerUnidadesCurriculares: " . $e->getMessage()); return []; }
    }
    public function obtenerEspacios() {
        try { $co = $this->Con(); $stmt = $co->query("SELECT esp_id, esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo"); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
        catch (Exception $e) { error_log("Error obtenerEspacios: " . $e->getMessage()); return []; }
    }
    public function obtenerSecciones() { // Incluye año y trayecto para mejor referencia
        try { 
            $co = $this->Con(); 
            $stmt = $co->query(
                "SELECT s.sec_id, s.sec_codigo, t.tra_numero, t.tra_anio 
                 FROM tbl_seccion s
                 JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                 WHERE s.sec_estado = 1 AND t.tra_estado = 1 
                 ORDER BY s.sec_codigo"
            ); 
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error obtenerSecciones: " . $e->getMessage()); return []; 
        }
    }

    public function obtenerHorarioCompletoPorDocente($doc_id, $fase_filtro = null, $anio_filtro = null) {
        $co = $this->Con();
        $response = ['resultado' => 'error', 'mensaje' => 'Error desconocido al obtener horario.'];
        try {
            if (empty($doc_id)) {
                return ['resultado' => 'error', 'mensaje' => 'ID de docente no proporcionado.'];
            }
            // Si se proporcionan filtros de fase y año, se deben usar.
            if (empty($fase_filtro) || empty($anio_filtro)) {
                 return ['resultado' => 'error', 'mensaje' => 'Fase y Año son requeridos para ver el horario específico.', 'horario_docente' => [], 'franjas_horarias' => []];
            }

            $sql = "SELECT
                        uh.hor_dia AS dia, 
                        uh.hor_inicio AS hora_inicio, 
                        uh.hor_fin AS hora_fin,
                        uc.uc_id, 
                        uc.uc_nombre, 
                        uc.uc_codigo,
                        te.esp_id, 
                        te.esp_codigo, 
                        te.esp_tipo,
                        ts.sec_id, 
                        ts.sec_codigo AS seccion_codigo,
                        th.hor_fase AS fase,
                        tt.tra_anio AS anio_trayecto -- Asegurar que tra_anio se selecciona
                    FROM 
                        tbl_docente td
                    INNER JOIN 
                        docente_horario dh ON td.doc_id = dh.doc_id
                    INNER JOIN 
                        tbl_horario th ON dh.hor_id = th.hor_id
                    INNER JOIN 
                        uc_horario uh ON th.hor_id = uh.hor_id
                    INNER JOIN 
                        tbl_uc uc ON uh.uc_id = uc.uc_id
                    INNER JOIN 
                        seccion_horario sh ON th.hor_id = sh.hor_id
                    INNER JOIN 
                        tbl_seccion ts ON sh.sec_id = ts.sec_id
                    INNER JOIN 
                        tbl_trayecto tt ON ts.tra_id = tt.tra_id
                    LEFT JOIN 
                        tbl_espacio te ON th.esp_id = te.esp_id
                    WHERE 
                        td.doc_id = :doc_id 
                        AND th.hor_estado = 1 
                        AND uc.uc_estado = 1  
                        AND ts.sec_estado = 1
                        AND tt.tra_estado = 1
                        AND (te.esp_estado = 1 OR te.esp_id IS NULL)";
            
          
            $params = [':doc_id' => $doc_id];
            if ($fase_filtro !== null) {
                $sql .= " AND th.hor_fase = :fase_filtro";
                $params[':fase_filtro'] = $fase_filtro;
            }
            if ($anio_filtro !== null) {
                $sql .= " AND tt.tra_anio = :anio_filtro";
                $params[':anio_filtro'] = $anio_filtro;
            }
            
            $sql .= " ORDER BY 
                        FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), 
                        uh.hor_inicio";

            $stmt = $co->prepare($sql);
            $stmt->execute($params);
            $horario_docente = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horario_docente)) {
                $response = ['resultado' => 'vacio', 'mensaje' => "El docente no tiene horario de clases asignado para la Fase $fase_filtro - Año $anio_filtro.", 'horario_docente' => [], 'franjas_horarias' => []];
            } else {
                $franjas_obj = [];
                foreach($horario_docente as $clase) {
                   $franja_key = $clase['hora_inicio'] . '-' . $clase['hora_fin'];
                   if (!isset($franjas_obj[$franja_key])) { 
                       $franjas_obj[$franja_key] = ['inicio' => $clase['hora_inicio'], 'fin' => $clase['hora_fin']];
                   }
                }
                $franjas_final = array_values($franjas_obj);
                usort($franjas_final, function($a, $b) { return strcmp($a['inicio'], $b['inicio']); });
                $response = ['resultado' => 'ok', 'horario_docente' => $horario_docente, 'franjas_horarias' => $franjas_final];
            }
        } catch (PDOException $e) {
            error_log("Error PDO obtenerHorarioCompletoPorDocente: " . $e->getMessage());
            $response = ['resultado' => 'error', 'mensaje' => "Error de base de datos al obtener horario: " . $e->getMessage(), 'horario_docente' => [], 'franjas_horarias' => []];
        }
        catch (Exception $e) {
            error_log("Error General obtenerHorarioCompletoPorDocente: " . $e->getMessage());
            $response = ['resultado' => 'error', 'mensaje' => "Error general al obtener horario: " . $e->getMessage(), 'horario_docente' => [], 'franjas_horarias' => []];
        }
        return $response;
    }
}
?>