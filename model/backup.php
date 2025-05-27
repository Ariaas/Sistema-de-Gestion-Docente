<?php
require_once("model/dbconnection.php"); 
require_once("config/config.php");      
require_once("model/db_bitacora.php");  
require_once("config/configBitacora.php"); 


class Mantenimiento
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
                    throw new Exception("No se pudo crear el directorio de respaldos en: " . $directorio_respaldos);
                }
            }

            $fecha_actual = date('Ymd_His');
            $sql_files_to_zip = [];

            $databases_config = [
                [
                    'host' => _DB_HOST_,
                    'name' => _DB_NAME_,
                    'user' => _DB_USER_,
                    'pass' => _DB_PASS_,
                    'alias' => 'orgdocente',
                    'pdo_class' => 'Connection'
                ],
                [
                    'host' => _BITA_DB_HOST_,
                    'name' => _BITA_DB_NAME_,
                    'user' => _BITA_DB_USER_,
                    'pass' => _BITA_DB_PASS_,
                    'alias' => 'bitacora',
                    'pdo_class' => 'Connection_bitacora'
                ]
            ];

            $mysqldump_path = $this->detectarRutaMysqldump();

            if ($mysqldump_path) {
                foreach ($databases_config as $db_config) {
                    
                    $nombre_sql = $directorio_respaldos . $db_config['name'] . '_' . $db_config['alias'] . '_' . $fecha_actual . '.sql';
                    $password_arg = empty($db_config['pass']) ? '' : '--password=' . escapeshellarg($db_config['pass']);

                    $command = sprintf(
                        '"%s" --host=%s --user=%s %s --databases %s --skip-lock-tables --routines --events > "%s" 2>&1',
                        $mysqldump_path,
                        escapeshellarg($db_config['host']),
                        escapeshellarg($db_config['user']),
                        $password_arg,
                        escapeshellarg($db_config['name']),
                        $nombre_sql
                    );

                    $this->_ejecutarComando($command);

                    if (!file_exists($nombre_sql) || filesize($nombre_sql) === 0) {
                        throw new Exception("El archivo de respaldo SQL para {$db_config['name']} ({$db_config['alias']}) se creó vacío o no existe.");
                    }
                    $sql_files_to_zip[] = $nombre_sql;
                }
            } else {
                
                foreach ($databases_config as $db_config) {
                    $pdo_connection = null;
                    if ($db_config['pdo_class'] === 'Connection') {
                        $pdo_connection = new Connection();
                    } else { 
                        $pdo_connection = new Connection_bitacora();
                    }

                    $resultado_alternativo = $this->GuardarRespaldoAlternativoUnico(
                        $pdo_connection,
                        $db_config['alias'], 
                        $db_config['name'],  
                        $directorio_respaldos,
                        $fecha_actual        
                    );

                    if ($resultado_alternativo['success']) {
                        $sql_files_to_zip[] = $resultado_alternativo['filepath_sql'];
                    } else {
                        $pdo_connection = null;
                        throw new Exception("Error en respaldo alternativo para {$db_config['alias']}: " . $resultado_alternativo['message']);
                    }
                    $pdo_connection = null; 
                }
            }

            if (empty($sql_files_to_zip)) {
                throw new Exception("No se generaron archivos SQL para respaldar.");
            }

            
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
                return ["status" => "success", "message" => "Respaldo de ambas bases de datos guardado y comprimido en: " . $zip_file_base_name];
            } else {
                foreach ($sql_files_to_zip as $sql_file) { 
                    if (file_exists($sql_file)) unlink($sql_file);
                }
                throw new Exception("Se generaron los respaldos SQL, pero no se pudieron comprimir.");
            }
        } catch (Exception $e) {
            error_log("Error en GuardarRespaldo: " . $e->getMessage());
            return ["status" => "error", "message" => "Error al generar el respaldo: " . $e->getMessage()];
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
                                $sqlContent .= implode(",\n", $valuesBatch) . ";\n";
                                $valuesBatch = [];
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
            if (strtolower(pathinfo($ruta_completa_zip, PATHINFO_EXTENSION)) !== 'zip') {
                throw new Exception("El archivo seleccionado no es un ZIP válido.");
            }

            $zip = new ZipArchive;
            $temp_dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'restore_' . uniqid() . DIRECTORY_SEPARATOR;

            if (!mkdir($temp_dir, 0777, true) && !is_dir($temp_dir)) {
                throw new RuntimeException("No se pudo crear el directorio temporal: " . $temp_dir);
            }

            if ($zip->open($ruta_completa_zip) !== TRUE) {
                throw new Exception("No se pudo abrir el archivo ZIP: " . $ruta_completa_zip);
            }
            $zip->extractTo($temp_dir);
            $zip->close();

            $archivos_sql_extraidos = glob($temp_dir . '*.sql');
            if (empty($archivos_sql_extraidos)) {
                $this->limpiarDirectorioTemporal($temp_dir);
                throw new Exception("No se encontraron archivos .sql dentro del ZIP.");
            }

            $mysql_path = $this->detectarRutaMysql();
            if ($mysql_path === null) {
                $this->limpiarDirectorioTemporal($temp_dir);
                throw new Exception("No se pudo encontrar el ejecutable 'mysql'. Configure la ruta manualmente si es necesario.");
            }

            $db_configs_map = [
                _DB_NAME_ . '_orgdocente_' => [ 
                    'host' => _DB_HOST_,
                    'name' => _DB_NAME_,
                    'user' => _DB_USER_,
                    'pass' => _DB_PASS_
                ],
                _BITA_DB_NAME_ . '_bitacora_' => [ 
                    'host' => _BITA_DB_HOST_,
                    'name' => _BITA_DB_NAME_,
                    'user' => _BITA_DB_USER_,
                    'pass' => _BITA_DB_PASS_
                ]
            ];

            $restored_dbs_count = 0;
            $errors = [];

            foreach ($archivos_sql_extraidos as $sql_file) {
                $filename = basename($sql_file);
                $target_db_config = null;

                foreach ($db_configs_map as $prefix => $config) {
                    if (strpos($filename, $prefix) === 0) {
                        $target_db_config = $config;
                        break;
                    }
                }

                if ($target_db_config) {
                    try {
                        $password_arg = empty($target_db_config['pass']) ? '' : '--password=' . escapeshellarg($target_db_config['pass']);
                        $command = sprintf(
                            '"%s" --host=%s --user=%s %s %s < "%s" 2>&1',
                            $mysql_path,
                            escapeshellarg($target_db_config['host']),
                            escapeshellarg($target_db_config['user']),
                            $password_arg,
                            escapeshellarg($target_db_config['name']),
                            $sql_file
                        );
                        $this->_ejecutarComando($command);
                        $restored_dbs_count++;
                    } catch (Exception $e) {
                        $errors[] = "Error restaurando {$target_db_config['name']} desde {$filename}: " . $e->getMessage();
                        error_log("Error restaurando {$target_db_config['name']}: " . $e->getMessage());
                    }
                } else {
                    $msg = "No se pudo determinar la base de datos para el archivo: {$filename}. Asegúrese que los nombres de archivo SQL dentro del ZIP sigan el formato 'nombreDBconfig_aliasDB_fecha.sql'.";
                    $errors[] = $msg;
                    error_log($msg);
                }
            }

            $this->limpiarDirectorioTemporal($temp_dir);

            if ($restored_dbs_count > 0 && empty($errors)) {
                return ["status" => "success", "message" => "$restored_dbs_count base(s) de datos restaurada(s) exitosamente desde: " . $archivo_zip_nombre];
            } elseif ($restored_dbs_count > 0 && !empty($errors)) {
                return ["status" => "warning", "message" => "$restored_dbs_count base(s) de datos restaurada(s) con errores: " . implode("; ", $errors)];
            } else {
                throw new Exception("No se restauró ninguna base de datos. Errores: " . implode("; ", $errors));
            }
        } catch (Exception $e) {
            error_log("Error en RestaurarSistema: " . $e->getMessage());
            if (isset($temp_dir) && is_dir($temp_dir)) {
                $this->limpiarDirectorioTemporal($temp_dir);
            }
            return ["status" => "error", "message" => "Error al restaurar: " . $e->getMessage()];
        }
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

    private function detectarRutaMysqldump()
    {
        $windows_paths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql*\\bin\\mysqldump.exe', 
            'C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server*\\bin\\mysqldump.exe',
        ];

        $unix_paths = [
            '/usr/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',
            '/opt/local/lib/mysql*/bin/mysqldump', 
            '/usr/local/bin/mysqldump', 
        ];

        $paths_to_check = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $windows_paths : $unix_paths;

        foreach ($paths_to_check as $path) {
            if (strpos($path, '*') !== false) { 
                $glob_results = glob($path);
                if ($glob_results && file_exists($glob_results[0])) {
                    return $glob_results[0];
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') { 
            $path_output = [];
            exec('which mysqldump 2>&1', $path_output, $return_var);
            if ($return_var === 0 && !empty($path_output[0]) && is_executable(trim($path_output[0]))) {
                return trim($path_output[0]);
            }
        } else { 
            $path_output = [];
            exec('where mysqldump 2>&1', $path_output, $return_var);
            if ($return_var === 0 && !empty($path_output[0])) {
                
                $potential_paths = explode("\n", trim($path_output[0]));
                foreach ($potential_paths as $p_path) {
                    if (file_exists(trim($p_path)) && is_executable(trim($p_path))) {
                        return trim($p_path);
                    }
                }
            }
        }
        return null;
    }

    private function detectarRutaMysql()
    {
        $windows_paths = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\wamp\\bin\\mysql\\mysql*\\bin\\mysql.exe',
            'C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysql.exe',
            'C:\\Program Files\\MySQL\\MySQL Server*\\bin\\mysql.exe',
        ];
        $unix_paths = [
            '/usr/bin/mysql',
            '/usr/local/mysql/bin/mysql',
            '/opt/lampp/bin/mysql',
            '/opt/local/lib/mysql*/bin/mysql',
            '/usr/local/bin/mysql',
        ];

        $paths_to_check = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $windows_paths : $unix_paths;

        foreach ($paths_to_check as $path) {
            if (strpos($path, '*') !== false) {
                $glob_results = glob($path);
                if ($glob_results && file_exists($glob_results[0])) {
                    return $glob_results[0];
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $path_output = [];
            exec('which mysql 2>&1', $path_output, $return_var);
            if ($return_var === 0 && !empty($path_output[0]) && is_executable(trim($path_output[0]))) {
                return trim($path_output[0]);
            }
        } else {
            $path_output = [];
            exec('where mysql 2>&1', $path_output, $return_var);
            if ($return_var === 0 && !empty($path_output[0])) {
                $potential_paths = explode("\n", trim($path_output[0]));
                foreach ($potential_paths as $p_path) {
                    if (file_exists(trim($p_path)) && is_executable(trim($p_path))) {
                        return trim($p_path);
                    }
                }
            }
        }
        return null;
    }
}
