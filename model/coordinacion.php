<?php
require_once('model/dbconnection.php');

class Coordinacion extends Connection
{
    private $cor_nombre;
    private $original_cor_nombre;
    private $coor_hora_descarga;

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
    
    public function getHoraDescarga()
    {
        return $this->coor_hora_descarga;
    }
    
    public function setHoraDescarga($hora)
    {
        if ($hora !== null && $hora !== '') {
            $hora = intval($hora);
            if ($hora < 1 || $hora > 99) {
                throw new Exception('La hora de descarga debe estar entre 1 y 99.');
            }
        }
        $this->coor_hora_descarga = $hora;
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
                $stmt = $co->prepare("INSERT INTO tbl_coordinacion (cor_nombre, coor_hora_descarga, cor_estado) VALUES (:cor_nombre, :coor_hora_descarga, 1)");
                $stmt->execute([
                    ':cor_nombre' => $this->cor_nombre,
                    ':coor_hora_descarga' => $this->coor_hora_descarga
                ]);
                $r['resultado'] = 'registrar';
                $r['mensaje'] = ' Coordinación registrada correctamente.';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            } finally {
                $co = null;
            }
        }
        return $r;
    }



    public function Modificar()
    {
        $r = [];
        
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $co->prepare("SELECT cor_nombre, coor_hora_descarga FROM tbl_coordinacion WHERE cor_nombre = :original_nombre");
            $stmt->execute([':original_nombre' => $this->original_cor_nombre]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$datosOriginales) {
                return ['resultado' => 'error', 'mensaje' => 'La coordinación no existe.'];
            }
            
            if ($datosOriginales['cor_nombre'] === $this->cor_nombre && 
                $datosOriginales['coor_hora_descarga'] == $this->coor_hora_descarga) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        }
        
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
                $co->prepare("DELETE FROM tbl_coordinacion WHERE cor_nombre = :nombre AND cor_estado = 0")
                   ->execute([':nombre' => $this->cor_nombre]);
            }

            $co->prepare("UPDATE tbl_coordinacion SET cor_nombre = :new_nombre, coor_hora_descarga = :coor_hora_descarga WHERE cor_nombre = :original_nombre")
               ->execute([
                   ':new_nombre' => $this->cor_nombre,
                   ':coor_hora_descarga' => $this->coor_hora_descarga,
                   ':original_nombre' => $this->original_cor_nombre
               ]);
            
            $r['resultado'] = 'modificar';
            $r['mensaje'] = ' La coordinación se ha modificado correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $r['mensaje'] = ' No se puede modificar el nombre porque está siendo usado en otros registros.';
            } else {
                $r['mensaje'] = $e->getMessage();
            }
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Eliminar()
    {
        $r = [];
        
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $co->prepare("SELECT cor_estado FROM tbl_coordinacion WHERE cor_nombre = :cor_nombre");
            $stmt->execute([':cor_nombre' => $this->cor_nombre]);
            $coordinacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$coordinacion) {
                $r['resultado'] = 'error';
                $r['mensaje'] = ' La coordinación que intenta eliminar no existe.';
                return $r;
            }

            if ($coordinacion['cor_estado'] == 0) {
                $r['resultado'] = 'error';
                $r['mensaje'] = ' La coordinación ya está desactivada.';
                return $r;
            }
            
            $co->prepare("UPDATE tbl_coordinacion SET cor_estado = 0 WHERE cor_nombre = :cor_nombre")
               ->execute([':cor_nombre' => $this->cor_nombre]);
            
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'La coordinación se ha eliminado correctamente.';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
        }
        return $r;
    }

    public function Listar()
    {
        $r = [];
        try {
            $co = $this->Con();
            $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $co->query("SELECT cor_nombre, coor_hora_descarga, cor_estado FROM tbl_coordinacion ORDER BY cor_nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $r['resultado'] = 'consultar';
            $r['mensaje'] = $data;
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        } finally {
            $co = null;
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
            $stmt = $co->prepare("UPDATE tbl_coordinacion SET cor_nombre = :nuevo_nombre, coor_hora_descarga = :coor_hora_descarga, cor_estado = 1 WHERE cor_nombre = :nombre_original");
            $stmt->execute([
                ':nuevo_nombre' => $this->cor_nombre,
                ':coor_hora_descarga' => $this->coor_hora_descarga,
                ':nombre_original' => $nombre_original
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
