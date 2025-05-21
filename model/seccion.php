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
    private $grupoId;

    //Construct
    public function __construct($codigoSeccion = null, $cantidadSeccion = null, $seccionId = null, $trayectoNumero = null, $trayectoAnio = null, $trayectoSeccion = null, $grupoId = null)
    {
        parent::__construct();

        $this->codigoSeccion = $codigoSeccion;
        $this->cantidadSeccion = $cantidadSeccion;
        $this->seccionId = $seccionId;
        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoSeccion = $trayectoSeccion;
        $this->grupoId = $grupoId;
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

    public function getGrupoId()
    {
        return $this->grupoId;
    }

    public function getseccionId()
    {
        return $this->seccionId;
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

    public function setGrupoId($grupoId)
    {
        $this->grupoId = $grupoId;
    }

    public function setseccionId($seccionId)
    {
        $this->seccionId = $seccionId;
    }

    //Methods

    /// Registrar

    function Registrar()
    {
        $r = array();

        if (!$this->Existe($this->codigoSeccion, $this->trayectoSeccion)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $stmt = $co->prepare("INSERT INTO tbl_seccion (
            tra_id,
            sec_codigo,
            sec_cantidad,
            sec_estado
        ) VALUES (
            :trayectoSeccion,
            :codigoSeccion,
            :cantidadSeccion,
            1
        )");
                $stmt->bindParam(':trayectoSeccion', $this->trayectoSeccion, PDO::PARAM_INT);
                $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/>Se registró la sección correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // Cerrar la conexión
            $co = null;
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La SECCIÓN colocado YA existe!';
        }

        return $r;
    }

    /// Actualizar

    function Modificar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->ExisteSeccion($this->seccionId)) {
            if (!$this->existe($this->codigoSeccion, $this->trayectoSeccion)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_seccion
                    SET sec_codigo = :codigoSeccion , sec_cantidad = :cantidadSeccion, tra_id = :trayectoId
                    WHERE sec_id = :seccionId");

                    $stmt->bindParam(':seccionId', $this->seccionId, PDO::PARAM_STR);
                    $stmt->bindParam(':codigoSeccion', $this->codigoSeccion, PDO::PARAM_STR);
                    $stmt->bindParam(':cantidadSeccion', $this->cantidadSeccion, PDO::PARAM_INT);
                    $stmt->bindParam(':trayectoId', $this->trayectoSeccion, PDO::PARAM_INT);

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

    /// Listar

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
                t.tra_numero, 
                t.tra_anio 
            FROM tbl_seccion s
            INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
            WHERE s.sec_estado = 1");
            } elseif ($accion === 'consultarUnion') {
                $sql = "SELECT 
                        g.gro_id,
                        GROUP_CONCAT(s.sec_codigo SEPARATOR '; ') AS secciones,
                        CONCAT(t.tra_numero, ' - ', t.tra_anio)       AS trayecto
                    FROM tbl_grupo g
                    JOIN seccion_grupo sg ON g.gro_id = sg.gro_id
                    JOIN tbl_seccion   s  ON sg.sec_id = s.sec_id
                    JOIN tbl_trayecto  t  ON s.tra_id = t.tra_id
                    WHERE g.grupo_estado = 1
                    GROUP BY g.gro_id";
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

    /// Consultar exitencia

    public function Existe($codigoSeccion, $trayectoSeccion)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("
            SELECT COUNT(*) AS cnt
            FROM tbl_seccion s
            INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
            WHERE s.sec_codigo = :codigoSeccion 
              AND t.tra_id = :trayectoSeccion 
              AND s.sec_estado = 1 
              AND t.tra_estado = 1
        ");

            $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoSeccion', $trayectoSeccion, PDO::PARAM_INT);
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
            $co = null; // Cerrar la conexión
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
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    function obtenerTrayectos()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT tra_id, tra_numero, tra_anio FROM tbl_trayecto WHERE tra_estado = 1");
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r = [];
        }
        $co = null;
        return $r;
    }


    function Unir($secciones)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $co->beginTransaction();

            $seccionesArray = json_decode($secciones, true);

            $in = implode(',', array_map('intval', $seccionesArray));
            $stmtTrayecto = $co->query("
                SELECT tra_id 
                FROM tbl_seccion 
                WHERE sec_id IN ($in) AND sec_estado = 1
                GROUP BY tra_id
            ");
            $trayectos = $stmtTrayecto->fetchAll(PDO::FETCH_COLUMN);

            if (count($trayectos) !== 1) {
                throw new Exception("Las secciones seleccionadas NO pertenecen al mismo trayecto.");
            }

            $stmtCheck = $co->prepare("
                SELECT COUNT(*) AS cnt
                FROM seccion_grupo sg
                INNER JOIN tbl_grupo g ON sg.gro_id = g.gro_id
                WHERE sg.sec_id = :seccionId AND g.grupo_estado = 1
            ");

            foreach ($seccionesArray as $seccionId) {
                $stmtCheck->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
                $stmtCheck->execute();
                $count = (int)$stmtCheck->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Al menos UNA de las SECCIONES <br/> YA pertenece a un grupo activo.");
                }
            }

            $stmtGrupo = $co->prepare("
                INSERT INTO tbl_grupo (grupo_estado) 
                VALUES (1)
            ");
            $stmtGrupo->execute();
            $grupoId = $co->lastInsertId();

            $stmtLink = $co->prepare("
                INSERT INTO seccion_grupo (gro_id, sec_id) 
                VALUES (:grupoId, :seccionId)
            ");
            $stmtLink->bindParam(':grupoId', $grupoId, PDO::PARAM_INT);

            foreach ($seccionesArray as $seccionId) {
                $stmtLink->bindValue(':seccionId', (int)$seccionId, PDO::PARAM_INT);
                $stmtLink->execute();
            }

            $co->commit();

            $r['resultado'] = 'unir';
            $r['mensaje']   = 'Secciones unidas!<br/>Se unieron las secciones correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje']   = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }

    function Separar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = ['resultado' => null, 'mensaje' => null];

        try {
            $stmt = $co->prepare("UPDATE tbl_grupo
            SET grupo_estado = 0
            WHERE gro_id = :grupoId");
            $stmt->bindParam(':grupoId', $this->grupoId, PDO::PARAM_INT);
            $stmt->execute();

            $r['resultado'] = 'separar';
            $r['mensaje'] = 'Secciones separadas!<br/>Se separaron las secciones correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }

        return $r;
    }
}
