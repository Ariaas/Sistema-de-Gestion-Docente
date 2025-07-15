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
                            u.uc_trayecto AS 'Número de Trayecto',
                            u.uc_nombre AS 'Nombre de la Unidad Curricular',
                            s.sec_codigo AS 'Código de Sección',
                            GROUP_CONCAT(DISTINCT CONCAT(d.doc_nombre, ' ', d.doc_apellido) SEPARATOR '\n') AS 'Nombre Completo del Docente'
                        FROM
                            tbl_uc u
                        INNER JOIN
                            uc_horario uh ON u.uc_codigo = uh.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        LEFT JOIN
                            uc_docente ud ON u.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                        LEFT JOIN
                            tbl_docente d ON ud.doc_cedula = d.doc_cedula
                        ";

            $conditions = [];
            $params = [];

            // Se usa una validación robusta que acepta el valor '0' para Trayecto Inicial
            if (isset($this->trayecto) && $this->trayecto !== '') {
                $conditions[] = "u.uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }
    
            if (!empty($this->seccion)) {
                $conditions[] = "s.sec_codigo = :seccion_id";
                $params[':seccion_id'] = $this->seccion;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }
            
            // Se agrupa por la unidad y la sección para consolidar docentes
            $sqlBase .= " GROUP BY u.uc_trayecto, s.sec_codigo, u.uc_nombre";
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
            $p = $co->prepare("SELECT DISTINCT 
                                   uc_trayecto AS tra_id, 
                                   CASE 
                                     WHEN uc_trayecto = 0 THEN 'Trayecto Inicial' 
                                     ELSE CONCAT('Trayecto ', uc_trayecto) 
                                   END AS tra_numero 
                               FROM tbl_uc ORDER BY uc_trayecto");
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
            $p = $co->prepare("SELECT s.sec_codigo 
                               FROM tbl_seccion s
                               JOIN tbl_anio a ON s.ani_anio = a.ani_anio AND s.ani_tipo = a.ani_tipo
                               WHERE a.ani_activo = 1 AND s.sec_estado = 1
                               ORDER BY s.sec_codigo");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerSecciones: " . $e->getMessage());
            return false;
        }
    }
}