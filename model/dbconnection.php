<?php
require_once('config/config.php');

class Connection extends PDO
{
    private static $sharedConex = null;

    public function __construct()
    {
        if (self::$sharedConex === null) {
            $this->initSharedConnection();
        }
    }

    private function initSharedConnection()
    {
        $conexstring = "mysql:host=" . _DB_HOST_ . ";dbname=" . _DB_NAME_ . ";charset=utf8";
        try {
            self::$sharedConex = new PDO($conexstring, _DB_USER_, _DB_PASS_);
            self::$sharedConex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
                self::$sharedConex->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
        } catch (PDOException $e) {
            die("Conexión Fallida: " . $e->getMessage());
        }
    }
    protected function Con()
    {
        if (self::$sharedConex === null) {
            $this->initSharedConnection();
        }
        return self::$sharedConex;
    }
}
