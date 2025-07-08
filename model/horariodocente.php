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
        $r = array();
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
            if ($this->Existe($this->doc_cedula, $this->hdo_lapso, $this->hdo_tipoactividad)['resultado'] == 'existe') {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'Esta actividad ya se encuentra registrada.';
                return $r;
            }
            $co = $this->Con();
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
            $r['mensaje'] = 'Registro Incluido!<br/>La actividad se registró correctamente.';
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
            $r['mensaje'] = 'Registro Modificado!<br/>La actividad se modificó correctamente.';
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
            $r['mensaje'] = 'Registro Eliminado!<br/>La actividad se eliminó correctamente.';
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

    // --- Funciones auxiliares ---
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
        $co = $this->Con();
        $sql = "SELECT uh.hor_dia, uh.hor_horainicio, uh.hor_horafin, uc.uc_codigo, uc.uc_nombre, esp.esp_codigo, sec.sec_codigo FROM docente_horario dh JOIN tbl_seccion sec ON dh.sec_codigo = sec.sec_codigo JOIN uc_horario uh ON sec.sec_codigo = uh.sec_codigo JOIN tbl_uc uc ON uh.uc_codigo = uc.uc_codigo JOIN tbl_horario hor ON sec.sec_codigo = hor.sec_codigo JOIN tbl_espacio esp ON hor.esp_codigo = esp.esp_codigo JOIN tbl_anio anio ON sec.ani_anio = anio.ani_anio AND sec.ani_tipo = anio.ani_tipo WHERE dh.doc_cedula = :doc_cedula AND anio.ani_activo = 1 ORDER BY FIELD(uh.hor_dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'), uh.hor_horainicio";
        $stmt = $co->prepare($sql);
        $stmt->execute([':doc_cedula' => $doc_cedula]);
        $horario_docente = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($horario_docente)) return ['resultado' => 'vacio', 'mensaje' => "El docente no tiene un horario de clases asignado."];
        $franjas_obj = [];
        foreach ($horario_docente as $clase) {
            $franja_key = $clase['hor_horainicio'] . '-' . $clase['hor_horafin'];
            if (!isset($franjas_obj[$franja_key])) {
                $franjas_obj[$franja_key] = ['inicio' => $clase['hor_horainicio'], 'fin' => $clase['hor_horafin']];
            }
        }
        usort($franjas_obj, fn($a, $b) => strcmp($a['inicio'], $b['inicio']));
        return ['resultado' => 'ok', 'horario' => $horario_docente, 'franjas' => array_values($franjas_obj)];
    }
}
