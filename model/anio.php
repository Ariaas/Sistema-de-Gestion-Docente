<?php
require_once('model/dbconnection.php');

class Anio extends Connection
{

    private $aniAnio;
    private $aniId;
    private $aniActivo;
    private $aniAperturaFase1;
    private $aniCierraFase1;
    private $aniAperturaFase2;
    private $aniCierraFase2;


    public function __construct($aniAnio = null, $aniId = null, $aniActivo = 1, $aniAperturaFase1 = null, $aniCierraFase1 = null, $aniAperturaFase2 = null, $aniCierraFase2 = null)
    {
        parent::__construct();

        $this->aniAnio = $aniAnio;
        $this->aniId = $aniId;
        $this->aniActivo = $aniActivo;
        $this->aniAperturaFase1 = $aniAperturaFase1;
        $this->aniCierraFase1 = $aniCierraFase1;
        $this->aniAperturaFase2 = $aniAperturaFase2;
        $this->aniCierraFase2 = $aniCierraFase2;
    }

    public function getAnio()
    {
        return $this->aniAnio;
    }
    public function getId()
    {
        return $this->aniId;
    }
    public function getActivo()
    {
        return $this->aniActivo;
    }
    public function getAperturaFase1()
    {
        return $this->aniAperturaFase1;
    }
    public function getCierraFase1()
    {
        return $this->aniCierraFase1;
    }
    public function getAperturaFase2()
    {
        return $this->aniAperturaFase2;
    }
    public function getCierraFase2()
    {
        return $this->aniCierraFase2;
    }
    public function setAnio($aniAnio)
    {
        $this->aniAnio = $aniAnio;
    }
    public function setId($aniId)
    {
        $this->aniId = $aniId;
    }
    public function setActivo($aniActivo)
    {
        $this->aniActivo = $aniActivo;
    }
    public function setAperturaFase1($aniAperturaFase1)
    {
        $this->aniAperturaFase1 = $aniAperturaFase1;
    }
    public function setCierraFase1($aniCierraFase1)
    {
        $this->aniCierraFase1 = $aniCierraFase1;
    }
    public function setAperturaFase2($aniAperturaFase2)
    {
        $this->aniAperturaFase2 = $aniAperturaFase2;
    }
    public function setCierraFase2($aniCierraFase2)
    {
        $this->aniCierraFase2 = $aniCierraFase2;
    }

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->aniAnio)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_anio (
                    ani_anio,
                    ani_apertura_fase1,
                    ani_cierra_fase1,
                    ani_apertura_fase2,
                    ani_cierra_fase2,
                    ani_activo,
                    ani_estado
                ) VALUES (
                    :aniAnio,
                    :aniAperturaFase1,
                    :aniCierraFase1,
                    :aniAperturaFase2,
                    :aniCierraFase2,
                    1,
                    1
                )");

                $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);
                $stmt->bindParam(':aniAperturaFase1', $this->aniAperturaFase1, PDO::PARAM_STR);
                $stmt->bindParam(':aniCierraFase1', $this->aniCierraFase1, PDO::PARAM_STR);
                $stmt->bindParam(':aniAperturaFase2', $this->aniAperturaFase2, PDO::PARAM_STR);
                $stmt->bindParam(':aniCierraFase2', $this->aniCierraFase2, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró el AÑO correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->aniId)) {
            if (!$this->Existe($this->aniAnio, $this->aniId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_anio
                    SET ani_anio = :aniAnio,
                        ani_apertura_fase1 = :aniAperturaFase1,
                        ani_cierra_fase1 = :aniCierraFase1,
                        ani_apertura_fase2 = :aniAperturaFase2,
                        ani_cierra_fase2 = :aniCierraFase2
                    WHERE ani_id = :aniId");

                    $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_INT);
                    $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);
                    $stmt->bindParam(':aniAperturaFase1', $this->aniAperturaFase1, PDO::PARAM_STR);
                    $stmt->bindParam(':aniCierraFase1', $this->aniCierraFase1, PDO::PARAM_STR);
                    $stmt->bindParam(':aniAperturaFase2', $this->aniAperturaFase2, PDO::PARAM_STR);
                    $stmt->bindParam(':aniCierraFase2', $this->aniCierraFase2, PDO::PARAM_STR);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el AÑO correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El AÑO colocada YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO colocada NO existe!';
        }
        return $r;
    }

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->aniId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_anio
                SET ani_estado = 0
                WHERE ani_id = :aniId");

                $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el AÑO correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO colocada NO existe!';
        }
        return $r;
    }


    function Activar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->aniId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_anio
                SET ani_activo = :aniActivo
                WHERE ani_id = :aniId");

                $stmt->bindParam(':aniActivo', $this->aniActivo, PDO::PARAM_INT);
                $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_INT);
                $stmt->execute();

                $r['resultado'] = 'activar';
                $r['mensaje'] = 'Estado actualizado correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'activar';
            $r['mensaje'] = 'No se pudo actualizar el estado!';
        }
        return $r;
    }

    public function Listar()
    {
        $this->DesactivarAnios();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT 
            ani_id,
            ani_anio,
            DATE_FORMAT(ani_apertura_fase1, '%d/%m/%Y') AS ani_apertura_fase1,
            DATE_FORMAT(ani_cierra_fase1, '%d/%m/%Y') AS ani_cierra_fase1,
            DATE_FORMAT(ani_apertura_fase2, '%d/%m/%Y') AS ani_apertura_fase2,
            DATE_FORMAT(ani_cierra_fase2, '%d/%m/%Y') AS ani_cierra_fase2,
            ani_activo,
            ani_estado
            FROM tbl_anio WHERE ani_estado = 1");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ExisteId($aniId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_anio WHERE ani_id=:aniId AND ani_estado = 1");
            $stmt->bindParam(':aniId', $aniId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El AÑO colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existe($aniAnio, $anioIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_anio WHERE ani_anio=:aniAnio AND ani_estado = 1";
            if ($anioIdExcluir !== null) {
                $sql .= " AND ani_id != :anioIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':aniAnio', $aniAnio, PDO::PARAM_STR);
            if ($anioIdExcluir !== null) {
                $stmt->bindParam(':anioIdExcluir', $anioIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El AÑO colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function DesactivarAnios()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $fechaActual = date('Y-m-d');

            $stmt = $co->prepare("UPDATE tbl_anio SET ani_activo = 0 WHERE ani_cierra_fase2 < :fechaActual AND ani_activo = 1");
            $stmt->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt->execute();

            $stmt2 = $co->prepare("UPDATE tbl_anio SET ani_activo = 1 WHERE ani_cierra_fase2 >= :fechaActual AND ani_activo = 0");
            $stmt2->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt2->execute();
        } catch (Exception $e) {
            throw new Exception("Error al desactivar/activar años: " . $e->getMessage());
        }
        $co = null;
    }
}
