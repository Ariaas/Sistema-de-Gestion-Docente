<?php
require_once('model/dbconnection.php');

class Coordinacion extends Connection
{
    private $cor_nombre;
    private $original_cor_nombre;

    public function __construct()
    {
        parent::__construct();
    }


    public function getNombre()
    {
        return $this->cor_nombre;
    }
    public function setNombre($nombre)
    {
        $this->cor_nombre = trim($nombre);
    }
    public function setOriginalNombre($nombre)
    {
        $this->original_cor_nombre = trim($nombre);
    }



    public function Registrar()
    {
        $r = [];


        $registro_existente = $this->BuscarPorNombre($this->cor_nombre);

        if ($registro_existente) {

            if ($registro_existente['cor_estado'] == 1) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'El nombre de la coordinación ya existe y está activa.';
            } else {

                if ($this->ActualizarYReactivar($registro_existente['cor_nombre'])) {
                    $r['resultado'] = 'registrar';
                    $r['mensaje'] = ' Coordinación registrada correctamente.';
                } else {
                    $r['resultado'] = 'error';
                    $r['mensaje'] = ' Hubo un problema al reactivar la coordinación.';
                }
            }
        } else {

            try {
                $co = $this->Con();
                $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $co->prepare("INSERT INTO tbl_coordinacion (cor_nombre, cor_estado) VALUES (:cor_nombre, 1)");
                $stmt->bindParam(':cor_nombre', $this->cor_nombre, PDO::PARAM_STR);
                $stmt->execute();
                $r['resultado'] = 'registrar';
                $r['mensaje'] = ' Coordinación registrada correctamente.';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        }
        return $r;
    }



    public function Modificar()
    {
        $r = [];
        if ($this->original_cor_nombre !== $this->cor_nombre) {
            if ($this->Existe($this->cor_nombre)) {
                $r['resultado'] = 'error';
                $r['mensaje'] = ' El nuevo nombre de la coordinación ya está en uso.';
                return $r;
            }
        }
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $registro_inactivo = $this->BuscarPorNombre($this->cor_nombre);
            if ($registro_inactivo && isset($registro_inactivo['cor_estado']) && $registro_inactivo['cor_estado'] == 0) {
                $stmtDel = $co->prepare("DELETE FROM tbl_coordinacion WHERE cor_nombre = :nombre AND cor_estado = 0");
                $stmtDel->bindParam(':nombre', $this->cor_nombre, PDO::PARAM_STR);
                $stmtDel->execute();
            }

            $stmt = $co->prepare("UPDATE tbl_coordinacion SET cor_nombre = :new_nombre WHERE cor_nombre = :original_nombre");
            $stmt->bindParam(':new_nombre', $this->cor_nombre, PDO::PARAM_STR);
            $stmt->bindParam(':original_nombre', $this->original_cor_nombre, PDO::PARAM_STR);
            $stmt->execute();
            $r['resultado'] = 'modificar';
            $r['mensaje'] = ' La coordinación se ha modificado correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $r['mensaje'] = ' No se puede modificar el nombre porque está siendo usado en otros registros.';
            } else {
                $r['mensaje'] = $e->getMessage();
            }
        }
        return $r;
    }

    public function Eliminar()
    {
        $r = [];
        if (!$this->Existe($this->cor_nombre)) {
            $r['resultado'] = 'error';
            $r['mensaje'] = ' La coordinación que intenta eliminar no existe.';
            return $r;
        }
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->prepare("UPDATE tbl_coordinacion SET cor_estado = 0 WHERE cor_nombre = :cor_nombre");
            $stmt->bindParam(':cor_nombre', $this->cor_nombre, PDO::PARAM_STR);
            $stmt->execute();
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'La coordinación se ha eliminado correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Listar()
    {
        $r = [];
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->query("SELECT cor_nombre FROM tbl_coordinacion WHERE cor_estado = 1 ORDER BY cor_nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        return $r;
    }

    public function Existe($nombre)
    {
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->prepare("SELECT cor_nombre FROM tbl_coordinacion WHERE cor_nombre = :nombre AND cor_estado = 1");
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }



    private function BuscarPorNombre($nombre)
    {
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->prepare("SELECT * FROM tbl_coordinacion WHERE cor_nombre = :nombre");
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }


    private function ActualizarYReactivar($nombre_original)
    {
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->prepare("UPDATE tbl_coordinacion SET cor_nombre = :nuevo_nombre, cor_estado = 1 WHERE cor_nombre = :nombre_original");


            $stmt->bindParam(':nuevo_nombre', $this->cor_nombre, PDO::PARAM_STR);

            $stmt->bindParam(':nombre_original', $nombre_original, PDO::PARAM_STR);

            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
