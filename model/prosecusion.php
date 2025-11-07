<?php

namespace App\Model;

use PDO;
use Exception;

class Prosecusion extends Connection
{
    private $pro_id;
    private $pro_estado;

    public function __construct($pro_id = null, $pro_estado = null)
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
                $r['anio_activo'] = (int)$anio_activo;

                $anio_destino = $r['anio_activo'] + 1;
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
            $stmtOrigen = $co->prepare("
                SELECT s.ani_anio, s.ani_tipo
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND s.sec_estado = 1
                  AND a.ani_activo = 1
                  AND a.ani_tipo = 'regular'
            ");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                return ['resultado' => 'error', 'mensaje' => 'No se encontró la sección de origen en el año activo.'];
            }

            $anioDestino = (int)$origen['ani_anio'] + 1;
            $aniTipoDestino = $origen['ani_tipo'];

            $prefijo = '';
            $indiceTrayecto = 0;
            if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                $prefijo = 'IIN';
                $indiceTrayecto = 3;
            } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                $prefijo = 'IN';
                $indiceTrayecto = 2;
            }

            $trayectoActual = (int)substr($seccionOrigenCodigo, $indiceTrayecto, 1);
            $ultimoDigito = substr($seccionOrigenCodigo, -1);
            $trayectoSiguiente = $trayectoActual + 1;

            $prefijoDestino = $prefijo;
            if ($trayectoSiguiente === 3 && $prefijo === 'IN') {
                $prefijoDestino = 'IIN';
            }

            $patronActual = $prefijo . $trayectoActual . '%';
            $patronSiguiente = $prefijoDestino . $trayectoSiguiente . '%';
            $patronUltimo = '%' . $ultimoDigito;

            $stmtDestinos = $co->prepare("
                SELECT s.sec_codigo, a.ani_anio
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE a.ani_anio = ?
                  AND a.ani_tipo = ?
                  AND s.sec_estado = 1
                  AND (s.sec_codigo LIKE ? OR s.sec_codigo LIKE ?)
                  AND s.sec_codigo LIKE ?
                ORDER BY s.sec_codigo
            ");
            $stmtDestinos->execute([$anioDestino, $aniTipoDestino, $patronActual, $patronSiguiente, $patronUltimo]);
            $r['mensaje'] = $stmtDestinos->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Error al buscar opciones: ' . $e->getMessage();
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
            $stmtOrigen = $co->prepare("
                SELECT s.ani_anio, s.ani_tipo
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND s.sec_estado = 1
                  AND a.ani_activo = 1
                  AND a.ani_tipo = 'regular'
            ");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                return $r;
            }

            $anioDestino = (int)$origen['ani_anio'] + 1;
            $aniTipoDestino = $origen['ani_tipo'];

            $prefijo = '';
            $indiceTrayecto = 0;
            if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                $prefijo = 'IIN';
                $indiceTrayecto = 3;
            } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                $prefijo = 'IN';
                $indiceTrayecto = 2;
            }

            $trayectoActual = (int)substr($seccionOrigenCodigo, $indiceTrayecto, 1);
            $restoCodigo = substr($seccionOrigenCodigo, $indiceTrayecto + 1);
            $trayectoSiguiente = $trayectoActual + 1;

            if ($trayectoSiguiente === 3 && $prefijo === 'IN') {
                $prefijo = 'IIN';
            }

            $seccionDestinoCodigo = $prefijo . $trayectoSiguiente . $restoCodigo;

            $stmtDestino = $co->prepare("
                SELECT 1
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND s.sec_estado = 1
                  AND a.ani_anio = ?
                  AND a.ani_tipo = ?
            ");
            $stmtDestino->execute([$seccionDestinoCodigo, $anioDestino, $aniTipoDestino]);

            if ($stmtDestino->fetchColumn()) {
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
        $r = [
            'puede_prosecusionar' => false,
            'cantidad_final' => 0,
            'cantidad_disponible' => 0,
            'mensaje' => '',
            'anio_origen' => null,
            'ani_tipo_origen' => null,
        ];

        try {
            $stmtOrigen = $co->prepare("
                SELECT s.sec_cantidad, s.ani_anio, s.ani_tipo
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND s.sec_estado = 1
                  AND a.ani_activo = 1
                  AND a.ani_tipo = 'regular'
            ");
            $stmtOrigen->execute([$seccionCodigo]);
            $origen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                $r['mensaje'] = 'La sección de origen no es válida o no pertenece al año activo.';
                return $r;
            }

            $cantidadTotal = (int)$origen['sec_cantidad'];
            $anioOrigen = (int)$origen['ani_anio'];
            $aniTipoOrigen = $origen['ani_tipo'];

            $stmtProsecusionados = $co->prepare("
                SELECT COALESCE(SUM(pro_cantidad), 0)
                FROM tbl_prosecusion
                WHERE sec_origen = ?
                  AND ani_origen = ?
                  AND ani_tipo_origen = ?
                  AND pro_estado = 1
            ");
            $stmtProsecusionados->execute([$seccionCodigo, $anioOrigen, $aniTipoOrigen]);
            $cantidadProsecusionada = (int)$stmtProsecusionados->fetchColumn();

            $cantidadDisponible = $cantidadTotal - $cantidadProsecusionada;

            if ($cantidadDisponible <= 0) {
                $r['mensaje'] = 'No quedan estudiantes disponibles para prosecusionar de esta sección.';
                return $r;
            }

            $r['puede_prosecusionar'] = true;
            $r['cantidad_final'] = $cantidadDisponible;
            $r['cantidad_disponible'] = $cantidadDisponible;
            $r['mensaje'] = 'Cálculo exitoso.';
            $r['anio_origen'] = $anioOrigen;
            $r['ani_tipo_origen'] = $aniTipoOrigen;
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

        $cantidad = (int)$cantidad;

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $calculoCantidad = $this->calcularCantidadProsecusion($seccionOrigenCodigo);
            if (!$calculoCantidad['puede_prosecusionar']) {
                return ['resultado' => 'error', 'mensaje' => $calculoCantidad['mensaje']];
            }

            if ($cantidad > $calculoCantidad['cantidad_disponible']) {
                $disponible = $calculoCantidad['cantidad_disponible'];
                return ['resultado' => 'error', 'mensaje' => "Solo hay {$disponible} estudiantes disponibles para prosecusionar de esta sección."];
            }

            $anioOrigen = $calculoCantidad['anio_origen'];
            $aniTipoOrigen = $calculoCantidad['ani_tipo_origen'];

            if ($anioOrigen === null || $aniTipoOrigen === null) {
                return ['resultado' => 'error', 'mensaje' => 'No se pudo determinar el año de la sección de origen.'];
            }

            $anioDestino = $anioOrigen + 1;

            if ($seccionDestinoCodigo === null) {
                $prefijo = '';
                $indiceTrayecto = 0;
                if (strpos($seccionOrigenCodigo, 'IIN') === 0) {
                    $prefijo = 'IIN';
                    $indiceTrayecto = 3;
                } elseif (strpos($seccionOrigenCodigo, 'IN') === 0) {
                    $prefijo = 'IN';
                    $indiceTrayecto = 2;
                }

                $trayectoActual = (int)substr($seccionOrigenCodigo, $indiceTrayecto, 1);
                $restoCodigo = substr($seccionOrigenCodigo, $indiceTrayecto + 1);
                $trayectoSiguiente = $trayectoActual + 1;

                if ($trayectoSiguiente === 3 && $prefijo === 'IN') {
                    $prefijo = 'IIN';
                }

                $seccionDestinoCodigo = $prefijo . $trayectoSiguiente . $restoCodigo;
            }

            $stmtDestino = $co->prepare("
                SELECT s.sec_cantidad, s.ani_anio, s.ani_tipo
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_codigo = ?
                  AND s.sec_estado = 1
                  AND a.ani_anio = ?
                  AND a.ani_tipo = 'regular'
                  AND a.ani_estado = 1
            ");
            $stmtDestino->execute([$seccionDestinoCodigo, $anioDestino]);
            $destino = $stmtDestino->fetch(PDO::FETCH_ASSOC);

            if (!$destino) {
                return ['resultado' => 'error', 'mensaje' => "La sección destino '{$seccionDestinoCodigo}' no existe o no está activa. No se puede realizar la prosecusión."];
            }

            $cantidadDestinoActual = (int)$destino['sec_cantidad'];
            $nuevaCantidadTotal = $cantidadDestinoActual + $cantidad;
            $aniTipoDestino = $destino['ani_tipo'];

            if ($nuevaCantidadTotal > 45 && !$confirmarExceso) {
                $mensaje = "La sección destino {$seccionDestinoCodigo} tiene actualmente {$cantidadDestinoActual} estudiantes y al sumar {$cantidad} alcanzará {$nuevaCantidadTotal}, superando el límite de 45. ¿Desea continuar?";
                return ['resultado' => 'confirmacion_requerida', 'mensaje' => $mensaje];
            }

            return $this->ProsecusionSeccion(
                $seccionOrigenCodigo,
                $anioOrigen,
                $aniTipoOrigen,
                $seccionDestinoCodigo,
                (int)$destino['ani_anio'],
                $aniTipoDestino,
                $cantidad,
                $cantidadDestinoActual
            );
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error: ' . $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function ProsecusionSeccion($seccionOrigenCodigo, $anioOrigen, $aniTipoOrigen, $seccionDestinoCodigo, $anioDestino, $aniTipoDestino, $cantidadFinal, $cantidadDestinoActual)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'prosecusion', 'mensaje' => '', 'seccionDestinoCodigo' => $seccionDestinoCodigo];

        try {
            $co->beginTransaction();

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
              WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ? AND sec_estado = 1
            ");
            $stmtUpdateDestino->execute([$cantidadFinal, $seccionDestinoCodigo, $anioDestino, $aniTipoDestino]);

            $co->commit();

            $totalDestino = $cantidadDestinoActual + $cantidadFinal;
            $r['mensaje'] = "Prosecusión realizada correctamente. La sección destino {$seccionDestinoCodigo} ahora tiene {$totalDestino} estudiantes.";
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Error en la prosecusión: ' . $e->getMessage();
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
