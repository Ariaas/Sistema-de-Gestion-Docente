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
                            t.tra_numero AS `Número de Trayecto`, 
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`, 
                            u.uc_nombre AS `Nombre de la Unidad Curricular`, 
                            s.sec_codigo AS `Código de Sección` 
                        FROM tbl_uc u 
                        INNER JOIN tbl_trayecto t ON u.tra_id = t.tra_id 
                        INNER JOIN uc_docente ud ON u.uc_id = ud.uc_id 
                        INNER JOIN tbl_docente d ON ud.doc_id = d.doc_id 
                        INNER JOIN tbl_seccion s ON t.tra_id = s.tra_id ";

            $conditions = [] ;
            $params = [];

            $conditions[] = "ud.uc_doc_estado  = '1'";
             
            if (!empty($this->trayecto)) {
                $conditions[] = "u.tra_id = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }

            if (!empty($this->nombreUnidad)) {
                $conditions[] = "u.uc_id = :uc_id_filter"; 
                $params[':uc_id_filter'] = $this->nombreUnidad; 
            }

            if (!empty($conditions)) {
            $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

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
           
            $p = $co->prepare("SELECT tra_id, tra_numero FROM tbl_trayecto ORDER BY tra_numero");
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
