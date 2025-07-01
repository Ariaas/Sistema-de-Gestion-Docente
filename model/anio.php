<?php
require_once('model/dbconnection.php');

class Anio extends Connection
{

    private $aniAnio;
    private $aniTipo;
    private $aniId;
    private $aniActivo;
    private $aniAperturaFase1;
    private $aniCierraFase1;
    private $aniAperturaFase2;
    private $aniCierraFase2;


    public function __construct($aniAnio = null, $aniId = null, $aniActivo = 1, $aniAperturaFase1 = null, $aniCierraFase1 = null, $aniAperturaFase2 = null, $aniCierraFase2 = null, $aniTipo = 'regular')
    {
        parent::__construct();

        $this->aniAnio = $aniAnio;
        $this->aniId = $aniId;
        $this->aniActivo = $aniActivo;
        $this->aniAperturaFase1 = $aniAperturaFase1;
        $this->aniCierraFase1 = $aniCierraFase1;
        $this->aniAperturaFase2 = $aniAperturaFase2;
        $this->aniCierraFase2 = $aniCierraFase2;
        $this->aniTipo = $aniTipo;
    }

    public function getAnio()
    {
        return $this->aniAnio;
    }
    public function getTipo()
    {
        return $this->aniTipo;
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
    public function setTipo($aniTipo)
    {
        $this->aniTipo = $aniTipo;
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

        if (!$this->Existe($this->aniAnio, null, $this->aniTipo)) {

            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {

                $stmt = $co->prepare("INSERT INTO tbl_anio (
                    ani_anio,
                    ani_tipo,
                    ani_apertura_fase1,
                    ani_cierra_fase1,
                    ani_apertura_fase2,
                    ani_cierra_fase2,
                    ani_activo,
                    ani_estado
                ) VALUES (
                    :aniAnio,
                    :aniTipo,
                    :aniAperturaFase1,
                    :aniCierraFase1,
                    :aniAperturaFase2,
                    :aniCierraFase2,
                    1,
                    1
                )");

                $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);
                $stmt->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
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
            if (!$this->Existe($this->aniAnio, $this->aniId, $this->aniTipo)) {
                try {
                    $stmt = $co->prepare("UPDATE tbl_anio
                    SET ani_anio = :aniAnio,
                        ani_tipo = :aniTipo,
                        ani_apertura_fase1 = :aniAperturaFase1,
                        ani_cierra_fase1 = :aniCierraFase1,
                        ani_apertura_fase2 = :aniAperturaFase2,
                        ani_cierra_fase2 = :aniCierraFase2
                    WHERE ani_id = :aniId");

                    $stmt->bindParam(':aniId', $this->aniId, PDO::PARAM_INT);
                    $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_STR);
                    $stmt->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
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
                a.ani_id,
                a.ani_anio,
                a.ani_tipo,
                DATE_FORMAT(a.ani_apertura_fase1, '%d/%m/%Y') AS ani_apertura_fase1,
                DATE_FORMAT(a.ani_cierra_fase1, '%d/%m/%Y') AS ani_cierra_fase1,
                DATE_FORMAT(a.ani_apertura_fase2, '%d/%m/%Y') AS ani_apertura_fase2,
                DATE_FORMAT(a.ani_cierra_fase2, '%d/%m/%Y') AS ani_cierra_fase2,
                a.ani_activo,
                a.ani_estado,
                per.ani_id AS per_id 
            FROM tbl_anio a
            LEFT JOIN tbl_anio per ON a.ani_anio = per.ani_anio AND per.ani_tipo = 'per' AND per.ani_estado = 1
            WHERE a.ani_estado = 1 AND a.ani_tipo != 'per'"); 
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

    public function RegistrarPer($idAnioRegular)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmtRegular = $co->prepare("SELECT * FROM tbl_anio WHERE ani_id = :id AND ani_tipo = 'regular' AND ani_estado = 1");
            $stmtRegular->bindParam(':id', $idAnioRegular, PDO::PARAM_INT);
            $stmtRegular->execute();
            $anioRegular = $stmtRegular->fetch(PDO::FETCH_ASSOC);

            if (!$anioRegular) {
                return ['resultado' => 'error', 'mensaje' => 'No se encontró un año válido para crear el PER!'];
            }

            $stmtExistePer = $co->prepare("SELECT ani_id FROM tbl_anio WHERE ani_anio = :anio AND ani_tipo = 'per' AND ani_estado = 1");
            $stmtExistePer->bindParam(':anio', $anioRegular['ani_anio'], PDO::PARAM_STR);
            $stmtExistePer->execute();
            if ($stmtExistePer->fetch()) {
                return ['resultado' => 'error', 'mensaje' => 'Ya existe un PER para el año ' . $anioRegular['ani_anio'] . '.'];
            }

            $aperturaFase1 = (new DateTime($anioRegular['ani_apertura_fase1']))->add(new DateInterval('P14D'));
            $aperturaFase2 = (new DateTime($anioRegular['ani_apertura_fase2']))->add(new DateInterval('P14D'));

            $this->setAnio($anioRegular['ani_anio']);
            $this->setTipo('per');
            $this->setAperturaFase1($aperturaFase1->format('Y-m-d'));
            $this->setAperturaFase2($aperturaFase2->format('Y-m-d'));
            $this->setCierraFase1(null); 
            $this->setCierraFase2(null); 

            return $this->Registrar();
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => 'Error al crear el PER: ' . $e->getMessage()];
        }
    }

    public function ConsultarPerPorAnio($idAnioRegular)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT 
                DATE_FORMAT(per.ani_apertura_fase1, '%d/%m/%Y') AS per_apertura_fase1,
                DATE_FORMAT(per.ani_apertura_fase2, '%d/%m/%Y') AS per_apertura_fase2
            FROM tbl_anio a
            JOIN tbl_anio per ON a.ani_anio = per.ani_anio AND per.ani_tipo = 'per'
            WHERE a.ani_id = :idAnioRegular AND a.ani_estado = 1 AND per.ani_estado = 1");

            $stmt->bindParam(':idAnioRegular', $idAnioRegular, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $r['resultado'] = 'consultar_per';
                $r['mensaje'] = $data;
            } else {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'No se encontró un PER para este año.';
            }
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

    public function Existe($aniAnio, $aniTipo, $anioIdExcluir = null,)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_anio WHERE ani_anio=:aniAnio AND ani_tipo=:aniTipo AND ani_estado = 1";
            if ($anioIdExcluir !== null) {
                $sql .= " AND ani_id != :anioIdExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':aniAnio', $aniAnio, PDO::PARAM_STR);
            $stmt->bindParam(':aniTipo', $aniTipo, PDO::PARAM_STR);
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

    ///Notificaciones

    public function Notificaciones()
    {
        require_once('model/notificaciones.php');
        $n = new Notificaciones();

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $co->query("SELECT ani_id, ani_anio, ani_cierra_fase1, ani_cierra_fase2 FROM tbl_anio WHERE ani_estado = 1");
        $anios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hoy = new DateTime();
        $hoy = new DateTime($hoy->format('Y-m-d'));

        foreach ($anios as $anio) {
            $cierreFase1 = new DateTime($anio['ani_cierra_fase1']);
            $cierreFase1 = new DateTime($cierreFase1->format('Y-m-d'));

            $diasFase1 = (int)$hoy->diff($cierreFase1)->format('%r%a');

            if ($diasFase1 === 20) {
                $mensaje = "La fase 1 del año {$anio['ani_anio']} está a punto de cerrarse: faltan 20 días.";
                $fin = (new DateTime($anio['ani_cierra_fase1']))->modify('+20 days')->format('Y-m-d');
                if (!$n->existeNotificacion($mensaje, $fin)) {
                    $n->RegistrarNotificacion($mensaje, $fin);
                }
            }

            if ($diasFase1 === 0) {
                $mensaje = "Hoy es el cierre de la fase 1 del año {$anio['ani_anio']}. ";
                $fin = (new DateTime($anio['ani_cierra_fase1']))->modify('+20 days')->format('Y-m-d');
                if (!$n->existeNotificacion($mensaje, $fin)) {
                    $n->RegistrarNotificacion($mensaje, $fin);
                }
            }

            $cierreFase2 = new DateTime($anio['ani_cierra_fase2']);
            $cierreFase2 = new DateTime($cierreFase2->format('Y-m-d'));

            $diasFase2 = (int)$hoy->diff($cierreFase2)->format('%r%a');

            if ($diasFase2 === 20) {
                $mensaje = "La fase 2 del año {$anio['ani_anio']} está a punto de cerrarse: faltan 20 días.";
                $fin = (new DateTime($anio['ani_cierra_fase2']))->modify('+20 days')->format('Y-m-d');
                if (!$n->existeNotificacion($mensaje, $fin)) {
                    $n->RegistrarNotificacion($mensaje, $fin);
                }
            }

            if ($diasFase2 === 0) {
                $mensaje = "Hoy es el cierre de la fase 2 del año {$anio['ani_anio']}. ";
                $fin = (new DateTime($anio['ani_cierra_fase2']))->modify('+20 days')->format('Y-m-d');
                if (!$n->existeNotificacion($mensaje, $fin)) {
                    $n->RegistrarNotificacion($mensaje, $fin);
                }
            }
        }
    }
}
