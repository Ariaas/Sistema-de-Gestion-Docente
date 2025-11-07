<?php

namespace App\Model;

use PDO;
use Exception;

class Eje extends Connection
{
    private $ejeNombre;
    private $ejeDescripcion;

    public function __construct()
    {
        parent::__construct();
    }

    public function getEje()
    {
        return $this->ejeNombre;
    }

    public function getDescripcion()
    {
        return $this->ejeDescripcion;
    }

    public function setEje($ejeNombre)
    {
        $this->ejeNombre = $ejeNombre;
    }

    public function setDescripcion($ejeDescripcion)
    {
        $this->ejeDescripcion = $ejeDescripcion;
    }

    public function Registrar()
    {
        $r = array();

        if ($this->ejeNombre === null || trim($this->ejeNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede estar vacío.';
            return $r;
        }

        $this->ejeNombre = trim($this->ejeNombre);

        if (strlen($this->ejeNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->ejeNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->ejeDescripcion !== null) {
            $this->ejeDescripcion = trim($this->ejeDescripcion);
            if (strlen($this->ejeDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT eje_estado FROM tbl_eje WHERE eje_nombre = :ejeNombre");
            $stmt->execute([':ejeNombre' => $this->ejeNombre]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                if ($existe['eje_estado'] == 1) {
                    return ['resultado' => 'registrar', 'mensaje' => 'ERROR! <br/> El EJE colocado YA existe!'];
                }
                
                $stmt = $co->prepare("UPDATE tbl_eje SET eje_descripcion = :ejeDescripcion, eje_estado = 1 WHERE eje_nombre = :ejeNombre");
            } else {
                $stmt = $co->prepare("INSERT INTO tbl_eje (eje_nombre, eje_descripcion, eje_estado) VALUES (:ejeNombre, :ejeDescripcion, 1)");
            }

            $stmt->execute([
                ':ejeNombre' => $this->ejeNombre,
                ':ejeDescripcion' => $this->ejeDescripcion
            ]);

            return ['resultado' => 'registrar', 'mensaje' => 'Registro Incluido!<br/>Se registró el EJE correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Modificar($ejeOriginal)
    {
        $r = array();

        if ($ejeOriginal === null || trim($ejeOriginal) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre original del eje es requerido.';
            return $r;
        }

        if ($this->ejeNombre === null || trim($this->ejeNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede estar vacío.';
            return $r;
        }

        $this->ejeNombre = trim($this->ejeNombre);

        if (strlen($this->ejeNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->ejeNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->ejeDescripcion !== null) {
            $this->ejeDescripcion = trim($this->ejeDescripcion);
            if (strlen($this->ejeDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT eje_nombre, eje_descripcion FROM tbl_eje WHERE eje_nombre = :ejeOriginal");
            $stmt->execute([':ejeOriginal' => $ejeOriginal]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datosOriginales) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> El eje no existe!'];
            }

            if ($datosOriginales['eje_nombre'] === $this->ejeNombre && 
                $datosOriginales['eje_descripcion'] === $this->ejeDescripcion) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }

            if ($this->Existe($this->ejeNombre, $ejeOriginal)) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> El EJE colocado YA existe!'];
            }

            if ($this->ejeNombre !== $ejeOriginal) {
                $co->prepare("DELETE FROM tbl_eje WHERE eje_nombre = :ejeNombre AND eje_estado = 0")
                   ->execute([':ejeNombre' => $this->ejeNombre]);
            }

            $stmt = $co->prepare("UPDATE tbl_eje SET eje_nombre = :ejeNombre, eje_descripcion = :ejeDescripcion WHERE eje_nombre = :ejeOriginal");
            $stmt->execute([
                ':ejeNombre' => $this->ejeNombre,
                ':ejeDescripcion' => $this->ejeDescripcion,
                ':ejeOriginal' => $ejeOriginal
            ]);

            return ['resultado' => 'modificar', 'mensaje' => 'Registro Modificado!<br/>Se modificó el EJE correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Eliminar()
    {
        $r = array();

        if ($this->ejeNombre === null || trim($this->ejeNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede estar vacío.';
            return $r;
        }

        $this->ejeNombre = trim($this->ejeNombre);

        if (strlen($this->ejeNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->ejeNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre del eje no puede exceder 100 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT eje_estado FROM tbl_eje WHERE eje_nombre = :ejeNombre");
            $stmt->execute([':ejeNombre' => $this->ejeNombre]);
            $eje = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$eje) {
                return ['resultado' => 'eliminar', 'mensaje' => 'ERROR! <br/> El EJE no existe!'];
            }

            if ($eje['eje_estado'] == 0) {
                return ['resultado' => 'eliminar', 'mensaje' => 'ERROR! <br/> El EJE ya está desactivado!'];
            }

            $co->prepare("UPDATE tbl_eje SET eje_estado = 0 WHERE eje_nombre = :ejeNombre")
               ->execute([':ejeNombre' => $this->ejeNombre]);

            return ['resultado' => 'eliminar', 'mensaje' => 'Registro Eliminado!<br/>Se eliminó el EJE correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->query("SELECT eje_nombre, eje_descripcion, eje_estado FROM tbl_eje");
            return ['resultado' => 'consultar', 'mensaje' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Existe($ejeNombre, $ejeExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $sql = "SELECT COUNT(*) FROM tbl_eje WHERE eje_nombre = :ejeNombre AND eje_estado = 1";
            $params = [':ejeNombre' => $ejeNombre];

            if ($ejeExcluir !== null) {
                $sql .= " AND eje_nombre != :ejeExcluir";
                $params[':ejeExcluir'] = $ejeExcluir;
            }

            $stmt = $co->prepare($sql);
            $stmt->execute($params);

            if ($stmt->fetchColumn() > 0) {
                return ['resultado' => 'existe', 'mensaje' => 'El EJE colocado YA existe!'];
            }
            return [];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }
}
