<?php
require_once('model/dbconnection.php');

Class Certificado extends Connection{  
    private $certificadoId;
    private $trayecto;
    private $nombreCertificado;
    private $tipoCertificado;
    
    public function __construct($certificadoId = null, $trayecto = null, $nombreCertificado = null, $tipoCertificado = null)
    {
        parent::__construct();

        $this->certificadoId = $certificadoId;
        $this->trayecto = $trayecto;
        $this->nombreCertificado = $nombreCertificado;
        $this->tipoCertificado = $tipoCertificado;
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
    public function get_tipoCertificado() {
        return $this->tipoCertificado;
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
    public function set_tipoCertificado($tipoCertificado) {
        $this->tipoCertificado = $tipoCertificado;
    }


    public function Registrar(){
        $r = array();

        $check_codigo = $this->Existe();
        if (!empty($check_codigo['resultado']) && $check_codigo['resultado'] == 'existe') {
            return $check_codigo; 
        }
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
            try {

                $stmt = $co->prepare("INSERT INTO tbl_certificacion( tra_id, cert_nombre, cert_tipo, cert_estado) 
                VALUES (:trayecto, :certificadonombre,:cert_tipo, 1)");

                $stmt->bindParam(':certificadonombre', $this->nombreCertificado, PDO::PARAM_STR);
                $stmt->bindParam(':trayecto', $this->trayecto, PDO::PARAM_INT);
                $stmt->bindParam(':cert_tipo', $this->tipoCertificado, PDO::PARAM_STR);
                        
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el certificado correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // 6. Cerrar la conexión
            $co = null;
    
        return $r;
 
        }

    public function Consultar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_certificacion INNER JOIN tbl_trayecto on tbl_trayecto.tra_id = tbl_certificacion.tra_id where cert_estado = 1");
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

        $check_codigo = $this->Existe();
        if (!empty($check_codigo['resultado']) && $check_codigo['resultado'] == 'existe') {
            return $check_codigo; 
        }
                try {
                    $stmt = $co->prepare("UPDATE tbl_certificacion
                    SET tra_id = :trayecto, cert_nombre = :certificadonombre, cert_tipo = :cert_tipo
                    WHERE cert_id = :certificadoId");

                    $stmt->bindParam(':cert_tipo', $this->tipoCertificado, PDO::PARAM_STR);
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
           
         $co = null;
        return $r;
    }

    /// Eliminar

    public function Eliminar(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
     
            try {
                $stmt = $co->prepare("UPDATE tbl_certificacion
                SET cert_estado = 0 WHERE cert_id = :certificadoId");
                $stmt->bindParam(':certificadoId', $this->certificadoId, PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el certificado correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
          $co = null;
        return $r;
 
    }

    public function Existe(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {

            $sql = "SELECT * FROM tbl_certificacion WHERE cert_nombre=:certificadonombre AND cert_estado = 1";

            if ($this->certificadoId !== null) {
                $sql .= " AND cert_id != :cert_id";
            }

            $stmt = $co->prepare( $sql);
            $stmt->bindParam(':certificadonombre', $this->nombreCertificado, PDO::PARAM_STR);

            if ($this->certificadoId !== null) {
                $stmt->bindParam(':cert_id', $this->certificadoId, PDO::PARAM_INT);
            }

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