<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

    private $codigoSeccion;
    private $cantidadSeccion;
    private $trayectoAnio;
    private $trayectoNumero;
    private $seccionId;
    private $trayectoSeccion;
    private $grupo;
    private $cohorteSeccion;
    private $nombreSeccion;

    public function __construct($codigoSeccion = null, $cantidadSeccion = null, $seccionId = null, $trayectoNumero = null, $trayectoAnio = null, $trayectoSeccion = null, $grupo = null, $cohorteSeccion = null, $nombreSeccion = null)
    {
        parent::__construct();

        $this->codigoSeccion = $codigoSeccion;
        $this->cantidadSeccion = $cantidadSeccion;
        $this->seccionId = $seccionId;
        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoSeccion = $trayectoSeccion;
        $this->grupo = $grupo;
        $this->cohorteSeccion = $cohorteSeccion;
        $this->nombreSeccion = $nombreSeccion;
    }

    // Getters
    public function getCodigoSeccion()
    {
        return $this->codigoSeccion;
    }

    public function getCantidadSeccion()
    {
        return $this->cantidadSeccion;
    }

    public function getTrayectoAnio()
    {
        return $this->trayectoAnio;
    }

    public function getTrayectoNumero()
    {
        return $this->trayectoNumero;
    }

    public function getTrayectoSeccion()
    {
        return $this->trayectoSeccion;
    }

    public function getGrupo()
    {
        return $this->grupo;
    }

    public function getseccionId()
    {
        return $this->seccionId;
    }

    public function getcohorteSeccion()
    {
        return $this->cohorteSeccion;
    }

    public function getNombreSeccion()
    {
        return $this->nombreSeccion;
    }

    // Setters
    public function setCodigoSeccion($codigoSeccion)
    {
        $this->codigoSeccion = $codigoSeccion;
    }

    public function setCantidadSeccion($cantidadSeccion)
    {
        $this->cantidadSeccion = $cantidadSeccion;
    }

    public function setTrayectoAnio($trayectoAnio)
    {
        $this->trayectoAnio = $trayectoAnio;
    }

    public function setTrayectoNumero($trayectoNumero)
    {
        $this->trayectoNumero = $trayectoNumero;
    }

    public function setTrayectoSeccion($trayectoSeccion)
    {
        $this->trayectoSeccion = $trayectoSeccion;
    }

    public function setGrupoId($grupo)
    {
        $this->grupo = $grupo;
    }

    public function setseccionId($seccionId)
    {
        $this->seccionId = $seccionId;
    }

    public function setcohorteSeccion($cohorteSeccion)
    {
        $this->cohorteSeccion = $cohorteSeccion;
    }

    public function setNombreSeccion($nombreSeccion)
    {
        $this->nombreSeccion = $nombreSeccion;
    }

    //Methods

    function Registrar()
    {
        $r = array();
        if ($this->cantidadSeccion > 45) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'No se puede registrar una sección con más de 45 estudiantes.';
            return $r;
        }
        if (!$this->Existe($this->codigoSeccion, $this->trayectoSeccion, $this->nombreSeccion)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_seccion (
            tra_id,
            sec_codigo,
            sec_cantidad,
            coh_id,
            sec_nombre,
            sec_estado
        ) VALUES (
            :trayectoSeccion,
            :codigoSeccion,
            :cantidadSeccion,
            :cohorteSeccion,
            :nombreSeccion,
            1
        )");
                $stmt->bindParam(':trayectoSeccion', $this->trayectoSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':cohorteSeccion', $this->cohorteSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':nombreSeccion', $this->nombreSeccion, PDO::PARAM_STR);

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
        if ($this->cantidadSeccion > 45) {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'No se puede modificar una sección con más de 45 estudiantes.';
            return $r;
        }
        if ($this->ExisteSeccion($this->seccionId)) {
            if (!$this->existe($this->codigoSeccion, $this->trayectoSeccion, $this->nombreSeccion, $this->seccionId)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_seccion
                    SET sec_codigo = :codigoSeccion , sec_cantidad = :cantidadSeccion, tra_id = :trayectoId, sec_nombre = :nombreSeccion, coh_id = :cohorteSeccion
                    WHERE sec_id = :seccionId");

                    $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_STR);
                    $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                    $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                    $stmt->bindParam(':trayectoId', $this->trayectoSeccion, PDO::PARAM_INT);
                    $stmt->bindParam(':nombreSeccion', $this->nombreSeccion, PDO::PARAM_STR);
                    $stmt->bindParam(':cohorteSeccion', $this->cohorteSeccion, PDO::PARAM_INT);

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
        if ($this->ExisteSeccion($this->seccionId)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_seccion
                SET sec_estado = 0
                WHERE sec_id = :seccionId");
                $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_STR);
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
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'consultar') {
                $stmt = $co->query("SELECT
                    s.sec_id, 
                    s.sec_codigo, 
                    s.sec_cantidad, 
                    t.tra_id,           
                    t.tra_numero, 
                    t.ani_id,
                    a.ani_anio,
                    s.coh_id,
                    c.coh_numero,
                    s.sec_nombre
                FROM tbl_seccion s
                INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                INNER JOIN tbl_anio a ON t.ani_id = a.ani_id
                INNER JOIN tbl_cohorte c ON s.coh_id = c.coh_id
                WHERE s.sec_estado = 1");
            } elseif ($accion === 'consultarUnion') {
                $sql = "SELECT 
                    s.sec_grupo,
                    GROUP_CONCAT(CONCAT(s.sec_codigo, c.coh_numero) SEPARATOR '; ') AS secciones,
                    CONCAT(t.tra_numero, ' - ', a.ani_anio) AS trayecto,
                    t.tra_id,
                    a.ani_id,
                    SUM(s.sec_cantidad) AS suma_cantidades
                FROM tbl_seccion s
                INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                INNER JOIN tbl_anio a ON t.ani_id = a.ani_id
                INNER JOIN tbl_cohorte c ON s.coh_id = c.coh_id
                WHERE s.sec_estado = 1
                  AND s.sec_grupo IS NOT NULL
                  AND s.sec_grupo != ''
                GROUP BY s.sec_grupo, t.tra_id, a.ani_id
                ORDER BY t.tra_numero, a.ani_anio, s.sec_grupo";
                $stmt = $co->query($sql);
            } else {
                throw new Exception("Acción inválida: $accion");
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = $accion;
            $r['mensaje']   = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje']   = $e->getMessage();
        }

        $co = null;
        return $r;
    }

    public function Existe($codigoSeccion, $trayectoSeccion, $nombreSeccion, $seccionIdExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "
                SELECT COUNT(*) AS cnt
                FROM tbl_seccion s
                INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                WHERE s.sec_codigo = :codigoSeccion 
                  AND s.sec_nombre = :nombreSeccion
                  AND t.tra_id = :trayectoSeccion 
                  AND s.sec_estado = 1 
                  AND t.tra_estado = 1
            ";
            if ($seccionIdExcluir !== null) {
                $sql .= " AND s.sec_id != :seccionIdExcluir";
            }
            $stmt = $co->prepare($sql);

            $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoSeccion', $trayectoSeccion, PDO::PARAM_INT);
            $stmt->bindParam(':nombreSeccion', $nombreSeccion, PDO::PARAM_STR);
            if ($seccionIdExcluir !== null) {
                $stmt->bindParam(':seccionIdExcluir', $seccionIdExcluir, PDO::PARAM_INT);
            }
            $stmt->execute();

            $count = (int) $stmt->fetchColumn();

            if ($count > 0) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La SECCIÓN colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function ExisteSeccion($seccionId)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_seccion WHERE sec_id=:seccionId AND sec_estado = 1");

            $stmt->bindParam(':seccionId', $seccionId, PDO::PARAM_STR);
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

    function obtenerTrayectos()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT t.tra_id, t.tra_numero, t.ani_id, a.ani_anio 
                            FROM tbl_trayecto t
                            INNER JOIN tbl_anio a ON t.ani_id = a.ani_id
                            WHERE t.tra_estado = 1 AND a.ani_activo = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }
}