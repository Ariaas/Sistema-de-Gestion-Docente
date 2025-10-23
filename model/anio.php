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
            $stmt = $co->query("SELECT 1 FROM tbl_malla WHERE mal_activa = 1 LIMIT 1");
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

        if (!is_int($this->aniAnio) || $this->aniAnio <= 0 || $this->aniAnio > 2100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año es inválido.';
            return $r;
        }

        $tipoValido = ['regular', 'intensivo'];
        $tipoTrim = is_string($this->aniTipo) ? trim($this->aniTipo) : $this->aniTipo;
        if (!is_string($tipoTrim) || !in_array($tipoTrim, $tipoValido, true) || $this->aniTipo !== $tipoTrim) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año es inválido.';
            return $r;
        }
        $this->aniTipo = $tipoTrim;

        if (!is_array($this->fases) || empty($this->fases)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fases son obligatorias y deben ser un arreglo.';
            return $r;
        }
        if (trim($this->aniTipo) === 'regular' && count($this->fases) < 2) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año regular debe tener dos fases.';
            return $r;
        }
        foreach ($this->fases as $fase) {
            if (!isset($fase['numero']) || !is_int($fase['numero']) || $fase['numero'] <= 0) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Cada fase debe tener un número válido.';
                return $r;
            }
            if (empty($fase['apertura']) || empty($fase['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Cada fase debe tener fecha de apertura y cierre.';
                return $r;
            }
            $apertura = date_create($fase['apertura']);
            $cierre = date_create($fase['cierre']);
            if (!$apertura || !$cierre) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Formato de fecha inválido en las fases.';
                return $r;
            }
            if ($cierre <= $apertura) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La fecha de cierre debe ser posterior a la apertura.';
                return $r;
            }
        }

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

            if ($this->ExisteInactivo($this->aniAnio, $this->aniTipo)) {
                $stmtDelFase = $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo");
                $stmtDelFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtDelFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtDelFase->execute();

                $stmtDelAnio = $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND ani_estado = 0");
                $stmtDelAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtDelAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtDelAnio->execute();
            }

            $stmtAnio = $co->prepare("INSERT INTO tbl_anio (ani_anio, ani_tipo, ani_activo, ani_estado) VALUES (:aniAnio, :aniTipo, 1, 1)");
            $stmtAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
            $stmtAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
            $stmtAnio->execute();

            $fechasFases = [];
            if ($this->aniTipo === 'intensivo') {
                $fase = $this->fases[0];
                $stmtFase = $co->prepare("INSERT INTO tbl_fase (ani_anio, ani_tipo, fase_numero, fase_apertura, fase_cierre) VALUES (:aniAnio, :aniTipo, :faseNumero, :faseApertura, :faseCierre)");
                $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                $stmtFase->execute();
                $fechasFases[$fase['numero']] = $fase['apertura'];
            } else {
                $stmtFase = $co->prepare("INSERT INTO tbl_fase (ani_anio, ani_tipo, fase_numero, fase_apertura, fase_cierre) VALUES (:aniAnio, :aniTipo, :faseNumero, :faseApertura, :faseCierre)");
                foreach ($this->fases as $fase) {
                    $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                    $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                    $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                    $stmtFase->execute();
                    $fechasFases[$fase['numero']] = $fase['apertura'];
                }
            }

            $this->Per($co, $this->aniAnio, $this->aniTipo, $fechasFases);

            $anioAnterior = $this->aniAnio - 1;
            $stmtFasesAnt = $co->prepare("SELECT fase_numero, fase_apertura FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtFasesAnt->execute([$anioAnterior, $this->aniTipo]);
            $fasesAnt = [];
            while ($row = $stmtFasesAnt->fetch(PDO::FETCH_ASSOC)) {
                $fasesAnt[$row['fase_numero']] = $row['fase_apertura'];
            }
            if (!empty($fasesAnt)) {
                $this->Per($co, $anioAnterior, $this->aniTipo, $fasesAnt);
            }

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

            $anioAnterior = $this->aniAnio - 1;
            $stmtFasesAnt = $co->prepare("SELECT fase_numero, fase_apertura FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtFasesAnt->execute([$anioAnterior, $this->aniTipo]);
            $fasesAnt = [];
            while ($row = $stmtFasesAnt->fetch(PDO::FETCH_ASSOC)) {
                $fasesAnt[$row['fase_numero']] = $row['fase_apertura'];
            }
            if (!empty($fasesAnt)) {
                $this->Per($co, $anioAnterior, $this->aniTipo, $fasesAnt);
            }

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
            $aperturaPerFase1 = $fechasFases[2];

            $stmtCheck = $co->prepare("SELECT COUNT(*) FROM tbl_per WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 1");
            $stmtCheck->execute([$anio, $tipo]);
            if ($stmtCheck->fetchColumn() > 0) {
                $stmtUpdate = $co->prepare("UPDATE tbl_per SET per_apertura = ? WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 1");
                $stmtUpdate->execute([$aperturaPerFase1, $anio, $tipo]);
            } else {
                $stmtPer1 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 1, ?)");
                $stmtPer1->execute([$anio, $tipo, $aperturaPerFase1]);
            }
        }

        $anioSiguiente = $anio + 1;
        $stmtFase1Sig = $co->prepare("SELECT fase_apertura FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ? AND fase_numero = 1");
        $stmtFase1Sig->execute([$anioSiguiente, $tipo]);
        $aperturaFase1Sig = $stmtFase1Sig->fetchColumn();

        if ($aperturaFase1Sig) {
            $stmtCheck2 = $co->prepare("SELECT COUNT(*) FROM tbl_per WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 2");
            $stmtCheck2->execute([$anio, $tipo]);
            if ($stmtCheck2->fetchColumn() > 0) {
                $stmtUpdate2 = $co->prepare("UPDATE tbl_per SET per_apertura = ? WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 2");
                $stmtUpdate2->execute([$aperturaFase1Sig, $anio, $tipo]);
            } else {
                $stmtPer2 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 2, ?)");
                $stmtPer2->execute([$anio, $tipo, $aperturaFase1Sig]);
            }
        }
    }

    public function Modificar($anioOriginal, $tipoOriginal)
    {
        $r = array();
        if ($anioOriginal === null || $tipoOriginal === null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Parámetros originales nulos.';
            return $r;
        }
        if (!$this->Existe($this->aniAnio, $this->aniTipo, $anioOriginal, $tipoOriginal)) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {
                $co->beginTransaction();

                if ($this->ExisteInactivo($this->aniAnio, $this->aniTipo)) {
                    $stmtDelFase = $co->prepare("DELETE FROM tbl_fase WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo");
                    $stmtDelFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                    $stmtDelFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                    $stmtDelFase->execute();

                    $stmtDelAnio = $co->prepare("DELETE FROM tbl_anio WHERE ani_anio = :aniAnio AND ani_tipo = :aniTipo AND ani_estado = 0");
                    $stmtDelAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                    $stmtDelAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                    $stmtDelAnio->execute();
                }

                $stmtAnio = $co->prepare("UPDATE tbl_anio SET ani_anio = :aniAnio, ani_tipo = :aniTipo WHERE ani_anio = :anioOriginal AND ani_tipo = :tipoOriginal");
                $stmtAnio->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                $stmtAnio->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                $stmtAnio->bindParam(':anioOriginal', $anioOriginal, PDO::PARAM_INT);
                $stmtAnio->bindParam(':tipoOriginal', $tipoOriginal, PDO::PARAM_STR);
                $stmtAnio->execute();

                $fechasFases = [];
                if ($this->aniTipo === 'intensivo') {
                    $fase = $this->fases[0];
                    $stmtFase = $co->prepare("UPDATE tbl_fase SET fase_apertura = :faseApertura, fase_cierre = :faseCierre, ani_anio = :aniAnio, ani_tipo = :aniTipo WHERE ani_anio = :anioOriginal AND ani_tipo = :tipoOriginal AND fase_numero = :faseNumero");
                    $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                    $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                    $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                    $stmtFase->bindParam(':anioOriginal', $anioOriginal, PDO::PARAM_INT);
                    $stmtFase->bindParam(':tipoOriginal', $tipoOriginal, PDO::PARAM_STR);
                    $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                    $stmtFase->execute();
                    $fechasFases[$fase['numero']] = $fase['apertura'];
                } else {
                    $stmtFase = $co->prepare("UPDATE tbl_fase SET fase_apertura = :faseApertura, fase_cierre = :faseCierre, ani_anio = :aniAnio, ani_tipo = :aniTipo WHERE ani_anio = :anioOriginal AND ani_tipo = :tipoOriginal AND fase_numero = :faseNumero");
                    foreach ($this->fases as $fase) {
                        $stmtFase->bindParam(':faseApertura', $fase['apertura'], PDO::PARAM_STR);
                        $stmtFase->bindParam(':faseCierre', $fase['cierre'], PDO::PARAM_STR);
                        $stmtFase->bindParam(':aniAnio', $this->aniAnio, PDO::PARAM_INT);
                        $stmtFase->bindParam(':aniTipo', $this->aniTipo, PDO::PARAM_STR);
                        $stmtFase->bindParam(':anioOriginal', $anioOriginal, PDO::PARAM_INT);
                        $stmtFase->bindParam(':tipoOriginal', $tipoOriginal, PDO::PARAM_STR);
                        $stmtFase->bindParam(':faseNumero', $fase['numero'], PDO::PARAM_INT);
                        $stmtFase->execute();
                        $fechasFases[$fase['numero']] = $fase['apertura'];
                    }
                }

                if ($this->aniTipo === 'regular') {
                    if (isset($fechasFases[2])) {
                        $aperturaPerFase1 = $fechasFases[2];
                        $stmtCheckPer1 = $co->prepare("SELECT COUNT(*) FROM tbl_per WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 1");
                        $stmtCheckPer1->execute([$this->aniAnio, $this->aniTipo]);
                        if ($stmtCheckPer1->fetchColumn() > 0) {
                            $stmtUpdatePer1 = $co->prepare("UPDATE tbl_per SET per_apertura = ? WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 1");
                            $stmtUpdatePer1->execute([$aperturaPerFase1, $this->aniAnio, $this->aniTipo]);
                        } else {
                            $stmtInsertPer1 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 1, ?)");
                            $stmtInsertPer1->execute([$this->aniAnio, $this->aniTipo, $aperturaPerFase1]);
                        }
                    }

                    $anioSiguiente = $this->aniAnio + 1;
                    $stmtFase1Sig = $co->prepare("SELECT fase_apertura FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ? AND fase_numero = 1");
                    $stmtFase1Sig->execute([$anioSiguiente, $this->aniTipo]);
                    $aperturaFase1Sig = $stmtFase1Sig->fetchColumn();

                    if ($aperturaFase1Sig) {
                        $stmtCheckPer2 = $co->prepare("SELECT COUNT(*) FROM tbl_per WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 2");
                        $stmtCheckPer2->execute([$this->aniAnio, $this->aniTipo]);
                        if ($stmtCheckPer2->fetchColumn() > 0) {
                            $stmtUpdatePer2 = $co->prepare("UPDATE tbl_per SET per_apertura = ? WHERE ani_anio = ? AND ani_tipo = ? AND per_fase = 2");
                            $stmtUpdatePer2->execute([$aperturaFase1Sig, $this->aniAnio, $this->aniTipo]);
                        } else {
                            $stmtInsertPer2 = $co->prepare("INSERT INTO tbl_per (ani_anio, ani_tipo, per_fase, per_apertura) VALUES (?, ?, 2, ?)");
                            $stmtInsertPer2->execute([$this->aniAnio, $this->aniTipo, $aperturaFase1Sig]);
                        }
                    }
                }

                $anioAnterior = $this->aniAnio - 1;
                $stmtFasesAnt = $co->prepare("SELECT fase_numero, fase_apertura FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ?");
                $stmtFasesAnt->execute([$anioAnterior, $this->aniTipo]);
                $fasesAnt = [];
                while ($row = $stmtFasesAnt->fetch(PDO::FETCH_ASSOC)) {
                    $fasesAnt[$row['fase_numero']] = $row['fase_apertura'];
                }
                if (!empty($fasesAnt) && $this->aniTipo === 'regular') {
                    $this->Per($co, $anioAnterior, $this->aniTipo, $fasesAnt);
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
        if ($this->aniAnio === null || $this->aniTipo === null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Año o tipo nulo.';
            return $r;
        }
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

            $sqlDesactivarRegular = "UPDATE tbl_anio a
                JOIN tbl_fase f1 ON a.ani_anio = f1.ani_anio AND a.ani_tipo = f1.ani_tipo AND f1.fase_numero = 1
                JOIN tbl_fase f2 ON a.ani_anio = f2.ani_anio AND a.ani_tipo = f2.ani_tipo AND f2.fase_numero = 2
                SET a.ani_activo = 0
                WHERE a.ani_activo = 1
                  AND a.ani_tipo = 'regular'
                  AND ( :fechaActual < f1.fase_apertura OR :fechaActual > f2.fase_cierre )";
            $stmt = $co->prepare($sqlDesactivarRegular);
            $stmt->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt->execute();

            $sqlActivarRegular = "UPDATE tbl_anio a
                JOIN tbl_fase f1 ON a.ani_anio = f1.ani_anio AND a.ani_tipo = f1.ani_tipo AND f1.fase_numero = 1
                JOIN tbl_fase f2 ON a.ani_anio = f2.ani_anio AND a.ani_tipo = f2.ani_tipo AND f2.fase_numero = 2
                SET a.ani_activo = 1
                WHERE a.ani_activo = 0
                  AND a.ani_tipo = 'regular'
                  AND :fechaActual >= f1.fase_apertura
                  AND :fechaActual <= f2.fase_cierre";
            $stmt2 = $co->prepare($sqlActivarRegular);
            $stmt2->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt2->execute();

            $sqlDesactivarIntensivo = "UPDATE tbl_anio a
                JOIN tbl_fase f1 ON a.ani_anio = f1.ani_anio AND a.ani_tipo = f1.ani_tipo AND f1.fase_numero = 1
                SET a.ani_activo = 0
                WHERE a.ani_activo = 1
                  AND a.ani_tipo = 'intensivo'
                  AND ( :fechaActual < f1.fase_apertura OR :fechaActual > f1.fase_cierre )";
            $stmt3 = $co->prepare($sqlDesactivarIntensivo);
            $stmt3->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt3->execute();

            $sqlActivarIntensivo = "UPDATE tbl_anio a
                JOIN tbl_fase f1 ON a.ani_anio = f1.ani_anio AND a.ani_tipo = f1.ani_tipo AND f1.fase_numero = 1
                SET a.ani_activo = 1
                WHERE a.ani_activo = 0
                  AND a.ani_tipo = 'intensivo'
                  AND :fechaActual >= f1.fase_apertura
                  AND :fechaActual <= f1.fase_cierre";
            $stmt4 = $co->prepare($sqlActivarIntensivo);
            $stmt4->bindParam(':fechaActual', $fechaActual, PDO::PARAM_STR);
            $stmt4->execute();
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

            if ($fase['ani_tipo'] === 'intensivo' && $fase['fase_numero'] != 1) {
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
                if ($fase['ani_tipo'] === 'intensivo') {
                    if ($dias_restantes == 0) {
                        $mensaje = "Hoy es el cierre del año {$fase['ani_anio']} (Intensivo).";
                    } else {
                        $mensaje = "El año {$fase['ani_anio']} (Intensivo) está a punto de cerrar: faltan {$dias_restantes} días.";
                    }
                } else {
                    if ($dias_restantes == 0) {
                        $mensaje = "Hoy es el cierre de la fase {$fase['fase_numero']} del año {$fase['ani_anio']} ({$tipoAnioTitle}).";
                    } else {
                        $mensaje = "La fase {$fase['fase_numero']} del año {$fase['ani_anio']} ({$tipoAnioTitle}) está a punto de cerrarse: faltan {$dias_restantes} días.";
                    }
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
            $stmtMalla = $co->query("SELECT COUNT(*) FROM tbl_malla WHERE mal_activa = 1");
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
