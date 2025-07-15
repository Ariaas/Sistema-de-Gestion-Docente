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
            // ▼▼▼ CORRECCIÓN EN LA CONSULTA SQL ▼▼▼
            $sqlBase = "SELECT 
                            u.uc_trayecto AS `Número de Trayecto`,
                            u.uc_nombre AS `Nombre de la Unidad Curricular`,
                            s.sec_codigo AS `Código de Sección`,
                            /* Se cambia GROUP_CONCAT por una simple concatenación para obtener una fila por docente */
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS `Nombre Completo del Docente`
                        FROM tbl_uc u
                        INNER JOIN uc_horario uh ON u.uc_codigo = uh.uc_codigo
                        INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        LEFT JOIN uc_docente ud ON u.uc_codigo = ud.uc_codigo AND ud.uc_doc_estado = 1
                        LEFT JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                        ";

            $conditions = [];
            $params = [];

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

            /* Se elimina la cláusula GROUP BY para evitar la agrupación de docentes */

            $sqlBase .= " ORDER BY u.uc_trayecto, u.uc_nombre, s.sec_codigo";

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
