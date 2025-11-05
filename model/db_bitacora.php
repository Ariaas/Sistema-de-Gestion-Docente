<?php
require_once('config/configBitacora.php');

class Connection_bitacora extends PDO
{
    /** @var PDO|null PDO compartido para la base de datos de bitácora */
    private static $sharedConex = null;

    public function __construct()
    {
        if (self::$sharedConex === null) {
            $this->initSharedConnection();
        }
    }

    private function initSharedConnection()
    {
        $conexstring = "mysql:host=" . _BITA_DB_HOST_ . ";dbname=" . _BITA_DB_NAME_ . ";charset=utf8";
        try {
            self::$sharedConex = new PDO($conexstring, _BITA_DB_USER_, _BITA_DB_PASS_);
            self::$sharedConex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
                self::$sharedConex->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
        } catch (PDOException $e) {
            die("Conexión Fallida: " . $e->getMessage());
        }
    }

    public function Con()
    {
        if (self::$sharedConex === null) {
            $this->initSharedConnection();
        }
        return self::$sharedConex;
    }
}
