<?php
require_once('model/dbconnection.php');

class Turno extends Connection
{
    private $idTurno;
    private $horaInicio;
    private $horaFin;

    public function __construct($idTurno = null, $horaInicio = null, $horaFin = null)
    {
        parent::__construct();

        $this->idTurno = $idTurno;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
    }

    // ... (Los getters y setters permanecen sin cambios) ...
    public function getIdTurno()
    {
        return $this->idTurno;
    }

    public function setIdTurno($idTurno)
    {
        $this->idTurno = $idTurno;
    }

    public function getHoraIncio()
    {
        return $this->horaInicio;
    }

    public function setHoraIncio($horaInicio)
    {
        $this->horaInicio = $horaInicio;
    }

    public function getHoraFin()
    {
        return $this->horaFin;
    }

    public function setHoraFin($horaFin)
    {
        $this->horaFin = $horaFin;
    }
    // ... (Fin de getters y setters) ...


    // NUEVO: Método para validar la lógica de las horas y el solapamiento
    private function validarSolapamiento()
    {
        // 1. Validar que la hora de fin sea mayor que la hora de inicio
        if ($this->horaFin <= $this->horaInicio) {
            return "La hora de fin debe ser mayor que la hora de inicio.";
        }

        // 2. Validar que el turno no se solape con otro existente
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // La consulta verifica si existe algún turno cuyo rango de tiempo se cruce con el nuevo rango.
        // Dos rangos (A, B) y (C, D) se solapan si A < D y C < B.
        $sql = "SELECT * FROM tbl_turno 
                WHERE tur_estado = 1 
                AND :horaInicio < tur_horaFin 
                AND :horaFin > tur_horaInicio";

        // Si estamos modificando, debemos excluir el propio turno de la validación
        if (!is_null($this->idTurno)) {
            $sql .= " AND tur_id != :idTurno";
        }

        $stmt = $co->prepare($sql);
        $stmt->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);

        if (!is_null($this->idTurno)) {
            $stmt->bindParam(':idTurno', $this->idTurno, PDO::PARAM_INT);
        }

        $stmt->execute();

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return "El rango de horas seleccionado se solapa con un turno ya existente.";
        }

        return null; // Si no hay errores, retorna null
    }


    public function Registrar()
    {
        $r = array();

        // MODIFICADO: Se llama a la validación antes de registrar
        $error_validacion = $this->validarSolapamiento();
        if ($error_validacion !== null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: ' . $error_validacion;
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $stmt = $co->prepare("INSERT INTO tbl_turno( tur_horaInicio, tur_horaFin, tur_estado) 
                                  VALUES (:horaInicio, :horaFin, 1)");

            $stmt->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);

            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/> Se registró el turno correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            // MODIFICADO: Se ordena por hora de inicio para mejor visualización
            $stmt = $co->query("SELECT 
                                tur_id, 
                                DATE_FORMAT(tur_horaInicio, '%H:%i') AS tur_horaInicio, 
                                DATE_FORMAT(tur_horaFin, '%H:%i') AS tur_horaFin 
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
        $co = null;
        return $r;
    }


    public function Modificar()
    {
        $r = array();

        // MODIFICADO: Se llama a la validación antes de modificar
        $error_validacion = $this->validarSolapamiento();
        if ($error_validacion !== null) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'ERROR: ' . $error_validacion;
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("UPDATE tbl_turno
                                  SET  tur_horaInicio = :horaInicio, tur_horaFin = :horaFin
                                  WHERE tur_id = :idTurno");

            $stmt->bindParam(':horaInicio', $this->horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':horaFin', $this->horaFin, PDO::PARAM_STR);
            $stmt->bindParam(':idTurno', $this->idTurno, PDO::PARAM_INT);

            $stmt->execute();

            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el turno correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_turno
                                  SET tur_estado = 0 WHERE tur_id = :idTurno");
            $stmt->bindParam(':idTurno', $this->idTurno, PDO::PARAM_INT);

            $stmt->execute();

            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el turno correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }
}
?>