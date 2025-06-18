<?php
require_once('model/dbconnection.php');

class Anio extends Connection
{

    private $aniAnio;
    private $aniId;
    private $aniActivo;


    public function __construct($aniAnio = null, $aniId = null, $aniActivo = 1)
    {
        parent::__construct();

        $this->aniAnio = $aniAnio;
        $this->aniId = $aniId;
        $this->aniActivo = $aniActivo;
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

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->aniAnio)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_anio (
                    ani_anio,
                    ani_activo,
                    ani_estado
                ) VALUES (
                    :aniAnio,
                    1,
                    1
                )");

                $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);

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
            if (!$this->existe($this->aniAnio)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_anio
                    SET ani_anio = :aniAnio 
                    WHERE ani_id = :aniId");

                    $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_INT);
                    $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);

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
                $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_STR);
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
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT ani_anio,ani_activo, ani_id FROM tbl_anio WHERE ani_estado = 1");
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
            $stmt->bindParam(':aniId', $aniId, PDO::PARAM_STR);
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

    public function Existe($aniAnio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_anio WHERE ani_anio=:aniAnio AND ani_estado = 1");
            $stmt->bindParam(':aniAnio', $aniAnio, PDO::PARAM_STR);
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
}
