
<?php
require_once('model/dbconnection.php');

Class Usuario extends Connection{
    private $usuarioId;
    private $nombreUsuario;
    private $contraseñaUsuario;
    private $correoUsuario;
    private $rolUsuario;

    public function __construct( $usuarioId = null, $nombreUsuario = null, $contraseñaUsuario = null, $correoUsuario = null,$rolUsuario = null,){

        parent::__construct();
        $this->usuarioId = $usuarioId;
        $this->nombreUsuario = $nombreUsuario;
        $this->contraseñaUsuario = $contraseñaUsuario;
        $this->correoUsuario = $correoUsuario;
        $this->rolUsuario = $rolUsuario;
        
    }

    public function get_usuarioId()
    {
        return $this->usuarioId;
    }

    public function get_nombreUsuario()
    {
        return $this->nombreUsuario;
    }

    public function get_contraseñaUsuario()
    {
        return $this->contraseñaUsuario;
    }

    public function get_correoUsuario()
    {
        return $this->correoUsuario;
    }

    public function get_rolUsuario()
    {
        return $this->rolUsuario;
    }

    // Setters
    public function set_usuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    public function set_nombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function set_contraseñaUsuario($contraseñaUsuario)
    {
        $this->contraseñaUsuario = $contraseñaUsuario;
    }

    public function set_correoUsuario($correoUsuario)
    {
        $this->correoUsuario = $correoUsuario;
    }

    public function set_rolUsuario($rolUsuario)
    {
        $this->rolUsuario = $rolUsuario;
    }


    public function Registrar(){
        $r = array();

     if (!$this->Existe()) {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            try {

                $stmt = $co->prepare("INSERT INTO tbl_titulo( tit_prefijo, tit_nombre, tit_estado) 
                VALUES (:prefijotitulo, :nombretitulo, 1)");

                $stmt->bindParam(':prefijotitulo', $this->prefijoTitulo, PDO::PARAM_STR);
                $stmt->bindParam(':nombretitulo', $this->nombreTitulo, PDO::PARAM_STR);
                        
                $stmt->execute();

                $r['resultado'] = 'registrar';
                $r['mensaje'] = 'Registro Incluido!<br/> Se registró el título correctamente!';
            } catch (Exception $e) {

                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }

            // 6. Cerrar la conexión
            $co = null;
        
        } else {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> El titulo colocado ya existe!';
        
            }
        return $r;
 
  
        }
    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT * FROM tbl_titulo where tit_estado = 1");
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
                    $stmt = $co->prepare("UPDATE tbl_titulo
                    SET tit_prefijo = :prefijotitulo, tit_nombre = :nombretitulo
                    WHERE tit_id = :tituloid");

                    $stmt->bindParam(':prefijotitulo', $this->prefijoTitulo,PDO::PARAM_STR);
                    $stmt->bindParam(':nombretitulo', $this->nombreTitulo,PDO::PARAM_STR );
                    $stmt->bindParam(':tituloid', $this->tituloId,PDO::PARAM_INT);

                    $stmt->execute();

                    $r['resultado'] = 'modificar';
                    $r['mensaje'] = 'Registro Modificado!<br/>Se modificó el titulo correctamente!';
                } catch (Exception $e) {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = $e->getMessage();
                }
            } else {
                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'ERROR! <br/> El titulo colocado YA existe!';
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
                $stmt = $co->prepare("UPDATE tbl_titulo
                SET tit_estado = 0 WHERE tit_id = :tituloid");
                 $stmt->bindParam(':tituloid', $this->tituloId,PDO::PARAM_INT);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó el titulo correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        }else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> El titulo colocado NO existe!';
        }
        return $r;
 
    }

    public function Existe(){
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->prepare("SELECT * FROM tbl_titulo WHERE tit_prefijo=:prefijotitulo AND tit_nombre=:nombretitulo AND tit_estado = 1");

            $stmt->bindParam(':prefijotitulo', $this->prefijoTitulo,PDO::PARAM_STR);
            $stmt->bindParam(':nombretitulo', $this->nombreTitulo,PDO::PARAM_STR);
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = ' El titulo colocado YA existe!';
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