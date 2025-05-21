<?php
require_once('model/dbconnection.php');

class Archivo extends Connection
{
    private $uploadDir = __DIR__ . '/../uploads/';

    public function __construct()
    {
        parent::__construct();
        // Crear carpeta si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    // Método principal para guardar (local + BD)
    public function guardarArchivo($file)
    {
        // 1. Validaciones (igual que antes)
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            return ['resultado' => 'error', 'mensaje' => 'Solo se permiten PDF, DOC, DOCX o TXT'];
        }

        if ($file['size'] > 5242880) { // 5MB
            return ['resultado' => 'error', 'mensaje' => 'El archivo excede 5MB'];
        }

        // 2. Usar nombre original SANITIZADO
        $nombreOriginal = basename($file['name']); // Elimina posibles rutas maliciosas
        $filePath = $this->uploadDir . $nombreOriginal;

        // 3. Verificar si el archivo ya existe
        if (file_exists($filePath)) {
            return [
                'resultado' => 'error',
                'mensaje' => 'Ya existe un archivo con este nombre. Renómbralo antes de subirlo.'
            ];
        }

        // 4. Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['resultado' => 'error', 'mensaje' => 'Error al guardar el archivo'];
        }

        return [
            'resultado' => 'guardar',
            'mensaje' => 'Archivo guardado correctamente',
            'nombre_guardado' => $nombreOriginal // Nombre original sin cambios
        ];
    }



    // Método para listar archivos (desde carpeta local)
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
}
