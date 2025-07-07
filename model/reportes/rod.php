<?php
require_once('model/dbconnection.php');

class Rod extends Connection
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

    /**
     * Obtiene la lista de todos los docentes activos con su información base.
     */
    public function obtenerDocentesBase()
    {
        $co = $this->con();
        try {
            $sql = "SELECT
                        d.doc_id,
                        CONCAT(d.doc_apellido, ' ', d.doc_nombre) AS nombre_completo,
                        d.doc_cedula,
                        d.doc_dedicacion,
                        -- Se concatenan los títulos profesionales con saltos de línea
                        GROUP_CONCAT(DISTINCT CONCAT(t.tit_prefijo, ' ', t.tit_nombre) SEPARATOR '\n') AS perfil_profesional,
                        -- Se suman todas las horas de actividades para la descarga
                        COALESCE(act.descarga_total, 0) AS horas_descarga,
                        -- NOTA: Las columnas 'Fecha de Ingreso' y 'Año de Concurso' no existen en la BD.
                        -- Se deberán añadir a la tabla 'tbl_docente' si se desean mostrar.
                        '' AS fecha_ingreso, -- Placeholder
                        '' AS anio_concurso  -- Placeholder
                    FROM
                        tbl_docente d
                    LEFT JOIN titulo_docente td ON d.doc_id = td.doc_id
                    LEFT JOIN tbl_titulo t ON td.tit_id = t.tit_id
                    LEFT JOIN (
                        SELECT doc_id, (act_integracion_intelectual + act_integracion_comunidad + act_gestion_academica + act_otras) AS descarga_total
                        FROM tbl_actividad
                    ) act ON d.doc_id = act.doc_id
                    WHERE d.doc_estado = 1
                    GROUP BY d.doc_id
                    ORDER BY d.doc_apellido, d.doc_nombre";

            $resultado = $co->prepare($sql);
            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerDocentesBase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las unidades curriculares y secciones asignadas a un docente específico,
     * filtrando por año y fase.
     */
    public function obtenerAsignacionesDocente($doc_id)
    {
        $co = $this->con();
        try {
            $params = [':doc_id' => $doc_id];

            $sql = "SELECT
                        uc.uc_nombre,
                        s.sec_codigo,
                        um.mal_hora_academica
                    FROM
                        uc_docente ud
                    JOIN tbl_uc uc ON ud.uc_id = uc.uc_id
                    -- Se asume que cada UC está en una malla activa para obtener las horas
                    LEFT JOIN uc_malla um ON uc.uc_id = um.uc_id
                    LEFT JOIN uc_horario uh ON uc.uc_id = uh.uc_id
                    LEFT JOIN tbl_horario h ON uh.hor_id = h.hor_id
                    LEFT JOIN seccion_horario sh ON h.hor_id = sh.hor_id
                    LEFT JOIN tbl_seccion s ON sh.sec_id = s.sec_id
                    WHERE ud.doc_id = :doc_id AND ud.uc_doc_estado = 1";

            if (!empty($this->anio_id)) {
                $sql .= " AND s.ani_id = :anio_id";
                $params[':anio_id'] = $this->anio_id;
            }
            if (!empty($this->fase)) {
                $sql .= " AND uc.uc_periodo = :fase";
                $params[':fase'] = $this->fase;
            }

            $sql .= " ORDER BY uc.uc_nombre";

            $resultado = $co->prepare($sql);
            $resultado->execute($params);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerAsignacionesDocente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la lista de años académicos activos para el filtro.
     */
    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT ani_id, ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}
