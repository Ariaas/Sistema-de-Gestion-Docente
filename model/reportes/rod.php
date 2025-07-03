<?php
require_once('model/dbconnection.php');

class Rod extends Connection
{
    private $anio_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor) {
        $this->anio_id = trim($valor);
    }

    /**
     * Obtiene todos los datos para el reporte, uniendo la información del docente
     * con su asignación a través de uc_docente y las tablas de horario.
     */
    public function obtenerDatosReporte()
    {
        $co = $this->con();
        try {
            $sql = "SELECT
                        d.doc_id,
                        CONCAT(d.doc_apellidos, ' ', d.doc_nombres) AS nombre_completo,
                        d.doc_cedula,
                        d.doc_fecha_ingreso,
                        d.doc_perfil_profesional,
                        d.doc_dedicacion,
                        d.doc_horas_max,
                        d.doc_horas_descarga,
                        d.doc_observacion,
                        uc.uc_nombre,
                        uc.uc_horas,
                        s.sec_codigo,
                        ud.uc_anio_concurso -- CAMBIO: Se obtiene el año de concurso desde la tabla uc_docente
                    FROM
                        tbl_docente d
                    LEFT JOIN
                        uc_docente ud ON d.doc_id = ud.doc_id
                    LEFT JOIN
                        tbl_uc uc ON ud.uc_id = uc.uc_id
                    LEFT JOIN 
                        uc_horario uh ON uc.uc_id = uh.uc_id
                    LEFT JOIN 
                        tbl_horario h ON uh.hor_id = h.hor_id
                    LEFT JOIN 
                        seccion_horario sh ON h.hor_id = sh.hor_id
                    LEFT JOIN 
                        tbl_seccion s ON sh.sec_id = s.sec_id
                    WHERE
                        d.doc_estado = 1 AND s.ani_id = :anio_id -- El filtro se aplica por el ID del año en la sección
                    ORDER BY
                        d.doc_apellidos, d.doc_nombres, uc.uc_nombre";
            
            $resultado = $co->prepare($sql);
            $resultado->execute([':anio_id' => $this->anio_id]);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerDatosReporte: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la lista de años académicos (lapsos) para el filtro.
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
?>