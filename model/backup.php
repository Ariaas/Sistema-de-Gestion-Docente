<?php

namespace App\Model;

use PDO;
use Exception;
use ZipArchive;
use RuntimeException;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/configBitacora.php';

class Mantenimiento extends Connection
{

    public function __construct()
    {
    }

    private function _ejecutarComando($command)
    {
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            throw new Exception("Error ejecutando comando (Código $return_var): " . implode("\n", $output));
        }
        return $output;
    }

   public function GuardarRespaldo()
{
    try {
        $directorio_respaldos = "respaldos/";
        if (!is_dir($directorio_respaldos)) {
            if (!mkdir($directorio_respaldos, 0777, true)) {
                throw new Exception("No se pudo crear el directorio de respaldos.");
            }
        }

        $fecha_actual = date('Ymd_His');
        $sql_files_to_zip = [];

       
        $pdo_connection_main = new Connection();
        $resultado_main = $this->GuardarRespaldoAlternativoUnico(
            $pdo_connection_main->Con(),
            'db_orgdocente',
            _DB_NAME_, 
            $directorio_respaldos,
            $fecha_actual
        );

        if (!$resultado_main['success']) {
            throw new Exception("Error en respaldo de db_orgdocente: " . $resultado_main['message']);
        }
        $sql_files_to_zip[] = $resultado_main['filepath_sql'];
        $pdo_connection_main = null;


       
        $pdo_connection_bitacora = new Connection_bitacora();
        $resultado_bitacora = $this->GuardarRespaldoAlternativoUnico(
            $pdo_connection_bitacora->Con(),
            'db_bitacora',
            _BITA_DB_NAME_, 
            $directorio_respaldos,
            $fecha_actual
        );

        if (!$resultado_bitacora['success']) {
            unlink($resultado_main['filepath_sql']);
            throw new Exception("Error en respaldo de db_bitacora: " . $resultado_bitacora['message']);
        }
        $sql_files_to_zip[] = $resultado_bitacora['filepath_sql'];
        $pdo_connection_bitacora = null;


        $zip_file_base_name = 'respaldo_completo_' . $fecha_actual . '.zip';
        $zip_file_path = $directorio_respaldos . $zip_file_base_name;
        $zip = new ZipArchive();

        if ($zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($sql_files_to_zip as $sql_file) {
                $zip->addFile($sql_file, basename($sql_file));
            }
            $zip->close();

            foreach ($sql_files_to_zip as $sql_file) {
                if (file_exists($sql_file)) {
                    unlink($sql_file);
                }
            }

            return [
                "status" => "success",
                "message" => "Respaldo de ambas bases de datos guardado y comprimido en: " . $zip_file_base_name,
                "filename" => $zip_file_base_name
            ];
        } else {
            throw new Exception("Se generaron los respaldos SQL, pero no se pudieron comprimir.");
        }
    } catch (Exception $e) {
        error_log("Error en GuardarRespaldo: " . $e->getMessage());
        return ["status" => "error", "message" => "Error al generar el respaldo: " . $e->getMessage()];
    }
}



    private function RestaurarDesdeSQL($pdo, $sql_file_path)
    {
        try {
            $sql_content = file_get_contents($sql_file_path);
            if ($sql_content === false) {
                throw new Exception("No se pudo leer el archivo de respaldo SQL.");
            }

            $pdo->exec($sql_content);

            return true;
        } catch (Exception $e) {
            throw new Exception("Error durante la restauración SQL: " . $e->getMessage());
        }
    }

    private function GuardarRespaldoAlternativoUnico($pdo, $db_alias, $db_name_real, $directorio_base, $fecha_suffix)
    {
        try {
            $sqlContent = "SET NAMES utf8mb4;\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $sqlContent .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
            $sqlContent .= "SET AUTOCOMMIT = 0;\n";
            $sqlContent .= "START TRANSACTION;\n\n";

            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $create_table_stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                if ($create_table_stmt) {
                    $create_info = $create_table_stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($create_info['Create Table'])) {
                        $sqlContent .= "DROP TABLE IF EXISTS `$table`;\n";
                        $sqlContent .= $create_info['Create Table'] . ";\n\n";
                    }
                    $create_table_stmt->closeCursor();
                }

                $rows_stmt = $pdo->query("SELECT * FROM `$table`");
                if ($rows_stmt) {
                    $rows = $rows_stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($rows) > 0) {
                        $columns = array_map(function ($col) {
                            return "`$col`";
                        }, array_keys($rows[0]));
                        $sqlContent .= "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES\n";
                        $valuesBatch = [];
                        foreach ($rows as $rowIndex => $row) {
                            $values = array_map(function ($value) use ($pdo) {
                                return is_null($value) ? 'NULL' : $pdo->quote($value);
                            }, array_values($row));
                            $valuesBatch[] = "(" . implode(', ', $values) . ")";

                            if (count($valuesBatch) >= 500 || $rowIndex === count($rows) - 1) {

                                $sqlContent .= implode(",\n", $valuesBatch);
                                $valuesBatch = [];


                                if ($rowIndex === count($rows) - 1) {

                                    $sqlContent .= ";\n\n";
                                } else {

                                    $sqlContent .= ",\n";
                                }
                            }
                        }
                        $sqlContent .= "\n";
                    }
                    $rows_stmt->closeCursor();
                }
            }

            $sqlContent .= "COMMIT;\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $nombre_sql = $directorio_base . $db_name_real . '_' . $db_alias . '_' . $fecha_suffix . '.sql';

            if (file_put_contents($nombre_sql, $sqlContent) === false) {
                throw new Exception("Error al escribir el archivo de respaldo alternativo para $db_alias.");
            }

            return [
                'success' => true,
                'message' => "Respaldo alternativo para $db_alias generado.",
                'filepath_sql' => $nombre_sql
            ];
        } catch (Exception $e) {
            error_log("Error en GuardarRespaldoAlternativoUnico para $db_alias: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error en respaldo alternativo para $db_alias: " . $e->getMessage()
            ];
        }
    }


    public function RestaurarSistema($archivo_zip_nombre)
{
    try {
        $directorio_respaldos = "respaldos/";
        $ruta_completa_zip = $directorio_respaldos . $archivo_zip_nombre;

        if (!file_exists($ruta_completa_zip)) {
            throw new Exception("El archivo de respaldo ZIP '$archivo_zip_nombre' no existe.");
        }

        $zip = new ZipArchive;
        $temp_dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'restore_' . uniqid() . DIRECTORY_SEPARATOR;
        if (!mkdir($temp_dir, 0777, true) && !is_dir($temp_dir)) {
            throw new RuntimeException("No se pudo crear el directorio temporal.");
        }
        if ($zip->open($ruta_completa_zip) !== TRUE) {
            throw new Exception("No se pudo abrir el archivo ZIP.");
        }
        $zip->extractTo($temp_dir);
        $zip->close();

        $archivos_sql_extraidos = glob($temp_dir . '*.sql');
        if (empty($archivos_sql_extraidos)) {
            $this->limpiarDirectorioTemporal($temp_dir);
            throw new Exception("No se encontraron archivos .sql dentro del ZIP.");
        }

        $pdo_connection_main = new Connection();
        $pdo_main = $pdo_connection_main->Con();

        $pdo_connection_bitacora = new Connection_bitacora();
        $pdo_bitacora = $pdo_connection_bitacora->Con();

        $restaurada_main = false;
        $restaurada_bitacora = false;

        foreach ($archivos_sql_extraidos as $sql_file) {
            $filename = basename($sql_file);

            if (strpos($filename, _DB_NAME_) !== false && strpos($filename, 'db_orgdocente') !== false) {
                $this->RestaurarDesdeSQL($pdo_main, $sql_file);
                $restaurada_main = true;
            } elseif (strpos($filename, _BITA_DB_NAME_) !== false && strpos($filename, 'db_bitacora') !== false) { 
                $this->RestaurarDesdeSQL($pdo_bitacora, $sql_file);
                $restaurada_bitacora = true;
            }
        }

        $pdo_connection_main = null;
        $pdo_connection_bitacora = null;
        $this->limpiarDirectorioTemporal($temp_dir);

        if (!$restaurada_main || !$restaurada_bitacora) {
            throw new Exception("Restauración incompleta. No se encontraron los archivos de respaldo para ambas bases de datos en el ZIP.");
        }

        return ["status" => "success", "message" => "Las bases de datos se han restaurado exitosamente desde: " . $archivo_zip_nombre];

    } catch (Exception $e) {
        error_log("Error en RestaurarSistema: " . $e->getMessage());
        if (isset($temp_dir) && is_dir($temp_dir)) {
            $this->limpiarDirectorioTemporal($temp_dir);
        }
        return ["status" => "error", "message" => "Error al restaurar: " . $e->getMessage()];
    }
}


    public function ObtenerRespaldos()
    {
        $directorio_respaldos = "respaldos/";
        $archivos = [];

        if (is_dir($directorio_respaldos)) {
            $contenido = scandir($directorio_respaldos);
            foreach ($contenido as $item) {
                if ($item != '.' && $item != '..' && strtolower(pathinfo($item, PATHINFO_EXTENSION)) == 'zip' && strpos($item, 'respaldo_completo_') === 0) {
                    $archivos[] = $item;
                }
            }
        }

        usort($archivos, function ($a, $b) use ($directorio_respaldos) {
            $timeA = filemtime($directorio_respaldos . $a);
            $timeB = filemtime($directorio_respaldos . $b);
            return $timeB <=> $timeA;
        });
        return $archivos;
    }

     private function limpiarDirectorioTemporal($dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->limpiarDirectorioTemporal($file);
            } else {
                unlink($file);
            }
        }
        if (is_dir($dirPath)) {
            rmdir($dirPath);
        }
    }
}

