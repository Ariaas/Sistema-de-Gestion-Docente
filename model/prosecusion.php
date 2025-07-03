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


    public function obtenerOpcionesDestinoManual($seccionOrigenId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'opcionesDestinoManual', 'mensaje' => []];

        try {
            $stmt = $co->prepare("SELECT s.sec_id, s.sec_codigo, s.ani_id, s.sec_cantidad
            FROM tbl_seccion s
            WHERE s.sec_id = ? AND s.sec_estado = 1");
            $stmt->execute([$seccionOrigenId]);
            $origen = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Sección de origen no encontrada.';
                return $r;
            }

            $codigoOrigen = $origen['sec_codigo'];
            $primerDigito = intval(substr($codigoOrigen, 0, 1));
            $ultimoDigito = substr($codigoOrigen, -1); 

            $patronDestino = ($primerDigito + 1) . '%' . $ultimoDigito;

            $stmt2 = $co->prepare("SELECT s.sec_id, s.sec_codigo, a.ani_anio
            FROM tbl_seccion s
            LEFT JOIN tbl_anio a ON s.ani_id = a.ani_id
            WHERE s.sec_codigo LIKE ? AND s.sec_estado = 1
            ORDER BY s.sec_codigo ASC");
            $stmt2->execute([$patronDestino]); 
            $destinos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $r['mensaje'] = $destinos;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al buscar opciones destino: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }



    public function calcularCantidadProsecusion($seccionId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['puede_prosecusionar' => false, 'cantidad_final' => 0, 'mensaje' => ''];

        try {
            $stmt = $co->prepare("SELECT sec_cantidad, ani_id FROM tbl_seccion WHERE sec_id = ?");
            $stmt->execute([$seccionId]);
            $origen = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$origen || $origen['sec_cantidad'] <= 0) {
                $r['mensaje'] = 'La sección de origen no tiene estudiantes para prosecusionar.';
                return $r;
            }

            $stmt2 = $co->prepare("SELECT SUM(rem_cantidad) as total FROM tbl_remedial WHERE sec_id = ?");
            $stmt2->execute([$seccionId]);
            $remedial_row = $stmt2->fetch(PDO::FETCH_ASSOC);
            $remedial = $remedial_row['total']; 

            $stmt3 = $co->prepare("SELECT SUM(per_aprobados) as total FROM remedial_anio WHERE ani_id = ?");
            $stmt3->execute([$origen['ani_id']]);
            $per_row = $stmt3->fetch(PDO::FETCH_ASSOC);
            $per_aprobados = $per_row['total']; 

            if ($remedial === null || $per_aprobados === null) {
                $r['mensaje'] = 'La prosecusión se podrá realizar cuando las notas de remedial y PER estén subidas.';
                return $r;
            }

            $cantidad_final = intval($origen['sec_cantidad']) - intval($remedial) + intval($per_aprobados);

            if ($cantidad_final <= 0) {
                $r['mensaje'] = 'La cantidad de estudiantes a prosecusionar es 0 o menor.';
                $r['cantidad_final'] = $cantidad_final;
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

    public function RealizarProsecusion($seccionOrigenId, $cantidad, $seccionDestinoId = null)
    {
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ['resultado' => 'error', 'mensaje' => 'La cantidad de estudiantes debe ser un número válido mayor a 0.'];
        }

        if ($seccionDestinoId === null) {
            $opciones = $this->obtenerOpcionesDestinoManual($seccionOrigenId);
            if ($opciones['resultado'] === 'opcionesDestinoManual' && !empty($opciones['mensaje'])) {
                $seccionDestinoId = $opciones['mensaje'][0]['sec_id']; 
            } else {
                return ['resultado' => 'error', 'mensaje' => 'No existe una sección destino válida para prosecusión automática.'];
            }
        }
        return $this->ProsecusionSeccion($seccionOrigenId, $seccionDestinoId, $cantidad);
    }

    public function ProsecusionSeccion($seccionOrigenId, $seccionDestinoId, $cantidadFinal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => 'prosecusion', 'mensaje' => '', 'seccionDestinoId' => $seccionDestinoId];

        try {
            $co->beginTransaction();

            $stmtInsert = $co->prepare("INSERT INTO tbl_prosecusion (sec_id_origen, sec_id_promocion, pro_estado) VALUES (?, ?, 1)");
            $stmtInsert->execute([$seccionOrigenId, $seccionDestinoId]);

            $stmtUpdateOrigen = $co->prepare("UPDATE tbl_seccion SET sec_estado = 0 WHERE sec_id = ?");
            $stmtUpdateOrigen->execute([$seccionOrigenId]);

            $stmtUpdateDestino = $co->prepare("UPDATE tbl_seccion SET sec_cantidad = ? WHERE sec_id = ?");
            $stmtUpdateDestino->execute([$cantidadFinal, $seccionDestinoId]);

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
                p.pro_id,
                so.sec_id as origen_id,
                so.sec_codigo as origen_codigo,
                so.sec_cantidad as origen_cantidad,
                ao.ani_anio as origen_anio,
                sd.sec_id as destino_id,
                sd.sec_codigo as destino_codigo,
                sd.sec_cantidad as destino_cantidad,
                ad.ani_anio as destino_anio
            FROM tbl_prosecusion p
            INNER JOIN tbl_seccion so ON p.sec_id_origen = so.sec_id
            INNER JOIN tbl_anio ao ON so.ani_id = ao.ani_id
            INNER JOIN tbl_seccion sd ON p.sec_id_promocion = sd.sec_id
            INNER JOIN tbl_anio ad ON sd.ani_id = ad.ani_id
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
            $stmt = $co->query("SELECT s.sec_id, s.sec_codigo, s.sec_cantidad, a.ani_anio
            FROM tbl_seccion s
            LEFT JOIN tbl_anio a ON s.ani_id = a.ani_id
            WHERE s.sec_estado = 1");
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

        try {
            $co->beginTransaction();

            $stmtGetOrigen = $co->prepare("SELECT sec_id_origen FROM tbl_prosecusion WHERE pro_id = ?");
            $stmtGetOrigen->execute([$this->pro_id]);
            $origen = $stmtGetOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$origen) {
                throw new Exception("No se encontró el registro de prosecusión.");
            }

            $stmtUpdateProsecusion = $co->prepare("UPDATE tbl_prosecusion SET pro_estado = 0 WHERE pro_id = ?");
            $stmtUpdateProsecusion->execute([$this->pro_id]);

            $stmtReactivarOrigen = $co->prepare("UPDATE tbl_seccion SET sec_estado = 1 WHERE sec_id = ?");
            $stmtReactivarOrigen->execute([$origen['sec_id_origen']]);

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
