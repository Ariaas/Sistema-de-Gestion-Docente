<?php
require_once('model/dbconnection.php');

class Horario extends Connection
{
    // Propiedades
    private $hor_id;
    private $esp_id;
    private $hor_fase;
    // private $hor_estado; // Se asume que tbl_horario tiene hor_estado

    public function __construct()
    {
        parent::__construct();
    }

    // Setters y Getters
    public function setId($hor_id) { $this->hor_id = $hor_id; }
    public function setEspacio($esp_id) { $this->esp_id = $esp_id; }
    public function setFase($hor_fase) { $this->hor_fase = $hor_fase; }
    
    public function getId() { return $this->hor_id; }
    public function getEspacio() { return $this->esp_id; }
    public function getFase() { return $this->hor_fase; }

    public function Registrar($dia, $hora_inicio, $hora_fin, $sec_id, $doc_id, $uc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            // Validación de datos de entrada
            if (is_null($this->esp_id) || empty($this->esp_id)) {
                throw new Exception("Espacio (aula/laboratorio) no ha sido seleccionado para la clase.");
            }
            if (is_null($this->hor_fase) || empty($this->hor_fase)) {
                throw new Exception("La fase del horario no ha sido seleccionada.");
            }
            if (is_null($uc_id) || empty($uc_id)) {
                throw new Exception("La unidad curricular no ha sido seleccionada para la clase.");
            }
            if (is_null($sec_id) || empty($sec_id)) {
                throw new Exception("La sección no ha sido seleccionada para la clase.");
            }
            if (is_null($doc_id) || empty($doc_id)) {
                throw new Exception("El docente no ha sido seleccionado para la clase.");
            }
            if (is_null($dia) || empty($dia)) {
                throw new Exception("El día no ha sido seleccionado para la clase.");
            }
            if (is_null($hora_inicio) || empty($hora_inicio)) {
                throw new Exception("La hora de inicio no ha sido seleccionada para la clase.");
            }
            if (is_null($hora_fin) || empty($hora_fin)) {
                throw new Exception("La hora de fin no ha sido seleccionada para la clase.");
            }

            $co->beginTransaction();

            // 1. Buscar o crear hor_id para la combinación (esp_id, hor_fase) en tbl_horario
            $stmt_hor = $co->prepare("SELECT hor_id FROM tbl_horario WHERE esp_id = :esp_id AND hor_fase = :hor_fase AND hor_estado = 1");
            $stmt_hor->bindParam(':esp_id', $this->esp_id);
            $stmt_hor->bindParam(':hor_fase', $this->hor_fase);
            $stmt_hor->execute();
            $hor_id = $stmt_hor->fetchColumn();

            if (!$hor_id) {
                $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_fase, hor_estado) VALUES (:esp_id, :hor_fase, 1)");
                $stmt_insert_hor->bindParam(':esp_id', $this->esp_id);
                $stmt_insert_hor->bindParam(':hor_fase', $this->hor_fase);
                $stmt_insert_hor->execute();
                $hor_id = $co->lastInsertId();
            }

            // 2. Verificar si ya existe una entrada idéntica en uc_horario
            $stmt_check_uc = $co->prepare("SELECT COUNT(*) FROM uc_horario 
                                          WHERE uc_id = :uc_id AND hor_id = :hor_id 
                                          AND hor_dia = :hor_dia AND hor_inicio = :hor_inicio AND hor_fin = :hor_fin");
            $stmt_check_uc->bindParam(':uc_id', $uc_id);
            $stmt_check_uc->bindParam(':hor_id', $hor_id);
            $stmt_check_uc->bindParam(':hor_dia', $dia);
            $stmt_check_uc->bindParam(':hor_inicio', $hora_inicio);
            $stmt_check_uc->bindParam(':hor_fin', $hora_fin);
            $stmt_check_uc->execute();
            if ($stmt_check_uc->fetchColumn() > 0) {
                throw new Exception("Conflicto: Ya existe una clase para la misma Unidad Curricular, en el mismo Espacio/Fase, día y bloque horario.");
            }

            // 3. Insertar en uc_horario (esta tabla NO tiene sec_id ni doc_id según tu schema)
            $stmt_insert_uc_hor = $co->prepare("INSERT INTO uc_horario (
                uc_id, hor_id, hor_dia, hor_inicio, hor_fin
            ) VALUES (
                :uc_id, :hor_id, :hor_dia, :hor_inicio, :hor_fin
            )");
            $stmt_insert_uc_hor->bindParam(':uc_id', $uc_id);
            $stmt_insert_uc_hor->bindParam(':hor_id', $hor_id);
            $stmt_insert_uc_hor->bindParam(':hor_dia', $dia);
            $stmt_insert_uc_hor->bindParam(':hor_inicio', $hora_inicio);
            $stmt_insert_uc_hor->bindParam(':hor_fin', $hora_fin);
            $stmt_insert_uc_hor->execute();
            
            // 4. Insertar en seccion_horario (tabla intermedia para la relación seccion-horario)
            // INSERT IGNORE evita errores si la relación ya existe para este hor_id.
            // Asume que un hor_id (bloque espacio/fase) tiene UNA sección.
            $stmt_sh = $co->prepare("INSERT IGNORE INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
            $stmt_sh->bindParam(':sec_id', $sec_id);
            $stmt_sh->bindParam(':hor_id', $hor_id);
            $stmt_sh->execute();
            
            // 5. Insertar en docente_horario (tabla intermedia para la relación docente-horario)
            // Asume que un hor_id (bloque espacio/fase) tiene UN docente.
            $stmt_dh = $co->prepare("INSERT IGNORE INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
            $stmt_dh->bindParam(':doc_id', $doc_id);
            $stmt_dh->bindParam(':hor_id', $hor_id);
            $stmt_dh->execute();
            
            $co->commit();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Clase registrada correctamente en el horario.';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            // Consulta ajustada para usar seccion_horario y docente_horario correctamente
            $stmt = $co->prepare("
                SELECT 
                    h.hor_id,
                    e.esp_codigo as espacio, 
                    h.hor_fase as fase, 
                    s.sec_codigo as seccion, 
                    tu.uc_codigo as cod_unidad_curricular,
                    tu.uc_nombre as unidad_curricular,
                    CONCAT(d.doc_nombre, ' ', d.doc_apellido) as docente,
                    uh.hor_dia as dia, 
                    uh.hor_inicio as hora_inicio, 
                    uh.hor_fin as hora_fin
                FROM tbl_horario h
                JOIN tbl_espacio e ON h.esp_id = e.esp_id
                JOIN uc_horario uh ON h.hor_id = uh.hor_id /* uh es alias de uc_horario */
                JOIN tbl_uc tu ON uh.uc_id = tu.uc_id
                LEFT JOIN seccion_horario sh ON h.hor_id = sh.hor_id /* sh es alias de seccion_horario. El JOIN es con h.hor_id */
                LEFT JOIN tbl_seccion s ON sh.sec_id = s.sec_id     /* s es alias de tbl_seccion. El JOIN es con sh.sec_id */
                LEFT JOIN docente_horario dh ON h.hor_id = dh.hor_id /* dh es alias de docente_horario. El JOIN es con h.hor_id */
                LEFT JOIN tbl_docente d ON dh.doc_id = d.doc_id       /* d es alias de tbl_docente. El JOIN es con dh.doc_id */
                WHERE h.hor_estado = 1
                ORDER BY e.esp_codigo, h.hor_fase, uh.hor_dia, uh.hor_inicio
            ");
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $resultados; 
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al listar horarios: " . $e->getMessage();
        }
        
        return $r;
    }

    public function Modificar($dia, $hora_inicio, $hora_fin, $sec_id, $doc_id, $uc_id)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            if (is_null($this->hor_id) || empty($this->hor_id)) {
                 throw new Exception("ID de Horario no especificado para la modificación.");
            }
             if (is_null($this->esp_id) || empty($this->esp_id)) { // esp_id para tbl_horario
                throw new Exception("Espacio no ha sido seleccionado para la modificación del bloque de horario.");
            }
            if (is_null($this->hor_fase) || empty($this->hor_fase)) { // hor_fase para tbl_horario
                throw new Exception("Fase no ha sido seleccionada para la modificación del bloque de horario.");
            }
            // Validaciones para los demás parámetros ($dia, $hora_inicio, etc.) si son obligatorios
            if (is_null($uc_id) || empty($uc_id)) throw new Exception("Unidad curricular no especificada.");
            if (is_null($dia) || empty($dia)) throw new Exception("Día no especificado.");
            if (is_null($hora_inicio) || empty($hora_inicio)) throw new Exception("Hora de inicio no especificada.");
            if (is_null($hora_fin) || empty($hora_fin)) throw new Exception("Hora de fin no especificada.");
            if (is_null($sec_id) || empty($sec_id)) throw new Exception("Sección no especificada.");
            if (is_null($doc_id) || empty($doc_id)) throw new Exception("Docente no especificado.");


            $co->beginTransaction(); 

            // 1. Actualizar tbl_horario (la cabecera del bloque)
            $stmt_hor_update = $co->prepare("UPDATE tbl_horario SET
                esp_id = :esp_id,
                hor_fase = :hor_fase
                WHERE hor_id = :hor_id_bloque"); // Usar un nombre diferente para el placeholder
            $stmt_hor_update->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_hor_update->bindParam(':esp_id', $this->esp_id);
            $stmt_hor_update->bindParam(':hor_fase', $this->hor_fase);
            $stmt_hor_update->execute();
            
            // 2. Actualizar la entrada en uc_horario.
            // Esta lógica asume que se está modificando UNA clase específica DENTRO del bloque this->hor_id.
            // Para ello, el frontend DEBERÍA enviar los identificadores de la clase original a modificar
            // (ej. uc_id original, dia original, hora_inicio original) si estos pueden cambiar.
            // Si solo se actualiza la UC, sección o docente de una franja horaria existente:
            // Se asume que $this->hor_id es el ID del bloque que se está editando, y los parámetros
            // $dia, $hora_inicio, $hora_fin, $uc_id son los NUEVOS valores para una entrada específica.
            // Es complejo sin un ID único para la fila de uc_horario.
            // Una estrategia simplificada podría ser eliminar la entrada vieja y crear una nueva si los campos clave (dia, hora) cambian.
            // O, si el frontend asegura que el 'pone' carga una clase específica y se modifica esa:
            // Necesitaríamos el uc_id original de la fila que se cargó en el formulario de modificación.
            // Esta parte es la más compleja de Modificar y depende de cómo el frontend identifique la fila a cambiar.
            // Por ahora, si se modifica una entrada en la lista, asumimos que los campos del formulario principal
            // corresponden a UNA entrada de uc_horario que estaba asociada a $this->hor_id.
            // Para hacer un UPDATE específico, necesitaríamos saber qué uc_id, dia, hora_inicio originales se están cambiando.
            // Una forma es que el formulario de modificación envíe el uc_id_original, dia_original, hora_inicio_original.
            // Si no, este UPDATE es muy general:
            $stmt_uc_update = $co->prepare("UPDATE uc_horario SET
                uc_id = :new_uc_id, 
                hor_dia = :new_hor_dia,
                hor_inicio = :new_hor_inicio,
                hor_fin = :new_hor_fin
                WHERE hor_id = :hor_id_bloque 
                AND uc_id = :current_uc_id /* Asumiendo que uc_id es el campo que se modifica */
                AND hor_dia = :current_hor_dia /* y que dia y hora_inicio identifican la fila a modificar */
                AND hor_inicio = :current_hor_inicio" 
                /* Nota: current_uc_id, current_hor_dia, current_hor_inicio deben venir del estado original de la fila que se edita */
            );
            // Esta es una suposición de cómo podría funcionar y requeriría que el JS envíe estos 'current_'.
            // Si no, se puede hacer un DELETE de la entrada específica de uc_horario y luego un INSERT.

            // 3. Actualizar seccion_horario (para el bloque hor_id)
            // Se asume que la sección es para todo el bloque hor_id.
            $stmt_del_sh = $co->prepare("DELETE FROM seccion_horario WHERE hor_id = :hor_id_bloque");
            $stmt_del_sh->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_del_sh->execute();
            if (!empty($sec_id)) { // Solo insertar si se provee una sección
                $stmt_ins_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id_bloque)");
                $stmt_ins_sh->bindParam(':sec_id', $sec_id);
                $stmt_ins_sh->bindParam(':hor_id_bloque', $this->hor_id);
                $stmt_ins_sh->execute();
            }

            // 4. Actualizar docente_horario (para el bloque hor_id)
            $stmt_del_dh = $co->prepare("DELETE FROM docente_horario WHERE hor_id = :hor_id_bloque");
            $stmt_del_dh->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_del_dh->execute();
            if (!empty($doc_id)) { // Solo insertar si se provee un docente
                $stmt_ins_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id_bloque)");
                $stmt_ins_dh->bindParam(':doc_id', $doc_id);
                $stmt_ins_dh->bindParam(':hor_id_bloque', $this->hor_id);
                $stmt_ins_dh->execute();
            }
            
            $co->commit(); 
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Bloque de Horario Modificado Correctamente.';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack(); 
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al modificar: " . $e->getMessage();
        }
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            if (is_null($this->hor_id) || empty($this->hor_id)) {
                 throw new Exception("ID de Horario no especificado para la eliminación.");
            }
            $co->beginTransaction();

            // Eliminar de uc_horario todas las clases asociadas a este tbl_horario.hor_id
            $stmt_del_uc_h = $co->prepare("DELETE FROM uc_horario WHERE hor_id = :hor_id_bloque");
            $stmt_del_uc_h->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_del_uc_h->execute();

            // Eliminar de seccion_horario
            $stmt_del_sh = $co->prepare("DELETE FROM seccion_horario WHERE hor_id = :hor_id_bloque");
            $stmt_del_sh->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_del_sh->execute();

            // Eliminar de docente_horario
            $stmt_del_dh = $co->prepare("DELETE FROM docente_horario WHERE hor_id = :hor_id_bloque");
            $stmt_del_dh->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_del_dh->execute();
            
            // Finalmente, marcar como inactivo (o eliminar) de tbl_horario
            $stmt_inactive_h = $co->prepare("UPDATE tbl_horario SET hor_estado = 0 WHERE hor_id = :hor_id_bloque");
            $stmt_inactive_h->bindParam(':hor_id_bloque', $this->hor_id);
            $stmt_inactive_h->execute();

            // O para borrado físico:
            // $stmt_del_h = $co->prepare("DELETE FROM tbl_horario WHERE hor_id = :hor_id_bloque");
            // $stmt_del_h->bindParam(':hor_id_bloque', $this->hor_id);
            // $stmt_del_h->execute();

            $co->commit();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Bloque de Horario y sus clases asociadas eliminados (o inactivados) correctamente.';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar: " . $e->getMessage();
        }
        return $r;
    }

    public function VerHorario($hor_id_param)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        
        try {
            // Esta consulta asume que $hor_id_param es el ID del bloque de tbl_horario.
            // Carga la información del bloque y la primera clase asociada que encuentre.
            // También carga la sección y docente asociados a ESE BLOQUE hor_id.
            $stmt = $co->prepare("
                SELECT 
                    h.hor_id, 
                    h.esp_id, 
                    h.hor_fase as fase, 
                    uh.hor_dia as dia,       
                    uh.hor_inicio,           
                    uh.hor_fin,              
                    sh.sec_id,               
                    uh.uc_id,                
                    dh.doc_id                
                FROM tbl_horario h
                LEFT JOIN uc_horario uh ON h.hor_id = uh.hor_id  /* Toma la primera clase de este bloque */
                LEFT JOIN seccion_horario sh ON h.hor_id = sh.hor_id /* Sección del bloque */
                LEFT JOIN docente_horario dh ON h.hor_id = dh.hor_id /* Docente del bloque */
                WHERE h.hor_id = :hor_id_bloque AND h.hor_estado = 1
                LIMIT 1 /* Es importante para asegurar que solo se obtenga una fila si hay múltiples clases en uh para este h.hor_id */
            ");
            $stmt->bindParam(':hor_id_bloque', $hor_id_param);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                $r['resultado'] = 'consultar';
                $r['mensaje'] = $resultado;
            } else {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Horario no encontrado o inactivo.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al ver horario: " . $e->getMessage();
        }
        return $r;
    }

    // Métodos para cargar selects (sin cambios respecto a versiones anteriores que funcionaban)
    public function obtenerUnidadesCurriculares()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT uc_id, uc_nombre, uc_codigo FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerUnidadesCurriculares: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerSecciones()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT sec_id, sec_codigo FROM tbl_seccion WHERE sec_estado = 1 ORDER BY sec_codigo");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerSecciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEspacios()
    {
        try {
            $co = $this->Con();
            $stmt = $co->query("SELECT esp_id, esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1 ORDER BY esp_codigo");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerEspacios: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDocentes() // Corregido nombre de función
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->query("SELECT doc_id, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido, doc_nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerDocentes: " . $e->getMessage());
            return [];
        }
    }
}
?>