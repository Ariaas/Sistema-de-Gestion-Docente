<?php
require_once('model/dbconnection.php');

class Seccion extends Connection
{

    private $codigoSeccion;
    private $cantidadSeccion;
    private $trayectoAnio;
    private $trayectoNumero;
    private $idSeccion;
    private $trayectoSeccion;


    //Construct
    public function __construct($codigoSeccion = null, $cantidadSeccion = null, $idSeccion = null, $trayectoNumero = null, $trayectoAnio = null, $trayectoSeccion = null)
    {
        parent::__construct();

        $this->codigoSeccion = $codigoSeccion;
        $this->cantidadSeccion = $cantidadSeccion;
        $this->idSeccion = $idSeccion;
        $this->trayectoNumero = $trayectoNumero;
        $this->trayectoAnio = $trayectoAnio;
        $this->trayectoSeccion = $trayectoSeccion;
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

    //Methods

    /// Registrar

    function Registrar()
    {
        $r = array();

        // Verificar si ya existe una sección con el mismo código y trayecto
        if (!$this->Existe($this->codigoSeccion, $this->trayectoNumero, $this->trayectoAnio)) {
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
            $r['mensaje'] = 'ERROR! <br/> La sección con el código especificado ya existe!';
        }

        return $r;
    }

    /// Actualizar

    // function Modificar()
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = array();
    //     if ($this->ExisteTrayecto($this->trayectoId)) {
    //         if (!$this->existe($this->trayectoNumero, $this->trayectoAnio)) {
    //             try {
    //                 $stmt = $co->prepare("UPDATE tbl_trayecto
    //                 SET tra_anio = :trayectoAnio, tra_numero = :trayectoNumero
    //                 WHERE tra_id = :trayectoId");

    //                 $stmt->bindParam(':trayectoAnio', $this->trayectoAnio, PDO::PARAM_STR);
    //                 $stmt->bindParam(':trayectoNumero', $this->trayectoNumero, PDO::PARAM_STR);
    //                 $stmt->bindParam(':trayectoId', $this->trayectoId, PDO::PARAM_INT);

    //                 $stmt->execute();

    //                 $r['resultado'] = 'modificar';
    //                 $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el trayecto correctamente!';
    //             } catch (Exception $e) {
    //                 $r['resultado'] = 'error';
    //                 $r['mensaje'] = $e->getMessage();
    //             }
    //         } else {
    //             $r['resultado'] = 'modificar';
    //             $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado YA existe!';
    //         }
    //     } else {
    //         $r['resultado'] = 'modificar';
    //         $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado NO existe!';
    //     }
    //     return $r;
    // }

    // /// Eliminar

    // function Eliminar()
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = array();
    //     if ($this->ExisteTrayecto($this->trayectoId)) {
    //         try {
    //             $stmt = $co->prepare("UPDATE tbl_trayecto
    //             SET tra_estado = 0
    //             WHERE tra_id = :trayectoId");
    //             $stmt->bindParam(':trayectoId', $this->trayectoId, PDO::PARAM_STR);
    //             $stmt->execute();

    //             $r['resultado'] = 'eliminar';
    //             $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el trayecto correctamente!';
    //         } catch (Exception $e) {
    //             $r['resultado'] = 'error';
    //             $r['mensaje'] = $e->getMessage();
    //         }
    //     } else {
    //         $r['resultado'] = 'eliminar';
    //         $r['mensaje'] = 'ERROR! <br/> El TRAYECTO colocado NO existe!';
    //     }
    //     return $r;
    // }

    /// Listar

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT 
                s.sec_codigo, 
                s.sec_cantidad, 
                t.tra_numero, 
                t.tra_anio 
            FROM tbl_seccion s
            INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
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

    /// Consultar exitencia

    public function Existe($codigoSeccion, $trayectoNumero, $trayectoAnio)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * 
            FROM tbl_seccion s
            INNER JOIN tbl_trayecto t ON s.tra_id = t.tra_id
            WHERE s.sec_codigo = :codigoSeccion 
            AND t.tra_numero = :trayectoNumero 
            AND t.tra_anio = :trayectoAnio 
            AND s.sec_estado = 1 
            AND t.tra_estado = 1");

            $stmt->bindParam(':codigoSeccion', $codigoSeccion, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoNumero', $trayectoNumero, PDO::PARAM_STR);
            $stmt->bindParam(':trayectoAnio', $trayectoAnio, PDO::PARAM_STR);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'La sección con el código especificado ya existe para el trayecto y año indicados.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Cerrar la conexión
        $co = null;
        return $r;
    }

    // public function ExisteTrayecto($trayectoId)
    // {
    //     $co = $this->Con();
    //     $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     $r = array();
    //     try {
    //         $stmt = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_id=:trayectoId AND tra_estado = 1");

    //         $stmt->bindParam(':trayectoId', $trayectoId, PDO::PARAM_STR);
    //         $stmt->execute();
    //         $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
    //         if ($fila) {
    //             $r['resultado'] = 'existe';
    //             $r['mensaje'] = ' El TRAYECTO colocado YA existe!';
    //         }
    //     } catch (Exception $e) {
    //         $r['resultado'] = 'error';
    //         $r['mensaje'] = $e->getMessage();
    //     }
    //     // Se cierra la conexión
    //     $co = null;
    //     return $r;
    // }

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
}
