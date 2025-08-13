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


    public function obtenerCuentaCupos()
    {
        $co = $this->con();
        try {
          
            $sql = "
                SELECT
                    s.sec_codigo,
                    s.sec_cantidad,
                    uh.uc_codigo,
                    uh.hor_dia,
                    uh.hor_horainicio
                FROM
                    tbl_seccion s
                LEFT JOIN
                    uc_horario uh ON s.sec_codigo = uh.sec_codigo
                WHERE
                    s.ani_anio = :anio_id AND s.sec_estado = 1
                ORDER BY
                    s.sec_codigo;
            ";

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