<?php
require_once('model/dbconnection.php');

class Anio extends Connection
{
    private $aniAnio;
    private $aniTipo;
    private $aniActivo;
    private $fases = [];

    public function __construct($aniAnio = null, $aniTipo = null, $aniActivo = 1, $fases = [])
    {
        parent::__construct();
        $this->aniAnio = $aniAnio;
        $this->aniTipo = $aniTipo;
        $this->aniActivo = $aniActivo;
        $this->fases = $fases;
    }

    public function getAnio()
    {
        return $this->aniAnio;
    }
    public function getTipo()
    {
        return $this->aniTipo;
    }
    public function getActivo()
    {
        return $this->aniActivo;
    }
    public function getFases()
    {
        return $this->fases;
    }

    public function setAnio($aniAnio)
    {
        $this->aniAnio = $aniAnio;
    }
    public function setTipo($aniTipo)
    {
        $this->aniTipo = $aniTipo;
    }
    public function setActivo($aniActivo)
    {
        $this->aniActivo = $aniActivo;
    }
    public function setFases($fases)
    {
        $this->fases = $fases;
    }

    private function MallaActiva()
    {
        $co = $this->Con();
        try {
            $stmt = $co->query("SELECT 1 FROM tbl_malla WHERE mal_activa = 1 AND mal_estado = 1 LIMIT 1");
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            return false;
        } finally {
            $co = null;
        }
    }

    public function Registrar()
    {
        $r = array();

        if (!$this->MallaActiva()) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'No se puede registrar un nuevo año si no hay una malla curricular activa.';
            return $r;
        }

        if ($this->Existe($this->aniAnio, $this->aniTipo)) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO colocado YA existe!';
            return $r;
        }

        if ($this->ExisteInactivo($this->aniAnio, $this->aniTipo)) {
            return $this->Reactivar();
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();

            $stmtAnio = $co->prepare("INSERT INTO tbl_anio (ani_anio, ani_tipo, ani_activo, ani_estado) VALUES (:aniAnio, :aniTipo, 1, 1)");
            $stmtAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
            $stmtAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
            $stmtAnio->execute();

            $stmtFase = $co->prepare("INSERT INTO tbl_fase (ani_anio, ani_tipo, fase_numero, fase_apertura, fase_cierre) VALUES (:aniAnio, :aniTipo, :faseNumero, :faseApertura, :faseCierre)");
            $fechasFases = [];
            foreach ($this->fases as $fase) {
                $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                $stmtFase->execute();
                $fechasFases[$fase['numero']] = $fase['apertura'];
            }

            $this->Per($co, $this->aniAnio, $this->aniTipo, $fechasFases);

            $co->commit();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el AÑO correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;

        return $r;
    }

    private function Reactivar()
    {
        $r = array();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();

            $stmtAnio = $co->prepare("UPDATE tbl_anio SET ani_estado = 1, ani_activo = 1 WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo");
            $stmtAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
            $stmtAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
            $stmtAnio->execute();

            $stmtFase = $co->prepare("UPDATE tbl_fase SET fase_apertura = :faseApertura, fase_cierre = :faseCierre WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND fase_numero = :faseNumero");
            $fechasFases = [];
            foreach ($this->fases as $fase) {
                $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                $stmtFase->execute();
                $fechasFases[$fase['numero']] = $fase['apertura'];
            }

            $this->Per($co, $this->aniAnio, $this->aniTipo, $fechasFases);

            $co->commit();
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró el AÑO correctamente!';
        } catch (Exception $e) {
            $co->rollBack();
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    private function Per($co, $anio, $tipo, $fechasFases)
    {
        if ($tipo !== 'regular') {
            return;
        }

        require_once('model/notificaciones.php');
        $n = new Notificaciones();
        $finNotificacion = (new DateTime())->modify('+20 days')->format('Y-m-d');

        if (isset($fechasFases[2])) {
            $aperturaFase2 = new DateTime($fechasFases[2]);
            $aperturaPerFase1 = $aperturaFase2->modify('+2 weeks')->format('Y-m-d');

            $stmtPer1 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 1, ?)");
            $stmtPer1->execute([$anio, $tipo, $aperturaPerFase1]);

            $mensaje1 = "En 2 semanas abrirá PER fase 1 del año {$anio}.";
            if (!$n->existeNotificacion($mensaje1, $finNotificacion)) {
                $n->RegistrarNotificacion($mensaje1, $finNotificacion);
            }
        }

        $anioAnterior = $anio - 1;
        $stmtCheck = $co->prepare("SELECT COUNT(*) FROM tbl_per WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 2");
        $stmtCheck->execute([$anioAnterior, $tipo]);
        $perFase2Existe = $stmtCheck->fetchColumn() > 0;

        if (!$perFase2Existe) {
            $stmtAnioAnt = $co->prepare("SELECT ani_anio FROM tbl_anio WHERE ani_anio = ? AND ani_tipo = ? AND ani_estado = 1");
            $stmtAnioAnt->execute([$anioAnterior, $tipo]);
            if ($stmtAnioAnt->fetch()) {
                if (isset($fechasFases[1])) {
                    $aperturaFase1Actual = new DateTime($fechasFases[1]);
                    $aperturaPerFase2Anterior = $aperturaFase1Actual->modify('+2 weeks')->format('Y-m-d');

                    $stmtPer2 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 2, ?)");
                    $stmtPer2->execute([$anioAnterior, $tipo, $aperturaPerFase2Anterior]);

                    $mensaje2 = "En 2 semanas abrirá PER de fase 2 del año {$anioAnterior}.";
                    if (!$n->existeNotificacion($mensaje2, $finNotificacion)) {
                        $n->RegistrarNotificacion($mensaje2, $finNotificacion);
                    }
                }
            }
        }
    }

    public function Modificar($anioOriginal, $tipoOriginal)
    {
        $r = array();
        if (!$this->Existe($this->aniAnio, $this->aniTipo, $anioOriginal, $tipoOriginal)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $co->beginTransaction();

                $stmtAnio = $co->prepare("UPDATE tbl_anio SET ani_anio = :aniAnio, ani_tipo = :aniTipo WHERE ani_anio = :anioOriginal AND ani_tipo = :tipoOriginal");
                $stmtAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtAnio->bindParam(':anioOriginal', $anioOriginal, PDO::PARAM_INT);
                $stmtAnio->bindParam(':tipoOriginal', $tipoOriginal, PDO::PARAM_STR);
                $stmtAnio->execute();

                $stmtFase = $co->prepare("UPDATE tbl_fase SET fase_apertura = :faseApertura, fase_cierre = :faseCierre WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND fase_numero = :faseNumero");
                foreach ($this->fases as $fase) {
                    $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                    $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                    $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                    $stmtFase->execute();
                }

                $co->commit();
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el AÑO correctamente!';
            } catch (Exception $e) {
                $co->rollBack();
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            $co = null;
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO colocado YA existe!';
        }
        return $r;
    }

    public function Eliminar()
    {
        $r = array();
        if ($this->Existe($this->aniAnio, $this->aniTipo)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $stmt = $co->prepare("UPDATE tbl_anio SET ani_estado = 0 WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo");
                $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmt->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el AÑO correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
            $co = null;
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El AÑO que intenta eliminar NO existe!';
        }
        return $r;
    }

    private function ExisteInactivo($aniAnio, $aniTipo)
    {
        $co = $this->Con();
        try {
            $stmt = $co->prepare("SELECT 1 FROM tbl_anio WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND ani_estado = 0");
            $stmt->bindParam(':aniAnio', $aniAnio, PDO::PARAM_INT);
            $stmt->bindParam(':aniTipo', $aniTipo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        } finally {
            $co = null;
        }
    }

    public function Listar()
    {
        $this->Notificaciones();
        $this->DesactivarAnios();
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("
                SELECT 
                    a.ani_anio,
                    a.ani_tipo,
                    a.ani_activo,
                    MAX(CASE WHEN f.fase_numero = 1 THEN DATE_FORMAT(f.fase_apertura, '%d/%m/%Y') ELSE NULL END) AS ani_apertura_fase1,
                    MAX(CASE WHEN f.fase_numero = 1 THEN DATE_FORMAT(f.fase_cierre, '%d/%m/%Y') ELSE NULL END) AS ani_cierra_fase1,
                    MAX(CASE WHEN f.fase_numero = 2 THEN DATE_FORMAT(f.fase_apertura, '%d/%m/%Y') ELSE NULL END) AS ani_apertura_fase2,
                    MAX(CASE WHEN f.fase_numero = 2 THEN DATE_FORMAT(f.fase_cierre, '%d/%m/%Y') ELSE NULL END) AS ani_cierra_fase2
                FROM tbl_anio a
                LEFT JOIN tbl_fase f ON a.ani_anio = f.ani_anio AND a.ani_tipo = f.ani_tipo
                WHERE a.ani_estado = 1
                GROUP BY a.ani_anio, a.ani_tipo, a.ani_activo
                ORDER BY a.ani_anio DESC, a.ani_tipo ASC
            ");
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

    public function Existe($aniAnio, $aniTipo, $anioExcluir = null, $tipoExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_anio WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND ani_estado = 1";
            if ($anioExcluir !== null && $tipoExcluir !== null) {
                $sql .= " AND NOT (ani_anio = :anioExcluir AND ani_tipo = :tipoExcluir)";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':aniAnio', $aniAnio, PDO::PARAM_INT);
            $stmt->bindParam(':aniTipo', $aniTipo, PDO::PARAM_STR);
            if ($anioExcluir !== null && $tipoExcluir !== null) {
                $stmt->bindParam(':anioExcluir', $anioExcluir, PDO::PARAM_INT);
                $stmt->bindParam(':tipoExcluir', $tipoExcluir, PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                return true;
            }
        } catch (Exception $e) {
        }
        $co = null;
        return false;
    }

    public function DesactivarAnios()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $fechaActual = date('Y-m-d');

            $sqlDesactivar = "UPDATE tbl_anio a 
                             JOIN tbl_fase f ON a.ani_anio = f.ani_anio AND a.ani_tipo = f.ani_tipo
                             SET a.ani_activo = 0 
                             WHERE f.fase_numero = 2 
                               AND f.fase_cierre < :fechaActual 
                               AND a.ani_activo = 1";
            $stmt = $co->prepare($sqlDesactivar);
            $stmt->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt->execute();

            $sqlActivar = "UPDATE tbl_anio a 
                           JOIN tbl_fase f ON a.ani_anio = f.ani_anio AND a.ani_tipo = f.ani_tipo
                           SET a.ani_activo = 1 
                           WHERE f.fase_numero = 2 
                             AND f.fase_cierre >= :fechaActual 
                             AND a.ani_activo = 0";
            $stmt2 = $co->prepare($sqlActivar);
            $stmt2->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt2->execute();
        } catch (Exception $e) {
            throw new Exception("Error al desactivar/activar años: " . $e->getMessage());
        }
        $co = null;
    }

    public function Notificaciones()
    {
        require_once('model/notificaciones.php');
        $n = new Notificaciones();

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $co->query("SELECT a.ani_anio, a.ani_tipo, f.fase_numero, f.fase_cierre 
                            FROM tbl_anio a 
                            JOIN tbl_fase f ON a.ani_anio = f.ani_anio AND a.ani_tipo = f.ani_tipo 
                            WHERE a.ani_estado = 1 AND a.ani_tipo != 'per'");
        $fases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);

        foreach ($fases as $fase) {
            if (empty($fase['fase_cierre'])) {
                continue;
            }

            $cierre = new DateTime($fase['fase_cierre']);
            $cierre->setTime(0, 0, 0);

            if ($hoy > $cierre) {
                continue;
            }

            $diferencia = $hoy->diff($cierre);
            $dias_restantes = (int)$diferencia->format('%r%a');

            $tipoAnioTitle = ucfirst($fase['ani_tipo']);
            $finNotificacion = (new DateTime())->modify('+20 days')->format('Y-m-d H:i:s');

            if ($dias_restantes <= 20) {
                if ($dias_restantes == 0) {
                    $mensaje = "Hoy es el cierre de la fase {$fase['fase_numero']} del año {$fase['ani_anio']} ({$tipoAnioTitle}).";
                } else {
                    $mensaje = "La fase {$fase['fase_numero']} del año {$fase['ani_anio']} ({$tipoAnioTitle}) está a punto de cerrarse: faltan {$dias_restantes} días.";
                }

                if (!$n->existeNotificacion($mensaje)) {
                    $n->RegistrarNotificacion($mensaje, $finNotificacion);
                }
            }
        }
    }

    public function Verificar()
    {
        $co = $this->Con();
        $r = ['malla_activa' => false, 'tipos_activos' => []];
        try {
            $stmtMalla = $co->query("SELECT COUNT(*) FROM tbl_malla WHERE mal_estado = 1 AND mal_activa = 1");
            if ($stmtMalla->fetchColumn() > 0) {
                $r['malla_activa'] = true;
            }

            $stmtAnio = $co->query("SELECT ani_tipo FROM tbl_anio WHERE ani_estado = 1 AND ani_activo = 1");
            $r['tipos_activos'] = $stmtAnio->fetchAll(PDO::FETCH_COLUMN, 0);

            $r['resultado'] = 'condiciones_registro';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function consultarPer()
    {
        $co = $this->Con();
        $r = ['resultado' => 'error', 'data' => ['per_fase1' => null, 'per_fase2' => null]];
        try {
            $stmt = $co->prepare("SELECT per_fase, per_apertura FROM tbl_per WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo");
            $stmt->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
            $stmt->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
            $stmt->execute();
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($fila['per_fase'] == 1) {
                    $r['data']['per_fase1'] = $fila['per_apertura'];
                } elseif ($fila['per_fase'] == 2) {
                    $r['data']['per_fase2'] = $fila['per_apertura'];
                }
            }
            $r['resultado'] = 'per_consultado';
        } catch (Exception $e) {
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
