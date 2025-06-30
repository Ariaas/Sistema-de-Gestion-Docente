<?php
require_once('model/dbconnection.php');

class Malla extends Connection {
    private $mal_id;
    private $mal_anio;
    private $mal_cohorte;
    private $mal_codigo;
    private $mal_nombre;
    private $mal_descripcion;
    private $mal_estado;

    public function __construct($mal_id = null, $mal_codigo = null, $mal_nombre = null, $mal_anio = null, $mal_cohorte = null, $mal_descripcion = null, $mal_estado = null)
    {
        parent::__construct();
        $this->mal_id = $mal_id;
        $this->mal_codigo = $mal_codigo;
        $this->mal_nombre = $mal_nombre;
        $this->mal_anio = $mal_anio;
        $this->mal_cohorte = $mal_cohorte;
        $this->mal_descripcion = $mal_descripcion;
        $this->mal_estado = $mal_estado;
    }

    public function getMalId() { return $this->mal_id; }
    public function getMalCodigo() { return $this->mal_codigo; }
    public function getMalNombre() { return $this->mal_nombre; }
    public function getMalCohorte() { return $this->mal_cohorte; }
    public function getMalDescripcion() { return $this->mal_descripcion; }
    public function getMalEstado() { return $this->mal_estado; }


    public function setMalId($mal_id) { $this->mal_id = $mal_id; }
    public function setMalCodigo($mal_codigo) { $this->mal_codigo = $mal_codigo; }
    public function setMalNombre($mal_nombre) { $this->mal_nombre = $mal_nombre; }
    public function setMalCohorte($mal_cohorte) { $this->mal_cohorte = $mal_cohorte; }
    public function setMalDescripcion($mal_descripcion) { $this->mal_descripcion = $mal_descripcion; }
    public function setMalEstado($mal_estado) { $this->mal_estado = $mal_estado; }

    public function Registrar($unidades){
        $r = array();
        $check_codigo = $this->Existecodigo();
        if (!empty($check_codigo['resultado']) && $check_codigo['resultado'] == 'existe') {
            return $check_codigo;
        }
        $check_nombre = $this->Existenombre();
        if (!empty($check_nombre['resultado']) && $check_nombre['resultado'] == 'existe') {
            return $check_nombre;
        }
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {

              $co->beginTransaction();
            $stmt = $co->prepare("INSERT INTO tbl_malla( mal_codigo, mal_nombre, mal_descripcion, mal_cohorte, mal_estado) 
            VALUES (:mal_codigo, :mal_nombre, :mal_descripcion, :mal_cohorte, 1)");
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);

            $stmt->execute();
            $this->mal_id = $co->lastInsertId();
 
             // 3. Insertar cada unidad curricular en `uc_pensum`
            $stmt_pensum = $co->prepare("INSERT INTO uc_pensum (mal_id, uc_id, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) 
                                         VALUES (:mal_id, :uc_id, :hora_ind, :hora_asis, :hora_acad)");
            
            foreach($unidades as $uc) {
                $stmt_pensum->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
                $stmt_pensum->bindParam(':uc_id', $uc['uc_id'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_ind', $uc['hora_independiente'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_asis', $uc['hora_asistida'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_acad', $uc['hora_academica'], PDO::PARAM_INT);
                $stmt_pensum->execute();
            }

            $co->commit();



            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/> Se registró la malla curricular correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_malla WHERE mal_estado = 1 ");
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

    public function Modificar($unidades){
        $check_codigo = $this->Existecodigo();
        if (!empty($check_codigo['resultado']) && $check_codigo['resultado'] == 'existe') {
            return $check_codigo;
        }
        $check_nombre = $this->Existenombre();
        if (!empty($check_nombre['resultado']) && $check_nombre['resultado'] == 'existe') {
            return $check_nombre;
        }
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {

             $co->beginTransaction();
            $stmt = $co->prepare("UPDATE tbl_malla SET  coh_id = :mal_cohorte, mal_codigo = :mal_codigo, mal_nombre = :mal_nombre,  mal_descripcion = :mal_descripcion WHERE mal_id = :mal_id");
            $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt_delete = $co->prepare("DELETE FROM uc_pensum WHERE mal_id = :mal_id");
            $stmt_delete->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            $stmt_delete->execute();


            $stmt_pensum = $co->prepare("INSERT INTO uc_pensum (mal_id, uc_id, mal_hora_independiente, mal_hora_asistida, mal_hora_academica) 
                                         VALUES (:mal_id, :uc_id, :hora_ind, :hora_asis, :hora_acad)");

            foreach($unidades as $uc) {
                $stmt_pensum->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
                $stmt_pensum->bindParam(':uc_id', $uc['uc_id'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_ind', $uc['hora_independiente'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_asis', $uc['hora_asistida'], PDO::PARAM_INT);
                $stmt_pensum->bindParam(':hora_acad', $uc['hora_academica'], PDO::PARAM_INT);
                $stmt_pensum->execute();
            }

            $co->commit();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la malla curricular correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("UPDATE tbl_malla SET mal_estado = 0 WHERE mal_id = :mal_id");
            $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la malla curricular correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existecodigo(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_malla WHERE mal_codigo = :mal_codigo AND mal_estado = 1";
            if ($this->mal_id !== null) {
                $sql .= " AND mal_id != :mal_id";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);

            if ($this->mal_id !== null) {
                $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = '¡Atención!<br>El código de la malla ya está en uso por otro registro.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

    public function Existenombre(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_malla WHERE mal_nombre = :mal_nombre AND mal_estado = 1";
            if ($this->mal_id !== null) {
                $sql .= " AND mal_id != :mal_id";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);

            if ($this->mal_id !== null) {
                $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = '¡Atención!<br>El nombre de la malla ya está en uso por otro registro.';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
    

      public function obtenerUnidadesCurriculares() {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {

            $stmt = $co->query("SELECT uc_id, uc_nombre FROM tbl_uc WHERE uc_estado = 1 ORDER BY uc_nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

   
    public function obtenerUnidadesPorMalla($mal_id) {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            // Asume que la tabla `uc_pensum` relaciona `mal_id` con `uc_id`
            $stmt = $co->prepare("SELECT p.uc_id, u.uc_nombre, p.mal_hora_independiente, p.mal_hora_asistida, p.mal_hora_academica 
                                 FROM uc_pensum p 
                                 JOIN tbl_uc u ON p.uc_id = u.uc_id
                                 WHERE p.mal_id = :mal_id");
            $stmt->bindParam(':mal_id', $mal_id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'ok';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }

}
?>