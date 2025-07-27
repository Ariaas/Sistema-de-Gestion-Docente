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
                            d.doc_cedula AS IDDocente,
                            CONCAT(d.doc_nombre, ' ', d.doc_apellido) AS NombreCompletoDocente,
                            d.doc_cedula AS CedulaDocente,
                            u.uc_nombre AS NombreUnidadCurricular,
                            CASE 
                                WHEN u.uc_trayecto IN (0, 1, 2) THEN CONCAT('IN', s.sec_codigo)
                                WHEN u.uc_trayecto IN (3, 4) THEN CONCAT('IIN', s.sec_codigo)
                                ELSE s.sec_codigo
                            END AS NombreSeccion
                        FROM
                            docente_horario dh
                        INNER JOIN
                            tbl_docente d ON dh.doc_cedula = d.doc_cedula
                        INNER JOIN
                            tbl_seccion s ON dh.sec_codigo = s.sec_codigo
                        INNER JOIN
                            uc_horario uh ON s.sec_codigo = uh.sec_codigo
                        INNER JOIN
                            tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        ";
            
            $conditions = ["d.doc_estado = 1", "s.sec_estado = 1"];
            $params = [];

            if (!empty($this->anio_id)) {
                $conditions[] = "s.ani_anio = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }

            if (!empty($this->fase)) {
                $fase_condition = '';
                switch ($this->fase) {
                    case '1':
                        $fase_condition = "(u.uc_periodo = 'Fase I' OR LOWER(u.uc_periodo) = 'anual')";
                        break;
                    case '2':
                        $fase_condition = "(u.uc_periodo = 'Fase II' OR LOWER(u.uc_periodo) = 'anual')";
                        break;
                    case 'Anual':
                        break;
                }
                if ($fase_condition) {
                    $conditions[] = $fase_condition;
                }
            }
            
            $sqlBase .= " WHERE " . implode(" AND ", $conditions);
            
            // --- CAMBIO: Se ajusta el ORDEN para agrupar UCs ---
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
