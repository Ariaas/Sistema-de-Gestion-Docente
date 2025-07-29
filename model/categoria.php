<?php
require_once('model/dbconnection.php');

class Categoria extends Connection
{

    private $categoriaNombre;
    private $categoriaDescripcion;


    public function __construct($categoriaNombre = null, $categoriaDescripcion = null)
    {
        parent::__construct();

        $this->categoriaNombre = $categoriaNombre;
        $this->categoriaDescripcion = $categoriaDescripcion;
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


    function Registrar()
    {
        $r = array();

        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $co->prepare("SELECT * FROM tbl_categoria WHERE cat_nombre = :categoriaNombre AND cat_estado = 1");
        $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);
        $stmt->execute();
        $existeActiva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existeActiva) {
            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'ERROR! <br/> La CATEGORÍA colocada ya existe!';
            $co = null;
            return $r;
        }

        $stmt = $co->prepare("SELECT * FROM tbl_categoria WHERE cat_nombre = :categoriaNombre AND cat_estado = 0");
        $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);
        $stmt->execute();
        $existeInactiva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existeInactiva) {
            $stmtReactivar = $co->prepare("UPDATE tbl_categoria SET cat_descripcion = :categoriaDescripcion, cat_estado = 1 WHERE cat_nombre = :categoriaNombre");
            $stmtReactivar->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);
            $stmtReactivar->bindParam(':categoriaDescripcion', $this->categoriaDescripcion, PDO::PARAM_STR);
            $stmtReactivar->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró la CATEGORÍA correctamente!';
            $co = null;
            return $r;
        }

        try {
            $stmt = $co->prepare("INSERT INTO tbl_categoria (
                cat_nombre,
                cat_descripcion,
                cat_estado
            ) VALUES (
                :categoriaNombre,
                :categoriaDescripcion,
                1
            )");

            $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);
            $stmt->bindParam(':categoriaDescripcion', $this->categoriaDescripcion, PDO::PARAM_STR);

            $stmt->execute();

            $r['resultado'] = 'registrar';
            $r['mensaje'] = 'Registro Incluido!<br/>Se registró la CATEGORÍA correctamente!';
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }

        $co = null;
        return $r;
    }


    function Modificar($categoriaOriginal)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if (!$this->Existe($this->categoriaNombre, $categoriaOriginal)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_categoria
                SET cat_nombre = :categoriaNombre, cat_descripcion = :categoriaDescripcion
                WHERE cat_nombre = :categoriaOriginal");

                $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);
                $stmt->bindParam(':categoriaDescripcion', $this->categoriaDescripcion, PDO::PARAM_STR);
                $stmt->bindParam(':categoriaOriginal', $categoriaOriginal, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'modificar';
                $r['mensaje'] = 'Registro Modificado!<br/>Se modificó la CATEGORÍA correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'modificar';
            $r['mensaje'] = 'ERROR! <br/> La CATEGORÍA colocada YA existe!';
        }
        return $r;
    }


    function Eliminar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        if ($this->Existe($this->categoriaNombre, NULL)) {
            try {
                $stmt = $co->prepare("UPDATE tbl_categoria
                SET cat_estado = 0
                WHERE cat_nombre = :categoriaNombre");

                $stmt->bindParam(':categoriaNombre', $this->categoriaNombre, PDO::PARAM_STR);

                $stmt->execute();

                $r['resultado'] = 'eliminar';
                $r['mensaje'] = 'Registro Eliminado!<br/>Se eliminó la CATEGORÍA correctamente!';
            } catch (Exception $e) {
                $r['resultado'] = 'error';
                $r['mensaje'] = $e->getMessage();
            }
        } else {
            $r['resultado'] = 'eliminar';
            $r['mensaje'] = 'ERROR! <br/> La Categoría no existe!';
        }
        return $r;
    }


    public function Listar()
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $stmt = $co->query("SELECT cat_nombre, cat_descripcion FROM tbl_categoria WHERE cat_estado = 1");
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

    public function Existe($categoriaNombre, $categoriaExcluir = NULL)
    {
        $co = $this->Con();
        $co->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $r = array();
        try {
            $sql = "SELECT * FROM tbl_categoria WHERE cat_nombre = :categoriaNombre AND cat_estado = 1";
            if ($categoriaExcluir !== NULL) {
                $sql .= " AND cat_nombre != :categoriaExcluir";
            }
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':categoriaNombre', $categoriaNombre, PDO::PARAM_STR);
            if ($categoriaExcluir !== NULL) {
                $stmt->bindParam(':categoriaExcluir', $categoriaExcluir, PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetchAll(PDO::FETCH_BOTH);
            if ($fila) {
                $r['resultado'] = 'existe';
                $r['mensaje'] = 'El CATEGORÍA colocado YA existe!';
            }
        } catch (Exception $e) {
            $r['resultado'] = 'error';
            $r['mensaje'] = $e->getMessage();
        }
        $co = null;
        return $r;
    }
}
