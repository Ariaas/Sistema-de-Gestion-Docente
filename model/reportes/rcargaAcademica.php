<?php

require_once('model/dbconnection.php');

class Carga extends Connection
{
    private $trayecto;
    private $seccion;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_trayecto($valor)
    {
        $this->trayecto = $valor;
    }

    public function set_seccion($valor)
    {
        $this->seccion = $valor;
    }

    public function obtenerUnidadesCurriculares()
    {
        $co = $this->con();
        try {

            $sqlBase = "SELECT 
                            u.uc_trayecto AS `Número de Trayecto`, 
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`, 
                            u.uc_nombre AS `Nombre de la Unidad Curricular`, 
                            s.sec_codigo AS `Código de Sección`,
                            s.sec_id
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
 
            if (!empty($this->seccion)) {
                $conditions[] = "s.sec_id = :seccion_id";
                $params[':seccion_id'] = $this->seccion;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sqlBase .= " GROUP BY u.uc_trayecto, s.sec_id, u.uc_id, d.doc_id";

            $sqlBase .= " ORDER BY u.uc_trayecto, s.sec_codigo, u.uc_nombre";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerUnidadesCurriculares: " . $e->getMessage());
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
            error_log("Error en Carga::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerSecciones()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT sec_id, sec_codigo FROM tbl_seccion ORDER BY sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}
