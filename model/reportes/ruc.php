<?php
// model/reportes/rucm.php

// Corregir la ruta si dbconnection.php está en la carpeta model/ principal
require_once('model/dbconnection.php');

class Ruc extends Connection
{
    private $nombreUnidad; // Esta propiedad almacenará el uc_id
    private $trayecto;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_trayecto($valor)
    {
        $this->trayecto = $valor;
    }

    // nombreUnidad realmente recibe el uc_id del formulario
    public function set_nombreUnidad($valor)
    {
        $this->nombreUnidad = $valor;
    }

    public function obtenerUnidadesCurriculares()
    {
        $co = $this->con();
        try {
            $sqlBase = "SELECT 
                            t.tra_numero AS `Número de Trayecto`, 
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`, 
                            u.uc_nombre AS `Nombre de la Unidad Curricular`, 
                            s.sec_codigo AS `Código de Sección` 
                        FROM tbl_uc u 
                        INNER JOIN tbl_trayecto t ON u.tra_id = t.tra_id 
                        INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id 
                        INNER JOIN tbl_docente d ON ud.doc_id = d.doc_id 
                        INNER JOIN tbl_seccion s ON t.tra_id = s.tra_id";

            $conditions = [];
            $params = [];

            if (!empty($this->trayecto)) {
                $conditions[] = "u.tra_id = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }

            // Filtrar por uc_id si se ha proporcionado (viene de $this->nombreUnidad)
            if (!empty($this->nombreUnidad)) {
                $conditions[] = "u.uc_id = :uc_id_filter"; // Comparación exacta por ID
                $params[':uc_id_filter'] = $this->nombreUnidad; // El valor es el uc_id
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

            // Orden crucial para el formato UNIDAD.jpg
            $sqlBase .= " ORDER BY u.uc_nombre, `Nombre Completo del Docente`, s.sec_codigo";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUnidadesCurriculares: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTrayectos()
    {
        $co = $this->con();
        try {
            // Asegúrate que la tabla tbl_trayecto tenga tra_id y tra_numero
            $p = $co->prepare("SELECT tra_id, tra_numero FROM tbl_trayecto ORDER BY tra_numero");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }

    // Este método se usa para poblar el desplegable de Unidades Curriculares en el formulario
    public function obtenerUc()
    {
        $co = $this->con();
        try {
            // Asegúrate que la tabla tbl_uc tenga uc_id y uc_nombre
            $p = $co->prepare("SELECT uc_id, uc_nombre FROM tbl_uc ORDER BY uc_nombre");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Corregido el mensaje de error para referenciar el método correcto
            error_log("Error en Ruc::obtenerUc: " . $e->getMessage());
            return false;
        }
    }
}
