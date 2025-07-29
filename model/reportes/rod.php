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

            $sql = "SELECT
                        d.doc_cedula,
                        CONCAT(d.doc_apellido, ', ', d.doc_nombre) AS nombre_completo,
                        d.doc_ingreso AS doc_fecha_ingreso,
                        d.doc_dedicacion,
                        d.doc_anio_concurso,
                        d.doc_tipo_concurso,
                        d.doc_observacion,
                        titulos.doc_perfil_profesional,
                        act.act_academicas,
                        act.doc_horas_descarga,
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
                        SELECT 
                            doc_cedula, 
                            act_academicas,
                            COALESCE(SUM(act_creacion_intelectual + act_integracion_comunidad + act_gestion_academica + act_otras), 0) as doc_horas_descarga
                        FROM tbl_actividad GROUP BY doc_cedula, act_academicas
                    ) AS act ON d.doc_cedula = act.doc_cedula
                    LEFT JOIN (
                        SELECT doc_cedula, GROUP_CONCAT(cor_nombre SEPARATOR ', ') as coordinaciones
                        FROM coordinacion_docente GROUP BY doc_cedula
                    ) AS coords ON d.doc_cedula = coords.doc_cedula
                    LEFT JOIN (
                        SELECT
                            d_inner.doc_cedula,
                            u.uc_nombre,
                            -- CORRECCIÓN: La subconsulta ahora usa la cohorte de la sección para encontrar la malla correcta
                            (
                                SELECT um.mal_hora_academica FROM uc_malla um 
                                JOIN tbl_malla m ON um.mal_codigo = m.mal_codigo 
                                WHERE um.uc_codigo = u.uc_codigo 
                                  AND m.mal_activa = 1
                                  AND m.mal_cohorte = SUBSTRING(s.sec_codigo, -1)
                                LIMIT 1
                            ) AS uc_horas,
                            CASE
                                WHEN u.uc_trayecto IN (0, 1, 2) THEN CONCAT('IN', s.sec_codigo)
                                WHEN u.uc_trayecto IN (3, 4) THEN CONCAT('IIN', s.sec_codigo)
                                ELSE s.sec_codigo
                            END AS sec_codigo_formateado
                        FROM uc_horario uh
                        JOIN tbl_seccion s ON uh.sec_codigo = s.sec_codigo
                        JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        JOIN (
                            SELECT
                                uh_inner.sec_codigo,
                                uh_inner.uc_codigo,
                                (
                                    SELECT d_inner.doc_cedula FROM uc_docente ud
                                    JOIN docente_horario dh ON ud.doc_cedula = dh.doc_cedula
                                    JOIN tbl_docente d_inner ON ud.doc_cedula = d_inner.doc_cedula
                                    WHERE ud.uc_codigo = uh_inner.uc_codigo AND dh.sec_codigo = uh_inner.sec_codigo
                                    ORDER BY d_inner.doc_ingreso ASC
                                    LIMIT 1
                                ) as doc_cedula_valido
                            FROM uc_horario uh_inner
                        ) AS valid_teacher ON uh.sec_codigo = valid_teacher.sec_codigo AND uh.uc_codigo = valid_teacher.uc_codigo
                        JOIN tbl_docente d_inner ON valid_teacher.doc_cedula_valido = d_inner.doc_cedula
                        WHERE s.ani_anio = :anio_anio AND s.sec_estado = 1 AND u.uc_periodo IN (".implode(', ', $placeholders).")
                    ) AS asig ON d.doc_cedula = asig.doc_cedula
                    
                    WHERE d.doc_estado = 1
                    ORDER BY d.doc_apellido, d.doc_nombre, asig.uc_nombre";
            
            $resultado = $co->prepare($sql);
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