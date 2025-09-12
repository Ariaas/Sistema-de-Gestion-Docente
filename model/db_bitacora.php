<?php
require_once('config/configBitacora.php');

class Connection_bitacora extends PDO
{
    private $conex;

    public function __construct()
    {
        $conexstring = "mysql:host=" . _BITA_DB_HOST_ . ";dbname=" . _BITA_DB_NAME_ . ";charset=utf8";

        try {
            $this->conex = new PDO($conexstring, _BITA_DB_USER_, _BITA_DB_PASS_);
            $this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Conexión Fallida: " . $e->getMessage());
        }
    }

    public function Con()
    {
        return $this->conex;
    }
}
