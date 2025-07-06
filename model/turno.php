<?php
require_once('model/dbconnection.php');

class Turno extends Connection
{
    private $nombreTurno;
    private $horaInicio;
    private $horaFin;

    public function __construct($nombreTurno = null, $horaInicio = null, $horaFin = null)
    {
        parent::__construct();
        $this->nombreTurno = $nombreTurno;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
    }

    public function getNombreTurno() { return $this->nombreTurno; }
    public function setNombreTurno($nombreTurno) { $this->nombreTurno = $nombreTurno; }
    public function getHoraInicio() { return $this->horaInicio; }
    public function setHoraInicio($horaInicio) { $this->horaInicio = $horaInicio; }
    public function getHoraFin() { return $this->horaFin; }
    public function setHoraFin($horaFin) { $this->horaFin = $horaFin; }

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
            $stmt_check->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
            $stmt_check->execute();
            
            $turnoExistente = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($turnoExistente) {
                if ($turnoExistente['tur_estado'] == 1) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = 'ERROR: El turno ya existe y se encuentra activo.';
                } else {
                    $stmt_update = $co->prepare(
                        "UPDATE tbl_turno SET tur_horaInicio = :horaInicio, tur_horaFin = :horaFin, tur_estado = 1 WHERE tur_nombre = :nombreTurno"
                    );
                    $stmt_update->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
                    $stmt_update->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);
                    $stmt_update->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
                    $stmt_update->execute();
                    $r['resultado'] = 'registrar';
                    $r['mensaje'] = '¡Turno Registrado Correctamente!';
                }
            } else {
                $stmt_insert = $co->prepare(
                    "INSERT INTO tbl_turno(tur_nombre, tur_horaInicio, tur_horaFin, tur_estado) VALUES (:nombreTurno, :horaInicio, :horaFin, 1)"
                );
                $stmt_insert->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
                $stmt_insert->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
                $stmt_insert->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);
                $stmt_insert->execute();
                $r['resultado'] = 'registrar';
                $r['mensaje'] = '¡Turno Registrado Correctamente!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error en la operación: " . $e->getMessage();
        }
        
        return $r;
    }

    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT 
                                tur_nombre, 
                                DATE_FORMAT(tur_horaInicio, '%h:%i %p') AS hora_inicio_12h, 
                                DATE_FORMAT(tur_horaInicio, '%H:%i') AS hora_inicio_24h, 
                                DATE_FORMAT(tur_horaFin, '%h:%i %p') AS hora_fin_12h,
                                DATE_FORMAT(tur_horaFin, '%H:%i') AS hora_fin_24h
                            FROM tbl_turno 
                            WHERE tur_estado = 1 
                            ORDER BY tur_horaInicio ASC");
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Modificar()
    {
        $r = array();
        
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

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("UPDATE tbl_turno SET tur_horaInicio = :horaInicio, tur_horaFin = :horaFin WHERE tur_nombre = :nombreTurno");
            $stmt->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);
            $stmt->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = '¡Turno Modificado Correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al modificar: " . $e->getMessage();
        }
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_turno SET tur_estado = 0 WHERE tur_nombre = :nombreTurno");
            $stmt->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = '¡Turno Eliminado Correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = "Error al eliminar: " . $e->getMessage();
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

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);

            if (!empty($this->nombreTurno)) {
                $stmt->bindParam(':nombreTurno', $this->nombreTurno, PDO::PARAM_STR);
            }

            $stmt->execute();
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