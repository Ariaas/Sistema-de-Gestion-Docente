<?php
require_once('model/dbconnection.php');

class Titulo extends Connection
{
    private $prefijoTitulo;
    private $nombreTitulo;


    private $originalPrefijoTitulo;
    private $originalNombreTitulo;

    public function __construct()
    {
        parent::__construct();
    }


    public function set_prefijo($prefijoTitulo)
    {
        $this->prefijoTitulo = $prefijoTitulo;
    }
    public function get_prefijo()
    {
        return $this->prefijoTitulo;
    }
    public function set_nombreTitulo($nombreTitulo)
    {
        $this->nombreTitulo = $nombreTitulo;
    }
    public function get_nombreTitulo()
    {
        return $this->nombreTitulo;
    }
    public function set_original_prefijo($originalPrefijoTitulo)
    {
        $this->originalPrefijoTitulo = $originalPrefijoTitulo;
    }
    public function set_original_nombre($originalNombreTitulo)
    {
        $this->originalNombreTitulo = $originalNombreTitulo;
    }

    public function Registrar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validaciones de entrada
        if (empty($this->prefijoTitulo) || trim($this->prefijoTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El prefijo del título es requerido'];
        }
        
        if (empty($this->nombreTitulo) || trim($this->nombreTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El nombre del título es requerido'];
        }

        $this->prefijoTitulo = trim($this->prefijoTitulo);
        $this->nombreTitulo = trim($this->nombreTitulo);

        if (strlen($this->prefijoTitulo) < 2 || strlen($this->prefijoTitulo) > 10) {
            return ['resultado' => 'error', 'mensaje' => 'El prefijo debe tener entre 2 y 10 caracteres'];
        }

        if (strlen($this->nombreTitulo) < 3 || strlen($this->nombreTitulo) > 100) {
            return ['resultado' => 'error', 'mensaje' => 'El nombre del título debe tener entre 3 y 100 caracteres'];
        }

        if ($this->existeActivo($co)) {
            return ['resultado' => 'error', 'mensaje' => '¡ERROR! <br/> El título colocado ya existe!.'];
        }

        if ($this->existeInactivo($co)) {
            return $this->reactivar($co);
        }

        try {
            $stmt = $co->prepare("INSERT INTO tbl_titulo(tit_prefijo, tit_nombre, tit_estado) VALUES (:prefijo, :nombre, 1)");
            $stmt->bindParam(':prefijo', $this->prefijoTitulo, PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $this->nombreTitulo, PDO::PARAM_STR);
            $stmt->execute();
            return ['resultado' => 'registrar', 'mensaje' => '¡Registro Incluido! <br/> Se registró el título correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
    }


    public function Consultar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {

            $stmt = $co->query("SELECT tit_prefijo, tit_nombre FROM tbl_titulo WHERE tit_estado = 1 ORDER BY tit_prefijo, tit_nombre");
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
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        // Validaciones de entrada
        if (empty($this->prefijoTitulo) || trim($this->prefijoTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El prefijo del título es requerido'];
        }
        
        if (empty($this->nombreTitulo) || trim($this->nombreTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El nombre del título es requerido'];
        }

        if (empty($this->originalPrefijoTitulo) || empty($this->originalNombreTitulo)) {
            return ['resultado' => 'error', 'mensaje' => 'Debe especificar el título original a modificar'];
        }

        $this->prefijoTitulo = trim($this->prefijoTitulo);
        $this->nombreTitulo = trim($this->nombreTitulo);

        if (strlen($this->prefijoTitulo) < 2 || strlen($this->prefijoTitulo) > 10) {
            return ['resultado' => 'error', 'mensaje' => 'El prefijo debe tener entre 2 y 10 caracteres'];
        }

        if (strlen($this->nombreTitulo) < 3 || strlen($this->nombreTitulo) > 100) {
            return ['resultado' => 'error', 'mensaje' => 'El nombre del título debe tener entre 3 y 100 caracteres'];
        }

        if ($this->prefijoTitulo !== $this->originalPrefijoTitulo || $this->nombreTitulo !== $this->originalNombreTitulo) {
            if ($this->Existe()) {
                $r['resultado'] = 'error';
                $r['mensaje'] = '¡ERROR! <br/> El titulo colocado ya existe!';
                return $r;
            }
        }

        try {

            $stmtDel = $co->prepare("DELETE FROM tbl_titulo WHERE tit_prefijo = :prefijotitulo AND tit_nombre = :nombretitulo AND tit_estado = 0");
            $stmtDel->bindParam(':prefijotitulo', $this->prefijoTitulo, PDO::PARAM_STR);
            $stmtDel->bindParam(':nombretitulo', $this->nombreTitulo, PDO::PARAM_STR);
            $stmtDel->execute();

            $stmt = $co->prepare("UPDATE tbl_titulo
            SET tit_prefijo = :new_prefijo, tit_nombre = :new_nombre
            WHERE tit_prefijo = :old_prefijo AND tit_nombre = :old_nombre");

            $stmt->bindParam(':new_prefijo', $this->prefijoTitulo, PDO::PARAM_STR);
            $stmt->bindParam(':new_nombre', $this->nombreTitulo, PDO::PARAM_STR);
            $stmt->bindParam(':old_prefijo', $this->originalPrefijoTitulo, PDO::PARAM_STR);
            $stmt->bindParam(':old_nombre', $this->originalNombreTitulo, PDO::PARAM_STR);
            $stmt->execute();

            $r['resultado'] = 'modificar';
            $r['mensaje'] = '¡Registro Modificado! <br/> Se modificó el título correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            if ($e->getCode() == '23000') {
                $r['mensaje'] = 'No se puede modificar el título porque está siendo utilizado por uno o más docentes.';
            } else {
                $r['mensaje'] = $e->getMessage();
            }
        }
        return $r;
    }

    public function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();

        // Validaciones de entrada
        if (empty($this->prefijoTitulo) || trim($this->prefijoTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El prefijo del título es requerido'];
        }
        
        if (empty($this->nombreTitulo) || trim($this->nombreTitulo) === '') {
            return ['resultado' => 'error', 'mensaje' => 'El nombre del título es requerido'];
        }

        $this->prefijoTitulo = trim($this->prefijoTitulo);
        $this->nombreTitulo = trim($this->nombreTitulo);

        if ($this->Existe()) {
            try {

                $stmt = $co->prepare("UPDATE tbl_titulo SET tit_estado = 0 WHERE tit_prefijo = :prefijotitulo AND tit_nombre = :nombretitulo");
                $stmt->bindParam(':prefijotitulo', $this->prefijoTitulo, PDO::PARAM_STR);
                $stmt->bindParam(':nombretitulo', $this->nombreTitulo, PDO::PARAM_STR);
                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = '¡Registro Eliminado! <br/> Se eliminó el título correctamente.';
            } catch (Exception $e) {
                $r['resultado'] = 'error';

                if ($e->getCode() == '23000') {
                    $r['mensaje'] = 'No se puede eliminar el título porque está siendo utilizado por uno o más docentes.';
                } else {
                    $r['mensaje'] = $e->getMessage();
                }
            }
        } else {
            $r['resultado'] = 'error';
            $r['mensaje'] = '¡ERROR! <br/> El título que intenta eliminar no existe.';
        }
        return $r;
    }

    public function Existe()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $sql = "SELECT 1 FROM tbl_titulo WHERE tit_prefijo = :prefijotitulo AND tit_nombre = :nombretitulo AND tit_estado = 1";

            if (!empty($this->originalPrefijoTitulo) && !empty($this->originalNombreTitulo)) {
                $sql .= " AND (tit_prefijo != :original_prefijo OR tit_nombre != :original_nombre)";
            }

            $stmt = $co->prepare($sql);
            $stmt->bindParam(':prefijotitulo', $this->prefijoTitulo, PDO::PARAM_STR);
            $stmt->bindParam(':nombretitulo', $this->nombreTitulo, PDO::PARAM_STR);

            if (!empty($this->originalPrefijoTitulo) && !empty($this->originalNombreTitulo)) {
                $stmt->bindParam(':original_prefijo', $this->originalPrefijoTitulo, PDO::PARAM_STR);
                $stmt->bindParam(':original_nombre', $this->originalNombreTitulo, PDO::PARAM_STR);
            }

            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_BOTH);
            return !empty($fila);
        } catch (Exception $e) {
            return true;
        }
    }

    private function existeActivo($co)
    {
        $sql = "SELECT 1 FROM tbl_titulo WHERE tit_prefijo = :prefijo AND tit_nombre = :nombre AND tit_estado = 1";
        $stmt = $co->prepare($sql);
        $stmt->bindParam(':prefijo', $this->prefijoTitulo, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $this->nombreTitulo, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return ($result != false);
    }

    
    private function existeInactivo($co)
    {
        $stmt = $co->prepare("SELECT 1 FROM tbl_titulo WHERE tit_prefijo = :prefijo AND tit_nombre = :nombre AND tit_estado = 0");
        $stmt->bindParam(':prefijo', $this->prefijoTitulo, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $this->nombreTitulo, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return ($result != false);
    }

    
    private function reactivar($co)
    {
        $stmt = $co->prepare("UPDATE tbl_titulo SET tit_estado = 1 WHERE tit_prefijo = :prefijo AND tit_nombre = :nombre");
        $stmt->bindParam(':prefijo', $this->prefijoTitulo, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $this->nombreTitulo, PDO::PARAM_STR);
        $stmt->execute();
        return ['resultado' => 'registrar', 'mensaje' => '¡Registro Incluido! <br/> Se registró el título correctamente!'];
    }
}
