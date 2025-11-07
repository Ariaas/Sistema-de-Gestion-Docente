<?php

namespace App\Model;

use PDO;
use PDOException;
use Exception;
use DateTime;

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

        if ($this->aniAnio === null || $this->aniAnio === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año no puede estar vacío.';
            return $r;
        }

        if (!is_numeric($this->aniAnio) || $this->aniAnio <= 0) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe ser un número entero positivo.';
            return $r;
        }

        if ((float)$this->aniAnio != (int)$this->aniAnio) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe ser un número entero, no decimal.';
            return $r;
        }

        $this->aniAnio = (int)$this->aniAnio;

        if ($this->aniAnio < 2000 || $this->aniAnio > 2100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe estar entre 2000 y 2100.';
            return $r;
        }

        if ($this->aniTipo === null || trim($this->aniTipo) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año no puede estar vacío.';
            return $r;
        }

        $this->aniTipo = trim($this->aniTipo);

        if (!in_array($this->aniTipo, ['regular', 'intensivo'], true)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año debe ser "regular" o "intensivo".';
            return $r;
        }

        if (!is_array($this->fases)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fases deben ser un arreglo.';
            return $r;
        }

        if (!isset($this->fases[0])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Debe proporcionar las fechas de apertura y cierre.';
            return $r;
        }

        $fase1 = $this->fases[0];

        if (empty($fase1['apertura']) || empty($fase1['cierre'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fechas de apertura y cierre son requeridas.';
            return $r;
        }

        if (!$this->validarFecha($fase1['apertura']) || !$this->validarFecha($fase1['cierre'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fechas deben tener formato válido (YYYY-MM-DD).';
            return $r;
        }

        if (strtotime($fase1['cierre']) <= strtotime($fase1['apertura'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'La fecha de cierre debe ser posterior a la fecha de apertura.';
            return $r;
        }

        if ($this->aniTipo === 'regular') {
            if (!isset($this->fases[1])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Un año regular debe tener datos para la fase 2.';
                return $r;
            }

            $fase2 = $this->fases[1];

            if (empty($fase2['apertura']) || empty($fase2['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Las fechas de apertura y cierre de la fase 2 son requeridas.';
                return $r;
            }

            if (!$this->validarFecha($fase2['apertura']) || !$this->validarFecha($fase2['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Las fechas de la fase 2 deben tener formato válido (YYYY-MM-DD).';
                return $r;
            }

            if (strtotime($fase2['cierre']) <= strtotime($fase2['apertura'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La fecha de cierre de la fase 2 debe ser posterior a su apertura.';
                return $r;
            }

            if (strtotime($fase2['apertura']) <= strtotime($fase1['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La apertura de la fase 2 debe ser posterior al cierre de la fase 1.';
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
            $infoDuplicacion = $this->prepararDuplicacion($anioAnterior, $this->aniAnio, $this->aniTipo, $co);
            if ($infoDuplicacion !== null) {
                $r['duplicacion'] = $infoDuplicacion;
            }
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

    private function validarFecha($fecha)
    {
        if (empty($fecha)) {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    public function Modificar($anioOriginal, $tipoOriginal)
    {
        $r = array();

        if ($anioOriginal === null || $tipoOriginal === null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Los parámetros originales son requeridos para modificar.';
            return $r;
        }

        if ($this->aniAnio === null || $this->aniAnio === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año no puede estar vacío.';
            return $r;
        }

        if (!is_numeric($this->aniAnio) || $this->aniAnio <= 0) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe ser un número entero positivo.';
            return $r;
        }

        if ((float)$this->aniAnio != (int)$this->aniAnio) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe ser un número entero, no decimal.';
            return $r;
        }

        $this->aniAnio = (int)$this->aniAnio;

        if ($this->aniAnio < 2000 || $this->aniAnio > 2100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe estar entre 2000 y 2100.';
            return $r;
        }

        if ($this->aniTipo === null || trim($this->aniTipo) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año no puede estar vacío.';
            return $r;
        }

        $this->aniTipo = trim($this->aniTipo);

        if (!in_array($this->aniTipo, ['regular', 'intensivo'], true)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año debe ser "regular" o "intensivo".';
            return $r;
        }

        if (!is_array($this->fases)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fases deben ser un arreglo.';
            return $r;
        }

        if (!isset($this->fases[0])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Debe proporcionar las fechas de apertura y cierre.';
            return $r;
        }

        $fase1 = $this->fases[0];

        if (empty($fase1['apertura']) || empty($fase1['cierre'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fechas de apertura y cierre son requeridas.';
            return $r;
        }

        if (!$this->validarFecha($fase1['apertura']) || !$this->validarFecha($fase1['cierre'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'Las fechas deben tener formato válido (YYYY-MM-DD).';
            return $r;
        }

        if (strtotime($fase1['cierre']) <= strtotime($fase1['apertura'])) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'La fecha de cierre debe ser posterior a la fecha de apertura.';
            return $r;
        }

        if ($this->aniTipo === 'regular') {
            if (!isset($this->fases[1])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Un año regular debe tener datos para la fase 2.';
                return $r;
            }

            $fase2 = $this->fases[1];

            if (empty($fase2['apertura']) || empty($fase2['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Las fechas de apertura y cierre de la fase 2 son requeridas.';
                return $r;
            }

            if (!$this->validarFecha($fase2['apertura']) || !$this->validarFecha($fase2['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'Las fechas de la fase 2 deben tener formato válido (YYYY-MM-DD).';
                return $r;
            }

            if (strtotime($fase2['cierre']) <= strtotime($fase2['apertura'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La fecha de cierre de la fase 2 debe ser posterior a su apertura.';
                return $r;
            }

            if (strtotime($fase2['apertura']) <= strtotime($fase1['cierre'])) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La apertura de la fase 2 debe ser posterior al cierre de la fase 1.';
                return $r;
            }
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

        if ($this->aniAnio === null || $this->aniAnio === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año no puede estar vacío.';
            return $r;
        }

        if (!is_numeric($this->aniAnio) || $this->aniAnio <= 0) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El año debe ser un número entero positivo.';
            return $r;
        }

        if ($this->aniTipo === null || trim($this->aniTipo) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El tipo de año no puede estar vacío.';
            return $r;
        }

        $this->aniTipo = trim($this->aniTipo);
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

    private function DesactivarAnios()
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

    private function Notificaciones()
    {
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

    private function prepararDuplicacion($anioOrigen, $anioDestino, $aniTipo, PDO $co)
    {
        if ($anioOrigen <= 0) {
            return null;
        }
        $stmtSecciones = $co->prepare("SELECT COUNT(*) FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?");
        $stmtSecciones->execute([$anioOrigen, $aniTipo]);
        $totalSecciones = (int)$stmtSecciones->fetchColumn();
        if ($totalSecciones === 0) {
            return null;
        }
        $stmtHorarios = $co->prepare("SELECT COUNT(*) FROM uc_horario WHERE ani_anio = ? AND ani_tipo = ?");
        $stmtHorarios->execute([$anioOrigen, $aniTipo]);
        $totalHorarios = (int)$stmtHorarios->fetchColumn();
        return [
            'anioOrigen' => (int)$anioOrigen,
            'anioDestino' => (int)$anioDestino,
            'aniTipo' => $aniTipo,
            'secciones' => $totalSecciones,
            'horarios' => $totalHorarios
        ];
    }

    public function duplicarSecciones($anioOrigen, $aniTipoOrigen, $anioDestino, $aniTipoDestino = null)
    {
        $aniTipoDestino = $aniTipoDestino ?? $aniTipoOrigen;
        $respuesta = ['resultado' => 'duplicar_secciones_ok', 'mensaje' => ''];
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $stmtOrigen = $co->prepare("SELECT sec_codigo, sec_cantidad, sec_estado, grupo_union_id FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtOrigen->execute([(int)$anioOrigen, $aniTipoOrigen]);
            $secciones = $stmtOrigen->fetchAll(PDO::FETCH_ASSOC);
            if (empty($secciones)) {
                $co->rollBack();
                $respuesta['mensaje'] = 'No existen secciones en el año de origen.';
                $co = null;
                return $respuesta;
            }
            $stmtDestino = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtDestino->execute([(int)$anioDestino, $aniTipoDestino]);
            $existentes = $stmtDestino->fetchAll(PDO::FETCH_COLUMN, 0);
            $existentes = array_flip($existentes);
            $stmtInsert = $co->prepare("INSERT INTO tbl_seccion (sec_codigo, ani_anio, ani_tipo, sec_cantidad, sec_estado, grupo_union_id) VALUES (?, ?, ?, ?, ?, ?)");
            $insertados = 0;
            $omitidos = 0;
            foreach ($secciones as $fila) {
                $codigo = $fila['sec_codigo'];
                if (isset($existentes[$codigo])) {
                    $omitidos++;
                    continue;
                }
                $stmtInsert->execute([$codigo, (int)$anioDestino, $aniTipoDestino, (int)$fila['sec_cantidad'], (int)$fila['sec_estado'], $fila['grupo_union_id']]);
                $insertados++;
            }
            $co->commit();
            if ($insertados === 0) {
                if ($omitidos > 0) {
                    $respuesta['mensaje'] = 'Las secciones ya existen en el año destino.';
                } else {
                    $respuesta['mensaje'] = 'No se pudieron duplicar secciones.';
                }
            } else {
                $respuesta['mensaje'] = 'Se duplicaron ' . $insertados . ' secciones.';
            }
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $respuesta['resultado'] = 'error';
            $respuesta['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $respuesta;
    }

    public function duplicarHorarios($anioOrigen, $aniTipoOrigen, $anioDestino, $aniTipoDestino = null, $faseObjetivo = null)
    {
        $aniTipoDestino = $aniTipoDestino ?? $aniTipoOrigen;
        $respuesta = ['resultado' => 'duplicar_horarios_ok', 'mensaje' => ''];
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $co->beginTransaction();
            $stmtOrigen = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtOrigen->execute([(int)$anioOrigen, $aniTipoOrigen]);
            $seccionesOrigen = $stmtOrigen->fetchAll(PDO::FETCH_COLUMN, 0);
            if (empty($seccionesOrigen)) {
                $co->rollBack();
                $respuesta['mensaje'] = 'No se encontraron secciones en el año de origen.';
                $co = null;
                return $respuesta;
            }
            $stmtDestino = $co->prepare("SELECT sec_codigo FROM tbl_seccion WHERE ani_anio = ? AND ani_tipo = ?");
            $stmtDestino->execute([(int)$anioDestino, $aniTipoDestino]);
            $seccionesDestino = $stmtDestino->fetchAll(PDO::FETCH_COLUMN, 0);
            if (empty($seccionesDestino)) {
                $co->rollBack();
                $respuesta['mensaje'] = 'Debe duplicar secciones antes de copiar los horarios.';
                $co = null;
                return $respuesta;
            }
            $seccionesDisponibles = array_values(array_intersect($seccionesOrigen, $seccionesDestino));
            if (empty($seccionesDisponibles)) {
                $co->rollBack();
                $respuesta['mensaje'] = 'No hay secciones coincidentes entre los años seleccionados.';
                $co = null;
                return $respuesta;
            }
            $faseObjetivo = $this->faseObjetivoDuplicacion((int)$anioDestino, $aniTipoDestino, $faseObjetivo, $co);
            $mapa = $this->mapaConversionFase();
            $infoUc = $this->cargarInfoUc($co);
            $docUcMap = $this->cargarDocUcMap($co);
            $placeholders = implode(',', array_fill(0, count($seccionesDisponibles), '?'));
            $paramsConsulta = array_merge([(int)$anioOrigen, $aniTipoOrigen], $seccionesDisponibles);
            $stmtUc = $co->prepare("SELECT uc_codigo, doc_cedula, subgrupo, sec_codigo, esp_numero, hor_dia, hor_horainicio, hor_horafin, esp_tipo, esp_edificio FROM uc_horario WHERE ani_anio = ? AND ani_tipo = ? AND sec_codigo IN ($placeholders)");
            $stmtUc->execute($paramsConsulta);
            $ucPorSeccion = [];
            while ($fila = $stmtUc->fetch(PDO::FETCH_ASSOC)) {
                $codigo = $fila['sec_codigo'];
                if (!isset($ucPorSeccion[$codigo])) {
                    $ucPorSeccion[$codigo] = [];
                }
                $ucPorSeccion[$codigo][] = $fila;
            }
            $stmtHorario = $co->prepare("SELECT sec_codigo, tur_nombre, hor_estado FROM tbl_horario WHERE ani_anio = ? AND ani_tipo = ? AND sec_codigo IN ($placeholders)");
            $stmtHorario->execute($paramsConsulta);
            $horarioPorSeccion = [];
            while ($fila = $stmtHorario->fetch(PDO::FETCH_ASSOC)) {
                $horarioPorSeccion[$fila['sec_codigo']] = $fila;
            }
            $stmtBloques = $co->prepare("SELECT sec_codigo, tur_horainicio, tur_horafin, bloque_sintetico FROM tbl_bloque_personalizado WHERE ani_anio = ? AND ani_tipo = ? AND sec_codigo IN ($placeholders)");
            $stmtBloques->execute($paramsConsulta);
            $bloquesPorSeccion = [];
            while ($fila = $stmtBloques->fetch(PDO::FETCH_ASSOC)) {
                $codigo = $fila['sec_codigo'];
                if (!isset($bloquesPorSeccion[$codigo])) {
                    $bloquesPorSeccion[$codigo] = [];
                }
                $bloquesPorSeccion[$codigo][] = $fila;
            }
            $stmtBloquesElim = $co->prepare("SELECT sec_codigo, tur_horainicio, tur_horafin FROM tbl_bloque_eliminado WHERE ani_anio = ? AND ani_tipo = ? AND sec_codigo IN ($placeholders)");
            $stmtBloquesElim->execute($paramsConsulta);
            $bloquesEliminados = [];
            while ($fila = $stmtBloquesElim->fetch(PDO::FETCH_ASSOC)) {
                $codigo = $fila['sec_codigo'];
                if (!isset($bloquesEliminados[$codigo])) {
                    $bloquesEliminados[$codigo] = [];
                }
                $bloquesEliminados[$codigo][] = $fila;
            }
            $stmtDelUc = $co->prepare("DELETE FROM uc_horario WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtDelDoc = $co->prepare("DELETE FROM docente_horario WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtDelBloque = $co->prepare("DELETE FROM tbl_bloque_personalizado WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtDelBloqueElim = $co->prepare("DELETE FROM tbl_bloque_eliminado WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtDelHorario = $co->prepare("DELETE FROM tbl_horario WHERE sec_codigo = ? AND ani_anio = ? AND ani_tipo = ?");
            $stmtInsertUc = $co->prepare("INSERT INTO uc_horario (uc_codigo, doc_cedula, subgrupo, sec_codigo, ani_anio, ani_tipo, esp_numero, hor_dia, hor_horainicio, hor_horafin, esp_tipo, esp_edificio) VALUES (:uc_codigo, :doc_cedula, :subgrupo, :sec_codigo, :ani_anio, :ani_tipo, :esp_numero, :hor_dia, :hor_horainicio, :hor_horafin, :esp_tipo, :esp_edificio)");
            $stmtInsertDoc = $co->prepare("INSERT INTO docente_horario (doc_cedula, sec_codigo, ani_anio, ani_tipo) VALUES (?, ?, ?, ?)");
            $stmtInsertBloque = $co->prepare("INSERT INTO tbl_bloque_personalizado (sec_codigo, ani_anio, ani_tipo, tur_horainicio, tur_horafin, bloque_sintetico) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertBloqueElim = $co->prepare("INSERT INTO tbl_bloque_eliminado (sec_codigo, ani_anio, ani_tipo, tur_horainicio, tur_horafin) VALUES (?, ?, ?, ?, ?)");
            $stmtInsertHorario = $co->prepare("INSERT INTO tbl_horario (sec_codigo, ani_anio, ani_tipo, tur_nombre, hor_estado) VALUES (?, ?, ?, ?, ?)");
            $clasesInsertadas = 0;
            foreach ($seccionesDisponibles as $seccion) {
                $stmtDelUc->execute([$seccion, (int)$anioDestino, $aniTipoDestino]);
                $stmtDelDoc->execute([$seccion, (int)$anioDestino, $aniTipoDestino]);
                $stmtDelBloque->execute([$seccion, (int)$anioDestino, $aniTipoDestino]);
                $stmtDelBloqueElim->execute([$seccion, (int)$anioDestino, $aniTipoDestino]);
                $stmtDelHorario->execute([$seccion, (int)$anioDestino, $aniTipoDestino]);
                if (isset($horarioPorSeccion[$seccion])) {
                    $filaHorario = $horarioPorSeccion[$seccion];
                    $stmtInsertHorario->execute([$seccion, (int)$anioDestino, $aniTipoDestino, $filaHorario['tur_nombre'], $filaHorario['hor_estado']]);
                }
                $docentesSeccion = [];
                if (isset($ucPorSeccion[$seccion])) {
                    foreach ($ucPorSeccion[$seccion] as $bloque) {
                        $ucActual = $bloque['uc_codigo'];
                        $ucConvertido = $ucActual;
                        if ($ucActual !== null && $ucActual !== '') {
                            $ucConvertido = $this->convertirUcPorFase($ucActual, $faseObjetivo, $mapa, $infoUc);
                            if (!isset($infoUc[$ucConvertido]) || (int)$infoUc[$ucConvertido]['estado'] !== 1) {
                                continue;
                            }
                        }
                        $docente = $bloque['doc_cedula'];
                        if ($ucConvertido !== null && $ucConvertido !== '') {
                            $docente = $this->validarDocenteParaUc($docente, $ucConvertido, $docUcMap);
                        } else {
                            $docente = null;
                        }
                        $stmtInsertUc->execute([
                            ':uc_codigo' => $ucConvertido,
                            ':doc_cedula' => $docente,
                            ':subgrupo' => $bloque['subgrupo'],
                            ':sec_codigo' => $seccion,
                            ':ani_anio' => (int)$anioDestino,
                            ':ani_tipo' => $aniTipoDestino,
                            ':esp_numero' => $bloque['esp_numero'],
                            ':hor_dia' => $bloque['hor_dia'],
                            ':hor_horainicio' => $bloque['hor_horainicio'],
                            ':hor_horafin' => $bloque['hor_horafin'],
                            ':esp_tipo' => $bloque['esp_tipo'],
                            ':esp_edificio' => $bloque['esp_edificio']
                        ]);
                        if ($docente !== null) {
                            $docentesSeccion[$docente] = true;
                        }
                        $clasesInsertadas++;
                    }
                }
                if (!empty($docentesSeccion)) {
                    foreach (array_keys($docentesSeccion) as $docente) {
                        $stmtInsertDoc->execute([$docente, $seccion, (int)$anioDestino, $aniTipoDestino]);
                    }
                }
                if (isset($bloquesPorSeccion[$seccion])) {
                    foreach ($bloquesPorSeccion[$seccion] as $bloque) {
                        $stmtInsertBloque->execute([$seccion, (int)$anioDestino, $aniTipoDestino, $bloque['tur_horainicio'], $bloque['tur_horafin'], (int)$bloque['bloque_sintetico']]);
                    }
                }
                if (isset($bloquesEliminados[$seccion])) {
                    foreach ($bloquesEliminados[$seccion] as $bloque) {
                        $stmtInsertBloqueElim->execute([$seccion, (int)$anioDestino, $aniTipoDestino, $bloque['tur_horainicio'], $bloque['tur_horafin']]);
                    }
                }
            }
            $co->commit();
            if ($clasesInsertadas === 0) {
                $respuesta['mensaje'] = 'No se copiaron bloques de horario.';
            } else {
                $respuesta['mensaje'] = 'Se duplicaron ' . $clasesInsertadas . ' bloques de horario.';
            }
        } catch (Exception $e) {
            if ($co->inTransaction()) {
                $co->rollBack();
            }
            $respuesta['resultado'] = 'error';
            $respuesta['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $respuesta;
    }

    private function faseObjetivoDuplicacion($anioDestino, $aniTipoDestino, $faseSolicitada, PDO $co)
    {
        if ($aniTipoDestino !== 'regular') {
            return 1;
        }
        if ($faseSolicitada !== null) {
            $faseSolicitada = (int)$faseSolicitada;
            if ($faseSolicitada < 1) {
                $faseSolicitada = 1;
            }
            if ($faseSolicitada > 2) {
                $faseSolicitada = 2;
            }
            return $faseSolicitada;
        }
        $stmt = $co->prepare("SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase WHERE ani_anio = ? AND ani_tipo = ? ORDER BY fase_numero ASC");
        $stmt->execute([(int)$anioDestino, $aniTipoDestino]);
        $fases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($fases)) {
            return 1;
        }
        $hoy = new DateTime('now');
        foreach ($fases as $fila) {
            if (empty($fila['fase_apertura'])) {
                continue;
            }
            $apertura = new DateTime($fila['fase_apertura']);
            $cierre = !empty($fila['fase_cierre']) ? new DateTime($fila['fase_cierre']) : null;
            if ($cierre !== null && $hoy >= $apertura && $hoy <= $cierre) {
                return (int)$fila['fase_numero'];
            }
            if ($hoy < $apertura) {
                return (int)$fila['fase_numero'];
            }
        }
        $ultima = end($fases);
        return (int)$ultima['fase_numero'];
    }

    private function mapaConversionFase()
    {
        $mapa = [
            '2to1' => [
                'PIELE072103' => 'PIIDI090103',
                'PIELE072203' => 'PIBAD090203',
                'PIELE072403' => 'PIABD090403',
                'PIELE078303' => 'PIMOB078303',
                'PIAUI120404' => 'PISEI120404',
                'PIINO078303' => 'PISIO078303',
                'PIREA084403' => 'PIGPI120404'
            ]
        ];
        $mapa['1to2'] = [];
        foreach ($mapa['2to1'] as $origen => $destino) {
            if (!isset($mapa['1to2'][$destino])) {
                $mapa['1to2'][$destino] = $origen;
            }
        }
        return $mapa;
    }

    private function convertirUcPorFase($ucCodigo, $faseObjetivo, array $mapa, array $ucInfo)
    {
        if (!isset($ucInfo[$ucCodigo])) {
            return $ucCodigo;
        }
        $periodo = strtoupper((string)$ucInfo[$ucCodigo]['periodo']);
        if ($faseObjetivo === 1) {
            if ($periodo === 'FASE II' && isset($mapa['2to1'][$ucCodigo])) {
                $destino = $mapa['2to1'][$ucCodigo];
                if (isset($ucInfo[$destino]) && (int)$ucInfo[$destino]['estado'] === 1) {
                    return $destino;
                }
            }
        } elseif ($faseObjetivo === 2) {
            if (($periodo === 'FASE I' || $periodo === '0') && isset($mapa['1to2'][$ucCodigo])) {
                $destino = $mapa['1to2'][$ucCodigo];
                if (isset($ucInfo[$destino]) && (int)$ucInfo[$destino]['estado'] === 1) {
                    return $destino;
                }
            }
        }
        return $ucCodigo;
    }

    private function validarDocenteParaUc($docCedula, $ucCodigo, array $docUcMap)
    {
        if ($docCedula === null) {
            return null;
        }
        $clave = (string)$docCedula;
        if (!isset($docUcMap[$clave])) {
            return null;
        }
        if (!isset($docUcMap[$clave][$ucCodigo])) {
            return null;
        }
        return $docCedula;
    }

    private function cargarInfoUc(PDO $co)
    {
        $stmt = $co->query("SELECT uc_codigo, uc_periodo, uc_estado, uc_trayecto FROM tbl_uc");
        $datos = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datos[$fila['uc_codigo']] = [
                'periodo' => $fila['uc_periodo'],
                'estado' => (int)$fila['uc_estado'],
                'trayecto' => $fila['uc_trayecto']
            ];
        }
        return $datos;
    }

    private function cargarDocUcMap(PDO $co)
    {
        $stmt = $co->query("SELECT uc_codigo, doc_cedula FROM uc_docente");
        $mapa = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $doc = (string)$fila['doc_cedula'];
            if (!isset($mapa[$doc])) {
                $mapa[$doc] = [];
            }
            $mapa[$doc][$fila['uc_codigo']] = true;
        }
        return $mapa;
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
