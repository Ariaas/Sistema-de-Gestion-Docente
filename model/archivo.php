<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $rem_id;
    private $sec_id;
    private $uc_id;
    private $doc_id;
    private $rem_cantidad;
    private $ani_id;
    private $per_aprobados;
    private $uc_nombre;
    private $seccion_codigo;

    private $archivosDir = __DIR__ . '/../archivos_subidos/';
    private $archivosPerDir = __DIR__ . '/../archivos_per/';

    public function __construct()
    {
        parent::__construct();
        // Crear ambos directorios si no existen
        if (!file_exists($this->archivosDir)) {
            mkdir($this->archivosDir, 0777, true);
        }
        if (!file_exists($this->archivosPerDir)) {
            mkdir($this->archivosPerDir, 0777, true);
        }
    }

    public function setRemId($rem_id)
    {
        $this->rem_id = $rem_id;
    }
    public function getRemId()
    {
        return $this->rem_id;
    }
    public function setSecId($sec_id)
    {
        $this->sec_id = $sec_id;
    }
    public function getSecId()
    {
        return $this->sec_id;
    }
    public function setUcId($uc_id)
    {
        $this->uc_id = $uc_id;
    }
    public function getUcId()
    {
        return $this->uc_id;
    }
    public function setDocId($doc_id)
    {
        $this->doc_id = $doc_id;
    }
    public function getDocId()
    {
        return $this->doc_id;
    }
    public function setRemCantidad($rem_cantidad)
    {
        $this->rem_cantidad = $rem_cantidad;
    }
    public function getRemCantidad()
    {
        return $this->rem_cantidad;
    }
    public function setAnioId($ani_id)
    {
        $this->ani_id = $ani_id;
    }
    public function getAnioId()
    {
        return $this->ani_id;
    }
    public function setPerAprobados($per_aprobados)
    {
        $this->per_aprobados = $per_aprobados;
    }
    public function getPerAprobados()
    {
        return $this->per_aprobados;
    }
    public function setUcNombre($uc_nombre)
    {
        $this->uc_nombre = $uc_nombre;
    }
    public function getUcNombre()
    {
        return $this->uc_nombre;
    }
    public function setSeccionCodigo($seccion_codigo)
    {
        $this->seccion_codigo = $seccion_codigo;
    }
    public function getSeccionCodigo()
    {
        return $this->seccion_codigo;
    }

    private function guardarArchivoDefinitivoLocal($archivo, $rem_id, $ucurricular, $seccion, $fecha_resguardo)
    {
        $ucurricular = preg_replace('/[^a-zA-Z0-9\s]/', '', $ucurricular);
        $ucurricular = str_replace(' ', '_', $ucurricular);
        $seccion = str_replace(' ', '_', $seccion);
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "DEF_{$rem_id}_{$ucurricular}_{$seccion}_" . $fecha_resguardo . ".{$extension}";
        $rutaFinal = $this->archivosDir . $nombreArchivo;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            throw new Exception('Error al guardar el archivo definitivo en el disco.');
        }
        return $nombreArchivo;
    }

    private function guardarArchivoPERLocal($archivo, $rem_id, $ucurricular, $seccion)
    {
        $ucurricular = preg_replace('/[^a-zA-Z0-9\s]/', '', $ucurricular);
        $ucurricular = str_replace(' ', '_', $ucurricular);
        $seccion = str_replace(' ', '_', $seccion);
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "PER_{$rem_id}_{$ucurricular}_{$seccion}_" . date('Y-m-d_H-i-s') . ".{$extension}";
        $rutaFinal = $this->archivosPerDir . $nombreArchivo;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            throw new Exception('Error al guardar el archivo PER en el disco.');
        }
        return $nombreArchivo;
    }

    public function guardarNotasRemedial($archivo, $fecha_para_nombre)
    {
        $this->Con()->beginTransaction();
        try {
            if (empty($this->getDocId())) {
                throw new Exception('No se ha podido identificar al docente.');
            }
            $p = $this->Con()->prepare("INSERT INTO tbl_remedial (sec_id, uc_id, doc_id, rem_cantidad, rem_estado) VALUES (:sec_id, :uc_id, :doc_id, :rem_cantidad, 1)");
            $secId = $this->getSecId();
            $ucId = $this->getUcId();
            $docId = $this->getDocId();
            $remCantidad = $this->getRemCantidad();
            $p->bindParam(':sec_id', $secId, PDO::PARAM_INT);
            $p->bindParam(':uc_id', $ucId, PDO::PARAM_INT);
            $p->bindParam(':doc_id', $docId, PDO::PARAM_INT);
            $p->bindParam(':rem_cantidad', $remCantidad, PDO::PARAM_INT);
            $p->execute();
            $this->setRemId($this->Con()->lastInsertId());

            $p = $this->Con()->prepare("INSERT INTO remedial_anio (ani_id, rem_id, per_cantidad, per_aprobados) VALUES (:ani_id, :rem_id, :per_cantidad, 0)");
            $anioId = $this->getAnioId();
            $lastRemId = $this->getRemId();
            $perCantidad = $this->getRemCantidad();
            $p->bindParam(':ani_id', $anioId, PDO::PARAM_INT);
            $p->bindParam(':rem_id', $lastRemId, PDO::PARAM_INT);
            $p->bindParam(':per_cantidad', $perCantidad, PDO::PARAM_INT);
            $p->execute();

            if (isset($archivo) && $archivo['error'] == UPLOAD_ERR_OK) {
                $this->guardarArchivoDefinitivoLocal($archivo, $this->getRemId(), $this->getUcNombre(), $this->getSeccionCodigo(), $fecha_para_nombre);
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro de remedial guardado con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    public function registrarAprobadosPer($archivo)
    {
        $this->Con()->beginTransaction();
        try {
            if (isset($archivo) && $archivo['error'] == UPLOAD_ERR_OK) {
                $this->guardarArchivoPERLocal($archivo, $this->getRemId(), $this->getUcNombre(), $this->getSeccionCodigo());
            }

            $p = $this->Con()->prepare("UPDATE remedial_anio SET per_aprobados = :per_aprobados WHERE rem_id = :rem_id");
            $aprobados = $this->getPerAprobados();
            $remId = $this->getRemId();
            $p->bindParam(':per_aprobados', $aprobados, PDO::PARAM_INT);
            $p->bindParam(':rem_id', $remId, PDO::PARAM_INT);
            $p->execute();

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Aprobados del remedial registrados con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error al registrar aprobados: ' . $e->getMessage()];
        }
    }

    public function listarRegistros()
    {
        $p = $this->Con()->prepare("
            SELECT r.rem_id, a.ani_anio, s.sec_codigo, uc.uc_nombre,
                   s.sec_cantidad, r.rem_cantidad AS cantidad_per, ra.per_aprobados
            FROM tbl_remedial r
            JOIN tbl_seccion s ON r.sec_id = s.sec_id
            JOIN tbl_uc uc ON r.uc_id = uc.uc_id
            JOIN remedial_anio ra ON r.rem_id = ra.rem_id
            JOIN tbl_anio a ON ra.ani_id = a.ani_id
            ORDER BY a.ani_anio DESC, s.sec_codigo ASC
        ");
        $p->execute();
        $registros = $p->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $key => $registro) {
            $nombreArchivoDef = null;
            $globPattern = $this->archivosDir . "DEF_" . $registro['rem_id'] . "_*.*";
            $foundFiles = glob($globPattern);
            if (!empty($foundFiles)) {
                $nombreArchivoDef = basename($foundFiles[0]);
            }
            $registros[$key]['archivo_definitivo'] = $nombreArchivoDef;
        }

        return $registros;
    }

    public function listarArchivosPerPorId($rem_id)
    {
        $archivos = [];
        $globPattern = $this->archivosPerDir . "PER_" . $rem_id . "_*.*";
        $foundFiles = glob($globPattern);
        if ($foundFiles !== false) {
            foreach ($foundFiles as $file) {
                $archivos[] = ['nombre_guardado' => basename($file)];
            }
        }
        return $archivos;
    }

    public function eliminarArchivoPer($nombreArchivo)
    {
        $ruta = $this->archivosPerDir . $nombreArchivo;
        if (file_exists($ruta)) {
            if (unlink($ruta)) {
                return ['success' => true, 'mensaje' => 'Archivo PER eliminado.'];
            }
            return ['success' => false, 'mensaje' => 'Error al eliminar el archivo PER.'];
        }
        return ['success' => false, 'mensaje' => 'Archivo PER no encontrado.'];
    }

    public function obtenerAnios()
    {
        $p = $this->Con()->prepare("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUnidadesPorDocente($doc_id)
    {
        $sql = "SELECT DISTINCT uc.uc_id, uc.uc_nombre
                FROM tbl_uc uc
                INNER JOIN uc_docente ud ON uc.uc_id = ud.uc_id
                WHERE ud.doc_id = :doc_id AND uc.uc_estado = 1 AND ud.uc_doc_estado = 1
                ORDER BY uc.uc_nombre ASC";
        $p = $this->Con()->prepare($sql);
        $p->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesPorAnio($anio_id)
    {
        $p = $this->Con()->prepare("SELECT sec_id, sec_codigo, sec_cantidad FROM tbl_seccion WHERE ani_id = :anio_id AND sec_estado = 1");
        $p->bindParam(':anio_id', $anio_id, PDO::PARAM_INT);
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}