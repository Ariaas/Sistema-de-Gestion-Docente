<?php
require_once('model/dbconnection.php');

class DefinitivoEmit extends Connection
{
    private $anio_id;
    private $fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = trim($valor);
    }

    public function set_fase($valor)
    {
        $this->fase = trim($valor);
    }

    public function obtenerDatosDefinitivoEmit()
    {
        $co = $this->con();
        try {
           
            $sqlBase = "SELECT
                            (
                                SELECT d.doc_cedula
                                FROM uc_docente ud
                                JOIN docente_horario dh ON ud.doc_cedula = dh.doc_cedula
                                JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE ud.uc_codigo = u.uc_codigo AND dh.sec_codigo = s.sec_codigo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS IDDocente,
                             (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM uc_docente ud
                                JOIN docente_horario dh ON ud.doc_cedula = dh.doc_cedula
                                JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE ud.uc_codigo = u.uc_codigo AND dh.sec_codigo = s.sec_codigo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS NombreCompletoDocente,
                            (
                                SELECT d.doc_cedula
                                FROM uc_docente ud
                                JOIN docente_horario dh ON ud.doc_cedula = dh.doc_cedula
                                JOIN tbl_docente d ON ud.doc_cedula = d.doc_cedula
                                WHERE ud.uc_codigo = u.uc_codigo AND dh.sec_codigo = s.sec_codigo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS CedulaDocente,
                            u.uc_nombre AS NombreUnidadCurricular,
                            s.sec_codigo AS NombreSeccion
                        FROM
                            uc_horario uh
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        ";
            
            $conditions = ["s.sec_estado = 1"];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            if (!empty($this->fase)) {
                $fase_condition = '';
                switch ($this->fase) {
                    case '1':
                        $fase_condition = "(u.uc_periodo = 'Fase I' OR u.uc_periodo LIKE '%anual%' OR u.uc_periodo = '0')";
                        break;
                    case '2':
                        $fase_condition = "(u.uc_periodo = 'Fase II' OR u.uc_periodo LIKE '%anual%')";
                        break;
                }
                if ($fase_condition) {
                    $conditions[] = $fase_condition;
                }
            }
            
            $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            $sqlBase .= " HAVING IDDocente IS NOT NULL"; 
            $sqlBase .= " ORDER BY NombreCompletoDocente, NombreUnidadCurricular, NombreSeccion";
            
            $resultado = $co->prepare($sqlBase);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerDatosDefinitivoEmit: " . $e->getMessage());
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
            error_log("Error en DefinitivoEmit::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}