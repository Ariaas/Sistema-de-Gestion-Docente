<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $uploadDir = __DIR__ . '/../archivos_subidos/';

    public function __construct()
    {
        parent::__construct();
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function guardarArchivo($archivo, $docente = '', $ucurricular = '', $fecha = '')
    {
        $carpetaDestino = 'archivos_subidos/';
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        // Sanitizar nombres
        $docente = preg_replace('/[^a-zA-Z0-9\s]/', '', $docente); // Permite espacios
        $ucurricular = preg_replace('/[^a-zA-Z0-9\s]/', '', $ucurricular); 
        $fecha = $fecha ?: date('Y-m-d');

        // Crear nombre personalizado
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
        $files = scandir($this->uploadDir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $archivos[] = [
                    'nombre_guardado' => $file,
                    'ruta_completa' => $this->uploadDir . $file
                ];
            }
        }
        return $archivos;
    }

    // Método para eliminar archivo (local)
    public function eliminarArchivo($nombreArchivo)
    {
        $ruta = $this->uploadDir . $nombreArchivo;

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



    public function obtenerdocente()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT doc_id,doc_nombre FROM tbl_docente");
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
