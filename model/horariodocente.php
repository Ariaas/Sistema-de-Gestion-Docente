<?php
require_once('model/dbconnection.php');

class HorarioDocente extends Connection
{
    private $doc_cedula;
    private $hdo_lapso;
    private $hdo_tipoactividad;
    private $hdo_descripcion;
    private $hdo_dependencia;
    private $hdo_observacion;
    private $hdo_horas;

    public function __construct($cedula = null, $lapso = null, $actividad = null, $desc = null, $dep = null, $obs = null, $horas = null)
    {
        parent::__construct();
        $this->doc_cedula = $cedula;
        $this->hdo_lapso = $lapso;
        $this->hdo_tipoactividad = $actividad;
        $this->hdo_descripcion = $desc;
        $this->hdo_dependencia = $dep;
        $this->hdo_observacion = $obs;
        $this->hdo_horas = $horas;
    }

    public function setDocCedula($val)
    {
        $this->doc_cedula = $val;
    }
    public function setHdoLapso($val)
    {
        $this->hdo_lapso = $val;
    }
    public function setHdoTipoactividad($val)
    {
        $this->hdo_tipoactividad = $val;
    }
    public function setHdoDescripcion($val)
    {
        $this->hdo_descripcion = $val;
    }
    public function setHdoDependencia($val)
    {
        $this->hdo_dependencia = $val;
    }
    public function setHdoObservacion($val)
    {
        $this->hdo_observacion = $val;
    }
    public function setHdoHoras($val)
    {
        $this->hdo_horas = $val;
    }

    public function Existe($cedula, $lapso, $actividad)
    {
        $r = array('resultado' => 'no_existe');
        try {
            $co = $this->Con();
            $stmt = $co->prepare("SELECT * FROM tbl_horario_docente WHERE doc_cedula = :cedula AND hdo_lapso = :lapso AND hdo_tipoactividad = :actividad AND hdo_estado = 1");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':lapso', $lapso);
            $stmt->bindParam(':actividad', $actividad);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'Esta actividad ya se encuentra registrada.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Registrar()
    {
        $r = array();
        try {
            $co = $this->Con();
            $stmtActivo = $co->prepare("SELECT * FROM tbl_horario_docente WHERE doc_cedula = :cedula AND hdo_lapso = :lapso AND hdo_tipoactividad = :actividad AND hdo_estado = 1");
            $stmtActivo->bindParam(':cedula', $this->doc_cedula);
            $stmtActivo->bindParam(':lapso', $this->hdo_lapso);
            $stmtActivo->bindParam(':actividad', $this->hdo_tipoactividad);
            $stmtActivo->execute();
            if ($stmtActivo->rowCount() > 0) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'Esta actividad ya se encuentra registrada.';
                return $r;
            }

            $stmtInactivo = $co->prepare("SELECT * FROM tbl_horario_docente WHERE doc_cedula = :cedula AND hdo_lapso = :lapso AND hdo_tipoactividad = :actividad AND hdo_estado = 0");
            $stmtInactivo->bindParam(':cedula', $this->doc_cedula);
            $stmtInactivo->bindParam(':lapso', $this->hdo_lapso);
            $stmtInactivo->bindParam(':actividad', $this->hdo_tipoactividad);
            $stmtInactivo->execute();
            if ($stmtInactivo->rowCount() > 0) {
                $sql = "UPDATE tbl_horario_docente SET hdo_descripcion = :descripcion, hdo_dependencia = :dependencia, hdo_observacion = :observacion, hdo_horas = :horas, hdo_estado = 1 WHERE doc_cedula = :cedula AND hdo_lapso = :lapso AND hdo_tipoactividad = :actividad";
                $stmtReactivar = $co->prepare($sql);
                $stmtReactivar->bindParam(':cedula', $this->doc_cedula);
                $stmtReactivar->bindParam(':lapso', $this->hdo_lapso);
                $stmtReactivar->bindParam(':actividad', $this->hdo_tipoactividad);
                $stmtReactivar->bindParam(':descripcion', $this->hdo_descripcion);
                $stmtReactivar->bindParam(':dependencia', $this->hdo_dependencia);
                $stmtReactivar->bindParam(':observacion', $this->hdo_observacion);
                $stmtReactivar->bindParam(':horas', $this->hdo_horas, PDO::PARAM_INT);
                $stmtReactivar->execute();
                $r['resultado'] = 'registrar';
                $r['mensaje'] = '¡Registro Incluido! La actividad se registró correctamente.';
                return $r;
            }

            $sql = "INSERT INTO tbl_horario_docente(doc_cedula, hdo_lapso, hdo_tipoactividad, hdo_descripcion, hdo_dependencia, hdo_observacion, hdo_horas, hdo_estado) VALUES (:cedula, :lapso, :actividad, :descripcion, :dependencia, :observacion, :horas, 1)";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':cedula', $this->doc_cedula);
            $stmt->bindParam(':lapso', $this->hdo_lapso);
            $stmt->bindParam(':actividad', $this->hdo_tipoactividad);
            $stmt->bindParam(':descripcion', $this->hdo_descripcion);
            $stmt->bindParam(':dependencia', $this->hdo_dependencia);
            $stmt->bindParam(':observacion', $this->hdo_observacion);
            $stmt->bindParam(':horas', $this->hdo_horas, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = '¡Registro Incluido! La actividad se registró correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Modificar($original_cedula, $original_lapso, $original_actividad)
    {
        $r = array();
        try {
            $co = $this->Con();
            $sql = "UPDATE tbl_horario_docente SET doc_cedula = :cedula, hdo_lapso = :lapso, hdo_tipoactividad = :actividad, hdo_descripcion = :descripcion, hdo_dependencia = :dependencia, hdo_observacion = :observacion, hdo_horas = :horas WHERE doc_cedula = :original_cedula AND hdo_lapso = :original_lapso AND hdo_tipoactividad = :original_actividad";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':cedula', $this->doc_cedula);
            $stmt->bindParam(':lapso', $this->hdo_lapso);
            $stmt->bindParam(':actividad', $this->hdo_tipoactividad);
            $stmt->bindParam(':descripcion', $this->hdo_descripcion);
            $stmt->bindParam(':dependencia', $this->hdo_dependencia);
            $stmt->bindParam(':observacion', $this->hdo_observacion);
            $stmt->bindParam(':horas', $this->hdo_horas);
            $stmt->bindParam(':original_cedula', $original_cedula);
            $stmt->bindParam(':original_lapso', $original_lapso);
            $stmt->bindParam(':original_actividad', $original_actividad);
            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = '¡Registro Modificado! La actividad se modificó correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Eliminar()
    {
        $r = array();
        try {
            $co = $this->Con();
            $sql = "UPDATE tbl_horario_docente SET hdo_estado = 0 WHERE doc_cedula = :cedula AND hdo_lapso = :lapso AND hdo_tipoactividad = :actividad";
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':cedula', $this->doc_cedula);
            $stmt->bindParam(':lapso', $this->hdo_lapso);
            $stmt->bindParam(':actividad', $this->hdo_tipoactividad);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = '¡Registro Eliminado! La actividad se eliminó correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Listar()
    {
        $r = array();
        try {
            $co = $this->Con();
            $sql = "SELECT thd.*, CONCAT(IFNULL(td.doc_prefijo,''), IF(td.doc_prefijo IS NOT NULL AND td.doc_prefijo != '', '. ', ''), td.doc_nombre, ' ', td.doc_apellido) AS doc_nombre_completo FROM tbl_horario_docente thd JOIN tbl_docente td ON thd.doc_cedula = td.doc_cedula WHERE thd.hdo_estado = 1 ORDER BY doc_nombre_completo, thd.hdo_lapso, thd.hdo_tipoactividad";
            $stmt = $co->query($sql);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function obtenerLapsosActivos()
    {
        $co = $this->Con();
        $sql = "SELECT CONCAT(f.ani_anio, '-', f.fase_numero) AS lapso_compuesto FROM tbl_fase f JOIN tbl_anio a ON f.ani_anio = a.ani_anio AND f.ani_tipo = a.ani_tipo WHERE a.ani_activo = 1 ORDER BY f.ani_anio DESC, f.fase_numero ASC";
        $stmt = $co->query($sql);
        return ['success' => true, 'lapsos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
    public function obtenerDocentes()
    {
        $co = $this->Con();
        $stmt = $co->query("SELECT doc_cedula, doc_prefijo, doc_nombre, doc_apellido FROM tbl_docente WHERE doc_estado = 1 ORDER BY doc_apellido, doc_nombre");
        return ['success' => true, 'teachers' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function obtenerHorarioCompletoPorDocente($doc_cedula)
    {
        try {
            $co = $this->Con();
            $sql = "
                SELECT 
                    CASE 
                        WHEN LOWER(uh.hor_dia) = 'lunes' THEN 'Lunes'
                        WHEN LOWER(uh.hor_dia) = 'martes' THEN 'Martes'
                        WHEN LOWER(uh.hor_dia) IN ('miercoles', 'miércoles') THEN 'Miércoles'
                        WHEN LOWER(uh.hor_dia) = 'jueves' THEN 'Jueves'
                        WHEN LOWER(uh.hor_dia) = 'viernes' THEN 'Viernes'
                        WHEN LOWER(uh.hor_dia) IN ('sabado', 'sábado') THEN 'Sábado'
                        ELSE uh.hor_dia 
                    END AS hor_dia,
                    uh.hor_horainicio, 
                    uh.hor_horafin, 
                    uc.uc_codigo, 
                    uc.uc_nombre, 
                    -- Se crea un código de espacio compuesto para mostrar, ya que no existe uno en la BD
                    CONCAT(uh.esp_edificio, ' - ', uh.esp_numero, ' (', uh.esp_tipo, ')') AS esp_codigo,
                    uh.sec_codigo
                FROM uc_docente ud
                -- Se une con uc_horario para obtener los detalles del horario
                JOIN uc_horario uh ON ud.uc_codigo = uh.uc_codigo
                JOIN tbl_uc uc ON ud.uc_codigo = uc.uc_codigo
                JOIN tbl_seccion sec ON uh.sec_codigo = sec.sec_codigo
                -- Se corrige el JOIN con tbl_espacio usando la clave compuesta
                JOIN tbl_espacio esp ON uh.esp_numero = esp.esp_numero AND uh.esp_tipo = esp.esp_tipo AND uh.esp_edificio = esp.esp_edificio
                JOIN tbl_anio anio ON sec.ani_anio = anio.ani_anio AND sec.ani_tipo = anio.ani_tipo
                WHERE ud.doc_cedula = :doc_cedula 
                  AND anio.ani_activo = 1
                -- Se elimina el filtro por 'uc_doc_estado' ya que no existe en la tabla uc_docente
                ORDER BY FIELD(LOWER(uh.hor_dia), 'lunes', 'martes', 'miercoles', 'miércoles', 'jueves', 'viernes', 'sabado', 'sábado'), uh.hor_horainicio
            ";

            $stmt = $co->prepare($sql);
            $stmt->execute([':doc_cedula' => $doc_cedula]);
            $horario_docente = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horario_docente)) {
                return ['resultado' => 'vacio', 'mensaje' => "El docente no tiene un horario de clases asignado para el año académico activo."];
            }

            $franjas_obj = [];
            foreach ($horario_docente as $clase) {
                $franja_key = $clase['hor_horainicio'] . '-' . $clase['hor_horafin'];
                if (!isset($franjas_obj[$franja_key])) {
                    $franjas_obj[$franja_key] = ['inicio' => $clase['hor_horainicio'], 'fin' => $clase['hor_horafin']];
                }
            }
            usort($franjas_obj, fn($a, $b) => strcmp($a['inicio'], $b['inicio']));

            return ['resultado' => 'ok', 'horario' => $horario_docente, 'franjas' => array_values($franjas_obj)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error al consultar el horario: ' . $e->getMessage()];
        }
    }
}
