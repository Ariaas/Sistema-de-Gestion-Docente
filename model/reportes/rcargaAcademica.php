<?php
// model/reportes/rucmodel.php

require_once('model/dbconnection.php'); // Asegúrate que esta ruta y el archivo sean correctos.

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

            if (!empty($this->seccion)) {
                $conditions[] = "s.sec_id = :seccion_id";
                $params[':seccion_id'] = $this->seccion;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

            // Ordenación crucial para la agrupación con rowspan
            $sqlBase .= " ORDER BY t.tra_numero, s.sec_codigo, u.uc_nombre";

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

    public function obtenerSecciones()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT sec_id, sec_codigo FROM tbl_seccion ORDER BY sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}
