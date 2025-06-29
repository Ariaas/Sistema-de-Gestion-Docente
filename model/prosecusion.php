<?php
// function Unir($secciones, $nombreGrupo)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];

    //     try {
    //         $seccionesArray = json_decode($secciones, true);
    //         if (json_last_error() !== JSON_ERROR_NONE || !is_array($seccionesArray) || empty($seccionesArray)) {
    //             throw new Exception("Datos de secciones para unir inválidos.");
    //         }
    //         if (!$nombreGrupo) {
    //             throw new Exception("Debe ingresar un nombre de grupo.");
    //         }

    //         $placeholders = implode(',', array_fill(0, count($seccionesArray), '?'));
    //         $stmtCant = $co->prepare("SELECT SUM(sec_cantidad) as suma FROM tbl_seccion WHERE sec_id IN ($placeholders) AND sec_estado = 1");
    //         $stmtCant->execute($seccionesArray);
    //         $suma = (int)$stmtCant->fetchColumn();
    //         if ($suma > 45) {
    //             throw new Exception("La suma de las cantidades de las secciones seleccionadas no puede superar 45");
    //         }

    //         $placeholders = implode(',', array_fill(0, count($seccionesArray), '?'));
    //         $stmtCheck = $co->prepare("SELECT COUNT(*) FROM tbl_seccion WHERE sec_id IN ($placeholders) AND sec_grupo IS NOT NULL AND sec_grupo != '' AND sec_estado = 1");
    //         $stmtCheck->execute($seccionesArray);
    //         $yaUnidas = $stmtCheck->fetchColumn();
    //         if ($yaUnidas > 0) {
    //             throw new Exception("Al menos una de las secciones seleccionadas ya pertenece a un grupo");
    //         }

    //         $stmtTrayecto = $co->prepare("SELECT tra_id FROM tbl_seccion WHERE sec_id IN ($placeholders) AND sec_estado = 1 GROUP BY tra_id");
    //         $stmtTrayecto->execute($seccionesArray);
    //         $trayectos = $stmtTrayecto->fetchAll(PDO::FETCH_COLUMN);
    //         if (count($trayectos) > 1) {
    //             throw new Exception("Las secciones seleccionadas NO pertenecen al mismo trayecto");
    //         }

    //         $stmtUpdate = $co->prepare("UPDATE tbl_seccion SET sec_grupo = ? WHERE sec_id IN ($placeholders)");
    //         $params = array_merge([$nombreGrupo], $seccionesArray);
    //         $stmtUpdate->execute($params);

    //         $r['resultado'] = 'unir';
    //         $r['mensaje'] = 'Secciones unidas!<br/>Se unieron las secciones correctamente!';
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }
    //     return $r;
    // }


    // public function Separar()
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];

    //     try {
    //         $grupo = $this->grupo;
    //         if (!$grupo) {
    //             throw new Exception("Grupo no válido.");
    //         }

    //         $stmt = $co->prepare("UPDATE tbl_seccion SET sec_grupo = NULL WHERE sec_grupo = ?");
    //         $stmt->execute([$grupo]);

    //         $r['resultado'] = 'separar';
    //         $r['mensaje'] = 'Secciones separadas!<br/>Se separaron las secciones correctamente!';
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }
    //     return $r;
    // }


    // public function ObtenerSeccionDestinoProsecusion($seccionOrigenId)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => 'obtenerSeccionDestinoProsecusion', 'mensaje' => []];

    //     try {
    //         $stmt = $co->prepare("SELECT s.sec_id, s.sec_codigo, s.sec_nombre, s.coh_id, s.tra_id, t.tra_numero, a.ani_anio
    //         FROM tbl_seccion s
    //         JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //         JOIN tbl_anio a ON t.ani_id = a.ani_id
    //         WHERE s.sec_id = ? AND s.sec_estado = 1");
    //         $stmt->execute([$seccionOrigenId]);
    //         $origen = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if (!$origen) {
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'Sección de origen no encontrada.';
    //             return $r;
    //         }
    //         $codigoDestino = (int)$origen['sec_codigo'] + 100;
    //         $trayectoDestino = (int)$origen['tra_numero'] + 1;
    //         $anioDestino = (int)$origen['ani_anio'] + 1;
    //         $cohorte = $origen['coh_id'];
    //         $nombre = $origen['sec_nombre'];

    //         $sql = "SELECT s.sec_id, s.sec_codigo, s.sec_nombre, s.coh_id, c.coh_numero, s.tra_id, t.tra_numero, a.ani_anio
    //         FROM tbl_seccion s
    //         JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //         JOIN tbl_anio a ON t.ani_id = a.ani_id
    //         JOIN tbl_cohorte c ON s.coh_id = c.coh_id
    //         WHERE s.sec_codigo = ?
    //           AND s.sec_nombre = ?
    //           AND s.coh_id = ?
    //           AND t.tra_numero = ?
    //           AND a.ani_anio = ?
    //           AND s.sec_estado = 1
    //         LIMIT 1";
    //         $stmt2 = $co->prepare($sql);
    //         $stmt2->execute([$codigoDestino, $nombre, $cohorte, $trayectoDestino, $anioDestino]);
    //         $destino = $stmt2->fetch(PDO::FETCH_ASSOC);

    //         if ($destino) {
    //             $r['mensaje'] = $destino;
    //         } else {
    //             $r['mensaje'] = null;
    //         }
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = "Error al buscar sección destino: " . $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }
    //     return $r;
    // }

    // public function ProsecusionSeccion($seccionOrigenId, $seccionDestinoId)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => 'prosecusion', 'mensaje' => ''];

    //     try {
    //         $stmt = $co->prepare("SELECT s.sec_id, s.sec_codigo, s.sec_nombre, s.coh_id, s.tra_id, t.tra_numero, a.ani_anio
    //         FROM tbl_seccion s
    //         JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //         JOIN tbl_anio a ON t.ani_id = a.ani_id
    //         WHERE s.sec_id IN (?, ?) AND s.sec_estado = 1");
    //         $stmt->execute([$seccionOrigenId, $seccionDestinoId]);
    //         $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         if (count($secciones) != 2) {
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'No se encontraron ambas secciones.';
    //             return $r;
    //         }
    //         $origen = null;
    //         $destino = null;
    //         foreach ($secciones as $sec) {
    //             if ($sec['sec_id'] == $seccionOrigenId) $origen = $sec;
    //             if ($sec['sec_id'] == $seccionDestinoId) $destino = $sec;
    //         }
    //         if (
    //             ((int)$destino['tra_numero'] !== (int)$origen['tra_numero'] + 1) ||
    //             ((int)$destino['ani_anio'] !== (int)$origen['ani_anio'] + 1) ||
    //             ($destino['coh_id'] != $origen['coh_id']) ||
    //             ($destino['sec_nombre'] !== $origen['sec_nombre']) ||
    //             ((int)$destino['sec_codigo'] !== (int)$origen['sec_codigo'] + 100)
    //         ) {
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'La sección destino no cumple las condiciones de prosecusión.';
    //             return $r;
    //         }
    //         $stmtInsert = $co->prepare("INSERT INTO tbl_prosecusion (sec_id_origen, sec_id_prosecusion) VALUES (?, ?)");
    //         $stmtInsert->execute([$seccionOrigenId, $seccionDestinoId]);

    //         $r['mensaje'] = '¡Prosecusión realizada correctamente!';
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = "Error: " . $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }
    //     return $r;
    // }