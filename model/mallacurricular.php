<?php
require_once('model/dbconnection.php');

Class Malla extends Connection{  
    private $mal_id;
    private $mal_codigo;
    private $mal_nombre;
    private $mal_anio;
    private $mal_cohorte;
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


// Getters
    public function getMalId() {
        return $this->mal_id;
    }

    public function getMalCodigo() {
        return $this->mal_codigo;
    }

    public function getMalNombre() {
        return $this->mal_nombre;
    }

    public function getMalAnio() {
        return $this->mal_anio;
    }

    public function getMalCohorte() {
        return $this->mal_cohorte;
    }

    public function getMalDescripcion() {
        return $this->mal_descripcion;
    }

    public function getMalEstado() {
        return $this->mal_estado;
    }

    // Setters
    public function setMalId($mal_id) {
        $this->mal_id = $mal_id;
    }

    public function setMalCodigo($mal_codigo) {
        $this->mal_codigo = $mal_codigo;
    }

     public function setMalNombre($mal_nombre) {
        $this->mal_nombre = $mal_nombre;
    }

    public function setMalAnio($mal_anio) {
        $this->mal_anio = $mal_anio;
    }

    public function setMalCohorte($mal_cohorte) {
        $this->mal_cohorte = $mal_cohorte;
    }

    public function setMalDescripcion($mal_descripcion) {
        $this->mal_descripcion = $mal_descripcion;
    }

    public function setMalEstado($mal_estado) {
        $this->mal_estado = $mal_estado;
    }

   

    public function Registrar(){
        $r = array();

            if (!$this->Existe()) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
            try {

                $stmt = $co->prepare("INSERT INTO tbl_malla( mal_codigo, mal_nombre, mal_anio, mal_cohorte, mal_descripcion, mal_estado) 
                 VALUES (:mal_codigo, :mal_nombre, :mal_anio, :mal_cohorte, :mal_descripcion, 1)");

                $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
                $stmt->bindParam(':mal_anio', $this->mal_anio, PDO::PARAM_STR);
                $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
                $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
              
                        
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró la malla curricular correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // 6. Cerrar la conexión
            $co = null;
        
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La malla curricular colocada ya existe!';
        
            }
        return $r;
 
        }

    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_malla  where mal_estado = 1");// cambiar a un inner join con unidad curricular
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


    public function Modificar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
       if (!$this->Existe()){
                try {
                    $stmt = $co->prepare("UPDATE tbl_malla
                    SET mal_codigo = :mal_codigo, mal_nombre = :mal_nombre, mal_anio = :mal_anio, mal_cohorte = :mal_cohorte, mal_descripcion = :mal_descripcion
                    WHERE mal_id = :mal_id");

                    $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
                    $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
                    $stmt->bindParam(':mal_anio', $this->mal_anio, PDO::PARAM_INT);
                    $stmt->bindParam(':mal_cohorte', $this->mal_cohorte, PDO::PARAM_STR);
                    $stmt->bindParam(':mal_descripcion', $this->mal_descripcion, PDO::PARAM_STR);
                    $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);
                   

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la malla curricular correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> La malla curricular colocada YA existe!';
         }
       
        return $r;
    }

    /// Eliminar

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
      
            try {
                $stmt = $co->prepare("UPDATE tbl_malla
                SET mal_estado = 0 WHERE mal_id = :mal_id");
                $stmt->bindParam(':mal_id', $this->mal_id, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la malla curricular correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
       
        return $r;
 
    }

   public function Existe(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_malla WHERE mal_codigo=:mal_codigo AND mal_nombre=:mal_nombre AND mal_estado = 1");

            $stmt->bindParam(':mal_codigo', $this->mal_codigo, PDO::PARAM_STR);
            $stmt->bindParam(':mal_nombre', $this->mal_nombre, PDO::PARAM_STR);
                 
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' La malla curricular colocada YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Se cierra la conexión
        $co = null;
        return $r;
    } 
}
?> 