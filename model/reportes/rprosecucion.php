<?php
require_once('model/dbconnection.php');

class ProsecucionReport extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerAniosAcademicos()
    {
        $co = $this->con();
        try {
            $stmt = $co->query("SELECT DISTINCT ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerAniosAcademicos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDatosProsecucion($anio)
    {
        $co = $this->con();
        try {
            $sql = "SELECT
                        s_origen.sec_codigo AS origen_codigo,
                        s_origen.sec_cantidad AS origen_cantidad,
                        p.sec_promocion AS promocion_codigo,
                        s_promocion.sec_cantidad AS promocion_cantidad
                    FROM
                        tbl_seccion AS s_origen
                    LEFT JOIN
                        tbl_prosecusion AS p ON s_origen.sec_codigo = p.sec_origen
                    LEFT JOIN
                        tbl_seccion AS s_promocion ON p.sec_promocion = s_promocion.sec_codigo AND s_promocion.ani_anio = :anio
                    WHERE
                        s_origen.ani_anio = :anio AND
                        (s_origen.sec_estado = 1 OR p.sec_promocion IS NOT NULL)
                       
                    ORDER BY
                        s_origen.sec_codigo ASC";

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDatosProsecucion: " . $e->getMessage());
            return [];
        }
    }
}
?>