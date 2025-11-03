<?php
require_once('model/dbconnection.php');

class Prosecusion extends Connection
{
    private $pro_id;
    private $pro_estado;

    public function __construct($pro_id = NULL, $pro_estado = NULL)
    {
        $this->pro_id = $pro_id;
        $this->pro_estado = $pro_estado;
        parent::__construct();
    }

    public function setProId($pro_id)
    {
        $this->pro_id = $pro_id;
    }
    public function setEstado($pro_estado)
    {
        $this->pro_estado = $pro_estado;
    }
    public function getProId()
    {
        return $this->pro_id;
    }
    public function getEstado()
    {
        return $this->pro_estado;
    }


    public function VerificarEstado()
    {
        $co = $this->Con();
        $r = ['anio_activo_existe' => false, 'anio_activo' => null, 'anio_destino_existe' => false];
        try {
            $stmtAnio = $co->query("SELECT ani_anio FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1 AND ani_tipo = 'regular'");
            $anio_activo = $stmtAnio->fetchColumn();

            if ($anio_activo) {
                $r['anio_activo_existe'] = true;
                $r['anio_activo'] = intval($anio_activo);

                $anio_destino = intval($anio_activo) + 1;
                $stmtDestino = $co->prepare("SELECT COUNT(*) FROM tbl_anio WHERE ani_anio = ? AND ani_estado = 1 AND ani_tipo = 'regular'");
                $stmtDestino->execute([$anio_destino]);
                if ($stmtDestino->fetchColumn() > 0) {
                    $r['anio_destino_existe'] = true;
                }
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function obtenerOpcionesDestinoManual($seccionOrigenCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'opcionesDestinoManual', 'mensaje' => []];

        try {
            $stmtOrigen = $co->prepare("SELECT s.ani_anio FROM tbl_seccion s INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo WHERE s.sec_codigo = ? AND a.ani_tipo = 'regular'");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origenAnio = $stmtOrigen->fetchColumn();

            if (!$origenAnio) {
                return ['resultado' => 'error', 'mensaje' => 'Año de origen no encontrado.'];
            }

            $anioDestino = intval($origenAnio) + 1;

            $prefijo = '';
            $digito_inicio = 0;
            if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                $prefijo = 'IIN';
                $digito_inicio = 3;
            } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                $prefijo = 'IN';
                $digito_inicio = 2;
            }

            $trayecto_actual = intval(substr($seccionOrigenCodigo, $digito_inicio, 1));
            $ultimo_digito_origen = substr($seccionOrigenCodigo, -1);
            $trayecto_siguiente = $trayecto_actual + 1;

            $prefijo_destino = $prefijo;
            if ($trayecto_siguiente == 3 && $prefijo == 'IN') {
                $prefijo_destino = 'IIN';
            }

            $stmt2 = $co->prepare("
                SELECT s.sec_codigo, a.ani_anio
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE a.ani_anio = ? 
                  AND s.sec_estado = 1 
                  AND a.ani_tipo = 'regular'
                  AND (s.sec_codigo LIKE ? OR s.sec_codigo LIKE ?)
                  AND s.sec_codigo LIKE ?
                ORDER BY s.sec_codigo
            ");

            $patron_actual = $prefijo . $trayecto_actual . '%';
            $patron_siguiente = $prefijo_destino . $trayecto_siguiente . '%';
            $patron_ultimo = '%' . $ultimo_digito_origen;

            $stmt2->execute([$anioDestino, $patron_actual, $patron_siguiente, $patron_ultimo]);
            $destinos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $r['mensaje'] = $destinos;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al buscar opciones: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function verificarDestinoAutomatico($seccionOrigenCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['existe' => false, 'seccion_destino' => null];

        try {
            $stmtOrigen = $co->prepare("SELECT s.ani_anio FROM tbl_seccion s INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo WHERE s.sec_codigo = ? AND a.ani_tipo = 'regular'");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origenAnio = $stmtOrigen->fetchColumn();

            if (!$origenAnio) {
                return $r;
            }

            $anioDestino = intval($origenAnio) + 1;

            $prefijo = '';
            $digito_inicio = 0;
            if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                $prefijo = 'IIN';
                $digito_inicio = 3;
            } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                $prefijo = 'IN';
                $digito_inicio = 2;
            }

            $trayecto_actual = intval(substr($seccionOrigenCodigo, $digito_inicio, 1));
            $resto_codigo = substr($seccionOrigenCodigo, $digito_inicio + 1);
            $trayecto_siguiente = $trayecto_actual + 1;

            if ($trayecto_siguiente == 3 && $prefijo == 'IN') {
                $prefijo = 'IIN';
            }

            $seccionDestinoCodigo = $prefijo . $trayecto_siguiente . $resto_codigo;

            $stmtDestino = $co->prepare("
                SELECT s.sec_codigo 
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND a.ani_anio = ?
                  AND s.sec_estado = 1
                  AND a.ani_tipo = 'regular'
            ");
            $stmtDestino->execute([$seccionDestinoCodigo, $anioDestino]);
            $existe = $stmtDestino->fetchColumn();

            if ($existe) {
                $r['existe'] = true;
                $r['seccion_destino'] = $seccionDestinoCodigo;
            }
        } catch (Exception $e) {
            $r['error'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }



    public function calcularCantidadProsecusion($seccionCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['puede_prosecusionar' => false, 'cantidad_final' => 0, 'cantidad_disponible' => 0, 'mensaje' => ''];

        try {
            $stmtOrigen = $co->prepare("SELECT sec_cantidad FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
            $stmtOrigen->execute([$seccionCodigo]);
            $cantidad_total = $stmtOrigen->fetchColumn();

            if ($cantidad_total === false || $cantidad_total === null) {
                $r['mensaje'] = 'La sección de origen no es válida o está inactiva.';
                return $r;
            }

            $cantidad_total = (int)$cantidad_total;

            $stmtProsecusionados = $co->prepare("
                SELECT COALESCE(SUM(pro_cantidad), 0) as total_prosecusionado
                FROM tbl_prosecusion
                WHERE sec_origen = ? AND pro_estado = 1
            ");
            $stmtProsecusionados->execute([$seccionCodigo]);
            $cantidad_prosecusionada = (int)$stmtProsecusionados->fetchColumn();

            $cantidad_disponible = $cantidad_total - $cantidad_prosecusionada;

            if ($cantidad_disponible <= 0) {
                $r['mensaje'] = 'No quedan estudiantes disponibles para prosecusionar de esta sección.';
                $r['cantidad_final'] = 0;
                $r['cantidad_disponible'] = 0;
                return $r;
            }

            $r['puede_prosecusionar'] = true;
            $r['cantidad_final'] = $cantidad_disponible;
            $r['cantidad_disponible'] = $cantidad_disponible;
            $r['mensaje'] = 'Cálculo exitoso.';
        } catch (Exception $e) {
            $r['mensaje'] = 'Error al calcular la cantidad: ' . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function RealizarProsecusion($seccionOrigenCodigo, $cantidad, $seccionDestinoCodigo = null, $confirmarExceso = false)
    {
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser mayor a cero.'];
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'error', 'mensaje' => ''];

        try {
            $stmtOrigen = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            if (!$stmtOrigen->fetchColumn()) {
                return ['resultado' => 'error', 'mensaje' => "La sección de origen no es válida o está inactiva."];
            }

            $calculoCantidad = $this->calcularCantidadProsecusion($seccionOrigenCodigo);
            if (!$calculoCantidad['puede_prosecusionar']) {
                return ['resultado' => 'error', 'mensaje' => $calculoCantidad['mensaje']];
            }

            if ($cantidad > $calculoCantidad['cantidad_disponible']) {
                return ['resultado' => 'error', 'mensaje' => "Solo hay {$calculoCantidad['cantidad_disponible']} estudiantes disponibles para prosecusionar de esta sección."];
            }

            if ($seccionDestinoCodigo === null) {
                $prefijo = '';
                $digito_inicio = 0;
                if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                    $prefijo = 'IIN';
                    $digito_inicio = 3;
                } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                    $prefijo = 'IN';
                    $digito_inicio = 2;
                }

                $trayecto_actual = intval(substr($seccionOrigenCodigo, $digito_inicio, 1));
                $resto_codigo = substr($seccionOrigenCodigo, $digito_inicio + 1);
                $trayecto_siguiente = $trayecto_actual + 1;

                if ($trayecto_siguiente == 3 && $prefijo == 'IN') {
                    $prefijo = 'IIN';
                }

                $seccionDestinoCodigo = $prefijo . $trayecto_siguiente . $resto_codigo;
            }

            $stmtDestino = $co->prepare("SELECT s.sec_cantidad, s.ani_anio FROM tbl_seccion s INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo WHERE s.sec_codigo = ? AND s.sec_estado = 1 AND a.ani_tipo = 'regular'");
            $stmtDestino->execute([$seccionDestinoCodigo]);
            $destino = $stmtDestino->fetch(PDO::FETCH_ASSOC);

            if (!$destino) {
                return ['resultado' => 'error', 'mensaje' => "La sección destino '{$seccionDestinoCodigo}' no existe o no está activa. No se puede realizar la prosecusión."];
            }

            $cantidadDestinoActual = (int)$destino['sec_cantidad'];
            $nuevaCantidadTotal = $cantidadDestinoActual + $cantidad;

            if ($nuevaCantidadTotal > 45 && !$confirmarExceso) {
                return [
                    'resultado' => 'confirmacion_requerida',
                    'mensaje' => "La sección destino {$seccionDestinoCodigo} tendrá {$nuevaCantidadTotal} estudiantes, superando el límite de 45. ¿Está seguro de que desea continuar?"
                ];
            }

            return $this->ProsecusionSeccion($seccionOrigenCodigo, $seccionDestinoCodigo, $cantidad);
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error: ' . $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function ProsecusionSeccion($seccionOrigenCodigo, $seccionDestinoCodigo, $cantidadFinal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'prosecusion', 'mensaje' => '', 'seccionDestinoCodigo' => $seccionDestinoCodigo];

        try {
            $co->beginTransaction();

            $stmtOrigenAnio = $co->prepare("SELECT ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
            $stmtOrigenAnio->execute([$seccionOrigenCodigo]);
            $origenInfo = $stmtOrigenAnio->fetch(PDO::FETCH_ASSOC);

            if (!$origenInfo) {
                $co->rollBack();
                return ['resultado' => 'error', 'mensaje' => 'La sección de origen no es válida o no tiene año asociado.'];
            }

            $anioOrigen = (int)$origenInfo['ani_anio'];
            $aniTipoOrigen = $origenInfo['ani_tipo'];

            $stmtDestinoAnio = $co->prepare("SELECT ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
            $stmtDestinoAnio->execute([$seccionDestinoCodigo]);
            $destinoInfo = $stmtDestinoAnio->fetch(PDO::FETCH_ASSOC);

            if (!$destinoInfo) {
                $co->rollBack();
                return ['resultado' => 'error', 'mensaje' => 'La sección destino no es válida o no tiene año asociado.'];
            }

            $anioDestino = (int)$destinoInfo['ani_anio'];
            $aniTipoDestino = $destinoInfo['ani_tipo'];

            $stmtCheck = $co->prepare("
                SELECT pro_cantidad
                FROM tbl_prosecusion
                WHERE sec_origen = ? AND ani_origen = ? AND ani_tipo_origen = ?
                  AND sec_promocion = ? AND ani_destino = ? AND ani_tipo_destino = ?
            ");
            $stmtCheck->execute([$seccionOrigenCodigo, $anioOrigen, $aniTipoOrigen, $seccionDestinoCodigo, $anioDestino, $aniTipoDestino]);
            $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                $stmtUpdate = $co->prepare("
                    UPDATE tbl_prosecusion
                       SET pro_cantidad = pro_cantidad + ?
                     WHERE sec_origen = ? AND ani_origen = ? AND ani_tipo_origen = ?
                       AND sec_promocion = ? AND ani_destino = ? AND ani_tipo_destino = ?
                ");
                $stmtUpdate->execute([$cantidadFinal, $seccionOrigenCodigo, $anioOrigen, $aniTipoOrigen, $seccionDestinoCodigo, $anioDestino, $aniTipoDestino]);
            } else {
                $stmtInsert = $co->prepare("
                    INSERT INTO tbl_prosecusion (
                        sec_origen, ani_origen, ani_tipo_origen,
                        sec_promocion, ani_destino, ani_tipo_destino,
                        pro_cantidad, pro_estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmtInsert->execute([
                    $seccionOrigenCodigo,
                    $anioOrigen,
                    $aniTipoOrigen,
                    $seccionDestinoCodigo,
                    $anioDestino,
                    $aniTipoDestino,
                    $cantidadFinal
                ]);
            }

            $stmtUpdateDestino = $co->prepare("
                UPDATE tbl_seccion
                   SET sec_cantidad = sec_cantidad + ?
                 WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?
            ");
            $stmtUpdateDestino->execute([$cantidadFinal, $seccionDestinoCodigo, $anioDestino, $aniTipoDestino]);

            $co->commit();

            $r['mensaje'] = 'Prosecusión realizada correctamente!';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error en la prosecusión: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("
            SELECT 
                p.sec_origen,
                p.ani_origen,
                p.ani_tipo_origen,
                p.sec_promocion,
                p.ani_destino,
                p.ani_tipo_destino,
                p.pro_cantidad,
                so.sec_codigo as origen_codigo,
                so.sec_cantidad as origen_cantidad,
                sd.sec_codigo as destino_codigo,
                sd.sec_cantidad as destino_cantidad
            FROM tbl_prosecusion p
            INNER JOIN tbl_seccion so ON p.sec_origen = so.sec_codigo AND p.ani_origen = so.ani_anio AND p.ani_tipo_origen = so.ani_tipo
            INNER JOIN tbl_seccion sd ON p.sec_promocion = sd.sec_codigo AND p.ani_destino = sd.ani_anio AND p.ani_tipo_destino = sd.ani_tipo
            WHERE p.pro_estado = 1 AND p.ani_tipo_origen = 'regular' AND p.ani_tipo_destino = 'regular'
            ORDER BY p.ani_origen DESC, p.sec_origen, p.sec_promocion
        ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ListarSeccionesOrigen()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("
                SELECT s.sec_codigo, s.sec_cantidad, a.ani_anio,
                       COALESCE(SUM(p.pro_cantidad), 0) as cantidad_prosecusionada
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                LEFT JOIN tbl_prosecusion p ON p.sec_origen = s.sec_codigo AND p.ani_origen = s.ani_anio AND p.ani_tipo_origen = s.ani_tipo AND p.pro_estado = 1
                WHERE s.sec_estado = 1 
                AND a.ani_activo = 1
                AND a.ani_tipo = 'regular'
                GROUP BY s.sec_codigo, s.sec_cantidad, a.ani_anio
                HAVING (s.sec_cantidad - COALESCE(SUM(p.pro_cantidad), 0)) > 0
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array_map(function ($item) {
                $item['cantidad_disponible'] = $item['sec_cantidad'] - $item['cantidad_prosecusionada'];
                return $item;
            }, $data);

            $r['resultado'] = 'consultarSeccionesOrigen';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'error', 'mensaje' => 'ID de prosecusión no proporcionado.'];

        if (empty($this->pro_id)) {
            return $r;
        }

        $partes = explode('-', $this->pro_id);

        if (count($partes) < 4) {
            return ['resultado' => 'error', 'mensaje' => 'Formato de ID inválido.'];
        }

        $sec_origen = $partes[0];
        $ani_origen = $partes[1];
        $sec_promocion = $partes[2];
        $ani_destino = $partes[3];

        try {
            $co->beginTransaction();

            $stmtObtenerTipo = $co->prepare("SELECT ani_tipo FROM tbl_seccion WHERE sec_codigo = ? AND ani_anio = ?");
            $stmtObtenerTipo->execute([$sec_origen, $ani_origen]);
            $ani_tipo_origen = $stmtObtenerTipo->fetchColumn();

            $stmtObtenerTipo->execute([$sec_promocion, $ani_destino]);
            $ani_tipo_destino = $stmtObtenerTipo->fetchColumn();

            $stmtCantidadPro = $co->prepare("
                SELECT pro_cantidad 
                FROM tbl_prosecusion 
                WHERE sec_origen = ? AND ani_origen = ? AND ani_tipo_origen = ? AND sec_promocion = ? AND ani_destino = ? AND ani_tipo_destino = ?
            ");
            $stmtCantidadPro->execute([$sec_origen, $ani_origen, $ani_tipo_origen, $sec_promocion, $ani_destino, $ani_tipo_destino]);
            $cantidad_prosecusionada = (int)$stmtCantidadPro->fetchColumn();

            $stmtRevertirDestino = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = sec_cantidad - ? WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtRevertirDestino->execute([$cantidad_prosecusionada, $sec_promocion, $ani_destino, $ani_tipo_destino]);

            $stmtDeleteProsecusion = $co->prepare("
                DELETE FROM tbl_prosecusion 
                WHERE sec_origen = ? AND ani_origen = ? AND ani_tipo_origen = ? AND sec_promocion = ? AND ani_destino = ? AND ani_tipo_destino = ?
            ");
            $stmtDeleteProsecusion->execute([$sec_origen, $ani_origen, $ani_tipo_origen, $sec_promocion, $ani_destino, $ani_tipo_destino]);

            $co->commit();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la PROSECUSIÓN correctamente!';
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar la prosecusión: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }
}
