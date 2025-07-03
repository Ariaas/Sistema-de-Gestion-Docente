<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    // Directorios para los archivos
    private $archivosDir = __DIR__ . '/../archivos_subidos/'; // Para notas definitivas
    private $archivosPerDir = __DIR__ . '/../archivos_per/';   // Para notas de remedial (PER)

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

    private function guardarArchivoDefinitivoLocal($archivo, $rem_id, $ucurricular, $seccion)
    {
        $ucurricular = preg_replace('/[^a-zA-Z0-9\s]/', '', $ucurricular);
        $ucurricular = str_replace(' ', '_', $ucurricular);
        $seccion = str_replace(' ', '_', $seccion);

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        // Nomenclatura: DEF_<ID_Remedial>_<UC>_<Seccion>_<Timestamp>.<ext>
        $nombreArchivo = "DEF_{$rem_id}_{$ucurricular}_{$seccion}_" . date('Y-m-d') . ".{$extension}";
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
        // Nomenclatura: PER_<ID_Remedial>_<UC>_<Seccion>_<Timestamp>.<ext>
        $nombreArchivo = "PER_{$rem_id}_{$ucurricular}_{$seccion}_" . date('Y-m-d_H-i-s') . ".{$extension}";
        $rutaFinal = $this->archivosPerDir . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            throw new Exception('Error al guardar el archivo PER en el disco.');
        }
        return $nombreArchivo;
    }

    public function guardarNotasRemedial($datos, $archivo)
    {
        $this->Con()->beginTransaction();
        try {
            // 1. Insertar en tbl_remedial para obtener el ID.
            $p = $this->Con()->prepare(
                "INSERT INTO tbl_remedial (sec_id, uc_id, doc_id, rem_cantidad, rem_estado) VALUES (?, ?, ?, ?, 1)"
            );
            $p->execute([$datos['seccion_id'], $datos['ucurricular'], $datos['docente'], $datos['cantidad_per']]);
            $rem_id = $this->Con()->lastInsertId();

            // 2. Insertar en la tabla de enlace remedial_anio.
            $p = $this->Con()->prepare(
                "INSERT INTO remedial_anio (ani_id, rem_id, per_cantidad, per_aprobados, fecha_resguardo) VALUES (?, ?, ?, 0, ?)"
            );
            $p->execute([$datos['anio'], $rem_id, $datos['cantidad_per'], $datos['fecha']]);

            // 3. Guardar el archivo definitivo si fue subido, usando el ID del remedial.
            if (isset($archivo) && $archivo['error'] == UPLOAD_ERR_OK) {
                $this->guardarArchivoDefinitivoLocal(
                    $archivo,
                    $rem_id,
                    $datos['uc_nombre'],
                    $datos['seccion_codigo']
                );
            }

            $this->Con()->commit();
            return ['success' => true, 'mensaje' => 'Registro de remedial guardado con éxito.'];
        } catch (Exception $e) {
            $this->Con()->rollBack();
            return ['success' => false, 'mensaje' => 'Error en la operación: ' . $e->getMessage()];
        }
    }

    public function registrarAprobadosPer($datos, $archivo)
    {
        $this->Con()->beginTransaction();
        try {
            // 1. Guardar el archivo PER si existe, usando la nomenclatura con el ID.
            if (isset($archivo) && $archivo['error'] == UPLOAD_ERR_OK) {
                $this->guardarArchivoPERLocal(
                    $archivo,
                    $datos['rem_id'],
                    $datos['uc_nombre'],
                    $datos['seccion_codigo']
                );
            }

            // 2. Actualizar la cantidad de aprobados.
            $p = $this->Con()->prepare("UPDATE remedial_anio SET per_aprobados = ? WHERE rem_id = ?");
            $p->execute([$datos['cantidad_aprobados'], $datos['rem_id']]);

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
            SELECT 
                r.rem_id, a.ani_anio, s.sec_codigo, uc.uc_nombre,
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

        // Para cada registro, buscar su archivo definitivo asociado en el disco.
        foreach ($registros as $key => $registro) {
            $nombreArchivoDef = null;
            // Busca archivos que comiencen con "DEF_<id_remedial>_"
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
        // Busca archivos que comiencen con "PER_<id_remedial>_"
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
            } else {
                return ['success' => false, 'mensaje' => 'Error al eliminar el archivo PER.'];
            }
        } else {
            return ['success' => false, 'mensaje' => 'Archivo PER no encontrado.'];
        }
    }

    public function obtenerAnios()
    {
        $co = $this->Con();
        $p = $co->prepare("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeccionesPorAnio($anio_id)
    {
        $co = $this->Con();
        $p = $co->prepare("SELECT sec_id, sec_codigo, sec_cantidad FROM tbl_seccion WHERE ani_id = ? AND sec_estado = 1");
        $p->execute([$anio_id]);
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerdocente()
    {
        $co = $this->Con();
        $p = $co->prepare("SELECT doc_id, CONCAT_WS(' ', doc_nombre, doc_apellido) AS doc_nombre_completo FROM tbl_docente");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerunidadcurricular()
    {
        $co = $this->Con();
        $p = $co->prepare("SELECT uc_id, uc_nombre FROM tbl_uc");
        $p->execute();
        return $p->fetchAll(PDO::FETCH_ASSOC);
    }
}