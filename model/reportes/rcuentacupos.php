<?php


require_once('model/dbconnection.php');

class CuentaCupos extends Connection
{
    private $anio_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_anio($valor)
    {
        $this->anio_id = $valor;
    }

    public function obtenerAnios()
    {
        $co = $this->con();
        try {
            $p = $co->prepare("SELECT DISTINCT ani_anio FROM tbl_anio WHERE ani_estado = 1 ORDER BY ani_anio DESC");
            $p->execute();
            return $p->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en CuentaCupos::obtenerAnios: " . $e->getMessage());
            return false;
        }
    }

    // --- CONSULTA FINAL Y SIMPLIFICADA ---
    public function obtenerCuentaCupos()
    {
        $co = $this->con();
        try {
            // Se extrae el primer dígito del código de sección para determinar el Trayecto.
            $sql = "SELECT
                        LEFT(s.sec_codigo, 1) AS Trayecto,
                        s.sec_codigo AS Seccion,
                        s.sec_cantidad AS Cantidad
                    FROM
                        tbl_seccion s
                    WHERE
                        s.ani_anio = :anio_id
                    ORDER BY
                        Trayecto, s.sec_codigo";

            $resultado = $co->prepare($sql);
            $resultado->execute([':anio_id' => $this->anio_id]);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en CuentaCupos::obtenerCuentaCupos: " . $e->getMessage());
            return false;
        }
    }
}
?>