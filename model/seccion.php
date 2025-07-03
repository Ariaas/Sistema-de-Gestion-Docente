<?php
require_once('model/dbconnection.php');

class Seccion extends Connection 
{
    public function __construct()
    {
        parent::__construct();
    }

    private function determinarFaseActual() {
        try {
            $stmt = $this->Con()->prepare("SELECT ani_apertura_fase1, ani_cierra_fase1, ani_apertura_fase2, ani_cierra_fase2 FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt->execute();
            $fechas = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($fechas) {
                $hoy = new DateTime();
           
                $apertura_f1 = new DateTime($fechas['ani_apertura_fase1']);
                $cierre_f1 = new DateTime($fechas['ani_cierra_fase1']);
                $cierre_f1->setTime(23, 59, 59);

                $apertura_f2 = new DateTime($fechas['ani_apertura_fase2']);
                $cierre_f2 = new DateTime($fechas['ani_cierra_fase2']);
                $cierre_f2->setTime(23, 59, 59);
    
                if ($hoy >= $apertura_f1 && $hoy <= $cierre_f1) {
                    return 'fase1';
                } elseif ($hoy >= $apertura_f2 && $hoy <= $cierre_f2) {
                    return 'fase2';
                }
            }
            return 'ninguna';
        } catch (Exception $e) {
            error_log("Error en determinarFaseActual: " . $e->getMessage());
            return 'ninguna';
        }
    }

    public function EjecutarPromocionAutomatica() {
        
        if ($this->determinarFaseActual() !== 'fase2' || isset($_SESSION['promocion_f2_ejecutada_session'])) {
            return null;
        }
    
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            $stmt_anio = $co->prepare("SELECT ani_id FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 LIMIT 1");
            $stmt_anio->execute();
            $anio_activo = $stmt_anio->fetch(PDO::FETCH_ASSOC);
    
            if (!$anio_activo) {
                 $_SESSION['promocion_f2_ejecutada_session'] = true;
                 return null;
            }
            $ani_id = $anio_activo['ani_id'];
    
            $stmt_secciones_f1 = $co->prepare("SELECT sec_id, sec_codigo, sec_cantidad FROM tbl_seccion WHERE ani_id = :ani_id AND sec_estado = 1 AND sec_codigo LIKE '_1%'");
            $stmt_secciones_f1->execute([':ani_id' => $ani_id]);
            $secciones_origen = $stmt_secciones_f1->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($secciones_origen)) {
                $_SESSION['promocion_f2_ejecutada_session'] = true;
                return null;
            }
    
            $reporte = ['exitos' => 0, 'fallos' => [], 'observaciones' => []];
    
            foreach ($secciones_origen as $origen) {
                $codigo_origen = $origen['sec_codigo'];
                $codigo_destino = substr_replace($codigo_origen, '2', 1, 1);
    
                $stmt_seccion_f2 = $co->prepare("SELECT sec_id FROM tbl_seccion WHERE ani_id = :ani_id AND sec_estado = 1 AND sec_codigo = :codigo_destino");
                $stmt_seccion_f2->execute([':ani_id' => $ani_id, ':codigo_destino' => $codigo_destino]);
                $seccion_destino = $stmt_seccion_f2->fetch(PDO::FETCH_ASSOC);
    
                if (!$seccion_destino) {
                    $reporte['fallos'][] = "No se encontró la sección de destino '{$codigo_destino}' para la sección de origen '{$codigo_origen}'.";
                    continue;
                }
    
                $co->beginTransaction();
                try {
                    $sec_id_destino = $seccion_destino['sec_id'];
                    $clases_origen_result = $this->ConsultarDetalles($origen['sec_id']);
                    $clases_origen = $clases_origen_result['mensaje'] ?? [];
                    
                    $this->EliminarPorSeccion($sec_id_destino, $co);
    
                    $trayecto_destino = substr($codigo_destino, 0, 1); 
                    $docentes = $this->obtenerDocentes(); 
                    
                    $nuevas_clases = [];
                  
    
                    foreach ($clases_origen as $clase) {
                       
                        $resultado_uc_docente = $this->obtenerUcPorDocente($clase['doc_id'], $trayecto_destino);
                        $ucs_posibles = $resultado_uc_docente['data']; 
    
                        if (count($ucs_posibles) === 1) {
                            $nuevas_clases[] = ['uc_id' => $ucs_posibles[0]['uc_id'], 'doc_id' => $clase['doc_id'], 'esp_id' => $clase['esp_id'], 'dia' => $clase['dia'], 'hora_inicio' => $clase['hora_inicio'], 'hora_fin' => $clase['hora_fin']];
                        } else {
                            $docente_info = array_values(array_filter($docentes, function($d) use ($clase) { return $d['doc_id'] == $clase['doc_id']; }))[0] ?? null;
                            $nombre_docente = $docente_info ? $docente_info['doc_nombre'] . ' ' . $docente_info['doc_apellido'] : 'ID ' . $clase['doc_id'];
                            if (empty($ucs_posibles)) {
                                $reporte['observaciones'][] = "Docente <strong>{$nombre_docente}</strong> no tiene UC de Fase 2 para el trayecto {$trayecto_destino}.";
                            } else {
                                $reporte['observaciones'][] = "Docente <strong>{$nombre_docente}</strong> tiene múltiples UCs de Fase 2 válidas para el trayecto {$trayecto_destino}, no se asignó automáticamente para la clase original de {$clase['dia']} a {$clase['hora_inicio']}.";
                            }
                        }
                    }
    
                    $error_conflicto = $this->validarConflictos($nuevas_clases, $sec_id_destino, $co);
                    if ($error_conflicto) throw new Exception($error_conflicto);
    
                    if (!empty($nuevas_clases)) {
                        $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_turno, hor_modalidad, hor_estado) VALUES (:esp_id, :hor_turno, 'presencial', 1)");
                        $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
                        $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
                        $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_id, :hor_id, :dia, :inicio, :fin)");
                        foreach ($nuevas_clases as $item) {
                            $stmt_insert_hor->execute([':esp_id' => $item['esp_id'], ':hor_turno' => $this->getTurnoEnum($item['hora_inicio'])]);
                            $new_hor_id = $co->lastInsertId();
                            $stmt_sh->execute([':sec_id' => $sec_id_destino, ':hor_id' => $new_hor_id]);
                            $stmt_dh->execute([':doc_id' => $item['doc_id'], ':hor_id' => $new_hor_id]);
                            $stmt_uh->execute([':uc_id' => $item['uc_id'], ':hor_id' => $new_hor_id, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
                        }
                    } else {
                        
                        $this->CrearHorarioVacioParaSeccion($sec_id_destino, $co);
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


    public function UnirHorarios($id_origen, $ids_a_unir) {

        if (empty($id_origen) || empty($ids_a_unir) || count($ids_a_unir) < 2) {
            return ['resultado' => 'error', 'mensaje' => 'Debe seleccionar al menos 2 secciones y una de origen.'];
        }
        
        try {
            $co_val = $this->Con();
          
            $clean_ids_a_unir = array_map('intval', $ids_a_unir);
            $placeholders = implode(',', array_fill(0, count($clean_ids_a_unir), '?'));
            $stmt = $co_val->prepare("SELECT sec_id, sec_codigo, ani_id FROM tbl_seccion WHERE sec_id IN ($placeholders)");
            $stmt->execute(array_values($clean_ids_a_unir));
            $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($secciones) !== count($ids_a_unir)) {
                 return ['resultado' => 'error', 'mensaje' => 'Una o más secciones seleccionadas no son válidas.'];
            }

         
            $seccion_origen_data = null;
            foreach ($secciones as $s) {
                if ($s['sec_id'] == $id_origen) {
                    $seccion_origen_data = $s;
                    break;
                }
            }
            if (!$seccion_origen_data) {
                 return ['resultado' => 'error', 'mensaje' => 'La sección de origen seleccionada no es válida o no está entre las secciones a unir.'];
            }


            $primer_anio_id = $seccion_origen_data['ani_id'];
            $primer_trayecto = substr($seccion_origen_data['sec_codigo'], 0, 1);

            foreach($secciones as $seccion) {
                if ($seccion['ani_id'] !== $primer_anio_id || substr($seccion['sec_codigo'], 0, 1) !== $primer_trayecto) {
                    return ['resultado' => 'error', 'mensaje' => 'Acción no permitida: Solo se pueden unir horarios de secciones del mismo año y trayecto.'];
                }
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error al validar las secciones: ' . $e->getMessage()];
        }
    
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        try {
            $co->beginTransaction();
    
            $clases_origen_result = $this->ConsultarDetalles($id_origen);
            $clases_origen = $clases_origen_result['mensaje'] ?? [];
    

            $ids_destinos = array_filter($ids_a_unir, function($id) use ($id_origen) {
                return $id != $id_origen;
            });
    
            foreach($ids_destinos as $id_destino) {
                
                $this->EliminarPorSeccion($id_destino, $co);
                
                if (!empty($clases_origen)) {
                
                    $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_turno, hor_modalidad, hor_estado) VALUES (:esp_id, :hor_turno, 'presencial', 1)");
                    $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
                    $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
                    $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_id, :hor_id, :dia, :inicio, :fin)");
                    
                    foreach ($clases_origen as $item) {
                     
                        $stmt_insert_hor->execute([':esp_id' => $item['esp_id'], ':hor_turno' => $this->getTurnoEnum($item['hora_inicio'])]);
                        $new_hor_id = $co->lastInsertId();
                        $stmt_sh->execute([':sec_id' => $id_destino, ':hor_id' => $new_hor_id]);
                        $stmt_dh->execute([':doc_id' => $item['doc_id'], ':hor_id' => $new_hor_id]);
                        $stmt_uh->execute([':uc_id' => $item['uc_id'], ':hor_id' => $new_hor_id, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
                    }
                } else {
                
                    $this->CrearHorarioVacioParaSeccion($id_destino, $co);
                }
            }
    
            $co->commit();
            return ['resultado' => 'unir_horarios_ok', 'mensaje' => '¡Horarios unidos y actualizados correctamente!'];
    
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => 'Error al unir los horarios: ' . $e->getMessage()];
        }
    }


    public function RegistrarSeccion($codigoSeccion, $cantidadSeccion, $anioId)
    {
    
        if (empty($codigoSeccion) || !isset($cantidadSeccion) || $cantidadSeccion === '' || empty($anioId)) {
            return ['resultado' => 'error', 'mensaje' => 'Todos los campos de la sección son obligatorios.'];
        }

        $cantidadInt = filter_var($cantidadSeccion, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 99]
        ]);
        
        if ($cantidadInt === false) {
            return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número entero entre 0 y 99.'];
        }

     
        if ($this->ExisteSeccion($codigoSeccion, $anioId)) {
            return ['resultado' => 'error', 'mensaje' => '¡ERROR! La sección con ese código ya existe para el año seleccionado.'];
        }
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $stmtSeccion = $co->prepare("INSERT INTO tbl_seccion (sec_codigo, sec_cantidad, ani_id, sec_estado) VALUES (:codigo, :cantidad, :anio, 1)");
            $stmtSeccion->bindParam(':codigo', $codigoSeccion, PDO::PARAM_STR);
            $stmtSeccion->bindParam(':cantidad', $cantidadInt, PDO::PARAM_INT);
            $stmtSeccion->bindParam(':anio', $anioId, PDO::PARAM_INT);
            $stmtSeccion->execute();
            $nuevo_sec_id = $co->lastInsertId();
            $this->CrearHorarioVacioParaSeccion($nuevo_sec_id, $co); 
            $co->commit();
            return ['resultado' => 'registrar_seccion_ok', 'mensaje' => '¡Se registró la sección correctamente!', 'nuevo_id' => $nuevo_sec_id, 'nuevo_codigo' => $codigoSeccion, 'nueva_cantidad' => $cantidadInt];
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    private function CrearHorarioVacioParaSeccion($sec_id, $co)
    {
     
        $stmtEspacio = $co->query("SELECT esp_id FROM tbl_espacio WHERE esp_estado = 1 LIMIT 1");
        $default_esp_id = $stmtEspacio->fetchColumn();
        if (!$default_esp_id) {
         
            throw new Exception("No hay espacios disponibles para crear un horario. Por favor, registre al menos un espacio.");
        }
        $stmtHorario = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_turno, hor_modalidad, hor_estado) VALUES (:esp_id, 'mañana', 'presencial', 1)");
        $stmtHorario->execute([':esp_id' => $default_esp_id]);
        $nuevo_hor_id = $co->lastInsertId();
        $stmtLink = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
        $stmtLink->execute([':sec_id' => $sec_id, ':hor_id' => $nuevo_hor_id]);
    }

    public function ExisteSeccion($codigoSeccion, $anioId)
    {
       
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT 1 FROM tbl_seccion WHERE sec_codigo = :codigo AND ani_id = :anio AND sec_estado = 1");
            $stmt->execute([':codigo' => $codigoSeccion, ':anio' => $anioId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) { 
         
            error_log("Error en ExisteSeccion: " . $e->getMessage());
            return true; 
        }
    }

    public function ListarAgrupado()
    {
        
        try {
          
            $stmt = $this->Con()->query("SELECT ts.sec_id, ts.sec_codigo, ts.sec_cantidad, a.ani_anio, a.ani_id FROM tbl_seccion ts JOIN tbl_anio a ON ts.ani_id = a.ani_id WHERE ts.sec_estado = 1 ORDER BY a.ani_anio DESC, ts.sec_codigo");
            return ['resultado' => 'consultar_agrupado', 'mensaje' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => "¡ERROR!<br/>Error al listar horarios: " . $e->getMessage()];
        }
    }
    
    private function validarConflictos($items_horario, $sec_id, $co) {

        $stmt_docente = $co->prepare("
            SELECT s.sec_codigo, doc.doc_nombre, doc.doc_apellido
            FROM uc_horario uh
            JOIN docente_horario dh ON uh.hor_id = dh.hor_id
            JOIN seccion_horario sh ON uh.hor_id = sh.hor_id
            JOIN tbl_seccion s ON sh.sec_id = s.sec_id
            JOIN tbl_docente doc ON dh.doc_id = doc.doc_id
            WHERE uh.hor_dia = :dia 
              AND uh.hor_horainicio = :inicio 
              AND dh.doc_id = :doc_id 
              AND sh.sec_id != :sec_id
              AND s.sec_estado = 1
        ");

        $stmt_espacio = $co->prepare("
            SELECT s.sec_codigo, esp.esp_codigo
            FROM uc_horario uh
            JOIN tbl_horario h ON uh.hor_id = h.hor_id
            JOIN seccion_horario sh ON uh.hor_id = sh.hor_id
            JOIN tbl_seccion s ON sh.sec_id = s.sec_id
            JOIN tbl_espacio esp ON h.esp_id = esp.esp_id
            WHERE uh.hor_dia = :dia 
              AND uh.hor_horainicio = :inicio 
              AND h.esp_id = :esp_id 
              AND sh.sec_id != :sec_id
              AND s.sec_estado = 1
        ");

        foreach ($items_horario as $item) {
    
            $dia_normalizado = strtolower(str_replace('é', 'e', str_replace('á', 'a', str_replace('í', 'i', str_replace('ó', 'o', str_replace('ú', 'u', $item['dia']))))));

            $stmt_docente->execute([
                ':dia' => $dia_normalizado,
                ':inicio' => $item['hora_inicio'],
                ':doc_id' => $item['doc_id'],
                ':sec_id' => $sec_id
            ]);
            $conflicto_docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);
            if ($conflicto_docente) {
                return "Conflicto de Horario: El docente <strong>" . htmlspecialchars($conflicto_docente['doc_nombre'] . ' ' . $conflicto_docente['doc_apellido']) . "</strong> ya tiene una clase asignada el día <strong>" . ucfirst($item['dia']) . "</strong> a las <strong>" . date("g:i a", strtotime($item['hora_inicio'])) . "</strong> en la sección <strong>IN" . htmlspecialchars($conflicto_docente['sec_codigo']) . "</strong>.";
            }

            $stmt_espacio->execute([
                ':dia' => $dia_normalizado,
                ':inicio' => $item['hora_inicio'],
                ':esp_id' => $item['esp_id'],
                ':sec_id' => $sec_id
            ]);
            $conflicto_espacio = $stmt_espacio->fetch(PDO::FETCH_ASSOC);
            if ($conflicto_espacio) {
                return "Conflicto de Horario: El espacio <strong>" . htmlspecialchars($conflicto_espacio['esp_codigo']) . "</strong> ya está ocupado el día <strong>" . ucfirst($item['dia']) . "</strong> a las <strong>" . date("g:i a", strtotime($item['hora_inicio'])) . "</strong> por la sección <strong>IN" . htmlspecialchars($conflicto_espacio['sec_codigo']) . "</strong>.";
            }
        }

        return null;
    }

    public function ValidarClaseEnVivo($doc_id, $esp_id, $dia, $hora_inicio, $sec_id)
    {
   
        if (empty($dia) || empty($hora_inicio) || empty($sec_id) || (empty($doc_id) && empty($esp_id))) {
            return ['conflicto' => false];
        }

        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      
            $dia_normalizado = strtolower(str_replace('é', 'e', str_replace('á', 'a', str_replace('í', 'i', str_replace('ó', 'o', str_replace('ú', 'u', $dia))))));


            if (!empty($doc_id)) {
                $stmt_docente = $co->prepare("
                    SELECT s.sec_codigo, doc.doc_nombre, doc.doc_apellido
                    FROM uc_horario uh
                    JOIN docente_horario dh ON uh.hor_id = dh.hor_id
                    JOIN seccion_horario sh ON uh.hor_id = sh.hor_id
                    JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                    JOIN tbl_docente doc ON dh.doc_id = doc.doc_id
                    WHERE uh.hor_dia = :dia 
                      AND uh.hor_horainicio = :inicio 
                      AND dh.doc_id = :doc_id 
                      AND sh.sec_id != :sec_id
                      AND s.sec_estado = 1
                    LIMIT 1
                ");
                $stmt_docente->execute([
                    ':dia' => $dia_normalizado,
                    ':inicio' => $hora_inicio,
                    ':doc_id' => $doc_id,
                    ':sec_id' => $sec_id
                ]);
                $conflicto = $stmt_docente->fetch(PDO::FETCH_ASSOC);
                if ($conflicto) {
                    return [
                        'conflicto' => true,
                        'tipo' => 'docente',
                        'mensaje' => "Conflicto: El docente <strong>" . htmlspecialchars($conflicto['doc_nombre'] . ' ' . $conflicto['doc_apellido']) . "</strong> ya tiene una clase en la sección <strong>IN" . htmlspecialchars($conflicto['sec_codigo']) . "</strong> a esta misma hora."
                    ];
                }
            }

            if (!empty($esp_id)) {
                $stmt_espacio = $co->prepare("
                    SELECT s.sec_codigo, esp.esp_codigo
                    FROM uc_horario uh
                    JOIN tbl_horario h ON uh.hor_id = h.hor_id
                    JOIN seccion_horario sh ON uh.hor_id = sh.hor_id
                    JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                    JOIN tbl_espacio esp ON h.esp_id = esp.esp_id
                    WHERE uh.hor_dia = :dia 
                      AND uh.hor_horainicio = :inicio 
                      AND h.esp_id = :esp_id 
                      AND sh.sec_id != :sec_id
                      AND s.sec_estado = 1
                    LIMIT 1
                ");
                $stmt_espacio->execute([
                    ':dia' => $dia_normalizado,
                    ':inicio' => $hora_inicio,
                    ':esp_id' => $esp_id,
                    ':sec_id' => $sec_id
                ]);
                $conflicto = $stmt_espacio->fetch(PDO::FETCH_ASSOC);
                if ($conflicto) {
                    return [
                        'conflicto' => true,
                        'tipo' => 'espacio',
                        'mensaje' => "Conflicto: El espacio <strong>" . htmlspecialchars($conflicto['esp_codigo']) . "</strong> ya está ocupado por la sección <strong>IN" . htmlspecialchars($conflicto['sec_codigo']) . "</strong> a esta misma hora."
                    ];
                }
            }

            return ['conflicto' => false];

        } catch (Exception $e) {
            error_log("Error en ValidarClaseEnVivo: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }

    public function Modificar($sec_id, $items_horario_json)
    {
    
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $items_horario = json_decode($items_horario_json, true);

          
            foreach ($items_horario as &$item) {
                $item['dia'] = strtolower(str_replace('é', 'e', str_replace('á', 'a', str_replace('í', 'i', str_replace('ó', 'o', str_replace('ú', 'u', $item['dia']))))));
            }
            unset($item);

            $error_conflicto = $this->validarConflictos($items_horario, $sec_id, $co);
            if ($error_conflicto) {
                return ['resultado' => 'error', 'mensaje' => $error_conflicto];
            }

            $co->beginTransaction();
            $this->EliminarPorSeccion($sec_id, $co); 
            
            if (!empty($items_horario)) {
                $stmt_insert_hor = $co->prepare("INSERT INTO tbl_horario (esp_id, hor_turno, hor_modalidad, hor_estado) VALUES (:esp_id, :hor_turno, 'presencial', 1)");
                $stmt_sh = $co->prepare("INSERT INTO seccion_horario (sec_id, hor_id) VALUES (:sec_id, :hor_id)");
                $stmt_dh = $co->prepare("INSERT INTO docente_horario (doc_id, hor_id) VALUES (:doc_id, :hor_id)");
                $stmt_uh = $co->prepare("INSERT INTO uc_horario (uc_id, hor_id, hor_dia, hor_horainicio, hor_horafin) VALUES (:uc_id, :hor_id, :dia, :inicio, :fin)");
                
                foreach ($items_horario as $item) {
                    $stmt_insert_hor->execute([':esp_id' => $item['esp_id'], ':hor_turno' => $this->getTurnoEnum($item['hora_inicio'])]);
                    $new_hor_id = $co->lastInsertId();
                    $stmt_sh->execute([':sec_id' => $sec_id, ':hor_id' => $new_hor_id]);
                    $stmt_dh->execute([':doc_id' => $item['doc_id'], ':hor_id' => $new_hor_id]);
                    $stmt_uh->execute([':uc_id' => $item['uc_id'], ':hor_id' => $new_hor_id, ':dia' => $item['dia'], ':inicio' => $item['hora_inicio'], ':fin' => $item['hora_fin']]);
                }
            } else {
           
                $this->CrearHorarioVacioParaSeccion($sec_id, $co);
            }
            $co->commit();
            return ['resultado' => 'modificar_ok', 'mensaje' => '¡Horario guardado correctamente!'];
        } catch (Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => "¡ERROR!<br/>" . $e->getMessage()];
        }
    }

    public function EliminarSeccionYHorario($sec_id) {
   
        if (empty($sec_id)) return ['resultado' => 'error', 'mensaje' => 'ID de sección no proporcionado.'];
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $this->EliminarPorSeccion($sec_id, $co); 
          
            $stmt = $co->prepare("UPDATE tbl_seccion SET sec_estado = 0 WHERE sec_id = :sec_id");
            $stmt->bindParam(':sec_id', $sec_id, PDO::PARAM_INT);
            $stmt->execute();
            $co->commit();
            return ['resultado' => 'eliminar_seccion_y_horario_ok', 'mensaje' => '¡Sección y horario eliminados correctamente!'];
        } catch(Exception $e) {
            if ($co->inTransaction()) $co->rollBack();
            return ['resultado' => 'error', 'mensaje' => '¡ERROR!<br/>' . $e->getMessage()];
        }
    }

    public function EliminarPorSeccion($sec_id, $co_externo = null)
    {
    
        $co = $co_externo ?? $this->Con();
        $es_transaccion_interna = ($co_externo === null);
        try {
            if ($es_transaccion_interna) $co->beginTransaction();

           
            $stmtHorIds = $co->prepare("SELECT hor_id FROM seccion_horario WHERE sec_id = :sec_id");
            $stmtHorIds->execute([':sec_id' => $sec_id]);
            $horarios_a_eliminar = $stmtHorIds->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($horarios_a_eliminar)) {
                $placeholders = implode(',', array_fill(0, count($horarios_a_eliminar), '?'));
                
                
                $co->prepare("DELETE FROM seccion_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
                $co->prepare("DELETE FROM docente_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
                $co->prepare("DELETE FROM uc_horario WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
                
              
                $co->prepare("UPDATE tbl_horario SET hor_estado = 0 WHERE hor_id IN ($placeholders)")->execute($horarios_a_eliminar);
            }
            if ($es_transaccion_interna) $co->commit();
        } catch (Exception $e) {
            if ($es_transaccion_interna && $co->inTransaction()) $co->rollBack();
            throw $e; 
        }
    }

    public function ConsultarDetalles($sec_id)
    {

        if(!$sec_id) return ['resultado' => 'error', 'mensaje' => 'Falta el ID de la sección.'];
        $co = $this->Con();
        try {
         
            $sql = "SELECT th.hor_id, th.esp_id, th.hor_turno, dh.doc_id, uh.uc_id, uh.hor_dia as dia, uh.hor_horainicio as hora_inicio, uh.hor_horafin as hora_fin FROM tbl_horario th INNER JOIN seccion_horario sh ON th.hor_id = sh.hor_id LEFT JOIN docente_horario dh ON th.hor_id = dh.hor_id LEFT JOIN uc_horario uh ON th.hor_id = uh.hor_id WHERE sh.sec_id = :sec_id AND th.hor_estado = 1 AND uh.uc_id IS NOT NULL";
            $stmt = $co->prepare($sql);
            $stmt->execute([':sec_id' => $sec_id]);
            $schedule_grid_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

      
            $turnos = $this->obtenerTurnos();
            $turnosMap = [];
            foreach($turnos as $turno) { $turnosMap[$turno['tur_horainicio']] = $turno['tur_id']; }
            
            foreach($schedule_grid_items as $key => $item){
                 
                 if (!empty($item['hora_inicio'])) { 
                     $time_key = $item['hora_inicio'] . ":00"; 
                     $schedule_grid_items[$key]['tur_id'] = $turnosMap[$time_key] ?? 0; 
                 } 
                 else { 
                     $schedule_grid_items[$key]['tur_id'] = 0; 
                 }
            }
            return ['resultado' => 'ok', 'mensaje' => $schedule_grid_items];
        } catch (Exception $e) {
            error_log("Error en ConsultarDetalles: " . $e->getMessage());
            return ['resultado' => 'error', 'mensaje' => "¡ERROR!<br/>Error al consultar detalles: " . $e->getMessage()];
        }
    }
    
    public function obtenerAnios() { 
        try { 
            return $this->Con()->query("SELECT ani_anio, ani_id FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC")->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error en obtenerAnios: " . $e->getMessage());
            return []; 
        }
    }
    

    public function obtenerTurnos() { 
        return [
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
         
            ['tur_id' => 11, 'tur_horainicio' => '17:00:00', 'tur_horafin' => '17:40:00'],
            ['tur_id' => 12, 'tur_horainicio' => '17:40:00', 'tur_horafin' => '18:20:00'],
            ['tur_id' => 13, 'tur_horainicio' => '18:20:00', 'tur_horafin' => '19:00:00'],
            ['tur_id' => 14, 'tur_horainicio' => '19:00:00', 'tur_horafin' => '19:40:00'],
            ['tur_id' => 15, 'tur_horainicio' => '19:40:00', 'tur_horafin' => '20:20:00'],
            ['tur_id' => 16, 'tur_horainicio' => '20:20:00', 'tur_horafin' => '21:00:00'],
            ['tur_id' => 17, 'tur_horainicio' => '21:00:00', 'tur_horafin' => '21:40:00'],
            ['tur_id' => 18, 'tur_horainicio' => '21:40:00', 'tur_horafin' => '22:20:00'],
            ['tur_id' => 19, 'tur_horainicio' => '22:20:00', 'tur_horafin' => '23:00:00']
        ]; 
    }

    public function obtenerUcPorDocente($doc_id, $trayecto_seccion = null) { 
        if (empty($doc_id)) {
            return ['data' => [], 'mensaje' => 'ID de docente no proporcionado.'];
        }
        
        $fase_actual = $this->determinarFaseActual();
        
        if ($fase_actual === 'ninguna') {
            return ['data' => [], 'mensaje' => 'Fuera de período de asignación de Unidades Curriculares.'];
        }
    
        try {
            $sql = "SELECT u.uc_id, u.uc_nombre, u.uc_codigo, u.uc_trayecto, u.uc_periodo 
                    FROM tbl_uc u 
                    INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id 
                    WHERE ud.doc_id = :doc_id AND u.uc_estado = 1";
            $params = [':doc_id' => $doc_id];

          
            if ($fase_actual === 'fase1') {
                $sql .= " AND (u.uc_periodo = '1' OR u.uc_periodo = 'anual')";
            } elseif ($fase_actual === 'fase2') {
                $sql .= " AND (u.uc_periodo = '2' OR u.uc_periodo = 'anual')";
            }
            
     
            if ($trayecto_seccion !== null && is_numeric($trayecto_seccion)) {
                $sql .= " AND u.uc_trayecto = :trayecto_seccion";
                $params[':trayecto_seccion'] = (int)$trayecto_seccion;
            }
            
            $sql .= " ORDER BY u.uc_codigo";
    
            $stmt = $this->Con()->prepare($sql);
            $stmt->execute($params);
            $ucs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if(empty($ucs)){
          
                $mensaje_extra = '';
                if ($trayecto_seccion !== null) {
                    $mensaje_extra .= " para el trayecto {$trayecto_seccion}";
                }
                $mensaje_extra .= " y la fase {$fase_actual}";

                return ['data' => [], 'mensaje' => 'El docente no tiene UCs disponibles' . $mensaje_extra . '.'];
            }
            return ['data' => $ucs, 'mensaje' => 'ok'];
    
        } catch (Exception $e) { 
            error_log("Error en obtenerUcPorDocente: " . $e->getMessage());
            return ['data' => [], 'mensaje' => 'Error al consultar las UCs.']; 
        } 
    }

    public function obtenerUnidadesCurriculares() { 
        try { 
            return $this->Con()->query("SELECT uc_id, uc_nombre, uc_codigo, uc_trayecto FROM tbl_uc WHERE uc_estado = 1")->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error en obtenerUnidadesCurriculares: " . $e->getMessage());
            return []; 
        } 
    }
    public function obtenerSecciones() { 
        try { 
            return $this->Con()->query("SELECT s.sec_id, s.sec_codigo, s.sec_cantidad, a.ani_anio, s.ani_id FROM tbl_seccion s JOIN tbl_anio a ON s.ani_id = a.ani_id WHERE s.sec_estado = 1 ORDER BY a.ani_anio DESC, s.sec_codigo")->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error en obtenerSecciones: " . $e->getMessage());
            return []; 
        } 
    }
    public function obtenerEspacios() { 
        try { 
            return $this->Con()->query("SELECT esp_id, esp_codigo, esp_tipo FROM tbl_espacio WHERE esp_estado = 1")->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error en obtenerEspacios: " . $e->getMessage());
            return []; 
        } 
    }
    public function obtenerDocentes() { 
        try { 
            return $this->Con()->query("SELECT doc_id, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1")->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) { 
            error_log("Error en obtenerDocentes: " . $e->getMessage());
            return []; 
        } 
    }
    private function getTurnoEnum($hora_inicio) { 
        $hour = intval(substr($hora_inicio, 0, 2)); 
        if ($hour >= 18) return 'noche'; 
        if ($hour >= 13) return 'tarde'; 
        return 'mañana'; 
    }
}