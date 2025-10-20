<?php
require_once('config/config.php');

// ---> INICIO DE LA MODIFICACIÓN <---
if (!class_exists('Connection')) {

    // --- Tu código original va aquí dentro ---
    class Connection extends PDO
    {
        private $conex;

        public function __construct()
        {
            $conexstring = "mysql:host=" . _DB_HOST_ . ";dbname=" . _DB_NAME_ . ";charset=utf8";

            try {
                $this->conex = new PDO($conexstring, _DB_USER_, _DB_PASS_);
                $this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Conexión Fallida: " . $e->getMessage());
            }
        }

        protected function Con()
        {
            return $this->conex;
        }

    }
    // --- Fin de tu código original ---

} // ---> FIN DE LA MODIFICACIÓN (Llave de cierre del 'if') <---
?>