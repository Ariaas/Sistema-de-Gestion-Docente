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
                COALESCE(CONCAT(d.doc_nombre, ' ', d.doc_apellido), 'NO ASIGNADO') AS `Nombre Completo del Docente`,
                GROUP_CONCAT(
                    DISTINCT CASE 
                        WHEN LEFT(s.sec_codigo, 1) IN ('0', '1', '2') THEN CONCAT('IN', s.sec_codigo)
                        WHEN LEFT(s.sec_codigo, 1) IN ('3', '4') THEN CONCAT('IIN', s.sec_codigo)
                        ELSE s.sec_codigo
                    END ORDER BY s.sec_codigo SEPARATOR '\n'
                ) AS `Código de Sección`
            FROM
                uc_horario uh
            INNER JOIN
                tbl_uc u ON uh.uc_codigo = u.uc_codigo
            LEFT JOIN
                tbl_docente d ON uh.doc_cedula = d.doc_cedula AND d.doc_estado = 1
            INNER JOIN
                tbl_seccion s ON uh.sec_codigo = s.sec_codigo
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

            $sqlBase .= " GROUP BY u.uc_trayecto, u.uc_nombre, `Nombre Completo del Docente`";
            $sqlBase .= " ORDER BY u.uc_trayecto, u.uc_nombre, `Nombre Completo del Docente`";

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
    
    public function obtenerUcPorTrayecto($trayecto_id)
    {
        $co = $this->con();
        try {
            $sql = "SELECT uc_codigo AS uc_id, uc_nombre FROM tbl_uc WHERE uc_estado = 1";
            $params = [];

            if ($trayecto_id !== '' && ($trayecto_id >= 0)) {
                $sql .= " AND uc_trayecto = :trayecto_id";
                $params[':trayecto_id'] = $trayecto_id;
            }

            $sql .= " ORDER BY uc_nombre";

            $p = $co->prepare($sql);
            $p->execute($params);
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Ruc::obtenerUcPorTrayecto: " . $e->getMessage());
            return false;
        }
    }
}