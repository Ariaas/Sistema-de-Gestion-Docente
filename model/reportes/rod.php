<?php
require_once('model/dbconnection.php');

class Rod extends Connection
{
    private $fase_numero;
    private $ani_anio;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_fase_y_anio($valor)
    {
        if ($valor) {
            $partes = explode('-', $valor);
            $this->fase_numero = $partes[0] ?? null;
            $this->ani_anio = $partes[1] ?? null;
        }
    }

    /**
     * Obtiene los datos para el reporte.
     * ESTA ES LA CONSULTA DEFINITIVA Y CORRECTA.
     */
    public function obtenerDatosReporte()
    {
        if (empty($this->fase_numero)) {
            return false;
        }

        $co = $this->con();
        try {
            $sql = "SELECT
                        d.doc_cedula,
                        CONCAT(d.doc_apellido, ' ', d.doc_nombre) AS nombre_completo,
                        d.doc_ingreso AS doc_fecha_ingreso,
                        (SELECT GROUP_CONCAT(CONCAT(t.tit_prefijo, ' ', t.tit_nombre) SEPARATOR ', ')
                         FROM titulo_docente td
                         JOIN tbl_titulo t ON td.tit_prefijo = t.tit_prefijo AND td.tit_nombre = t.tit_nombre
                         WHERE td.doc_cedula = d.doc_cedula) AS doc_perfil_profesional,
                        d.doc_dedicacion,
                        d.doc_anio_concurso,
                        d.doc_tipo_concurso,
                        CASE d.doc_dedicacion
                            WHEN 'Exclusivo' THEN 36
                            WHEN 'Tiempo Completo' THEN 30
                            WHEN 'Medio Tiempo' THEN 18
                            WHEN 'Tiempo Convencional' THEN 12
                            ELSE 0
                        END AS doc_horas_max,
                        (SELECT COALESCE(SUM(act.act_creacion_intelectual + act.act_integracion_comunidad + act.act_gestion_academica + act.act_otras), 0)
                         FROM tbl_actividad act
                         WHERE act.doc_cedula = d.doc_cedula) AS doc_horas_descarga,
                        d.doc_observacion,
                        (SELECT GROUP_CONCAT(cor_nombre SEPARATOR ', ')
                         FROM coordinacion_docente 
                         WHERE doc_cedula = d.doc_cedula) AS coordinaciones,
                        uc.uc_nombre,
                        um.mal_hora_academica AS uc_horas,
                        s.sec_codigo
                    FROM
                        tbl_docente d
                    LEFT JOIN
                        uc_docente ud ON d.doc_cedula = ud.doc_cedula
                    LEFT JOIN
                        tbl_uc uc ON ud.uc_codigo = uc.uc_codigo
                    LEFT JOIN
                        uc_malla um ON uc.uc_codigo = um.uc_codigo
                    LEFT JOIN
                        uc_horario uh ON uc.uc_codigo = uh.uc_codigo
                    LEFT JOIN
                        tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                    WHERE
                        d.doc_estado = 1
                        AND SUBSTRING_INDEX(s.ani_tipo, '-', -1) = :fase_numero
                    ORDER BY
                        d.doc_apellido, d.doc_nombre, uc.uc_nombre";
            
            $resultado = $co->prepare($sql);
            $resultado->execute([':fase_numero' => $this->fase_numero]);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerDatosReporte: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las fases y años disponibles desde la tabla tbl_fase para el select.
     */
    public function obtenerFasesActivas()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT fase_numero, ani_anio FROM tbl_fase GROUP BY fase_numero, ani_anio ORDER BY ani_anio DESC, fase_numero ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerFasesActivas: " . $e->getMessage());
            return false;
        }
    }
}
?>