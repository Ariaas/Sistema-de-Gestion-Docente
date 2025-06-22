<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

    private $codigoSeccion;
    private $cantidadSeccion;
    private $trayectoAnio;
    private $trayectoNumero;
    private $seccionId;
    private $trayectoSeccion;
    private $grupo;
    private $cohorteSeccion;
    private $nombreSeccion;

    public function __construct($codigoSeccion = null, $cantidadSeccion = null, $seccionId = null, $trayectoNumero = null, $trayectoAnio = null, $trayectoSeccion = null, $grupo = null, $cohorteSeccion = null, $nombreSeccion = null)
    {
        parent::__construct();

        $this->codigoSeccion = $codigoSeccion;
        $this->cantidadSeccion = $cantidadSeccion;
        $this->seccionId = $seccionId;
        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoSeccion = $trayectoSeccion;
        $this->grupo = $grupo;
        $this->cohorteSeccion = $cohorteSeccion;
        $this->nombreSeccion = $nombreSeccion;
    }

    // Getters
    public function getCodigoSeccion()
    {
        return $this->codigoSeccion;
    }

    public function getCantidadSeccion()
    {
        return $this->cantidadSeccion;
    }

    public function getTrayectoAnio()
    {
        return $this->trayectoAnio;
    }

    public function getTrayectoNumero()
    {
        return $this->trayectoNumero;
    }

    public function getTrayectoSeccion()
    {
        return $this->trayectoSeccion;
    }

    public function getGrupo()
    {
        return $this->grupo;
    }

    public function getseccionId()
    {
        return $this->seccionId;
    }

    public function getcohorteSeccion()
    {
        return $this->cohorteSeccion;
    }

    public function getNombreSeccion()
    {
        return $this->nombreSeccion;
    }

    // Setters
    public function setCodigoSeccion($codigoSeccion)
    {
        $this->codigoSeccion = $codigoSeccion;
    }

    public function setCantidadSeccion($cantidadSeccion)
    {
        $this->cantidadSeccion = $cantidadSeccion;
    }

    public function setTrayectoAnio($trayectoAnio)
    {
        $this->trayectoAnio = $trayectoAnio;
    }

    public function setTrayectoNumero($trayectoNumero)
    {
        $this->trayectoNumero = $trayectoNumero;
    }

    public function setTrayectoSeccion($trayectoSeccion)
    {
        $this->trayectoSeccion = $trayectoSeccion;
    }

    public function setGrupoId($grupo)
    {
        $this->grupo = $grupo;
    }

    public function setseccionId($seccionId)
    {
        $this->seccionId = $seccionId;
    }

    public function setcohorteSeccion($cohorteSeccion)
    {
        $this->cohorteSeccion = $cohorteSeccion;
    }

    public function setNombreSeccion($nombreSeccion)
    {
        $this->nombreSeccion = $nombreSeccion;
    }

    //Methods

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->codigoSeccion, $this->trayectoSeccion, $this->nombreSeccion)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_seccion (
            tra_id,
            sec_codigo,
            sec_cantidad,
            coh_id,
            sec_nombre,
            sec_estado
        ) VALUES (
            :trayectoSeccion,
            :codigoSeccion,
            :cantidadSeccion,
            :cohorteSeccion,
            :nombreSeccion,
            1
        )");
                $stmt->bindParam(':trayectoSeccion', $this->trayectoSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':cohorteSeccion', $this->cohorteSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':nombreSeccion', $this->nombreSeccion, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró la sección correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteSeccion($this->seccionId)) {
            if (!$this->existe($this->codigoSeccion, $this->trayectoSeccion, $this->nombreSeccion, $this->seccionId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_seccion
                    SET sec_codigo = :codigoSeccion , sec_cantidad = :cantidadSeccion, tra_id = :trayectoId
                    WHERE sec_id = :seccionId");

                    $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_STR);
                    $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                    $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                    $stmt->bindParam(':trayectoId', $this->trayectoSeccion, PDO::PARAM_INT);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la sección correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado NO existe!';
        }
        return $r;
    }

    // /// Eliminar

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteSeccion($this->seccionId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_seccion
                SET sec_estado = 0
                WHERE sec_id = :seccionId");
                $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la sección correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocada NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'consultar') {
                $stmt = $co->query("SELECT
                    s.sec_id, 
                    s.sec_codigo, 
                    s.sec_cantidad, 
                    t.tra_id,           
                    t.tra_numero, 
                    t.ani_id,
                    a.ani_anio,
                    s.coh_id,
                    c.coh_numero,
                    s.sec_nombre
                FROM tbl_seccion s
                INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                INNER JOIN tbl_anio a ON t.ani_id = a.ani_id
                INNER JOIN tbl_cohorte c ON s.coh_id = c.coh_id
                WHERE s.sec_estado = 1");
            } 
            // elseif ($accion === 'consultarUnion') {
            //     $sql = "SELECT 
            //             g.gro_id,
            //             GROUP_CONCAT(s.sec_codigo SEPARATOR '; ') AS secciones,
            //             CONCAT(t.ani_id, ' - ', t.tra_anio)       AS trayecto
            //         FROM tbl_grupo g
            //         JOIN seccion_grupo sg ON g.gro_id = sg.gro_id
            //         JOIN tbl_seccion   s  ON sg.sec_id = s.sec_id
            //         JOIN tbl_trayecto  t  ON s.tra_id = t.tra_id
            //         WHERE g.grupo_estado = 1
            //         GROUP BY g.gro_id";
            //     $stmt = $co->query($sql);
            // } 
            else {
                throw new Exception("Acción inválida: $accion");
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = $accion;
            $r['mensaje']   = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje']   = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Existe($codigoSeccion, $trayectoSeccion, $nombreSeccion, $seccionIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "
                SELECT COUNT(*) AS cnt
                FROM tbl_seccion s
                INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                WHERE s.sec_codigo = :codigoSeccion 
                  AND s.sec_nombre = :nombreSeccion
                  AND t.tra_id = :trayectoSeccion 
                  AND s.sec_estado = 1 
                  AND t.tra_estado = 1
            ";
            if ($seccionIdExcluir !== null) {
                $sql .= " AND s.sec_id != :seccionIdExcluir";
            }
            $stmt = $co->prepare($sql);

            $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoSeccion', $trayectoSeccion, PDO::PARAM_INT);
            $stmt->bindParam(':nombreSeccion', $nombreSeccion, PDO::PARAM_STR);
            if ($seccionIdExcluir !== null) {
                $stmt->bindParam(':seccionIdExcluir', $seccionIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();

            $count = (int) $stmt->fetchColumn();

            if ($count > 0) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La SECCIÓN colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    // public function Existe($codigoSeccion, $trayectoSeccion)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = array();
    //     try {
    //         $stmt = $co->prepare("
    //         SELECT COUNT(*) AS cnt
    //         FROM tbl_seccion s
    //         INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //         WHERE s.sec_codigo = :codigoSeccion 
    //           AND s.sec_nombre = :nombreSeccion
    //           AND t.tra_id = :trayectoSeccion 
    //           AND s.sec_estado = 1 
    //           AND t.tra_estado = 1
    //     ");

    //         $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
    //         $stmt->bindParam(':trayectoSeccion', $trayectoSeccion, PDO::PARAM_INT);
    //         $stmt->bindParam(':nombreSeccion', $nombreSeccion, PDO::PARAM_STR);
    //         $stmt->execute();

    //         $count = (int) $stmt->fetchColumn();

    //         if ($count > 0) {
    //             $r['resultado'] = 'existe';
    //             $r['mensaje'] = 'La SECCIÓN colocada YA existe!';
    //         }
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }
    //     return $r;
    // }

    public function ExisteSeccion($seccionId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_seccion WHERE sec_id=:seccionId AND sec_estado = 1");

            $stmt->bindParam(':seccionId', $seccionId, PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La SECCIÓN colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function obtenerTrayectos()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT t.tra_id, t.tra_numero, t.ani_id, a.ani_anio 
                            FROM tbl_trayecto t
                            INNER JOIN tbl_anio a ON t.ani_id = a.ani_id
                            WHERE t.tra_estado = 1 AND a.ani_activo = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    function obtenerCohorte()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT coh_id, coh_numero FROM tbl_cohorte WHERE coh_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }

    // function Unir($secciones)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];
    //     $maxCapacidad = 45;

    //     try {
    //         $co->beginTransaction();

    //         $seccionesArray = json_decode($secciones, true);
    //         if (json_last_error() !== JSON_ERROR_NONE || !is_array($seccionesArray) || empty($seccionesArray)) {
    //             throw new Exception("Datos de secciones para unir inválidos.");
    //         }

    //         $in = implode(',', array_map('intval', $seccionesArray));

    //         $stmtInfoSecciones = $co->query("
    //             SELECT tra_id, SUM(sec_cantidad) as total_cantidad, COUNT(DISTINCT tra_id) as count_trayectos
    //             FROM tbl_seccion 
    //             WHERE sec_id IN ($in) AND sec_estado = 1
    //         ");
    //         $info = $stmtInfoSecciones->fetch(PDO::FETCH_ASSOC);

    //         if (!$info || $info['count_trayectos'] === null) {
    //             throw new Exception("Una o más secciones seleccionadas no son válidas o están inactivas.");
    //         }
    //         if ($info['count_trayectos'] > 1) {
    //             throw new Exception("Las secciones seleccionadas NO pertenecen al mismo trayecto.");
    //         }
    //         if ((int)$info['total_cantidad'] > $maxCapacidad) {
    //             throw new Exception("La suma de estudiantes (" . $info['total_cantidad'] . ") de las secciones a unir excede la capacidad máxima de " . $maxCapacidad . ".");
    //         }


    //         $stmtCheck = $co->prepare("
    //             SELECT COUNT(*) AS cnt
    //             FROM seccion_grupo sg
    //             INNER JOIN tbl_grupo g ON sg.gro_id = g.gro_id
    //             WHERE sg.sec_id = :seccionId AND g.grupo_estado = 1
    //         ");

    //         foreach ($seccionesArray as $seccionId) {
    //             $stmtCheck->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
    //             $stmtCheck->execute();
    //             $count = (int)$stmtCheck->fetchColumn();

    //             if ($count > 0) {
    //                 throw new Exception("Al menos UNA de las SECCIONES <br/> YA pertenece a un grupo activo.");
    //             }
    //         }

    //         $stmtGrupo = $co->prepare("
    //             INSERT INTO tbl_grupo (grupo_estado) 
    //             VALUES (1)
    //         ");
    //         $stmtGrupo->execute();
    //         $grupoId = $co->lastInsertId();

    //         $stmtLink = $co->prepare("
    //             INSERT INTO seccion_grupo (gro_id, sec_id) 
    //             VALUES (:grupoId, :seccionId)
    //         ");
    //         $stmtLink->bindParam(':grupoId', $grupoId, PDO::PARAM_INT);

    //         foreach ($seccionesArray as $seccionId) {
    //             $stmtLink->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
    //             $stmtLink->execute();
    //         }

    //         $co->commit();

    //         $r['resultado'] = 'unir';
    //         $r['mensaje']   = 'Secciones unidas!<br/>Se unieron las secciones correctamente!';
    //     } catch (Exception $e) {
    //         $co->rollBack();
    //         $r['resultado'] = 'error';
    //         $r['mensaje']   = $e->getMessage();
    //     } finally {
    //         $co = null;
    //     }

    //     return $r;
    // }

    // function Separar()
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => null, 'mensaje' => null];

    //     try {
    //         $stmt = $co->prepare("UPDATE tbl_grupo
    //         SET grupo_estado = 0
    //         WHERE gro_id = :grupoId");
    //         $stmt->bindParam(':grupoId', $this->grupo, PDO::PARAM_INT);
    //         $stmt->execute();

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

    // public function ObtenerSeccionesDestinoElegibles($seccionesOrigenIds)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => 'obtenerSeccionesDestino', 'mensaje' => []];

    //     if (empty($seccionesOrigenIds)) {
    //         $r['mensaje'] = [];
    //         $co = null;
    //         return $r;
    //     }

    //     try {
    //         $placeholdersOrigen = implode(',', array_fill(0, count($seccionesOrigenIds), '?'));

    //         $sqlCheckTrayectosOrigen = "
    //             SELECT s.sec_id, t.tra_id, t.tra_numero, t.tra_anio
    //             FROM tbl_seccion s
    //             JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //             WHERE s.sec_id IN ($placeholdersOrigen) AND s.sec_estado = 1
    //         ";
    //         $stmtCheckTrayectosOrigen = $co->prepare($sqlCheckTrayectosOrigen);
    //         $stmtCheckTrayectosOrigen->execute($seccionesOrigenIds);
    //         $seccionesOrigenInfo = $stmtCheckTrayectosOrigen->fetchAll(PDO::FETCH_ASSOC);

    //         if (empty($seccionesOrigenInfo) || count($seccionesOrigenInfo) != count(array_unique($seccionesOrigenIds))) {
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'Una o más secciones de origen no son válidas o no se encontraron.';
    //             $co = null;
    //             return $r;
    //         }

    //         $trayectoComunInfo = null;
    //         foreach ($seccionesOrigenInfo as $i => $info) {
    //             if ($i == 0) {
    //                 $trayectoComunInfo = ['tra_id' => $info['tra_id'], 'tra_numero' => (int)$info['tra_numero'], 'tra_anio' => (int)$info['tra_anio']];
    //             } elseif ($info['tra_id'] != $trayectoComunInfo['tra_id']) {
    //                 $r['resultado'] = 'error';
    //                 $r['mensaje'] = "Las secciones seleccionadas NO pertenecen al mismo trayecto.";
    //                 $co = null;
    //                 return $r;
    //             }
    //         }

    //         $traAnioOrigen = $trayectoComunInfo['tra_anio'];
    //         $traNumeroOrigen = $trayectoComunInfo['tra_numero'];

    //         $sqlDestino = "
    //             SELECT s.sec_id, s.sec_codigo, t.tra_numero, t.tra_anio
    //             FROM tbl_seccion s
    //             JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //             WHERE s.sec_estado = 1 
    //               AND (
    //                   (t.tra_anio = ? AND t.tra_numero > ?) OR /* Mismo año, trayecto superior */
    //                   (t.tra_anio > ?) /* Año superior */
    //               )
    //               AND s.sec_id NOT IN ($placeholdersOrigen) /* No incluir las secciones origen como destino */
    //             ORDER BY t.tra_anio ASC, t.tra_numero ASC, s.sec_codigo ASC
    //         ";

    //         $stmtDestino = $co->prepare($sqlDestino);
    //         $paramsForDestino = array_merge([$traAnioOrigen, $traNumeroOrigen, $traAnioOrigen], $seccionesOrigenIds);
    //         $stmtDestino->execute($paramsForDestino);

    //         $seccionesDestino = $stmtDestino->fetchAll(PDO::FETCH_ASSOC);
    //         $r['mensaje'] = $seccionesDestino;
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = "Error al obtener secciones destino: " . $e->getMessage();
    //     } finally {
    //         if ($co) {
    //             $co = null;
    //         }
    //     }
    //     return $r;
    // }

    // public function PromocionarSecciones($seccionesOrigenIdsOriginal, $seccionDestinoId)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = ['resultado' => 'promocionar', 'mensaje' => ''];
    //     $maxCapacidad = 45;

    //     $seccionesOrigenIds = array_values(array_diff($seccionesOrigenIdsOriginal, [$seccionDestinoId]));

    //     try {
    //         $co->beginTransaction();

    //         $stmtCheckDestino = $co->prepare("
    //             SELECT s.sec_id, s.tra_id, s.sec_cantidad, t.tra_numero, t.tra_anio 
    //             FROM tbl_seccion s
    //             JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //             WHERE s.sec_id = :sec_id AND s.sec_estado = 1
    //         ");
    //         $stmtCheckDestino->bindParam(':sec_id', $seccionDestinoId, PDO::PARAM_INT);
    //         $stmtCheckDestino->execute();
    //         $destino = $stmtCheckDestino->fetch(PDO::FETCH_ASSOC);

    //         if (!$destino) {
    //             $co->rollBack();
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'La sección no es válida.';
    //             if ($co) {
    //                 $co = null;
    //             }
    //             return $r;
    //         }
    //         $cantidadOriginalDestino = (int)$destino['sec_cantidad'];
    //         $traAnioDestino = (int)$destino['tra_anio'];
    //         $traNumeroDestino = (int)$destino['tra_numero'];

    //         $placeholdersOrigen = implode(',', array_fill(0, count($seccionesOrigenIds), '?'));
    //         $sqlInfoOrigen = "
    //             SELECT s.sec_id, s.tra_id, s.sec_cantidad, t.tra_numero, t.tra_anio
    //             FROM tbl_seccion s
    //             JOIN tbl_trayecto t ON s.tra_id = t.tra_id
    //             WHERE s.sec_id IN ($placeholdersOrigen) AND s.sec_estado = 1
    //         ";
    //         $stmtInfoOrigen = $co->prepare($sqlInfoOrigen);
    //         $stmtInfoOrigen->execute($seccionesOrigenIds);
    //         $seccionesOrigenDetalles = $stmtInfoOrigen->fetchAll(PDO::FETCH_ASSOC);

            

    //         $trayectoOrigenComun = null;
    //         $sumaCantidadOrigen = 0;
    //         foreach ($seccionesOrigenDetalles as $i => $origen) {
    //             if ($i == 0) {
    //                 $trayectoOrigenComun = ['tra_id' => $origen['tra_id'], 'tra_numero' => (int)$origen['tra_numero'], 'tra_anio' => (int)$origen['tra_anio']];
    //             } elseif ($origen['tra_id'] != $trayectoOrigenComun['tra_id']) {
    //                 $co->rollBack();
    //                 $r['resultado'] = 'error';
    //                 $r['mensaje'] = 'Todas las secciones deben pertenecer al mismo trayecto.';
    //                 if ($co) {
    //                     $co = null;
    //                 }
    //                 return $r;
    //             }
    //             $sumaCantidadOrigen += (int)$origen['sec_cantidad'];
    //         }

    //         if (!(($traAnioDestino > $trayectoOrigenComun['tra_anio']) ||
    //             ($traAnioDestino == $trayectoOrigenComun['tra_anio'] && $traNumeroDestino > $trayectoOrigenComun['tra_numero']))) {
    //             $co->rollBack();
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = 'La sección debe pertenecer a un trayecto superior.';
    //             if ($co) {
    //                 $co = null;
    //             }
    //             return $r;
    //         }

    //         $nuevaCantidadDestino = $cantidadOriginalDestino + $sumaCantidadOrigen;
    //         if ($nuevaCantidadDestino > $maxCapacidad) {
    //             $co->rollBack();
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = "La capacidad de la sección destino se excede del máximo (Actual: $cantidadOriginalDestino, Nueva: $nuevaCantidadDestino, Máx: $maxCapacidad).";
    //             if ($co) {
    //                 $co = null;
    //             }
    //             return $r;
    //         }

    //         $stmtUpdateDestinoCantidad = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = :cantidad WHERE sec_id = :sec_id");
    //         $stmtUpdateDestinoCantidad->bindParam(':cantidad', $nuevaCantidadDestino, PDO::PARAM_INT);
    //         $stmtUpdateDestinoCantidad->bindParam(':sec_id', $seccionDestinoId, PDO::PARAM_INT);
    //         $stmtUpdateDestinoCantidad->execute();

    //         $stmtGrupoCheck = $co->prepare("
    //             SELECT sg.gro_id 
    //             FROM seccion_grupo sg
    //             JOIN tbl_grupo g ON sg.gro_id = g.gro_id
    //             WHERE sg.sec_id = :sec_id AND g.grupo_estado = 1
    //         ");
    //         $stmtSepararGrupo = $co->prepare("UPDATE tbl_grupo SET grupo_estado = 0 WHERE gro_id = :gro_id");
    //         $stmtDesactivarSeccionOrigen = $co->prepare("UPDATE tbl_seccion SET sec_estado = 0 WHERE sec_id = :sec_id");
    //         $stmtInsertPromocion = $co->prepare("
    //             INSERT INTO tbl_promocion (sec_id_origen, sec_id_promocion) 
    //             VALUES (:sec_id_origen, :sec_id_promocion)
    //         ");
    //         $stmtInsertPromocion->bindParam(':sec_id_promocion', $seccionDestinoId, PDO::PARAM_INT);

    //         $countPromocionadas = 0;
    //         foreach ($seccionesOrigenIds as $origenId) {
    //             $stmtGrupoCheck->bindParam(':sec_id', $origenId, PDO::PARAM_INT);
    //             $stmtGrupoCheck->execute();
    //             $grupo = $stmtGrupoCheck->fetch(PDO::FETCH_ASSOC);
    //             if ($grupo) {
    //                 $stmtSepararGrupo->bindParam(':gro_id', $grupo['gro_id'], PDO::PARAM_INT);
    //                 $stmtSepararGrupo->execute();
    //             }

    //             $stmtDesactivarSeccionOrigen->bindParam(':sec_id', $origenId, PDO::PARAM_INT);
    //             $stmtDesactivarSeccionOrigen->execute();

    //             $stmtInsertPromocion->bindParam(':sec_id_origen', $origenId, PDO::PARAM_INT);
    //             $stmtInsertPromocion->execute();
    //             $countPromocionadas++;
    //         }

    //         $co->commit();
    //         $r['mensaje'] = "¡Secciones promovidas correctamente!<br/> Cantidad de la sección: " . $nuevaCantidadDestino;
    //     } catch (Exception $e) {
    //         if ($co->inTransaction()) {
    //             $co->rollBack();
    //         }
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = "Error: " . $e->getMessage();
    //     } finally {
    //         if ($co) {
    //             $co = null;
    //         }
    //     }
    //     return $r;
    // }
}
