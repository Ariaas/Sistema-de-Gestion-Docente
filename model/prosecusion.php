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
        $r = ['anio_activo_existe' => false];
        try {
            $stmtAnio = $co->query("SELECT COUNT(*) FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1");
            if ($stmtAnio->fetchColumn() > 0) {
                $r['anio_activo_existe'] = true;
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
            $stmtOrigen = $co->prepare("SELECT ani_anio FROM tbl_seccion WHERE sec_codigo = ?");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origenAnio = $stmtOrigen->fetchColumn();

            if (!$origenAnio) {
                return ['resultado' => 'error', 'mensaje' => 'Año de origen no encontrado.'];
            }

            $anioDestino = intval($origenAnio) + 1;

            $trayecto_origen = substr($seccionOrigenCodigo, 0, 1);
            $trayecto_destino = intval($trayecto_origen) + 1;
            $ultimo_digito_origen = substr($seccionOrigenCodigo, -1);

            $stmt2 = $co->prepare("
                SELECT s.sec_codigo, a.ani_anio
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE a.ani_anio = ? 
                  AND s.sec_estado = 1 
                  AND a.ani_tipo != 'intensivo'
                  AND s.sec_codigo LIKE ? 
                  AND s.sec_codigo LIKE ?
            ");

            $stmt2->execute([$anioDestino, $trayecto_destino . '%', '%' . $ultimo_digito_origen]);
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



    public function calcularCantidadProsecusion($seccionCodigo)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['puede_prosecusionar' => false, 'cantidad_final' => 0, 'mensaje' => ''];

        try {
            $stmtOrigen = $co->prepare("SELECT ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
            $stmtOrigen->execute([$seccionCodigo]);
            $origen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                $r['mensaje'] = 'La sección de origen no es válida o está inactiva.';
                return $r;
            }

            $stmtAprobados = $co->prepare("SELECT SUM(apro_cantidad) as total FROM tbl_aprobados WHERE sec_codigo = ? AND apro_estado = 1");
            $stmtAprobados->execute([$seccionCodigo]);
            $aprobados_regulares = (int)$stmtAprobados->fetchColumn();

            $stmtPer = $co->prepare("SELECT SUM(per_aprobados) as total FROM per_aprobados WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtPer->execute([$seccionCodigo, $origen['ani_anio'], $origen['ani_tipo']]);
            $aprobados_per = (int)$stmtPer->fetchColumn();

            $cantidad_final = $aprobados_regulares + $aprobados_per;

            if ($cantidad_final <= 0) {
                $r['mensaje'] = 'No hay datos suficientes sobre las notas de esta sección.';
                $r['cantidad_final'] = 0;
                return $r;
            }

            $r['puede_prosecusionar'] = true;
            $r['cantidad_final'] = $cantidad_final;
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
            return ['resultado' => 'error', 'mensaje' => 'No hay datos suficientes sobre las notas de esta sección.'];
        }

        $co = $this->Con();

        if ($seccionDestinoCodigo === null) { 
            $stmtOrigen = $co->prepare("SELECT ani_anio FROM tbl_seccion WHERE sec_codigo = ?");
            $stmtOrigen->execute([$seccionOrigenCodigo]);
            $origenAnio = $stmtOrigen->fetchColumn();

            if (!$origenAnio) {
                return ['resultado' => 'error', 'mensaje' => 'Sección de origen no encontrada para prosecusión automática.'];
            }

            $trayecto_origen = substr($seccionOrigenCodigo, 0, 1);
            $resto_codigo = substr($seccionOrigenCodigo, 1);
            $trayecto_destino = intval($trayecto_origen) + 1;
            $seccionDestinoCodigo = $trayecto_destino . $resto_codigo;
        }

        $stmtDestino = $co->prepare("SELECT sec_cantidad FROM tbl_seccion WHERE sec_codigo = ? AND sec_estado = 1");
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
    }

    public function ProsecusionSeccion($seccionOrigenCodigo, $seccionDestinoCodigo, $cantidadFinal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'prosecusion', 'mensaje' => '', 'seccionDestinoCodigo' => $seccionDestinoCodigo];

        try {
            $co->beginTransaction();

            $stmtInsert = $co->prepare("INSERT INTO tbl_prosecusion (sec_origen, sec_promocion, pro_estado) VALUES (?, ?, 1)");
            $stmtInsert->execute([$seccionOrigenCodigo, $seccionDestinoCodigo]);

            $stmtUpdateOrigen = $co->prepare("UPDATE tbl_seccion SET sec_estado = 0 WHERE sec_codigo = ?");
            $stmtUpdateOrigen->execute([$seccionOrigenCodigo]);

            $stmtUpdateDestino = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = sec_cantidad + ? WHERE sec_codigo = ?");
            $stmtUpdateDestino->execute([$cantidadFinal, $seccionDestinoCodigo]);

            $co->commit();

            $r['mensaje'] = 'Prosecusión realizada correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
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
                p.sec_promocion,
                so.sec_codigo as origen_codigo,
                so.sec_cantidad as origen_cantidad,
                ao.ani_anio as origen_anio,
                sd.sec_codigo as destino_codigo,
                sd.sec_cantidad as destino_cantidad,
                ad.ani_anio as destino_anio
            FROM tbl_prosecusion p
            INNER JOIN tbl_seccion so ON p.sec_origen = so.sec_codigo
            INNER JOIN tbl_anio ao ON so.ani_anio = ao.ani_anio AND so.ani_tipo = ao.ani_tipo
            INNER JOIN tbl_seccion sd ON p.sec_promocion = sd.sec_codigo
            INNER JOIN tbl_anio ad ON sd.ani_anio = ad.ani_anio AND sd.ani_tipo = ad.ani_tipo
            WHERE p.pro_estado = 1
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
            $currentYear = date('Y');

            $stmt = $co->prepare("
                SELECT s.sec_codigo, s.sec_cantidad, a.ani_anio
                FROM tbl_seccion s
                INNER JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                WHERE s.sec_estado = 1 
                AND a.ani_activo = 0 
                AND a.ani_anio = ?
                AND a.ani_tipo != 'intensivo'
                AND NOT EXISTS (
                    SELECT 1 FROM tbl_prosecusion p WHERE p.sec_origen = s.sec_codigo
                )
            ");
            $stmt->execute([$currentYear]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        list($sec_origen, $sec_promocion) = explode('-', $this->pro_id);


        try {
            $co->beginTransaction();

            $stmtCantidadOrigen = $co->prepare("SELECT sec_cantidad FROM tbl_seccion WHERE sec_codigo = ?");
            $stmtCantidadOrigen->execute([$sec_origen]);
            $cantidad_origen = (int)$stmtCantidadOrigen->fetchColumn();

            $stmtRevertirDestino = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = sec_cantidad - ? WHERE sec_codigo = ?");
            $stmtRevertirDestino->execute([$cantidad_origen, $sec_promocion]);

            $stmtDeleteProsecusion = $co->prepare("DELETE FROM tbl_prosecusion WHERE sec_origen = ? AND sec_promocion = ?");
            $stmtDeleteProsecusion->execute([$sec_origen, $sec_promocion]);

            $stmtReactivarOrigen = $co->prepare("UPDATE tbl_seccion SET sec_estado = 1 WHERE sec_codigo = ?");
            $stmtReactivarOrigen->execute([$sec_origen]);

            $co->commit();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la PROSECUSIÓN correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar la prosecusión: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }
}
