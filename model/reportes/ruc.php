<?php

require_once('model/dbconnection.php');

class Ruc extends Connection
{
    private $nombreUnidad;
    private $trayecto;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_trayecto($valor)
    {
        $this->trayecto = $valor;
    }

    public function set_nombreUnidad($valor)
    {
        $this->nombreUnidad = $valor;
    }

    public function obtenerUnidadesCurriculares()
    {
        $co = $this->con();
        try {
            $sqlBase = "SELECT 
                            u.uc_trayecto AS `Número de Trayecto`, 
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`, 
                            u.uc_nombre AS `Nombre de la Unidad Curricular`, 
                            s.sec_codigo AS `Código de Sección` 
                        FROM tbl_uc u
                        INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id 
                        INNER JOIN tbl_docente d ON ud.doc_id = d.doc_id 
                        INNER JOIN uc_horario uh ON u.uc_id = uh.uc_id
                        INNER JOIN seccion_horario sh ON uh.hor_id = sh.hor_id
                        INNER JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                        ";

            $conditions = [];
            $params = [];

            if (!empty($this->trayecto)) {
                $conditions[] = "u.uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }

            if (!empty($this->nombreUnidad)) {
                $conditions[] = "u.uc_id = :uc_id_filter";
                $params[':uc_id_filter'] = $this->nombreUnidad;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

            $sqlBase .= " GROUP BY u.uc_trayecto, d.doc_id, u.uc_id, s.sec_id";

            $sqlBase .= " ORDER BY u.uc_trayecto, u.uc_nombre, `Nombre Completo del Docente`, s.sec_codigo";

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
            $p = $co->prepare("SELECT DISTINCT uc_trayecto AS tra_id, CONCAT('Trayecto ', uc_trayecto) AS tra_numero FROM tbl_uc WHERE uc_trayecto > 0 ORDER BY uc_trayecto");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }


    public function obtenerUc()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT uc_id, uc_nombre FROM tbl_uc ORDER BY uc_nombre");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUc: " . $e->getMessage());
            return false;
        }
    }
}
