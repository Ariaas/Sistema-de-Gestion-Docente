<?php
require_once('model/dbconnection.php');

Class Certificado extends Connection{  
    private $certificadoId;
    private $trayecto;
    private $nombreCertificado;
    
    public function __construct($certificadoId = null, $trayecto = null, $nombreCertificado = null)
    {
        parent::__construct();

        $this->certificadoId = $certificadoId;
        $this->trayecto = $trayecto;
        $this->nombreCertificado = $nombreCertificado;
    }


    public function get_certificadoId() {
        return $this->certificadoId;
    }

    public function get_trayecto() {
        return $this->trayecto;
    }

    public function get_nombreCertificado() {
        return $this->nombreCertificado;
    }

    // Setters
    public function set_certificadoId($certificadoId) {
        $this->certificadoId = $certificadoId;
    }

    public function set_trayecto($trayecto) {
        $this->trayecto = $trayecto;
    }

    public function set_nombreCertificado($nombreCertificado) {
        $this->nombreCertificado = $nombreCertificado;
    }

    public function Registrar(){
        $r = array();

            if (!$this->Existe()) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
            try {

                $stmt = $co->prepare("INSERT INTO tbl_certificado( tra_id, cert_nombre, cert_estado) 
                VALUES (:trayecto, :certificadonombre, 1)");

                $stmt->bindParam(':certificadonombre', $this->nombreCertificado, PDO::PARAM_STR);
                $stmt->bindParam(':trayecto', $this->trayecto, PDO::PARAM_INT);
                        
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el certificado correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // 6. Cerrar la conexión
            $co = null;
        
         } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El certificado colocado ya existe!';
        
            }
        return $r;
 
        }

    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_certificado INNER JOIN tbl_trayecto on tbl_trayecto.tra_id = tbl_certificado.tra_id where cert_estado = 1");
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
                    $stmt = $co->prepare("UPDATE tbl_certificado
                    SET tra_id = :trayecto, cert_nombre = :certificadonombre
                    WHERE cert_id = :certificadoId");


                    $stmt->bindParam(':certificadonombre', $this->nombreCertificado, PDO::PARAM_STR);
                    $stmt->bindParam(':trayecto', $this->trayecto, PDO::PARAM_INT);
                    $stmt->bindParam(':certificadoId', $this->certificadoId, PDO::PARAM_INT);
                   

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el certificado correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El certificado colocado YA existe!';
         }
       
        return $r;
    }

    /// Eliminar

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
       if (!$this->Existe()){ 
            try {
                $stmt = $co->prepare("UPDATE tbl_certificado
                SET cert_estado = 0 WHERE cert_id = :certificadoId");
                $stmt->bindParam(':certificadoId', $this->certificadoId, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el certificado correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        }else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El certificado colocado NO existe!';
        }
        return $r;
 
    }

    public function Existe(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_certificado WHERE tra_id=:trayecto AND cert_nombre=:certificadonombre AND cert_estado = 1");

            $stmt->bindParam(':certificadonombre', $this->nombreCertificado, PDO::PARAM_STR);
            $stmt->bindParam(':trayecto', $this->trayecto, PDO::PARAM_INT);
                 
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El certificado colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        // Se cierra la conexión
        $co = null;
        return $r;
    }

    public function obtenerTrayectos(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $p = $co->prepare("SELECT * FROM tbl_trayecto WHERE tra_estado = 1");
        $p->execute();
        $r = $p->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }
}
?>