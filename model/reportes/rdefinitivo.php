<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class DefinitivoEmit extends Connection
{
    private $anio_id;
    private $ani_tipo;
    private $fase;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = trim($valor);
    }

    public function set_ani_tipo($valor)
    {
        $this->ani_tipo = trim($valor);
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
                                FROM uc_horario uh2
                                JOIN tbl_docente d ON uh2.doc_cedula = d.doc_cedula
                                WHERE uh2.uc_codigo = uh.uc_codigo 
                                AND uh2.sec_codigo = uh.sec_codigo
                                AND uh2.ani_anio = uh.ani_anio
                                AND uh2.ani_tipo = uh.ani_tipo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS IDDocente,
                            (
                                SELECT CONCAT(d.doc_nombre, ' ', d.doc_apellido)
                                FROM uc_horario uh2
                                JOIN tbl_docente d ON uh2.doc_cedula = d.doc_cedula
                                WHERE uh2.uc_codigo = uh.uc_codigo 
                                AND uh2.sec_codigo = uh.sec_codigo
                                AND uh2.ani_anio = uh.ani_anio
                                AND uh2.ani_tipo = uh.ani_tipo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS NombreCompletoDocente,
                            (
                                SELECT d.doc_cedula
                                FROM uc_horario uh2
                                JOIN tbl_docente d ON uh2.doc_cedula = d.doc_cedula
                                WHERE uh2.uc_codigo = uh.uc_codigo 
                                AND uh2.sec_codigo = uh.sec_codigo
                                AND uh2.ani_anio = uh.ani_anio
                                AND uh2.ani_tipo = uh.ani_tipo
                                ORDER BY d.doc_ingreso ASC
                                LIMIT 1
                            ) AS CedulaDocente,
                            u.uc_nombre AS NombreUnidadCurricular,
                            s.sec_codigo AS NombreSeccion
                        FROM
                            uc_horario uh
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                            AND uh.ani_anio = s.ani_anio 
                            AND uh.ani_tipo = s.ani_tipo
                        ";
            
            $conditions = ["s.sec_estado = 1"];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            if (!empty($this->ani_tipo)) {
                $conditions[] = "s.ani_tipo = :ani_tipo";
                $params[':ani_tipo'] = $this->ani_tipo;
            }

            
            if (!empty($this->fase) && strtolower($this->ani_tipo) !== 'intensivo') {
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
            $p = $co->prepare("SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DefinitivoEmit::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}