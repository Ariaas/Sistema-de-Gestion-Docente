<?php

require_once('model/dbconnection.php');

class Carga extends Connection
{
    private $anio_id; 
    private $trayecto;
    private $seccion;

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
                            CASE 
                                WHEN u.uc_trayecto IN (0, 1, 2) THEN CONCAT('IN', s.sec_codigo)
                                WHEN u.uc_trayecto IN (3, 4) THEN CONCAT('IIN', s.sec_codigo)
                                ELSE s.sec_codigo
                            END AS 'Código de Sección',
                            (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM docente_horario dh
                                JOIN uc_docente ud ON dh.doc_cedula = ud.doc_cedula
                                JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE dh.sec_codigo = uh.sec_codigo AND ud.uc_codigo = uh.uc_codigo
                                LIMIT 1
                            ) AS 'Nombre Completo del Docente'
                        FROM
                            uc_horario uh
                        INNER JOIN
                            tbl_uc u ON uh.uc_codigo = u.uc_codigo
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
            
            $sqlBase .= " ORDER BY u.uc_trayecto, s.sec_codigo, u.uc_nombre";

            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Carga::obtenerUnidadesCurriculares: " . $e->getMessage());
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
            error_log("Error en Carga::obtenerAnios: " . $e->getMessage());
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