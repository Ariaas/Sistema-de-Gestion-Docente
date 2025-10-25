<?php
require_once('model/dbconnection.php');

class Rod extends Connection
{
    private $anio_id;
    private $fase_numero;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
    }

    public function set_fase($valor)
    {
        $this->fase_numero = $valor;
    }

    public function obtenerDatosReporte()
    {
        if (empty($this->fase_numero) || empty($this->anio_id)) {
            return [];
        }

        $co = $this->con();
        try {
            $periodos_permitidos = ($this->fase_numero == 1) ? ['FASE I', 'ANUAL', 'anual', '0'] : ['FASE II', 'ANUAL', 'anual'];

            $params = [':anio_anio' => $this->anio_id];
            $placeholders = [];
            foreach ($periodos_permitidos as $index => $periodo) {
                $key = ":periodo_" . $index;
                $placeholders[] = $key;
                $params[strval($key)] = $periodo;
            }

           
            $sql_asignaciones = "
            WITH HorasPorBloqueUnico AS (
.
                SELECT DISTINCT
                    uh.doc_cedula,
                    uh.uc_codigo,
                    uh.hor_dia,
                    uh.hor_horainicio,
                    uh.hor_horafin,
                    ROUND(TIMESTAMPDIFF(MINUTE, STR_TO_DATE(uh.hor_horainicio, '%H:%i'), STR_TO_DATE(uh.hor_horafin, '%H:%i')) / 40) AS horas_bloque
                FROM uc_horario uh
                JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                WHERE
                    s.ani_anio = :anio_anio
                    AND s.sec_estado = 1
                    AND uh.doc_cedula IS NOT NULL
                    AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")
            )

            SELECT
                hbu.doc_cedula,
                u.uc_nombre,
                SUM(hbu.horas_bloque) AS uc_horas,
                (
                    
                    SELECT GROUP_CONCAT(DISTINCT
                        CASE
                            WHEN u_inner.uc_trayecto IN ('0', '1', '2') THEN CONCAT('IN', s_inner.sec_codigo)
                            WHEN u_inner.uc_trayecto IN ('3', '4') THEN CONCAT('IIN', s_inner.sec_codigo)
                            ELSE s_inner.sec_codigo
                        END
                        ORDER BY s_inner.sec_codigo ASC SEPARATOR ' - '
                    )
                    FROM uc_horario uh_inner
                    JOIN tbl_seccion s_inner ON uh_inner.sec_codigo = s_inner.sec_codigo
                    JOIN tbl_uc u_inner ON uh_inner.uc_codigo = u_inner.uc_codigo
                    WHERE uh_inner.doc_cedula = hbu.doc_cedula AND uh_inner.uc_codigo = hbu.uc_codigo AND s_inner.ani_anio = :anio_anio
                ) AS sec_codigo_formateado
            FROM HorasPorBloqueUnico hbu
            JOIN tbl_uc u ON hbu.uc_codigo = u.uc_codigo
            GROUP BY hbu.doc_cedula, hbu.uc_codigo, u.uc_nombre
            ";

            
            $fullQuery = "
            SELECT
                d.doc_cedula,
                CONCAT(d.doc_apellido, ', ', d.doc_nombre) AS nombre_completo,
                d.doc_ingreso AS doc_fecha_ingreso,
                d.doc_dedicacion,
                d.doc_anio_concurso,
                d.doc_tipo_concurso,
                d.doc_observacion,
                titulos.doc_perfil_profesional,
                act.act_academicas,
                COALESCE(descarga_coords.total_horas_descarga, 0) AS doc_horas_descarga,
                coords.coordinaciones,
                asig.uc_nombre,
                asig.uc_horas,
                asig.sec_codigo_formateado AS sec_codigo
            FROM
                tbl_docente d
            LEFT JOIN (
                SELECT doc_cedula, GROUP_CONCAT(tit_prefijo, ' ', tit_nombre SEPARATOR ' / ') as doc_perfil_profesional
                FROM titulo_docente GROUP BY doc_cedula
            ) AS titulos ON d.doc_cedula = titulos.doc_cedula
            LEFT JOIN (
                SELECT doc_cedula, act_academicas FROM tbl_actividad GROUP BY doc_cedula, act_academicas
            ) AS act ON d.doc_cedula = act.doc_cedula
            LEFT JOIN (
                SELECT cd.doc_cedula, SUM(c.coor_hora_descarga) AS total_horas_descarga
                FROM coordinacion_docente cd JOIN tbl_coordinacion c ON cd.cor_nombre = c.cor_nombre
                WHERE cd.cor_doc_estado = 1 AND c.cor_estado = 1 GROUP BY cd.doc_cedula
            ) AS descarga_coords ON d.doc_cedula = descarga_coords.doc_cedula
            LEFT JOIN (
                SELECT doc_cedula, GROUP_CONCAT(cor_nombre SEPARATOR ', ') as coordinaciones
                FROM coordinacion_docente WHERE cor_doc_estado = 1 GROUP BY doc_cedula
            ) AS coords ON d.doc_cedula = coords.doc_cedula
            LEFT JOIN (
                {$sql_asignaciones}
            ) AS asig ON d.doc_cedula = asig.doc_cedula
            WHERE d.doc_estado = 1
            ORDER BY d.doc_apellido, d.doc_nombre, asig.uc_nombre";

            $resultado = $co->prepare($fullQuery);
            $resultado->execute($params);

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerDatosReporte: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}
