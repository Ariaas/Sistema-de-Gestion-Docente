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
            $fase_nombre = ($this->fase_numero == 1) ? 'Fase I' : 'Fase II';

            $sql = "SELECT
                        d.doc_cedula,
                        CONCAT(d.doc_apellido, ', ', d.doc_nombre) AS nombre_completo,
                        d.doc_ingreso AS doc_fecha_ingreso,
                        d.doc_dedicacion,
                        d.doc_anio_concurso,
                        d.doc_tipo_concurso,
                        d.doc_observacion,
                        titulos.doc_perfil_profesional,
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
                        SELECT doc_cedula, COALESCE(SUM(act_creacion_intelectual + act_integracion_comunidad + act_gestion_academica + act_otras), 0) as doc_horas_descarga
                        FROM tbl_actividad GROUP BY doc_cedula
                    ) AS act ON d.doc_cedula = act.doc_cedula
                    LEFT JOIN (
                        SELECT doc_cedula, GROUP_CONCAT(cor_nombre SEPARATOR ', ') as coordinaciones
                        FROM coordinacion_docente GROUP BY doc_cedula
                    ) AS coords ON d.doc_cedula = coords.doc_cedula
                    LEFT JOIN (
                        SELECT
                            dh.doc_cedula,
                            u.uc_nombre,
                            um.mal_hora_academica AS uc_horas,
                            CASE
                                WHEN u.uc_trayecto IN (0, 1, 2) THEN CONCAT('IN', s.sec_codigo)
                                WHEN u.uc_trayecto IN (3, 4) THEN CONCAT('IIN', s.sec_codigo)
                                ELSE s.sec_codigo
                            END AS sec_codigo_formateado
                        FROM docente_horario dh
                        INNER JOIN tbl_seccion s ON dh.sec_codigo = s.sec_codigo AND s.sec_estado = 1
                        INNER JOIN uc_horario uh ON s.sec_codigo = uh.sec_codigo
                        INNER JOIN tbl_uc u ON uh.uc_codigo = u.uc_codigo
                        INNER JOIN uc_malla um ON u.uc_codigo = um.uc_codigo AND um.mal_codigo IN (SELECT mal_codigo FROM tbl_malla WHERE mal_activa = 1)
                        WHERE s.ani_anio = :anio_anio AND (u.uc_periodo = :fase_nombre OR u.uc_periodo = 'Anual')
                    ) AS asig ON d.doc_cedula = asig.doc_cedula
                    WHERE d.doc_estado = 1
                    ORDER BY d.doc_apellido, d.doc_nombre, asig.uc_nombre";
            
            $resultado = $co->prepare($sql);
            $resultado->execute([':anio_anio' => $this->anio_id, ':fase_nombre' => $fase_nombre]);
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