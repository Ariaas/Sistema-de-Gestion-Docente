<?php

namespace App\Model;

use PDO;
use Exception;

class Categoria extends Connection
{
    private $categoriaNombre;
    private $categoriaDescripcion;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCategoria()
    {
        return $this->categoriaNombre;
    }

    public function getDescripcion()
    {
        return $this->categoriaDescripcion;
    }

    public function setCategoria($categoriaNombre)
    {
        $this->categoriaNombre = $categoriaNombre;
    }

    public function setDescripcion($categoriaDescripcion)
    {
        $this->categoriaDescripcion = $categoriaDescripcion;
    }

    public function Registrar()
    {
        $r = array();

        if ($this->categoriaNombre === null || trim($this->categoriaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede estar vacío.';
            return $r;
        }

        $this->categoriaNombre = trim($this->categoriaNombre);

        if (strlen($this->categoriaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->categoriaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->categoriaDescripcion !== null) {
            $this->categoriaDescripcion = trim($this->categoriaDescripcion);
            if (strlen($this->categoriaDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT cat_estado FROM tbl_categoria WHERE cat_nombre = :categoriaNombre");
            $stmt->execute([':categoriaNombre' => $this->categoriaNombre]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                if ($existe['cat_estado'] == 1) {
                    return ['resultado' => 'registrar', 'mensaje' => 'ERROR! <br/> La CATEGORÍA colocada ya existe!'];
                }
                
                $stmt = $co->prepare("UPDATE tbl_categoria SET cat_descripcion = :categoriaDescripcion, cat_estado = 1 WHERE cat_nombre = :categoriaNombre");
            } else {
                $stmt = $co->prepare("INSERT INTO tbl_categoria (cat_nombre, cat_descripcion, cat_estado) VALUES (:categoriaNombre, :categoriaDescripcion, 1)");
            }

            $stmt->execute([
                ':categoriaNombre' => $this->categoriaNombre,
                ':categoriaDescripcion' => $this->categoriaDescripcion
            ]);

            return ['resultado' => 'registrar', 'mensaje' => 'Registro Incluido!<br/>Se registró la CATEGORÍA correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Modificar($categoriaOriginal)
    {
        $r = array();

        if ($categoriaOriginal === null || trim($categoriaOriginal) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre original de la categoría es requerido.';
            return $r;
        }

        if ($this->categoriaNombre === null || trim($this->categoriaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede estar vacío.';
            return $r;
        }

        $this->categoriaNombre = trim($this->categoriaNombre);

        if (strlen($this->categoriaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->categoriaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede exceder 100 caracteres.';
            return $r;
        }

        if ($this->categoriaDescripcion !== null) {
            $this->categoriaDescripcion = trim($this->categoriaDescripcion);
            if (strlen($this->categoriaDescripcion) > 500) {
                $r['resultado'] = 'error';
                $r['mensaje'] = 'La descripción no puede exceder 500 caracteres.';
                return $r;
            }
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT cat_nombre, cat_descripcion FROM tbl_categoria WHERE cat_nombre = :categoriaOriginal");
            $stmt->execute([':categoriaOriginal' => $categoriaOriginal]);
            $datosOriginales = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$datosOriginales) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> La categoría no existe!'];
            }

            if ($datosOriginales['cat_nombre'] === $this->categoriaNombre && 
                $datosOriginales['cat_descripcion'] === $this->categoriaDescripcion) {
                return ['resultado' => 'modificar', 'mensaje' => 'No se realizaron cambios.'];
            }

            if ($this->Existe($this->categoriaNombre, $categoriaOriginal)) {
                return ['resultado' => 'modificar', 'mensaje' => 'ERROR! <br/> La CATEGORÍA colocada YA existe!'];
            }

            if ($this->categoriaNombre !== $categoriaOriginal) {
                $co->prepare("DELETE FROM tbl_categoria WHERE cat_nombre = :categoriaNombre AND cat_estado = 0")
                   ->execute([':categoriaNombre' => $this->categoriaNombre]);
            }

            $stmt = $co->prepare("UPDATE tbl_categoria SET cat_nombre = :categoriaNombre, cat_descripcion = :categoriaDescripcion WHERE cat_nombre = :categoriaOriginal");
            $stmt->execute([
                ':categoriaNombre' => $this->categoriaNombre,
                ':categoriaDescripcion' => $this->categoriaDescripcion,
                ':categoriaOriginal' => $categoriaOriginal
            ]);

            return ['resultado' => 'modificar', 'mensaje' => 'Registro Modificado!<br/>Se modificó la CATEGORÍA correctamente!'];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Eliminar()
    {
        $r = array();

        if ($this->categoriaNombre === null || trim($this->categoriaNombre) === '') {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede estar vacío.';
            return $r;
        }

        $this->categoriaNombre = trim($this->categoriaNombre);

        if (strlen($this->categoriaNombre) < 3) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría debe tener al menos 3 caracteres.';
            return $r;
        }

        if (strlen($this->categoriaNombre) > 100) {
            $r['resultado'] = 'error';
            $r['mensaje'] = 'El nombre de la categoría no puede exceder 100 caracteres.';
            return $r;
        }

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $co->prepare("SELECT cat_estado FROM tbl_categoria WHERE cat_nombre = :categoriaNombre");
            $stmt->execute([':categoriaNombre' => $this->categoriaNombre]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$categoria) {
                return ['resultado' => 'eliminar', 'mensaje' => 'ERROR! <br/> La Categoría no existe!'];
            }

            if ($categoria['cat_estado'] == 0) {
                return ['resultado' => 'eliminar', 'mensaje' => 'ERROR! <br/> La Categoría ya está desactivada!'];
            }

            $co->prepare("UPDATE tbl_categoria SET cat_estado = 0 WHERE cat_nombre = :categoriaNombre")
               ->execute([':categoriaNombre' => $this->categoriaNombre]);

            return ['resultado' => 'eliminar', 'mensaje' => 'Registro Eliminado!<br/>Se eliminó la CATEGORÍA correctamente!'];
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
            $stmt = $co->query("SELECT cat_nombre, cat_descripcion, cat_estado FROM tbl_categoria");
            return ['resultado' => 'consultar', 'mensaje' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }

    public function Existe($categoriaNombre, $categoriaExcluir = null)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $sql = "SELECT COUNT(*) FROM tbl_categoria WHERE cat_nombre = :categoriaNombre AND cat_estado = 1";
            $params = [':categoriaNombre' => $categoriaNombre];

            if ($categoriaExcluir !== null) {
                $sql .= " AND cat_nombre != :categoriaExcluir";
                $params[':categoriaExcluir'] = $categoriaExcluir;
            }

            $stmt = $co->prepare($sql);
            $stmt->execute($params);

            if ($stmt->fetchColumn() > 0) {
                return ['resultado' => 'existe', 'mensaje' => 'La CATEGORÍA colocada YA existe!'];
            }
            return [];
        } catch (Exception $e) {
            return ['resultado' => 'error', 'mensaje' => $e->getMessage()];
        } finally {
            $co = null;
        }
    }
}
