<?php
require_once('model/dbconnection.php');

class Turno extends Connection
{
    private $nombreTurno;
    private $horaInicio;
    private $horaFin;
    private $nombreTurnoOriginal;

    public function __construct()
    {
        parent::__construct();
    }

    public function getNombreTurno() { return $this->nombreTurno; }
    public function setNombreTurno($nombreTurno) { $this->nombreTurno = trim($nombreTurno); }
    public function getHoraInicio() { return $this->horaInicio; }
    public function setHoraInicio($horaInicio) { $this->horaInicio = $horaInicio; }
    public function getHoraFin() { return $this->horaFin; }
    public function setHoraFin($horaFin) { $this->horaFin = $horaFin; }
    public function setNombreTurnoOriginal($nombre) { $this->nombreTurnoOriginal = trim($nombre); }

    public function Registrar()
    {
        $r = array();
        
        $solapamiento = $this->chequearSolapamiento();
        if(isset($solapamiento['solapamiento']) && $solapamiento['solapamiento'] === true){
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: El rango de horas se solapa con el turno activo: ' . $solapamiento['turno_choca'];
            return $r;
        }

        if ($this->horaFin <= $this->horaInicio) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: La hora de fin debe ser mayor que la hora de inicio.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt_check = $co->prepare("SELECT tur_estado FROM tbl_turno WHERE tur_nombre = :nombreTurno");
            $stmt_check->execute([':nombreTurno' => $this->nombreTurno]);
            
            $turnoExistente = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($turnoExistente) {
                if ($turnoExistente['tur_estado'] == 1) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = 'ERROR: El turno ya existe y se encuentra activo.';
                } else {
                    $co->prepare("UPDATE tbl_turno SET tur_horaInicio = :horaInicio, tur_horaFin = :horaFin, tur_estado = 1 WHERE tur_nombre = :nombreTurno")
                       ->execute([
                           ':horaInicio' => $this->horaInicio,
                           ':horaFin' => $this->horaFin,
                           ':nombreTurno' => $this->nombreTurno
                       ]);
                    $r['resultado'] = 'registrar';
                    $r['mensaje'] = '¡Turno Registrado Correctamente!';
                }
            } else {
                $co->prepare("INSERT INTO tbl_turno(tur_nombre, tur_horaInicio, tur_horaFin, tur_estado) VALUES (:nombreTurno, :horaInicio, :horaFin, 1)")
                   ->execute([
                       ':nombreTurno' => $this->nombreTurno,
                       ':horaInicio' => $this->horaInicio,
                       ':horaFin' => $this->horaFin
                   ]);
                $r['resultado'] = 'registrar';
                $r['mensaje'] = '¡Turno Registrado Correctamente!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error en la operación: " . $e->getMessage();
        } finally {
            $co = null;
        }
        
        return $r;
    }

    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = [];
        try {
            $stmt = $co->query("SELECT 
                                tur_nombre, 
                                DATE_FORMAT(tur_horaInicio, '%h:%i %p') AS hora_inicio_12h, 
                                DATE_FORMAT(tur_horaInicio, '%H:%i') AS hora_inicio_24h, 
                                DATE_FORMAT(tur_horaFin, '%h:%i %p') AS hora_fin_12h,
                                DATE_FORMAT(tur_horaFin, '%H:%i') AS hora_fin_24h,
                                tur_estado
                            FROM tbl_turno 
                            ORDER BY tur_horaInicio ASC");
            
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Modificar()
    {
        $r = [];
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        try {
            $stmt = $co->prepare("SELECT tur_nombre, tur_horaInicio, tur_horaFin FROM tbl_turno WHERE tur_nombre = :nombreOriginal");
            $stmt->execute([':nombreOriginal' => $this->nombreTurnoOriginal]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datosOriginales) {
                return ['resultado' => 'error', 'mensaje' => 'El turno no existe.'];
            }
            
            if ($datosOriginales['tur_nombre'] === $this->nombreTurno && 
                $datosOriginales['tur_horaInicio'] === $this->horaInicio &&
                $datosOriginales['tur_horaFin'] === $this->horaFin) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
        
        $solapamiento = $this->chequearSolapamiento();
        if(isset($solapamiento['solapamiento']) && $solapamiento['solapamiento'] === true){
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: El rango de horas se solapa con el turno: ' . $solapamiento['turno_choca'];
            return $r;
        }

        if ($this->horaFin <= $this->horaInicio) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: La hora de fin debe ser mayor que la hora de inicio.';
            return $r;
        }

        try {
            $co->prepare("UPDATE tbl_turno SET tur_nombre = :nombreTurno, tur_horaInicio = :horaInicio, tur_horaFin = :horaFin WHERE tur_nombre = :nombreOriginal")
               ->execute([
                   ':nombreTurno' => $this->nombreTurno,
                   ':horaInicio' => $this->horaInicio,
                   ':horaFin' => $this->horaFin,
                   ':nombreOriginal' => $this->nombreTurnoOriginal
               ]);
            $r['resultado'] = 'modificar';
            $r['mensaje'] = '¡Turno Modificado Correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al modificar: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = [];
        
        try {
            $stmt = $co->prepare("SELECT tur_estado FROM tbl_turno WHERE tur_nombre = :nombreTurno");
            $stmt->execute([':nombreTurno' => $this->nombreTurno]);
            $turno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$turno) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'El turno que intenta eliminar no existe.';
                return $r;
            }

            if ($turno['tur_estado'] == 0) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'El turno ya está desactivado.';
                return $r;
            }
            
            $co->prepare("UPDATE tbl_turno SET tur_estado = 0 WHERE tur_nombre = :nombreTurno")
               ->execute([':nombreTurno' => $this->nombreTurno]);
            
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = '¡Turno Eliminado Correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar: " . $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function chequearSolapamiento()
    {
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT tur_nombre FROM tbl_turno 
                    WHERE tur_estado = 1 
                    AND :horaInicio < tur_horaFin 
                    AND :horaFin > tur_horaInicio";

            if (!empty($this->nombreTurno)) {
                $sql .= " AND tur_nombre != :nombreTurno";
            }

            $params = [
                ':horaInicio' => $this->horaInicio,
                ':horaFin' => $this->horaFin
            ];

            if (!empty($this->nombreTurno)) {
                $params[':nombreTurno'] = $this->nombreTurno;
            }

            $stmt = $co->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return ['solapamiento' => true, 'turno_choca' => $resultado['tur_nombre']];
            } else {
                return ['solapamiento' => false];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>