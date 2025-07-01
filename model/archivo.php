<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $archivosDir = __DIR__ . '/../archivos_subidos/';

    public function __construct()
    {
        parent::__construct();
        if (!file_exists($this->archivosDir)) {
            mkdir($this->archivosDir, 0777, true);
        }
    }

    public function guardarArchivo($archivo, $docente = '', $ucurricular = '', $fecha = '')
    {
        $carpetaDestino = 'archivos_subidos/';
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $docente = preg_replace('/[^a-zA-Z0-9\s]/', '', $docente);
        $ucurricular = preg_replace('/[^a-zA-Z0-9\s]/', '', $ucurricular); 
        $fecha = $fecha ?: date('Y-m-d');

        $docente = str_replace(' ', '_', $docente);
        $ucurricular = str_replace(' ', '_', $ucurricular);

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "{$docente}_{$ucurricular}_{$fecha}.{$extension}";
        $rutaFinal = $carpetaDestino . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
            return [
                'success' => true,
                'mensaje' => "Archivo guardado como: {$nombreArchivo}",
                'nombre_archivo' => $nombreArchivo
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => 'Error al guardar el archivo'
            ];
        }
    }

    public function listarArchivosLocales()
    {
        $archivos = [];
        $files = scandir($this->archivosDir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $archivos[] = [
                    'nombre_guardado' => $file,
                    'ruta_completa' => $this->archivosDir . $file
                ];
            }
        }
        return $archivos;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        $ruta = $this->archivosDir . $nombreArchivo;

        if (file_exists($ruta)) {
            if (unlink($ruta)) {
                return ['resultado' => 'eliminar', 'mensaje' => 'Archivo eliminado'];
            } else {
                return ['resultado' => 'error', 'mensaje' => 'Error al eliminar'];
            }
        } else {
            return ['resultado' => 'error', 'mensaje' => 'Archivo no encontrado'];
        }
    }

 
    public function obtenerDocentesConArchivos()
    {
        $docentesInfo = [];
        $archivosLocales = $this->listarArchivosLocales();
        $todosLosDocentes = $this->obtenerdocente();

        foreach ($archivosLocales as $archivo) {
            $nombreArchivo = $archivo['nombre_guardado'];

            foreach ($todosLosDocentes as $docente) {
                $nombreCompleto = $docente['doc_nombre_completo'];
                $llaveDocente = str_replace(' ', '_', $nombreCompleto) . '_';

                if (strpos($nombreArchivo, $llaveDocente) === 0) {

                    preg_match('/_(\d{4}-\d{2}-\d{2})\./', $nombreArchivo, $matches);
                    $fecha = $matches[1] ?? null;

                    if ($fecha) {
                        if (!isset($docentesInfo[$nombreCompleto]) || $fecha > $docentesInfo[$nombreCompleto]) {
                            $docentesInfo[$nombreCompleto] = $fecha;
                        }
                    }
                    break;
                }
            }
        }

        $resultadoFinal = [];
        foreach ($docentesInfo as $nombre => $fecha) {
            $resultadoFinal[] = ['nombre' => $nombre, 'fecha' => $fecha];
        }

        usort($resultadoFinal, function ($a, $b) {
            return $b['fecha'] <=> $a['fecha'];
        });

        return $resultadoFinal;
    }

    public function obtenerdocente()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT doc_id, CONCAT_WS(' ', doc_nombre, doc_apellido) AS doc_nombre_completo FROM tbl_docente");

        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }

    public function obtenerunidadcurricular()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT uc_id,uc_nombre FROM tbl_uc");
        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }
}
