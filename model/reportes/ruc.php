<?php
require_once('model/dbconnection.php');

class Ruc extends Connection
{
    private $anio_id; 
    private $trayecto;
    private $nombreUnidad;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
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
                            u.uc_nombre AS `Nombre de la Unidad Curricular`,
                            CASE 
                                WHEN LEFT(s.sec_codigo, 1) IN ('0', '1', '2') THEN CONCAT('IN', s.sec_codigo)
                                WHEN LEFT(s.sec_codigo, 1) IN ('3', '4') THEN CONCAT('IIN', s.sec_codigo)
                                ELSE s.sec_codigo
                            END AS `Código de Sección`,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`
                        FROM
                            uc_horario uh
                        INNER JOIN
                            tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        INNER JOIN
                            tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        LEFT JOIN (
                            docente_horario dh
                            INNER JOIN uc_docente ud ON dh.doc_cedula = ud.doc_cedula
                            INNER JOIN tbl_docente d ON dh.doc_cedula = d.doc_cedula
                        ) ON dh.sec_codigo = s.sec_codigo AND ud.uc_codigo = u.uc_codigo
                        ";
            
            $conditions = [];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            } else {
                $conditions[] = "s.ani_anio IN (SELECT ani_anio FROM tbl_anio WHERE ani_activo = 1)";
            }
            $conditions[] = "s.sec_estado = 1";

            if (isset($this->trayecto) && $this->trayecto !== '') {
                $conditions[] = "u.uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $this->trayecto;
            }

            if (!empty($this->nombreUnidad)) {
                $conditions[] = "u.uc_codigo = :uc_id_filter";
                $params[':uc_id_filter'] = $this->nombreUnidad;
            }

            if (!empty($conditions)) {
                $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            }

            $sqlBase .= " ORDER BY u.uc_trayecto, u.uc_nombre, `Nombre Completo del Docente`, s.sec_codigo";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUnidadesCurriculares: " . $e->getMessage());
            return false;
        }
    }

   
    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT * FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerAnios: " . $e->getMessage());
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
            error_log("Error en Ruc::obtenerTrayectos: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUc()
    {
       
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT uc_codigo AS uc_id, uc_nombre FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_nombre");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUc: " . $e->getMessage());
            return false;
        }
    }
}