<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

    private $seccionId;
    private $anioId;
    private $codigoSeccion;
    private $cantidadSeccion;

    public function __construct($seccionId = null, $anioId = null, $codigoSeccion = null, $cantidadSeccion = null)
    {
        parent::__construct();
        $this->seccionId = $seccionId;
        $this->anioId = $anioId;
        $this->codigoSeccion = $codigoSeccion;
        $this->cantidadSeccion = $cantidadSeccion;
    }

    public function getseccionId()
    {
        return $this->seccionId;
    }
    public function getAnioId()
    {
        return $this->anioId;
    }
    public function getCodigoSeccion()
    {
        return $this->codigoSeccion;
    }
    public function getCantidadSeccion()
    {
        return $this->cantidadSeccion;
    }

    public function setseccionId($seccionId)
    {
        $this->seccionId = $seccionId;
    }
    public function setAnioId($anioId)
    {
        $this->anioId = $anioId;
    }
    public function setCodigoSeccion($codigoSeccion)
    {
        $this->codigoSeccion = $codigoSeccion;
    }
    public function setCantidadSeccion($cantidadSeccion)
    {
        $this->cantidadSeccion = $cantidadSeccion;
    }



    //Methods

    function Registrar()
    {
        $r = array();
        // if ($this->cantidadSeccion > 45) {
        //     $r['resultado'] = 'registrar';
        //     $r['mensaje'] = 'No se puede registrar una sección con más de 45 estudiantes.';
        //     return $r;
        // }
        if (!$this->Existe($this->codigoSeccion, $this->anioId)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_seccion (
            sec_codigo,
            sec_cantidad,
            ani_id,
            sec_estado
            
        ) VALUES (
            :codigoSeccion,
            :cantidadSeccion,
            :anioId,
            1
        )");
                $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':anioId', $this->anioId, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró la sección correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
        }

        return $r;
    }

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        // if ($this->cantidadSeccion > 45) {
        //     $r['resultado'] = 'modificar';
        //     $r['mensaje'] = 'No se puede modificar una sección con más de 45 estudiantes.';
        //     return $r;
        // }
        if ($this->ExisteId($this->seccionId)) {
            if (!$this->Existe($this->codigoSeccion, $this->anioId, $this->seccionId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_seccion 
                    SET ani_id = :anioId, 
                    sec_codigo = :codigoSeccion, 
                    sec_cantidad = :cantidadSeccion 
                    WHERE sec_id = :seccionId");

                    $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_INT);
                    $stmt->bindParam(':anioId', $this->anioId, PDO::PARAM_INT);
                    $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                    $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la sección correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado NO existe!';
        }
        return $r;
    }

    // /// Eliminar

    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteId($this->seccionId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_seccion
                SET sec_estado = 0
                WHERE sec_id = :seccionId");
                $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_INT);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la sección correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocada NO existe!';
        }
        return $r;
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT s.sec_id, s.sec_codigo, a.ani_anio, s.sec_cantidad, s.ani_id 
            FROM tbl_seccion s 
            INNER JOIN tbl_anio a 
            ON s.ani_id = a.ani_id 
            WHERE s.sec_estado = 1");

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

    public function Existe($codigoSeccion, $anioId, $secIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_seccion WHERE sec_codigo = :codigoSeccion AND ani_id = :anioId AND sec_estado = 1";
            if ($secIdExcluir !== null) {
                $sql .= " AND sec_id != :secIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
            $stmt->bindParam(':anioId', $anioId, PDO::PARAM_INT);
            if ($secIdExcluir !== null) {
                $stmt->bindParam(':secIdExcluir', $secIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La SECCIÓN colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function ExisteId($seccionId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_seccion WHERE sec_id=:seccionId AND sec_estado = 1");

            $stmt->bindParam(':seccionId', $seccionId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La SECCIÓN colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    function obtenerAnios()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT ani_anio, ani_id FROM tbl_anio WHERE ani_activo = 1 AND ani_estado = 1 ORDER BY ani_anio DESC");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }
}
