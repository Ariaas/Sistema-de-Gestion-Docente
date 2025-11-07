<?php

namespace App\Model\Reportes;

use App\Model\Connection;
use PDO;
use PDOException;
use Exception;

class Rod extends Connection
{
    private $anio_id;
    private $ani_tipo;
    private $fase_numero;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
    }

    public function set_ani_tipo($valor)
    {
        $this->ani_tipo = $valor;
    }

    public function set_fase($valor)
    {
        $this->fase_numero = $valor;
    }

    public function obtenerDatosReporte()
    {
        if (empty($this->anio_id) || empty($this->ani_tipo)) {
            return [];
        }
        
        
        $esIntensivo = strtolower($this->ani_tipo) === 'intensivo';
        
        
        if (!$esIntensivo && empty($this->fase_numero)) {
            return [];
        }

        $co = $this->con();
        try {
            
            if ($esIntensivo) {
                
                $periodos_permitidos = ['FASE I', 'FASE II', 'ANUAL', 'anual', '0'];
            } else {
               
                $periodos_permitidos = ($this->fase_numero == 1) ? ['FASE I', 'ANUAL', 'anual', '0'] : ['FASE II', 'ANUAL', 'anual'];
            }

            $params = [':anio_anio' => $this->anio_id, ':ani_tipo' => $this->ani_tipo];
            $placeholders = [];
            foreach ($periodos_permitidos as $index => $periodo) {
                $key = ":periodo_" . $index;
                $placeholders[] = $key;
                $params[strval($key)] = $periodo;
            }

           
            $sql_asignaciones = "
            WITH HorasPorBloqueUnico AS (
                SELECT 
                    uh.doc_cedula,
                    uh.uc_codigo,
                    uh.sec_codigo,
                    uh.hor_dia,
                    uh.hor_horainicio,
                    uh.hor_horafin,
                    uh.subgrupo,
                    ROUND(TIMESTAMPDIFF(MINUTE, STR_TO_DATE(uh.hor_horainicio, '%H:%i'), STR_TO_DATE(uh.hor_horafin, '%H:%i')) / 40) AS horas_bloque
                FROM uc_horario uh
                INNER JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo 
                    AND uh.ani_anio = s.ani_anio 
                    AND uh.ani_tipo = s.ani_tipo
                INNER JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                INNER JOIN tbl_docente d ON uh.doc_cedula = d.doc_cedula
                WHERE
                    s.ani_anio = :anio_anio
                    AND s.ani_tipo = :ani_tipo
                    AND s.sec_estado = 1
                    AND uh.doc_cedula IS NOT NULL
                    AND d.doc_estado = 1
                    AND u.uc_periodo IN (" . implode(', ', $placeholders) . ")
            ),
            HorasSinDuplicarSubgrupos AS (
                SELECT 
                    doc_cedula,
                    uc_codigo,
                    sec_codigo,
                    hor_dia,
                    MIN(hor_horainicio) as hor_horainicio,
                    MIN(hor_horafin) as hor_horafin,
                    MIN(horas_bloque) as horas_bloque
                FROM HorasPorBloqueUnico
                GROUP BY doc_cedula, uc_codigo, sec_codigo, hor_dia
            )
            SELECT
                hsd.doc_cedula,
                u.uc_nombre,
                SUM(hsd.horas_bloque) AS uc_horas,
                (
                    SELECT GROUP_CONCAT(DISTINCT s_inner.sec_codigo ORDER BY s_inner.sec_codigo ASC SEPARATOR ' - ')
                    FROM uc_horario uh_inner
                    INNER JOIN tbl_seccion s_inner ON uh_inner.sec_codigo = s_inner.sec_codigo 
                        AND uh_inner.ani_anio = s_inner.ani_anio 
                        AND uh_inner.ani_tipo = s_inner.ani_tipo
                    INNER JOIN tbl_uc u_inner ON uh_inner.uc_codigo = u_inner.uc_codigo
                    WHERE uh_inner.doc_cedula = hsd.doc_cedula 
                        AND uh_inner.uc_codigo = hsd.uc_codigo 
                        AND s_inner.ani_anio = :anio_anio
                        AND s_inner.ani_tipo = :ani_tipo
                        AND s_inner.sec_estado = 1
                ) AS sec_codigo_formateado
            FROM HorasSinDuplicarSubgrupos hsd
            JOIN tbl_uc u ON hsd.uc_codigo = u.uc_codigo
            GROUP BY hsd.doc_cedula, hsd.uc_codigo, u.uc_nombre
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
                SELECT td.doc_cedula, GROUP_CONCAT(DISTINCT CONCAT(td.tit_prefijo, ' ', td.tit_nombre) ORDER BY td.tit_prefijo, td.tit_nombre SEPARATOR ' / ') as doc_perfil_profesional
                FROM titulo_docente td
                GROUP BY td.doc_cedula
            ) AS titulos ON d.doc_cedula = titulos.doc_cedula
            LEFT JOIN (
                SELECT doc_cedula, MAX(act_academicas) as act_academicas 
                FROM tbl_actividad 
                WHERE act_estado = 1
                GROUP BY doc_cedula
            ) AS act ON d.doc_cedula = act.doc_cedula
            LEFT JOIN (
                SELECT cd.doc_cedula, SUM(c.coor_hora_descarga) AS total_horas_descarga
                FROM coordinacion_docente cd 
                INNER JOIN tbl_coordinacion c ON cd.cor_nombre = c.cor_nombre
                WHERE cd.cor_doc_estado = 1 AND c.cor_estado = 1 
                GROUP BY cd.doc_cedula
            ) AS descarga_coords ON d.doc_cedula = descarga_coords.doc_cedula
            LEFT JOIN (
                SELECT cd.doc_cedula, GROUP_CONCAT(DISTINCT cd.cor_nombre ORDER BY cd.cor_nombre SEPARATOR ', ') as coordinaciones
                FROM coordinacion_docente cd
                WHERE cd.cor_doc_estado = 1 
                GROUP BY cd.doc_cedula
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
            $p = $co->prepare("SELECT ani_anio, ani_tipo, CONCAT(ani_anio, ' - ', ani_tipo) as anio_completo FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC, ani_tipo ASC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rod::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }
}
